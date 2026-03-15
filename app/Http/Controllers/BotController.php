<?php

namespace App\Http\Controllers;

use App\Events\MensajeBot;
use App\Models\Configuracion;
use App\Models\Conversation;
use App\Models\Tenant;
use App\Models\TenantInstance;
use App\Services\ExternalDbService;
use App\Services\MediaPipelineService;
use App\Services\PromptTagResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        // Prioridad: BD (cifrado) → .env → vacío
        $this->apiUrl = rtrim(
            Configuracion::get('evolution_url', config('services.evolution.url', '')),
            '/'
        );
        $this->apiKey = Configuracion::get('evolution_key', config('services.evolution.key', ''));
    }

    /**
     * Determina si el usuario puede pausar/reanudar bot o instancias.
     */
    private function canPauseInstances(): bool
    {
        $user = auth()->user();

        $isSuperAdminImpersonating = $user
            && !$user->tenant_id
            && !empty(session('tenancy_impersonate_id'));

        return $user
            && ($user->can('instancias.pausar')
                || $user->hasRole('anfitrion')
                || $user->hasRole('super_admin')
                || $isSuperAdminImpersonating);
    }

    /**
     * Tenant objetivo para operaciones de instancias en contexto tenant/impersonación.
     */
    private function currentTenantId(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if (!empty($user->tenant_id)) {
            return (string) $user->tenant_id;
        }

        if (!empty(session('tenancy_impersonate_id'))) {
            return (string) session('tenancy_impersonate_id');
        }

        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // VISTAS / ADMIN
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Lista todas las instancias conectadas.
     */
    public function index()
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/fetchInstances");

            $instancias = $response->successful() ? collect($response->json()) : collect();
        } catch (\Exception) {
            $instancias = collect();
        }

        $botActivo = Configuracion::get('bot_activo', '0') === '1';

        // Instancias registradas en BD (ligadas al tenant actual)
        $user = auth()->user();
        $dbInstances = TenantInstance::where('tenant_id', $user->tenant_id)
            ->get()
            ->keyBy('instance_name');

        // Límite de instancias del plan
        $limitAlcanzado = false;
        $maxInstancias   = null;
        $totalInstancias = $dbInstances->count();
        if ($user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant && $tenant->max_instances) {
                $maxInstancias   = $tenant->max_instances;
                $limitAlcanzado  = $totalInstancias >= $maxInstancias;
            }
        }

        return view('bot.index', compact('instancias', 'botActivo', 'dbInstances', 'limitAlcanzado', 'maxInstancias', 'totalInstancias'));
    }

    /**
     * Activa o desactiva el bot.
     */
    public function toggleBot()
    {
        abort_unless($this->canPauseInstances(), 403, 'No tienes permiso para pausar o reanudar el bot.');

        $actual = Configuracion::get('bot_activo', '0') === '1';
        $nuevo  = $actual ? '0' : '1';

        Configuracion::set('bot_activo', $nuevo, 'bot', 'Estado del bot (1=activo, 0=inactivo)');

        $msg = $nuevo === '1' ? 'Bot activado correctamente.' : 'Bot desactivado correctamente.';

        return redirect()->route('bot.index')->with('success', $msg);
    }

    /**
     * Página para conectar un número nuevo.
     */
    public function conectar()
    {
        return view('bot.conectar');
    }

    /**
     * Muestra los logs recientes del bot en la vista de conversaciones.
     */
    public function conversaciones()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        if (file_exists($logPath) && filesize($logPath) > 0) {
            $lines = file($logPath);
            // Filtra solo logs del bot (que contienen [Bot])
            $botLogs = array_filter($lines, function($line) {
                return str_contains($line, '[Bot]');
            });
            $logs = array_slice(array_reverse(array_values($botLogs)), 0, 200);
        }

        $botActivo = Configuracion::get('bot_activo', '0') === '1';

        return view('bot.conversaciones', compact('logs', 'botActivo'));
    }

    /**
     * Diagnóstico: prueba la conexión con Evolution API (solo en local/debug).
     */
    public function diagnostico()
    {
        abort_unless(config('app.debug'), 403);

        $probe = fn(string $method, string $url, array $body = []) => rescue(function () use ($method, $url, $body) {
            $req = Http::withHeaders(['apikey' => $this->apiKey])->timeout(6);
            $res = $method === 'get' ? $req->get($url, $body) : $req->{$method}($url, $body);
            return ['status' => $res->status(), 'body' => $res->json() ?? substr($res->body(), 0, 300)];
        }, fn ($e) => ['status' => 'exception', 'error' => $e->getMessage()]);

        $fake = 'probe_diag_test';
        $fakePhone = '5500000000000';

        return response()->json([
            'api_url'    => $this->apiUrl,
            'version'    => $probe('get', "{$this->apiUrl}/"),
            'instances'  => $probe('get', "{$this->apiUrl}/instance/fetchInstances"),
            'pairing_probes' => [
                'POST /instance/pairingCode/{name}'   => $probe('post', "{$this->apiUrl}/instance/pairingCode/{$fake}", ['phoneNumber' => $fakePhone]),
                'PUT  /instance/pairingCode/{name}'   => $probe('put',  "{$this->apiUrl}/instance/pairingCode/{$fake}", ['phoneNumber' => $fakePhone]),
                'GET  /instance/pairingCode/{name}'   => $probe('get',  "{$this->apiUrl}/instance/pairingCode/{$fake}", ['phoneNumber' => $fakePhone]),
                'POST /instance/pairing-code/{name}'  => $probe('post', "{$this->apiUrl}/instance/pairing-code/{$fake}", ['phoneNumber' => $fakePhone]),
                'GET  /instance/connect/{name}?number'=> $probe('get',  "{$this->apiUrl}/instance/connect/{$fake}", ['number' => $fakePhone]),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // WEBHOOK PRINCIPAL
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Recibe eventos de Evolution API para una instancia específica.
     * URL: POST /webhook/whatsapp/{instancia}
     */
    public function recibirWebhook(Request $request, string $instancia)
    {
        $event = $request->input('event');
        $data  = $request->input('data', []);

        Log::channel('bot')->info("[Bot] Webhook recibido — instancia={$instancia} evento={$event}", [
            'fromMe'    => data_get($data, 'key.fromMe'),
            'remoteJid' => data_get($data, 'key.remoteJid'),
            'tipo'      => array_keys(data_get($data, 'message', []) ?: []),
        ]);

        // Solo procesamos mensajes nuevos
        // Evolution API puede enviar el evento como 'MESSAGES_UPSERT' o 'messages.upsert'
        $eventNorm = strtolower(str_replace(['_', '.'], '', $event ?? ''));
        if ($eventNorm !== 'messagesupsert') {
            return response()->json(['status' => 'ignored']);
        }

        // El bot debe estar activo (interruptor global)
        if (Configuracion::get('bot_activo', '0') !== '1') {
            return response()->json(['status' => 'bot_off']);
        }

        // Esta instancia específica debe estar activa
        $instObj = TenantInstance::where('instance_name', $instancia)->first();
        if ($instObj && !$instObj->activo) {
            return response()->json(['status' => 'instance_off']);
        }

        // Ignorar mensajes propios
        if (data_get($data, 'key.fromMe', false)) {
            return response()->json(['status' => 'own_message']);
        }

        $remoteJid = data_get($data, 'key.remoteJid', '');

        // Ignorar grupos
        if (str_contains($remoteJid, '@g.us')) {
            return response()->json(['status' => 'group_ignored']);
        }

        // Extraer texto del mensaje
        $texto   = data_get($data, 'message.conversation')
            ?? data_get($data, 'message.extendedTextMessage.text')
            ?? '';
        $caption = data_get($data, 'message.imageMessage.caption', ''); // caption de imagen (compat)

        // Contexto enriquecido cuando el usuario comparte una tarjeta de anuncio/publicación
        // (ej. Facebook/Instagram con preview en WhatsApp)
        $contextoAnuncio = $this->extraerContextoAnuncio($data);

        // Texto que se guardará en BD
        $mensajeParaGuardar = $texto;

        $messageKeys = array_keys(data_get($data, 'message', []) ?: []);

        // ── Detección de tipo de media ────────────────────────────────────────
        $mediaTipo   = null;
        $mediaMsgKey = null;
        if (in_array('imageMessage', $messageKeys)) {
            $mediaTipo = 'image';    $mediaMsgKey = 'imageMessage';
        } elseif (in_array('videoMessage', $messageKeys)) {
            $mediaTipo = 'video';    $mediaMsgKey = 'videoMessage';
        } elseif (in_array('documentMessage', $messageKeys)) {
            $mediaTipo = 'documento'; $mediaMsgKey = 'documentMessage';
        } elseif (in_array('audioMessage', $messageKeys) || in_array('pttMessage', $messageKeys)) {
            $mediaTipo = 'audio';
            $mediaMsgKey = in_array('audioMessage', $messageKeys) ? 'audioMessage' : 'pttMessage';
        }

        if ($mediaTipo !== null) {
            $captionMedia = data_get($data, "message.{$mediaMsgKey}.caption", '');
            if ($mediaTipo === 'image') $caption = $captionMedia; // compatibilidad

            $pipeline = app(MediaPipelineService::class);

            if ($pipeline->handler($mediaTipo)) {
                // ── Pipeline configurado ──────────────────────────────────────
                $mediaData = $this->descargarMedia($instancia, $data);

                if ($mediaData) {
                    $resultado = $pipeline->procesar([
                        'tipo'     => $mediaTipo,
                        'base64'   => $mediaData['base64'],
                        'mimeType' => $mediaData['mimeType'],
                        'caption'  => $captionMedia ?: $texto,
                        'filename' => data_get($data, "message.{$mediaMsgKey}.fileName", ''),
                    ]);

                    if ($resultado['ok']) {
                        $destino = $resultado['destino'];

                        if ($destino === 'enviar_texto') {
                            $this->enviarTexto($instancia, $remoteJid, $resultado['texto']);
                            $conv = Conversation::create([
                                'phone'        => preg_replace('/@.*/', '', $remoteJid),
                                'instancia'    => $instancia,
                                'contact_name' => null,
                                'user_message' => $resultado['texto_label'],
                                'bot_response' => $resultado['texto'],
                                'status'       => 'ok',
                            ]);
                            broadcast(new MensajeBot($conv));
                            return response()->json(['status' => 'ok_pipeline_texto']);
                        }

                        if (!empty($resultado['media_url'])) {
                            $this->enviarMedia($instancia, $remoteJid, 'image', $resultado['media_url'], $resultado['texto']);
                            if ($destino === 'enviar_media') {
                                $conv = Conversation::create([
                                    'phone'        => preg_replace('/@.*/', '', $remoteJid),
                                    'instancia'    => $instancia,
                                    'contact_name' => null,
                                    'user_message' => $resultado['texto_label'],
                                    'bot_response' => '[Imagen generada]',
                                    'status'       => 'ok',
                                ]);
                                broadcast(new MensajeBot($conv));
                                return response()->json(['status' => 'ok_pipeline_media']);
                            }
                        }

                        // pasar_a_bot / ambos → el texto va al LLM
                        $texto              = $resultado['texto'];
                        $mensajeParaGuardar = $resultado['texto_label'];

                    } else {
                        if (!empty(trim($captionMedia))) {
                            $texto = $mensajeParaGuardar = $captionMedia;
                        } else {
                            Log::channel('bot')->warning("[Bot] Pipeline para {$mediaTipo} falló — instancia={$instancia}");
                            return response()->json(['status' => 'pipeline_failed']);
                        }
                    }
                } else {
                    if (!empty(trim($captionMedia))) {
                        $texto = $mensajeParaGuardar = $captionMedia;
                    } else {
                        Log::channel('bot')->warning("[Bot] No se pudo descargar {$mediaTipo} — instancia={$instancia}");
                        return response()->json(['status' => 'media_download_failed']);
                    }
                }

            } else {
                // ── Fallback: toggles legacy ──────────────────────────────────
                if ($mediaTipo === 'image') {
                    if (Configuracion::get('bot_vision_activo', '0') === '1') {
                        $descripcion = $this->analizarImagen($instancia, $data, $captionMedia);
                        if ($descripcion !== null) {
                            $texto              = $descripcion;
                            $mensajeParaGuardar = '🖼 ' . $descripcion;
                            Log::channel('bot')->info("[Bot] Imagen analizada — instancia={$instancia}", ['preview' => substr($descripcion, 0, 80)]);
                        } elseif (!empty(trim($captionMedia))) {
                            $texto = $mensajeParaGuardar = $captionMedia;
                        } else {
                            Log::channel('bot')->info("[Bot] Imagen recibida sin descripción disponible — instancia={$instancia}");
                            return response()->json(['status' => 'imagen_sin_vision']);
                        }
                    } elseif (!empty(trim($captionMedia))) {
                        $texto = $mensajeParaGuardar = $captionMedia;
                    }
                } elseif ($mediaTipo === 'audio') {
                    if (Configuracion::get('bot_audio_activo', '0') !== '1') {
                        Log::channel('bot')->info("[Bot] Audio recibido pero transcripción desactivada — instancia={$instancia}");
                        return response()->json(['status' => 'audio_desactivado']);
                    }
                    $transcripcion = $this->transcribirAudio($instancia, $data);
                    if ($transcripcion !== null) {
                        $texto              = $transcripcion;
                        $mensajeParaGuardar = '🎙 ' . $transcripcion;
                        Log::channel('bot')->info("[Bot] Audio transcrito — instancia={$instancia}", [
                            'chars'   => strlen($transcripcion),
                            'preview' => substr($transcripcion, 0, 80),
                        ]);
                    } else {
                        Log::channel('bot')->info("[Bot] Audio recibido sin servicio de transcripción activo — instancia={$instancia}");
                        return response()->json(['status' => 'audio_no_transcripcion']);
                    }
                }
            }
        }

        // Si viene contexto de anuncio, se añade al texto para que la IA sepa de qué publicación llegó
        if (!empty($contextoAnuncio)) {
            $texto = trim((string) $texto);
            $texto = trim($texto . "\n\n[CONTEXTO_ANUNCIO]\n" . $contextoAnuncio);

            if (!empty(trim($mensajeParaGuardar))) {
                $mensajeParaGuardar = trim($mensajeParaGuardar . "\n\n[CONTEXTO_ANUNCIO]\n" . $contextoAnuncio);
            } else {
                $mensajeParaGuardar = "[CONTEXTO_ANUNCIO]\n" . $contextoAnuncio;
            }
        }

        if (empty(trim($texto))) {
            return response()->json(['status' => 'no_text']);
        }

        // Número limpio (sin @s.whatsapp.net)
        $telefono = preg_replace('/@.*/', '', $remoteJid);

        $modoRespuesta = Configuracion::get('bot_modo_respuesta', 'ia');
        if (in_array($modoRespuesta, ['pasos', 'hibrido'], true)) {
            $pasos = $this->resolverBotPorPasos($instancia, $telefono, $texto);
            if ($pasos['handled']) {
                $textoPasos = $pasos['response'];

                $conv = Conversation::create([
                    'phone'        => $telefono,
                    'instancia'    => $instancia,
                    'contact_name' => null,
                    'user_message' => $mensajeParaGuardar,
                    'bot_response' => $textoPasos,
                    'status'       => 'ok',
                ]);
                broadcast(new MensajeBot($conv));

                $this->enviarTexto($instancia, $remoteJid, $textoPasos);

                return response()->json(['status' => 'ok_steps']);
            }

            if ($modoRespuesta === 'pasos') {
                $textoSinCoincidencia = 'No encontre una opcion para tu mensaje. Escribe menu para ver opciones.';

                $conv = Conversation::create([
                    'phone'        => $telefono,
                    'instancia'    => $instancia,
                    'contact_name' => null,
                    'user_message' => $mensajeParaGuardar,
                    'bot_response' => $textoSinCoincidencia,
                    'status'       => 'ok',
                ]);
                broadcast(new MensajeBot($conv));

                $this->enviarTexto($instancia, $remoteJid, $textoSinCoincidencia);

                return response()->json(['status' => 'ok_steps_no_match']);
            }
        }

        // Leer configuración del bot
        $proveedor = Configuracion::get('bot_ia_proveedor', 'openai');
        $prompt    = Configuracion::get('system_prompt', 'Eres un asistente útil. Responde siempre en español.');

        // Resolver etiquetas [TAG] en el system_prompt (e.g. [CATALOGO_AGENDA], [API_ZOOM])
        // Se pasa el mensaje del usuario para que el resolver pueda contextualizar los registros de catálogo
        $prompt = app(PromptTagResolverService::class)->resolve($prompt, $texto);

        // ── Historial de conversación ───────────────────────────────────────────
        // Se cargan los últimos intercambios de esta conversación para que la IA
        // tenga contexto real y no repita preguntas ya respondidas.
        $historialIA = $this->obtenerHistorialConversacion($telefono, $instancia);

        // ── Paso IA ─────────────────────────────────────────────────────────────
        // Si hay pasos configurados, inyectamos la instrucción del paso actual
        // directamente en el system prompt para guiar a la IA sin ciclos.
        $instruccionPaso = $this->determinarPasoIA($telefono, $instancia);
        if ($instruccionPaso !== '') {
            $prompt .= "\n\n--- GUÍA DE ETAPA ACTUAL ---\n"
                . $instruccionPaso . "\n\n"
                . 'REGLAS CRÍTICAS: (1) Nunca repitas información o preguntas que ya aparezcan en el historial de conversación. '
                . '(2) Si el usuario ya respondió algo, no lo vuelvas a pedir. '
                . '(3) Avanza en la conversación siguiendo la guía de etapa. '
                . '(4) Sé conciso y evita respuestas largas redundantes.';
        } elseif ($historialIA !== []) {
            // Sin pasos configurados, inyectamos reglas anti-ciclo mínimas
            $prompt .= "\n\nREGLA: Revisa el historial de conversación antes de responder. "
                . 'No repitas preguntas ni información ya mencionada en mensajes anteriores.';
        }

        // Construir contexto desde la BD externa configurada
        $contexto = $this->construirContexto($telefono, []);

        // Llamar a la IA seleccionada (con historial multi-turno)
        $respuestaTexto = $this->llamarIA($proveedor, $prompt, $contexto, $texto, $historialIA);

        if ($respuestaTexto === null) {
            Log::channel('bot')->error("[Bot] La IA no respondió — instancia={$instancia} proveedor={$proveedor}");
            return response()->json(['status' => 'ai_error']);
        }

        // ── Extraer marcadores [[MEDIA:module:id]] de la respuesta ───────────
        $marcadoresMedia = PromptTagResolverService::extraerMarcadoresMedia($respuestaTexto);
        $textoLimpio     = PromptTagResolverService::limpiarMarcadoresMedia($respuestaTexto);

        // Guardar conversación en BD y emitir evento en tiempo real
        $clienteNombre = null;  // El lookup de nombre se hace desde catálogos dinámicos
        $conv = Conversation::create([
            'phone'        => $telefono,
            'instancia'    => $instancia,
            'contact_name' => $clienteNombre,
            'user_message' => $mensajeParaGuardar,   // incluye 🎙 si fue audio
            'bot_response' => $textoLimpio,
            'status'       => 'ok',
        ]);
        broadcast(new MensajeBot($conv));

        // Enviar respuesta de texto (sin los marcadores de media)
        $this->enviarTexto($instancia, $remoteJid, $textoLimpio);

        // Enviar archivos adjuntos de catálogo (si el bot incluyó marcadores [[MEDIA]])
        if (!empty($marcadoresMedia)) {
            $this->enviarMediaDeCatalogo($instancia, $remoteJid, $marcadoresMedia);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Resuelve una respuesta por flujo de pasos y persiste estado por contacto+instancia.
     */
    private function resolverBotPorPasos(string $instancia, string $telefono, string $mensaje): array
    {
        $flowRaw = Configuracion::get('bot_flujo_pasos', '');
        if (trim((string) $flowRaw) === '') {
            return ['handled' => false, 'response' => null];
        }

        $flow = json_decode($flowRaw, true);
        if (!is_array($flow) || empty($flow['inicio']) || empty($flow['steps']) || !is_array($flow['steps'])) {
            return ['handled' => false, 'response' => null];
        }

        $steps = $flow['steps'];
        $inicio = (string) $flow['inicio'];
        if (!isset($steps[$inicio]) || !is_array($steps[$inicio])) {
            return ['handled' => false, 'response' => null];
        }

        $resolver = app(PromptTagResolverService::class);

        $fallbackGlobal = (string) ($flow['fallback'] ?? 'No entendi tu opcion. Escribe menu para ver las opciones.');
        $mensajeNorm = mb_strtolower(trim($mensaje));
        $stateKey = "bot_flow_state:{$instancia}:{$telefono}";

        if (in_array($mensajeNorm, ['menu', 'inicio', 'reset', 'reiniciar'], true)) {
            Cache::forever($stateKey, $inicio);
            $respInicio = $resolver->resolve((string) ($steps[$inicio]['mensaje'] ?? $fallbackGlobal), $mensaje);
            return ['handled' => true, 'response' => $respInicio];
        }

        $stepActualId = Cache::get($stateKey, $inicio);
        if (!isset($steps[$stepActualId]) || !is_array($steps[$stepActualId])) {
            $stepActualId = $inicio;
        }

        $stepActual = $steps[$stepActualId];
        $opciones = is_array($stepActual['opciones'] ?? null) ? $stepActual['opciones'] : [];

        foreach ($opciones as $matcherRaw => $nextStepId) {
            $alternativas = array_filter(array_map('trim', explode('|', (string) $matcherRaw)));
            foreach ($alternativas as $alt) {
                if (mb_strtolower($alt) === $mensajeNorm) {
                    $nextStepId = (string) $nextStepId;
                    if (isset($steps[$nextStepId]) && is_array($steps[$nextStepId])) {
                        Cache::forever($stateKey, $nextStepId);
                        $respuesta = $resolver->resolve((string) ($steps[$nextStepId]['mensaje'] ?? $fallbackGlobal), $mensaje);
                        return ['handled' => true, 'response' => $respuesta];
                    }
                }
            }
        }

        $fallbackStep = (string) ($stepActual['fallback'] ?? '');
        if ($fallbackStep !== '') {
            return ['handled' => true, 'response' => $fallbackStep];
        }

        return ['handled' => false, 'response' => null];
    }

    /**
     * Extrae contexto útil de anuncios/publicaciones compartidas en WhatsApp.
     * Evolution suele enviar esto dentro de contextInfo.externalAdReply.
     */
    private function extraerContextoAnuncio(array $data): string
    {
        $contextInfo =
            data_get($data, 'message.extendedTextMessage.contextInfo')
            ?? data_get($data, 'message.imageMessage.contextInfo')
            ?? data_get($data, 'message.videoMessage.contextInfo')
            ?? data_get($data, 'message.documentMessage.contextInfo')
            ?? [];

        $externalAd = is_array($contextInfo) ? ($contextInfo['externalAdReply'] ?? null) : null;
        $externalAd = is_array($externalAd) ? $externalAd : [];

        $partes = [];

        $title = trim((string) ($externalAd['title'] ?? ''));
        $body  = trim((string) ($externalAd['body'] ?? ''));
        $sourceUrl = trim((string) (
            $externalAd['sourceUrl']
            ?? $externalAd['canonicalUrl']
            ?? $externalAd['matchedText']
            ?? ''
        ));

        if ($title !== '') {
            $partes[] = 'Titulo anuncio: ' . $title;
        }
        if ($body !== '') {
            $partes[] = 'Texto anuncio: ' . $body;
        }
        if ($sourceUrl !== '') {
            $partes[] = 'URL anuncio: ' . $sourceUrl;
        }

        // A veces el contenido viene como mensaje citado dentro del contextInfo
        $quotedConversation = trim((string) data_get($contextInfo, 'quotedMessage.conversation', ''));
        $quotedExtended     = trim((string) data_get($contextInfo, 'quotedMessage.extendedTextMessage.text', ''));
        $quotedImageCaption = trim((string) data_get($contextInfo, 'quotedMessage.imageMessage.caption', ''));
        $quotedVideoCaption = trim((string) data_get($contextInfo, 'quotedMessage.videoMessage.caption', ''));

        $quoted = collect([$quotedConversation, $quotedExtended, $quotedImageCaption, $quotedVideoCaption])
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->all();

        if (!empty($quoted)) {
            $partes[] = 'Contenido compartido: ' . implode(' | ', $quoted);
        }

        // Metadata adicional útil para clasificar que viene de un anuncio/post compartido
        if (!empty(data_get($contextInfo, 'isForwarded')) || (int) data_get($contextInfo, 'forwardingScore', 0) > 0) {
            $partes[] = 'Tipo: mensaje reenviado/compartido';
        }

        return implode("\n", $partes);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INSTANCIAS — EVOLUTION API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Crea una instancia en Evolution API y devuelve el QR (JSON).
     * El webhook se configura automáticamente con la URL única de la instancia.
     */
    public function crearInstancia(Request $request)
    {
        abort_unless(auth()->user()->can('instancias.crear'), 403, 'No tienes permiso para crear instancias.');

        $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras, números, guiones y guiones bajos.',
        ]);

        $nombre = $request->nombre;
        $usarQr = $request->input('metodo', 'qr') !== 'phone';
        // Teléfono opcional para modo phone — dígitos únicamente con código de país
        $telefono = $usarQr ? null : (preg_replace('/\D/', '', (string) $request->input('telefono', '')) ?: null);

        // Verificar límite de instancias del plan
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant && $tenant->max_instances) {
                $count = TenantInstance::where('tenant_id', $user->tenant_id)->count();
                if ($count >= $tenant->max_instances) {
                    return response()->json([
                        'success' => false,
                        'message' => "Has alcanzado el límite de {$tenant->max_instances} instancia(s) para tu plan. Contacta al administrador para ampliar tu límite.",
                    ], 422);
                }
            }
        }

        try {
            $createBody = [
                'instanceName' => $nombre,
                'qrcode'       => $usarQr,
                'integration'  => 'WHATSAPP-BAILEYS',
            ];
            // En modo phone incluimos el número para que Evolution API inicie
            // el flujo de pairing code desde la creación (funciona en v2.1+)
            if (! $usarQr && $telefono) {
                $createBody['number'] = $telefono;
            }

            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->apiUrl}/instance/create", $createBody);

            // Si ya existe (409), eliminamos y reintentamos
            if ($response->status() === 409) {
                Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(10)
                    ->delete("{$this->apiUrl}/instance/delete/{$nombre}");

                $response = Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(15)
                    ->post("{$this->apiUrl}/instance/create", $createBody);
            }

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->extraerMensajeError($response),
                ], 422);
            }

            $data = $response->json();

            // ── Modo QR: extraer QR del response o pedirlo vía connect ────────
            $qr = null;
            if ($usarQr) {
                $qr = data_get($data, 'qrcode.base64');

                if (! $qr) {
                    sleep(1);
                    $qrResponse = Http::withHeaders(['apikey' => $this->apiKey])
                        ->timeout(15)
                        ->get("{$this->apiUrl}/instance/connect/{$nombre}");

                    if ($qrResponse->successful()) {
                        $qr = data_get($qrResponse->json(), 'base64')
                            ?? data_get($qrResponse->json(), 'qrcode.base64')
                            ?? data_get($qrResponse->json(), 'qrcode');
                    }
                }
            }

            // ── Modo Phone: intentar obtener pairing code ahora mismo ─────────
            $pairingCode = null;
            if (! $usarQr && $telefono) {
                // 1) Puede venir directo en la respuesta del create
                $pairingCode = data_get($data, 'pairingCode')
                    ?? data_get($data, 'hash.pairingCode')
                    ?? data_get($data, 'qrcode.pairingCode');

                // 2) Si no viene, llamar /instance/connect y esperar el código
                if (! $pairingCode) {
                    sleep(2);
                    $connectRes = Http::withHeaders(['apikey' => $this->apiKey])
                        ->timeout(15)
                        ->get("{$this->apiUrl}/instance/connect/{$nombre}");

                    if ($connectRes->successful()) {
                        $candidato = data_get($connectRes->json(), 'pairingCode')
                            ?? data_get($connectRes->json(), 'code');

                        // El pairing code es corto (≤12 chars); el string del QR es muy largo
                        if ($candidato && strlen((string) $candidato) <= 12) {
                            $pairingCode = $candidato;
                        }
                    }
                }

                // 3) Último intento: POST /instance/pairingCode (v2.1+)
                if (! $pairingCode) {
                    $pairRes = Http::withHeaders(['apikey' => $this->apiKey])
                        ->timeout(10)
                        ->post("{$this->apiUrl}/instance/pairingCode/{$nombre}", [
                            'phoneNumber' => $telefono,
                        ]);
                    if ($pairRes->successful()) {
                        $pairingCode = data_get($pairRes->json(), 'code')
                            ?? data_get($pairRes->json(), 'pairingCode');
                    }
                }
            }

            // ── Configurar webhook único por instancia ─────────────────────
            $webhookUrl = route('webhook.whatsapp', ['instancia' => $nombre]);

            try {
                Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(10)
                    ->post("{$this->apiUrl}/webhook/set/{$nombre}", [
                        'webhook' => [
                            'enabled'  => true,
                            'url'      => $webhookUrl,
                            'byEvents' => false,
                            'base64'   => false,
                            'events'   => [
                                'MESSAGES_UPSERT',
                                'CONNECTION_UPDATE',
                                'MESSAGES_UPDATE',
                            ],
                        ],
                    ]);

                Log::channel('bot')->info("[Bot] Webhook configurado para {$nombre}: {$webhookUrl}");
            } catch (\Exception $e) {
                Log::channel('bot')->warning("[Bot] No se pudo configurar el webhook para {$nombre}: " . $e->getMessage());
            }

            // Registrar la instancia en BD ligada al tenant actual
            $tenantId = auth()->user()->tenant_id;
            if ($tenantId && !TenantInstance::where('instance_name', $nombre)->exists()) {
                $esLaPrimera = !TenantInstance::where('tenant_id', $tenantId)->exists();
                TenantInstance::create([
                    'tenant_id'     => $tenantId,
                    'instance_name' => $nombre,
                    'activo'        => true,
                    'is_default'    => $esLaPrimera,
                ]);
            }

            return response()->json([
                'success'      => true,
                'qr'           => $qr,
                'instancia'    => $nombre,
                'webhook_url'  => $webhookUrl,
                'pairingCode'  => $pairingCode,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar con Evolution API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve el estado de conexión de una instancia (para polling).
     */
    public function estadoConexion(string $instancia)
    {
        $enc = rawurlencode($instancia);
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/connectionState/{$enc}");

            if ($response->successful()) {
                $data  = $response->json();
                $state = data_get($data, 'instance.state')
                    ?? data_get($data, 'instance.connectionStatus')
                    ?? data_get($data, 'connectionStatus')
                    ?? data_get($data, 'state')
                    ?? 'unknown';

                return response()->json([
                    'success'   => true,
                    'state'     => $state,
                    'conectado' => strtolower($state) === 'open',
                ]);
            }

            return response()->json(['success' => false, 'state' => 'unknown', 'conectado' => false]);
        } catch (\Exception) {
            return response()->json(['success' => false, 'state' => 'error', 'conectado' => false]);
        }
    }

    /**
     * Refresca el QR de una instancia existente (JSON).
     */
    public function refrescarQr(string $instancia)
    {
        $enc = rawurlencode($instancia);
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->get("{$this->apiUrl}/instance/connect/{$enc}");

            if ($response->successful()) {
                $qr = data_get($response->json(), 'base64')
                    ?? data_get($response->json(), 'qrcode.base64');

                return response()->json(['success' => true, 'qr' => $qr]);
            }

            return response()->json(['success' => false, 'message' => 'No se pudo refrescar el QR.'], 422);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'Error de conexión con Evolution API.'], 500);
        }
    }

    /**
     * Obtiene la configuración actual de webhook y settings de una instancia (JSON).
     */
    public function getConfig(string $instancia)
    {
        $enc = rawurlencode($instancia);
        try {
            $webhookRes  = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/webhook/find/{$enc}");

            $settingsRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/settings/find/{$enc}");

            // Siempre devolvemos la URL predefinida (no la que tenga Evolution guardada)
            $webhookPredefinido = route('webhook.whatsapp', ['instancia' => $instancia]);

            $webhookData = $webhookRes->successful() ? $webhookRes->json() : [];
            $webhookData['url'] = $webhookPredefinido; // sobrescribir con la URL correcta

            return response()->json([
                'success'      => true,
                'webhook'      => $webhookData,
                'settings'     => $settingsRes->successful() ? $settingsRes->json() : null,
                'webhook_url'  => $webhookPredefinido,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Guarda la configuración de webhook y settings de una instancia (JSON).
     * El webhook URL siempre se fuerza al predefinido de la instancia.
     */
    public function setConfig(Request $request, string $instancia)
    {
        $request->validate([
            'events'            => ['nullable', 'array'],
            'events.*'          => ['string'],
            'reject_call'       => ['nullable'],
            'groups_ignore'     => ['nullable'],
            'always_online'     => ['nullable'],
            'read_messages'     => ['nullable'],
            'read_status'       => ['nullable'],
            'sync_full_history' => ['nullable'],
        ]);

        $enc    = rawurlencode($instancia);
        $errors = [];

        // ── Webhook: siempre usamos la URL predefinida de la instancia ───────
        $webhookUrl = route('webhook.whatsapp', ['instancia' => $instancia]);
        try {
            // Evolution API v2 requiere el payload dentro de la clave "webhook"
            $wRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->post("{$this->apiUrl}/webhook/set/{$enc}", [
                    'webhook' => [
                        'enabled'  => true,
                        'url'      => $webhookUrl,
                        'byEvents' => false,
                        'base64'   => false,
                        'events'   => $request->input('events', []),
                    ],
                ]);

            if (! $wRes->successful()) {
                $errors[] = 'Webhook: ' . $this->extraerMensajeError($wRes);
            }
        } catch (\Exception $e) {
            $errors[] = 'Webhook: ' . $e->getMessage();
        }

        // ── Settings — Evolution API v2 usa camelCase ────────────────────────
        try {
            $rejectCall      = $request->boolean('reject_call');
            $settingsPayload = [
                'rejectCall'      => $rejectCall,
                'msgCall'         => $rejectCall ? ($request->input('msg_call', '') ?: 'Llamadas no disponibles.') : '',
                'groupsIgnore'    => $request->boolean('groups_ignore'),
                'alwaysOnline'    => $request->boolean('always_online'),
                'readMessages'    => $request->boolean('read_messages'),
                'readStatus'      => $request->boolean('read_status'),
                'syncFullHistory' => $request->boolean('sync_full_history'),
            ];

            $sRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->post("{$this->apiUrl}/settings/set/{$enc}", $settingsPayload);

            if (! $sRes->successful()) {
                $errors[] = 'Settings: ' . $this->extraerMensajeError($sRes);
            }
        } catch (\Exception $e) {
            $errors[] = 'Settings: ' . $e->getMessage();
        }

        if ($errors) {
            return response()->json(['success' => false, 'message' => implode(' | ', $errors)], 422);
        }

        return response()->json(['success' => true, 'webhook_url' => $webhookUrl]);
    }

    /**
     * Elimina una instancia de Evolution API.
     */
    public function eliminarInstancia(string $instancia)
    {
        abort_unless(auth()->user()->can('instancias.eliminar'), 403, 'No tienes permiso para eliminar instancias.');

        $enc = rawurlencode($instancia);
        try {
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->delete("{$this->apiUrl}/instance/delete/{$enc}");
        } catch (\Exception) {
            return redirect()->route('bot.index')
                ->with('error', 'Error al conectar con Evolution API.');
        }

        // Eliminar también el registro en BD
        TenantInstance::where('instance_name', $instancia)->delete();

        return redirect()->route('bot.index')
            ->with('success', "Instancia «{$instancia}» eliminada correctamente.");
    }

    /**
     * Registra una instancia de Evolution API que ya existe pero no está ligada al tenant.
     */
    public function registrarInstancia(Request $request): JsonResponse
    {
        $request->validate(['instancia' => ['required', 'string', 'max:100']]);

        $instancia = $request->instancia;
        $tenantId  = $this->currentTenantId();

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Sin tenant asignado.'], 422);
        }

        if (TenantInstance::where('instance_name', $instancia)->exists()) {
            return response()->json(['success' => false, 'message' => 'Esta instancia ya está registrada.'], 422);
        }

        $esLaPrimera = !TenantInstance::where('tenant_id', $tenantId)->exists();
        TenantInstance::create([
            'tenant_id'     => $tenantId,
            'instance_name' => $instancia,
            'activo'        => true,
            'is_default'    => $esLaPrimera,
        ]);

        return response()->json(['success' => true, 'is_default' => $esLaPrimera]);
    }

    /**
     * Activa o desactiva una instancia individual (sin afectar el interruptor global del bot).
     */
    public function toggleInstance(Request $request): JsonResponse
    {
        abort_unless($this->canPauseInstances(), 403, 'No tienes permiso para pausar instancias.');

        $request->validate(['instancia' => ['required', 'string']]);

        $tenantId = $this->currentTenantId();
        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'No hay tenant activo para esta operación.'], 422);
        }

        $inst = TenantInstance::where('instance_name', $request->instancia)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $inst->update(['activo' => !$inst->activo]);

        return response()->json(['success' => true, 'activo' => $inst->activo]);
    }

    /**
     * Marca una instancia como predeterminada (y desmarca las demás del tenant).
     */
    public function setDefault(Request $request): JsonResponse
    {
        $request->validate(['instancia' => ['required', 'string']]);

        $tenantId = $this->currentTenantId();
        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'No hay tenant activo para esta operación.'], 422);
        }

        TenantInstance::where('tenant_id', $tenantId)->update(['is_default' => false]);
        TenantInstance::where('instance_name', $request->instancia)
            ->where('tenant_id', $tenantId)
            ->update(['is_default' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Devuelve logs recientes filtrados para esta instancia + estado de conexión actual.
     */
    public function getLogs(string $instancia): JsonResponse
    {
        $entries = [];
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            $lines = $this->tailLines($logFile, 1500);

            $currentEntry = null;
            foreach ($lines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.*)$/', $line, $m)) {
                    if ($currentEntry !== null) {
                        $entries[] = $currentEntry;
                    }
                    $currentEntry = [
                        'timestamp' => $m[1],
                        'level'     => strtolower($m[2]),
                        'message'   => $m[3],
                    ];
                } elseif ($currentEntry !== null && trim($line) !== '') {
                    $currentEntry['message'] .= "\n" . $line;
                }
            }
            if ($currentEntry !== null) {
                $entries[] = $currentEntry;
            }

            // Filtrar: solo entradas que mencionen la instancia o sean errores del bot
            $entries = array_values(array_filter($entries, function ($e) use ($instancia) {
                $msg = $e['message'];
                return str_contains($msg, $instancia)
                    || str_contains($msg, '[Bot]')
                    || str_contains($msg, '[BotController]')
                    || str_contains($msg, '[ExternalDb]')
                    || in_array($e['level'], ['error', 'critical', 'emergency', 'alert']);
            }));

            // Últimas 150 entradas relevantes, más recientes primero
            $entries = array_reverse(array_slice($entries, -150));
        }

        // Estado actual de la instancia en Evolution API
        $estado   = null;
        $estadoOk = false;
        try {
            $enc = rawurlencode($instancia);
            $res = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(5)
                ->get("{$this->apiUrl}/instance/connectionState/{$enc}");
            if ($res->successful()) {
                $estadoOk = true;
                $estado   = $res->json();
            } else {
                $estado = ['error' => 'HTTP ' . $res->status() . ': ' . $res->body()];
            }
        } catch (\Exception $e) {
            $estado = ['error' => $e->getMessage()];
        }

        return response()->json([
            'success'      => true,
            'logs'         => $entries,
            'estado'       => $estado,
            'estado_ok'    => $estadoOk,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'instancia'    => $instancia,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS — LÓGICA DEL BOT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Lee los últimos bytes del archivo de log y devuelve las líneas resultantes.
     */
    private function tailLines(string $file, int $maxLines = 1500): array
    {
        $handle = @fopen($file, 'r');
        if (!$handle) return [];

        $size     = filesize($file);
        if ($size === 0) {
            fclose($handle);
            return [];
        }
        $readSize = min(512 * 1024, $size); // Últimos 512 KB
        fseek($handle, max(0, $size - $readSize));
        $content = fread($handle, $readSize);
        fclose($handle);

        $lines = explode("\n", $content);
        return array_slice(
            array_filter($lines, fn($l) => trim($l) !== ''),
            -$maxLines
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS — DESCARGA DE MEDIA
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Descarga cualquier media de Evolution API y devuelve base64 + mimeType.
     * Retorna null si la descarga falla.
     */
    private function descargarMedia(string $instancia, array $data): ?array
    {
        $enc = rawurlencode($instancia);
        try {
            $res = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(30)
                ->post("{$this->apiUrl}/chat/getBase64FromMediaMessage/{$enc}", [
                    'message' => [
                        'key'     => data_get($data, 'key'),
                        'message' => data_get($data, 'message'),
                    ],
                ]);

            if (!$res->successful()) {
                Log::channel('bot')->warning("[Bot] descargarMedia HTTP {$res->status()} — instancia={$instancia}");
                return null;
            }

            $base64   = data_get($res->json(), 'base64');
            $mimeType = data_get($res->json(), 'mediaType') ?? 'application/octet-stream';

            return $base64 ? compact('base64', 'mimeType') : null;
        } catch (\Exception $e) {
            Log::channel('bot')->warning("[Bot] descargarMedia error — instancia={$instancia}: " . $e->getMessage());
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ANÁLISIS DE IMÁGENES (VISION) — legacy
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Descarga la imagen de Evolution API y la analiza con OpenAI Vision o Gemini.
     * Retorna la descripción/análisis de la imagen, o null si falla.
     *
     * Evolution API: POST /chat/getBase64FromMediaMessage/{instance}
     * Body: { "message": { "key": {...}, "message": {...} } }
     */
    private function analizarImagen(string $instancia, array $data, string $caption = ''): ?string
    {
        $proveedor = Configuracion::get('bot_vision_proveedor', 'openai');

        // Descargar imagen desde Evolution API → base64
        $enc = rawurlencode($instancia);
        try {
            $res = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(30)
                ->post("{$this->apiUrl}/chat/getBase64FromMediaMessage/{$enc}", [
                    'message' => [
                        'key'     => data_get($data, 'key'),
                        'message' => data_get($data, 'message'),
                    ],
                ]);

            if (!$res->successful()) {
                Log::channel('bot')->warning("[Bot] No se pudo descargar imagen — instancia={$instancia}: HTTP {$res->status()}");
                return null;
            }

            $base64   = data_get($res->json(), 'base64');
            $mimeType = data_get($res->json(), 'mediaType')
                ?? data_get($data, 'message.imageMessage.mimetype')
                ?? 'image/jpeg';

            if (!$base64) {
                Log::channel('bot')->warning("[Bot] Respuesta de descarga de imagen sin base64 — instancia={$instancia}");
                return null;
            }
        } catch (\Exception $e) {
            Log::channel('bot')->warning("[Bot] Error al descargar imagen — instancia={$instancia}: " . $e->getMessage());
            return null;
        }

        $promptVision = 'Describe detalladamente el contenido de esta imagen en español. '
            . 'Si contiene texto, transcríbelo. Si es una captura de pantalla, documento o formulario, extrae la información relevante.';

        if (!empty(trim($caption))) {
            $promptVision .= ' El usuario también escribió: "' . $caption . '"';
        }

        if ($proveedor === 'gemini') {
            return $this->analizarImagenGemini($base64, $mimeType, $promptVision);
        }

        return $this->analizarImagenOpenAI($base64, $mimeType, $promptVision);
    }

    /**
     * Analiza una imagen con OpenAI GPT-4o Vision.
     * Formato: message.content = [ { type: image_url, ... }, { type: text, ... } ]
     */
    private function analizarImagenOpenAI(string $base64, string $mimeType, string $prompt): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        $model  = Configuracion::get('openai_model', 'gpt-4o');
        if (!$apiKey) return null;

        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [[
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'      => 'image_url',
                            'image_url' => ['url' => "data:{$mimeType};base64,{$base64}", 'detail' => 'auto'],
                        ],
                        ['type' => 'text', 'text' => $prompt],
                    ],
                ]],
                'max_tokens' => 1000,
            ]);

            return data_get($res->json(), 'choices.0.message.content');
        } catch (\Exception $e) {
            Log::channel('bot')->warning("[Bot] Error vision OpenAI: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Analiza una imagen con Gemini (multimodal inline_data).
     */
    private function analizarImagenGemini(string $base64, string $mimeType, string $prompt): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');
        if (!$apiKey) return null;

        try {
            $res = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'parts' => [
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                            ['text' => $prompt],
                        ],
                    ]],
                ]);

            return data_get($res->json(), 'candidates.0.content.parts.0.text');
        } catch (\Exception $e) {
            Log::channel('bot')->warning("[Bot] Error vision Gemini: " . $e->getMessage());
            return null;
        }
    }

    // TRANSCRIPCIÓN DE AUDIO
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Intenta transcribir un mensaje de audio/PTT usando Whisper (OpenAI) o Gemini nativo.
     * Descarga el audio de Evolution API en base64 y lo pasa al servicio configurado.
     *
     * Prioridad: Whisper (si activo) → Gemini nativo (si activo)
     */
    private function transcribirAudio(string $instancia, array $data): ?string
    {
        $whisperActivo     = Configuracion::get('openai_whisper_activo', '0') === '1';
        $geminiAudioActivo = Configuracion::get('gemini_audio_activo',   '0') === '1';

        $usarWhisper = $whisperActivo && Configuracion::get('openai_key');
        $usarGemini  = $geminiAudioActivo && Configuracion::get('gemini_key');

        if (!$usarWhisper && !$usarGemini) {
            return null;
        }

        // Descargar audio desde Evolution API → base64
        $enc = rawurlencode($instancia);
        try {
            $res = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(25)
                ->post("{$this->apiUrl}/chat/getBase64FromMediaMessage/{$enc}", [
                    'message' => [
                        'key'     => data_get($data, 'key'),
                        'message' => data_get($data, 'message'),
                    ],
                    'convertToMp4' => false,
                ]);

            if (!$res->successful()) {
                Log::channel('bot')->warning("[Bot] No se pudo descargar audio — instancia={$instancia}: HTTP {$res->status()}");
                return null;
            }

            $base64   = data_get($res->json(), 'base64');
            $mimeType = data_get($res->json(), 'mediaType')
                ?? data_get($data, 'message.audioMessage.mimetype')
                ?? data_get($data, 'message.pttMessage.mimetype')
                ?? 'audio/ogg';

            if (!$base64) {
                Log::channel('bot')->warning("[Bot] Respuesta de descarga sin base64 — instancia={$instancia}");
                return null;
            }
        } catch (\Exception $e) {
            Log::channel('bot')->warning("[Bot] Error al descargar audio — instancia={$instancia}: " . $e->getMessage());
            return null;
        }

        // Transcribir: Whisper primero, Gemini como fallback
        if ($usarWhisper) {
            $resultado = $this->transcribirConWhisper($base64, $mimeType);
            if ($resultado !== null) return $resultado;
        }

        if ($usarGemini) {
            return $this->transcribirConGeminiNativo($base64, $mimeType);
        }

        return null;
    }

    /**
     * Transcribe audio usando la API de Whisper de OpenAI.
     * Soporta OGG, MP3, MP4, WAV, WEBM (formatos aceptados por Whisper).
     */
    private function transcribirConWhisper(string $base64Audio, string $mimeType): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        if (!$apiKey) return null;

        $audioContent = base64_decode($base64Audio);
        if (!$audioContent) return null;

        // Extensión basada en el mimetype
        $ext = match(true) {
            str_contains($mimeType, 'ogg')  => 'ogg',
            str_contains($mimeType, 'mpeg') => 'mp3',
            str_contains($mimeType, 'mp4')  => 'mp4',
            str_contains($mimeType, 'wav')  => 'wav',
            str_contains($mimeType, 'webm') => 'webm',
            str_contains($mimeType, 'm4a')  => 'm4a',
            default                          => 'ogg',
        };

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->attach('file', $audioContent, "audio.{$ext}")
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model'           => 'whisper-1',
                    'response_format' => 'text',
                    'language'        => 'es',
                ]);

            if (!$response->successful()) {
                Log::channel('bot')->error('[Bot] Whisper error HTTP ' . $response->status(), [
                    'body' => $response->json() ?? $response->body(),
                ]);
                return null;
            }

            $transcripcion = trim($response->body());
            Log::channel('bot')->info('[Bot] Whisper OK: ' . substr($transcripcion, 0, 100));
            return $transcripcion ?: null;
        } catch (\Exception $e) {
            Log::channel('bot')->error('[Bot] Whisper excepción: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Transcribe audio usando la capacidad multimodal nativa de Gemini (Flash/Pro 1.5+).
     * Envía el audio como inline_data directamente en la petición.
     */
    private function transcribirConGeminiNativo(string $base64Audio, string $mimeType): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');

        if (!$apiKey) return null;

        try {
            $response = Http::timeout(60)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    [
                        'contents' => [[
                            'parts' => [
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data'      => $base64Audio,
                                    ],
                                ],
                                [
                                    'text' => 'Transcribe exactamente lo que dice este audio. Devuelve únicamente la transcripción, sin comentarios ni explicaciones adicionales.',
                                ],
                            ],
                        ]],
                    ]
                );

            if (!$response->successful()) {
                Log::channel('bot')->error('[Bot] Gemini audio error HTTP ' . $response->status(), [
                    'body' => $response->json() ?? $response->body(),
                ]);
                return null;
            }

            $transcripcion = trim(data_get($response->json(), 'candidates.0.content.parts.0.text') ?? '');
            Log::channel('bot')->info('[Bot] Gemini audio OK: ' . substr($transcripcion, 0, 100));
            return $transcripcion ?: null;
        } catch (\Exception $e) {
            Log::channel('bot')->error('[Bot] Gemini audio excepción: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Devuelve los últimos $limit intercambios de una conversación como array
     * de mensajes en formato multi-turno [role => user|assistant, content => string].
     * Se usan en los proveedores de IA para dar contexto real y evitar ciclos.
     */
    private function obtenerHistorialConversacion(string $telefono, string $instancia, int $limit = 10): array
    {
        $convs = Conversation::where('phone', $telefono)
            ->where('instancia', $instancia)
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        $messages = [];
        foreach ($convs as $conv) {
            $userMsg  = trim((string) $conv->user_message);
            $botMsg   = trim((string) $conv->bot_response);
            if ($userMsg !== '') $messages[] = ['role' => 'user',      'content' => $userMsg];
            if ($botMsg  !== '') $messages[] = ['role' => 'assistant', 'content' => $botMsg];
        }

        return $messages;
    }

    /**
     * Devuelve la instrucción de etapa que corresponde al número de mensaje actual
     * según la config «bot_pasos_ia» (JSON: array de {desde, hasta, instruccion}).
     * Retorna cadena vacía si no hay pasos configurados o no hay coincidencia.
     */
    private function determinarPasoIA(string $telefono, string $instancia): string
    {
        $pasosRaw = Configuracion::get('bot_pasos_ia', '');
        if (trim((string) $pasosRaw) === '') return '';

        $pasos = json_decode($pasosRaw, true);
        if (!is_array($pasos) || empty($pasos)) return '';

        // El número del PRÓXIMO mensaje es total_de_mensajes_enviados + 1
        $turno = Conversation::where('phone', $telefono)
            ->where('instancia', $instancia)
            ->count() + 1;

        foreach ($pasos as $paso) {
            $desde = (int) ($paso['desde'] ?? 1);
            $hasta = (int) ($paso['hasta'] ?? 9999);
            if ($turno >= $desde && $turno <= $hasta) {
                return trim((string) ($paso['instruccion'] ?? ''));
            }
        }

        return '';
    }

    /**
     * Construye el contexto para la IA leyendo todas las BDs externas configuradas en ext_dbs.
     */
    private function construirContexto(string $telefono, array $recursos): string
    {
        try {
            return (new ExternalDbService())->construirContextoMultiple();
        } catch (\Exception $e) {
            Log::channel('bot')->warning('[Bot] Error al construir contexto con BD externa: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Llama al proveedor de IA seleccionado y devuelve el texto de respuesta.
     * $historial: array de ['role'=>'user'|'assistant', 'content'=>string] con mensajes previos.
     */
    private function llamarIA(string $proveedor, string $prompt, string $contexto, string $mensaje, array $historial = []): ?string
    {
        $systemContent = $prompt;

        if ($contexto) {
            $systemContent .= "\n\n--- INFORMACIÓN CONTEXTUAL ---\n" . $contexto;
        }

        try {
            if ($proveedor === 'deepseek') return $this->llamarDeepSeek($systemContent, $mensaje, $historial);
            if ($proveedor === 'gemini')   return $this->llamarGemini($systemContent, $mensaje, $historial);
            return $this->llamarOpenAI($systemContent, $mensaje, $historial);
        } catch (\Exception $e) {
            Log::channel('bot')->error("[Bot] Error en IA ({$proveedor}): " . $e->getMessage());
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PROVEEDORES DE IA
    // ──────────────────────────────────────────────────────────────────────────

    private function llamarOpenAI(string $system, string $user, array $historial = []): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        $model  = Configuracion::get('openai_model', 'gpt-4o-mini');

        if (! $apiKey) {
            Log::channel('bot')->warning('[Bot] OpenAI: API Key no configurada.');
            return null;
        }

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($historial as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => (string) $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $user];

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $model,
                'messages' => $messages,
            ]);

        if (! $response->successful()) {
            Log::channel('bot')->error('[Bot] OpenAI error HTTP ' . $response->status(), [
                'model' => $model,
                'body'  => $response->json() ?? $response->body(),
            ]);
            return null;
        }

        return data_get($response->json(), 'choices.0.message.content');
    }

    private function llamarDeepSeek(string $system, string $user, array $historial = []): ?string
    {
        $apiKey = Configuracion::get('deepseek_key');
        $model  = Configuracion::get('deepseek_model', 'deepseek-chat');

        if (! $apiKey) {
            Log::channel('bot')->warning('[Bot] DeepSeek: API Key no configurada.');
            return null;
        }

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($historial as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => (string) $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $user];

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.deepseek.com/v1/chat/completions', [
                'model'    => $model,
                'messages' => $messages,
            ]);

        if (! $response->successful()) {
            Log::channel('bot')->error('[Bot] DeepSeek error HTTP ' . $response->status(), [
                'model' => $model,
                'body'  => $response->json() ?? $response->body(),
            ]);
            return null;
        }

        return data_get($response->json(), 'choices.0.message.content');
    }

    private function llamarGemini(string $system, string $user, array $historial = []): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');

        if (! $apiKey) {
            Log::channel('bot')->warning('[Bot] Gemini: API Key no configurada.');
            return null;
        }

        // Gemini maneja historial con role: 'user'|'model'
        $contents = [];
        foreach ($historial as $msg) {
            $contents[] = [
                'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => (string) $msg['content']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $user]]];

        $response = Http::timeout(30)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'system_instruction' => [
                        'parts' => [['text' => $system]],
                    ],
                    'contents' => $contents,
                ]
            );

        return $response->successful()
            ? data_get($response->json(), 'candidates.0.content.parts.0.text')
            : null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS — EVOLUTION API (ENVÍO)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Envía un mensaje de texto por WhatsApp.
     */
    private function enviarTexto(string $instancia, string $remoteJid, string $texto): void
    {
        $enc = rawurlencode($instancia);
        try {
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->apiUrl}/message/sendText/{$enc}", [
                    'number' => $remoteJid,
                    'text'   => $texto,
                ]);
        } catch (\Exception $e) {
            Log::channel('bot')->error("[Bot] Error al enviar texto a {$remoteJid}: " . $e->getMessage());
        }
    }

    /**
     * Envía una imagen, video, documento o audio por WhatsApp.
     * Evolution API v2: POST /message/sendMedia/{instance}
     * mediatype: 'image' | 'video' | 'document' | 'audio'
     */
    private function enviarMedia(string $instancia, string $remoteJid, string $tipo, string $url, string $caption = '', string $fileName = '', bool $viewOnce = false): void
    {
        $enc     = rawurlencode($instancia);
        $payload = [
            'number'    => $remoteJid,
            'mediatype' => $tipo,
            'media'     => $url,
            'caption'   => $caption,
        ];
        if ($tipo === 'document' && !empty($fileName)) {
            $payload['fileName'] = $fileName;
        }
        if ($viewOnce) {
            $payload['viewOnce'] = true;
        }
        try {
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(20)
                ->post("{$this->apiUrl}/message/sendMedia/{$enc}", $payload);
        } catch (\Exception $e) {
            Log::channel('bot')->error("[Bot] Error al enviar {$tipo} a {$remoteJid}: " . $e->getMessage());
        }
    }

    /**
     * Resuelve marcadores [[MEDIA:module:id]] y envía los archivos de catálogo al usuario.
     */
    private function enviarMediaDeCatalogo(string $instancia, string $remoteJid, array $marcadores): void
    {
        $mediaConfig = json_decode(Configuracion::get('bot_catalog_media', '{}'), true) ?? [];

        foreach ($marcadores as $m) {
            $slug     = $m[1];
            $recordId = (int) $m[2];
            $config   = $mediaConfig[$slug] ?? null;

            if (!$config || empty($config['activo'])) {
                continue;
            }

            // Normalizar al nuevo formato de array de campos (backward compat)
            $camposMedia = $config['campos'] ?? [];
            if (empty($camposMedia) && !empty($config['campo_slug'])) {
                $camposMedia = [['campo_slug' => $config['campo_slug'], 'mediatype' => $config['mediatype'] ?? 'image']];
            }
            $camposMedia = array_values(array_filter($camposMedia, fn($c) => !empty($c['campo_slug'])));
            if (empty($camposMedia)) continue;

            try {
                $modulo = \App\Models\CatalogModule::where('slug', $slug)->first();
                if (!$modulo) continue;

                $record = \App\Models\CatalogRecord::where('module_id', $modulo->id)
                    ->where('id', $recordId)
                    ->first();
                if (!$record) continue;

                $caption = '';
                if (!empty($config['caption_campo']) && !empty($record->datos[$config['caption_campo']])) {
                    $caption = (string) $record->datos[$config['caption_campo']];
                }

                // Enviar cada campo de archivo configurado para este módulo
                foreach ($camposMedia as $campoConfig) {
                    $path = $record->datos[$campoConfig['campo_slug']] ?? null;
                    if (empty($path)) continue;

                    $url      = str_starts_with($path, 'http') ? $path : \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                    $tipo     = $campoConfig['mediatype'] ?? 'image';
                    $fileName = basename(parse_url($url, PHP_URL_PATH));
                    $viewOnce = !empty($campoConfig['view_once']);

                    $this->enviarMedia($instancia, $remoteJid, $tipo, $url, $caption, $fileName, $viewOnce);

                    Log::channel('bot')->info("[Bot] Media de catálogo enviada — módulo={$slug} record={$recordId} campo={$campoConfig['campo_slug']} tipo={$tipo} viewOnce=" . ($viewOnce ? 'true' : 'false'));
                }

            } catch (\Exception $e) {
                Log::channel('bot')->warning("[Bot] Error al enviar media de catálogo — {$slug}:{$recordId}: " . $e->getMessage());
            }
        }
    }

    /**
     * Extrae un mensaje legible de una respuesta de error de Evolution API.
     */
    private function extraerMensajeError(\Illuminate\Http\Client\Response $response): string
    {
        $body = $response->json();

        $msg = data_get($body, 'message');
        if (is_array($msg)) {
            $msg = implode('. ', $msg);
        }
        $msg = $msg
            ?? data_get($body, 'error')
            ?? data_get($body, 'response.message')
            ?? null;

        $rawBody = $response->body();
        $detail  = $rawBody !== '' ? " | Respuesta: {$rawBody}" : '';

        return ($msg ?? "HTTP {$response->status()}") . $detail;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API — CONVERSACIONES EN TIEMPO REAL
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Genera un código de emparejamiento de 8 dígitos para vincular sin QR.
     * La instancia debe existir previamente. POST /bot/pairing-code/{instancia}
     */
    public function pairingCode(Request $request, string $instancia): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);

        // Solo dígitos con código de país, ej: 5215512345678
        $phone = preg_replace('/\D/', '', $request->phone);

        $enc = rawurlencode($instancia);
        try {
            // ── Paso 1: iniciar conexión WebSocket de Baileys ─────────────────
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/connect/{$enc}");

            // ── Paso 2: esperar hasta que la instancia esté en "connecting" ───
            // El WebSocket de Baileys arranca de forma asíncrona; sin este wait
            // la llamada a pairingCode llega antes de que el socket exista.
            $estadoConectando = false;
            for ($i = 0; $i < 6; $i++) {
                sleep(1);
                $stateRes = Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(5)
                    ->get("{$this->apiUrl}/instance/connectionState/{$enc}");

                $state = data_get($stateRes->json(), 'instance.state')
                    ?? data_get($stateRes->json(), 'state')
                    ?? '';

                if ($state === 'connecting') {
                    $estadoConectando = true;
                    break;
                }

                // Si ya está open, no podemos pedir pairing code
                if ($state === 'open') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta instancia ya está conectada a WhatsApp. Desconéctala primero desde Evolution API.',
                    ], 422);
                }
            }

            // ── Paso 3: solicitar el código de emparejamiento ─────────────────
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->apiUrl}/instance/pairingCode/{$enc}", [
                    'phoneNumber' => $phone,
                ]);

            if ($response->successful()) {
                $code = data_get($response->json(), 'code')
                    ?? data_get($response->json(), 'pairingCode');
                if ($code) {
                    return response()->json(['success' => true, 'code' => $code]);
                }
            }

            // ── Fallback: connect con number param (algunas builds < v2.1) ────
            $response2 = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->get("{$this->apiUrl}/instance/connect/{$enc}", [
                    'number' => $phone,
                ]);

            if ($response2->successful()) {
                $code = data_get($response2->json(), 'pairingCode');
                if ($code) {
                    return response()->json(['success' => true, 'code' => $code]);
                }
            }

            $err = $this->extraerMensajeError($response);
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener el código. ' . ($err ?: 'Intenta conectar mediante QR.'),
            ], 422);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina todas las conversaciones de un contacto específico.
     * DELETE /bot/conversaciones/{phone}
     */
    public function eliminarConversacionesPorTelefono(string $phone): JsonResponse
    {
        $deleted = Conversation::where('phone', $phone)->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * Elimina TODAS las conversaciones del tenant actual.
     * DELETE /bot/conversaciones
     */
    public function eliminarTodasConversaciones(): JsonResponse
    {
        $deleted = Conversation::query()->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * Trunca el archivo de log de Laravel. Solo accesible por super_admin.
     * DELETE /admin/logs
     */
    /** Archivos de log permitidos por canal. */
    private static array $logCanales = [
        'bot'          => 'bot.log',
        'configuracion'=> 'configuracion.log',
        'sistema'      => 'laravel.log',
    ];

    /** GET /admin/logs/{canal} — retorna últimas líneas del log. */
    public function verLog(string $canal): JsonResponse
    {
        $archivo = self::$logCanales[$canal] ?? null;
        if (!$archivo) {
            return response()->json(['error' => 'Canal no válido'], 404);
        }

        $path    = storage_path("logs/{$archivo}");
        $entries = [];

        if (file_exists($path)) {
            $lines   = $this->tailLines($path, 2000);
            $current = null;

            foreach ($lines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.*)$/', $line, $m)) {
                    if ($current !== null) $entries[] = $current;
                    $current = ['timestamp' => $m[1], 'level' => strtolower($m[2]), 'message' => $m[3]];
                } elseif ($current !== null && trim($line) !== '') {
                    $current['message'] .= "\n" . $line;
                }
            }
            if ($current !== null) $entries[] = $current;
            $entries = array_reverse(array_slice($entries, -200));
        }

        return response()->json([
            'canal'     => $canal,
            'entries'   => $entries,
            'size'      => file_exists($path) ? round(filesize($path) / 1024, 1) : 0,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /** DELETE /admin/logs/{canal} — vacía el archivo de log del canal. */
    public function clearLogs(string $canal = 'sistema'): JsonResponse
    {
        $archivo = self::$logCanales[$canal] ?? null;
        if (!$archivo) {
            return response()->json(['error' => 'Canal no válido'], 404);
        }

        $path = storage_path("logs/{$archivo}");
        if (file_exists($path)) {
            file_put_contents($path, '');
        }

        Log::channel('configuracion')->info("[Admin] Log '{$canal}' limpiado por " . (auth()->user()?->email ?? 'sistema'));

        return response()->json(['success' => true]);
    }

    /**
     * Lista los contactos únicos con su último mensaje (para el panel izquierdo).
     */
    public function listarContactos(): JsonResponse
    {
        $contactos = Conversation::selectRaw('phone, contact_name, instancia, MAX(created_at) as ultimo, COUNT(*) as total')
            ->groupBy('phone', 'contact_name', 'instancia')
            ->orderByDesc('ultimo')
            ->get();

        return response()->json($contactos);
    }

    /**
     * Devuelve los últimos 50 mensajes de un contacto específico.
     */
    public function mensajesPorTelefono(string $phone): JsonResponse
    {
        $mensajes = Conversation::where('phone', $phone)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($mensajes);
    }
}
