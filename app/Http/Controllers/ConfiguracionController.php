<?php

namespace App\Http\Controllers;

use App\Models\CatalogField;
use App\Models\CatalogModule;
use App\Models\Configuracion;
use App\Models\SavedPrompt;
use App\Services\ExternalDbService;
use App\Services\MediaPipelineService;
use App\Services\PromptTagResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Definición de todos los campos gestionados, agrupados por servicio.
     * Solo se almacena metadata — los valores reales se leen de la BD cifrados.
     */
    private array $campos = [
        'evolution' => [
            'label'       => 'Evolution API',
            'descripcion' => 'API para gestionar instancias de WhatsApp',
            'claves'      => [
                'evolution_url' => ['label' => 'URL del servidor', 'tipo' => 'text',     'placeholder' => 'https://tu-evolution-api.com'],
                'evolution_key' => ['label' => 'API Key global',   'tipo' => 'password', 'placeholder' => 'Tu API Key de Evolution'],
            ],
        ],
        'openai' => [
            'label'       => 'ChatGPT (OpenAI)',
            'descripcion' => 'Modelos GPT para generación de respuestas',
            'claves'      => [
                'openai_key'   => ['label' => 'API Key',        'tipo' => 'password', 'placeholder' => 'sk-...'],
                'openai_model' => ['label' => 'Modelo por defecto', 'tipo' => 'text', 'placeholder' => 'gpt-4o'],
            ],
        ],
        'deepseek' => [
            'label'       => 'DeepSeek',
            'descripcion' => 'Modelos DeepSeek para inferencia de lenguaje',
            'claves'      => [
                'deepseek_key'   => ['label' => 'API Key',           'tipo' => 'password', 'placeholder' => 'sk-...'],
                'deepseek_model' => ['label' => 'Modelo por defecto', 'tipo' => 'text',    'placeholder' => 'deepseek-chat'],
            ],
        ],
        'gemini' => [
            'label'       => 'Google Gemini',
            'descripcion' => 'Modelos Gemini de Google para IA generativa',
            'claves'      => [
                'gemini_key'   => ['label' => 'API Key',           'tipo' => 'password', 'placeholder' => 'AIza...'],
                'gemini_model' => ['label' => 'Modelo por defecto', 'tipo' => 'text',    'placeholder' => 'gemini-1.5-pro'],
            ],
        ],
        'google' => [
            'label'       => 'Google (Calendar & Drive)',
            'descripcion' => 'Acceso a Google Calendar y Google Drive mediante OAuth2',
            'claves'      => [
                'google_client_id'     => ['label' => 'Client ID',     'tipo' => 'password', 'placeholder' => '12345-abc.apps.googleusercontent.com'],
                'google_client_secret' => ['label' => 'Client Secret', 'tipo' => 'password', 'placeholder' => 'GOCSPX-...'],
            ],
        ],
        'assembly' => [
            'label'       => 'AssemblyAI',
            'descripcion' => 'Transcripción de audio/voz a texto',
            'claves'      => [
                'assembly_key' => ['label' => 'API Key', 'tipo' => 'password', 'placeholder' => 'Tu API Key de AssemblyAI'],
            ],
        ],
        'zoom' => [
            'label'       => 'Zoom',
            'descripcion' => 'Integración con reuniones y webinars de Zoom',
            'claves'      => [
                'zoom_account_id'     => ['label' => 'Account ID',     'tipo' => 'password', 'placeholder' => 'Tu Account ID de Zoom'],
                'zoom_client_id'      => ['label' => 'Client ID',      'tipo' => 'password', 'placeholder' => 'Tu Client ID de Zoom'],
                'zoom_client_secret'  => ['label' => 'Client Secret',  'tipo' => 'password', 'placeholder' => 'Tu Client Secret de Zoom'],
            ],
        ],
    ];

    /**
     * Muestra la página de configuración.
     * Las API Keys solo pasan como booleano (configurada/no).
     * El system_prompt se pasa descifrado para que sea editable.
     */
    public function index()
    {
        $estado = [];
        foreach ($this->campos as $grupo => $info) {
            foreach ($info['claves'] as $clave => $meta) {
                $estado[$clave] = Configuracion::isConfigured($clave);
            }
        }

        $systemPrompt = Configuracion::get('system_prompt', '');
        $botProveedor = Configuracion::get('bot_ia_proveedor', 'openai');
        $botModoRespuesta = Configuracion::get('bot_modo_respuesta', 'ia');
        $botFlujoPasos = Configuracion::get('bot_flujo_pasos', $this->defaultBotFlujoPasos());
        $botPasosIA = Configuracion::get('bot_pasos_ia', $this->defaultBotPasosIA());

        // Modelos actuales (valor real, no solo booleano)
        $iaModelos = [
            'openai'   => Configuracion::get('openai_model',   'gpt-4o'),
            'deepseek' => Configuracion::get('deepseek_model', 'deepseek-chat'),
            'gemini'   => Configuracion::get('gemini_model',   'gemini-1.5-flash'),
        ];

        // Toggles de capacidades adicionales (audio / imagen)
        $iaToggles = [
            'openai_whisper' => Configuracion::get('openai_whisper_activo', '0') === '1',
            'openai_imagen'  => Configuracion::get('openai_imagen_activo',  '0') === '1',
            'gemini_audio'   => Configuracion::get('gemini_audio_activo',   '0') === '1',
            'gemini_vision'  => Configuracion::get('gemini_vision_activo',  '0') === '1',
        ];

        // Toggles de análisis de medios del bot (imagen/audio entrante) — legacy
        $botMedia = [
            'vision_activo'    => Configuracion::get('bot_vision_activo',   '0') === '1',
            'vision_proveedor' => Configuracion::get('bot_vision_proveedor', 'openai'),
            'audio_activo'     => Configuracion::get('bot_audio_activo',    '0') === '1',
        ];

        // Pipeline de medios (nuevo — reemplaza los toggles legacy si está configurado)
        $pipelineRaw = Configuracion::get('bot_media_pipeline', '');
        $pipeline = !empty($pipelineRaw)
            ? (json_decode($pipelineRaw, true) ?: MediaPipelineService::defaultPipeline())
            : MediaPipelineService::defaultPipeline();
        $pasosDisponibles  = MediaPipelineService::pasosDisponibles();
        $destinosDisponibles = MediaPipelineService::destinosDisponibles();

        // Load all external DB connections; strip passwords before passing to the view.
        $extDbsRaw = json_decode(Configuracion::get('ext_dbs', '[]'), true) ?? [];
        $extDbs = array_map(function ($conn) {
            $conn['has_password'] = ($conn['password'] ?? '') !== '';
            $conn['password']     = '';   // never expose real password to frontend
            return $conn;
        }, $extDbsRaw);

        // WhatsApp verification prompt — only show section if any active catalog has a phone field
        $promptVerificacion = Configuracion::get('bot_prompt_verificacion', '');
        $hasPhoneField = false;

        // Módulos con campos de archivo (para config de media adjunta del bot)
        $modulosConArchivos = collect();
        try {
            $hasPhoneField = CatalogModule::where('activo', true)
                ->whereHas('fields', fn($q) => $q->where('tipo', 'phone'))
                ->exists();

            $modulosConArchivos = CatalogModule::where('activo', true)
                ->whereHas('fields', fn($q) => $q->where('tipo', 'file'))
                ->with(['fields' => fn($q) => $q->orderBy('orden')])
                ->orderBy('orden')
                ->get();
        } catch (\Exception) {
            // tenant DB might not have catalog_modules yet
        }

        // Configuración de media adjunta de catálogos (bot_catalog_media)
        $catalogMediaConfig = json_decode(Configuracion::get('bot_catalog_media', '{}'), true) ?? [];

        $availableTags = [];
        try {
            $availableTags = app(PromptTagResolverService::class)->availableTags();
        } catch (\Exception) {}

        $botTimezone    = Configuracion::get('bot_timezone', '');
        $botCanvasLayout = json_decode(Configuracion::get('bot_canvas_layout', ''), true) ?: null;

        $savedPrompts   = collect();
        $promptActivoId = null;
        try {
            $savedPrompts   = SavedPrompt::orderBy('nombre')->get();
            $promptActivoId = (int) (Configuracion::get('bot_prompt_activo', '0')) ?: null;
        } catch (\Exception) {}

        // Estado de conexión con Google (OAuth2)
        $googleConectado = Configuracion::isConfigured('google_refresh_token');
        $googleEmail     = Configuracion::get('google_account_email', '');

        return view('configuracion.index', [
            'grupos'             => $this->campos,
            'estado'             => $estado,
            'systemPrompt'       => $systemPrompt,
            'botProveedor'       => $botProveedor,
            'botModoRespuesta'   => $botModoRespuesta,
            'botFlujoPasos'      => $botFlujoPasos,
            'botPasosIA'         => $botPasosIA,
            'extDbs'             => $extDbs,
            'promptVerificacion' => $promptVerificacion,
            'hasPhoneField'      => $hasPhoneField,
            'availableTags'      => $availableTags,
            'iaModelos'          => $iaModelos,
            'iaToggles'          => $iaToggles,
            'botMedia'              => $botMedia,
            'pipeline'              => $pipeline,
            'pasosDisponibles'      => $pasosDisponibles,
            'destinosDisponibles'   => $destinosDisponibles,
            'modulosConArchivos'    => $modulosConArchivos,
            'catalogMediaConfig'    => $catalogMediaConfig,
            'savedPrompts'          => $savedPrompts,
            'promptActivoId'        => $promptActivoId,
            'botTimezone'           => $botTimezone,
            'botCanvasLayout'       => $botCanvasLayout,
            'googleConectado'    => $googleConectado,
            'googleEmail'        => $googleEmail,
        ]);
    }

    /**
     * Guarda las claves enviadas. Solo actualiza si el campo no está vacío.
     * Un campo vacío significa "mantener el valor actual" (excepto system_prompt
     * que sí puede guardarse vacío para borrarlo).
     */
    public function update(Request $request)
    {
        $request->validate([
            'bot_modo_respuesta' => ['nullable', 'in:ia,pasos,hibrido'],
            'bot_flujo_pasos'    => ['nullable', 'string'],
            'bot_pasos_ia'       => ['nullable', 'string'],
        ]);

        $guardados = 0;

        if ($request->has('bot_pasos_ia')) {
            $pasosRaw = trim((string) $request->input('bot_pasos_ia', ''));
            if ($pasosRaw !== '') {
                $pasos = json_decode($pasosRaw, true);
                if (!is_array($pasos)) {
                    return redirect()->route('configuracion.index')
                        ->with('error', 'Los pasos IA deben ser un JSON valido (array de etapas).');
                }
                Configuracion::set('bot_pasos_ia', json_encode($pasos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 'bot', 'Etapas de guia para la IA (anti-ciclo)');
            } else {
                Configuracion::clear('bot_pasos_ia');
            }
            $guardados++;
        }

        if ($request->filled('bot_modo_respuesta')) {
            Configuracion::set('bot_modo_respuesta', $request->input('bot_modo_respuesta'), 'bot', 'Modo de respuesta del bot: ia|pasos|hibrido');
            $guardados++;
        }

        if ($request->has('bot_flujo_pasos')) {
            $flujoRaw = trim((string) $request->input('bot_flujo_pasos', ''));
            if ($flujoRaw !== '') {
                $flujo = json_decode($flujoRaw, true);
                if (!is_array($flujo) || !isset($flujo['inicio']) || !isset($flujo['steps']) || !is_array($flujo['steps'])) {
                    return redirect()->route('configuracion.index')
                        ->with('error', 'El flujo por pasos debe ser JSON valido y contener las claves "inicio" y "steps".');
                }

                Configuracion::set('bot_flujo_pasos', json_encode($flujo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 'bot', 'Flujo conversacional por pasos en formato JSON');
                $guardados++;
            } else {
                Configuracion::clear('bot_flujo_pasos');
                $guardados++;
            }
        }

        // Guardar zona horaria del bot
        if ($request->has('bot_timezone')) {
            $tz = $request->input('bot_timezone', '');
            if (!empty($tz) && in_array($tz, \DateTimeZone::listIdentifiers())) {
                Configuracion::set('bot_timezone', $tz, 'bot', 'Zona horaria del bot para [HORA_ACTUAL]');
            } else {
                Configuracion::clear('bot_timezone');
            }
            $guardados++;
        }

        // Guardar prompt de verificación WhatsApp
        if ($request->has('bot_prompt_verificacion')) {
            $pv = $request->input('bot_prompt_verificacion', '');
            if (trim($pv) !== '') {
                Configuracion::set('bot_prompt_verificacion', $pv, 'bot', 'Prompt de verificación de WhatsApp');
            } else {
                Configuracion::clear('bot_prompt_verificacion');
            }
            $guardados++;
        }

        // Guardar ID del prompt activo
        if ($request->has('bot_prompt_activo')) {
            $id = (int) $request->input('bot_prompt_activo');
            if ($id > 0) {
                Configuracion::set('bot_prompt_activo', (string) $id, 'bot', 'ID del prompt activo del bot');
            } else {
                Configuracion::clear('bot_prompt_activo');
            }
            $guardados++;
        }

        // Guardar layout del canvas N8N
        if ($request->has('bot_canvas_layout')) {
            $layout = trim((string) $request->input('bot_canvas_layout', ''));
            if ($layout !== '' && $layout !== '{}') {
                Configuracion::set('bot_canvas_layout', $layout, 'bot', 'Layout del canvas de nodos del bot');
            }
            $guardados++;
        }

        // Guardar system_prompt
        if ($request->has('system_prompt')) {
            $prompt = $request->input('system_prompt', '');
            if (trim($prompt) !== '') {
                Configuracion::set('system_prompt', $prompt, 'bot', 'Prompt del sistema para el bot');
            } else {
                Configuracion::clear('system_prompt');
            }
            $guardados++;
        }

        // Guardar proveedor de IA seleccionado
        if ($request->filled('bot_ia_proveedor')) {
            $proveedor = $request->input('bot_ia_proveedor');
            if (in_array($proveedor, ['openai', 'deepseek', 'gemini'])) {
                Configuracion::set('bot_ia_proveedor', $proveedor, 'bot', 'Proveedor de IA activo para el bot');
                $guardados++;
            }
        }

        // Guardar toggles de capacidades de IA (siempre guardan aunque sean '0')
        $iaToggleCampos = [
            'openai_whisper_activo' => 'Whisper — transcripción de audio',
            'openai_imagen_activo'  => 'DALL-E / Vision — lectura de imágenes',
            'gemini_audio_activo'   => 'Gemini — audio nativo',
            'gemini_vision_activo'  => 'Gemini — lectura de imágenes',
        ];
        foreach ($iaToggleCampos as $campo => $desc) {
            if ($request->has($campo)) {
                Configuracion::set($campo, $request->input($campo) === '1' ? '1' : '0', 'ia', $desc);
                $guardados++;
            }
        }

        // Guardar array de conexiones externas (JSON), preservando contraseñas no modificadas
        if ($request->has('ext_dbs')) {
            $incoming = json_decode($request->input('ext_dbs', '[]'), true);
            if (is_array($incoming)) {
                $existing    = json_decode(Configuracion::get('ext_dbs', '[]'), true) ?? [];
                $existingById = collect($existing)->keyBy('id')->toArray();

                $merged = array_map(function ($conn) use ($existingById) {
                    $id = $conn['id'] ?? '';
                    if (($conn['password'] ?? '') === '' && isset($existingById[$id])) {
                        $conn['password'] = $existingById[$id]['password'] ?? '';
                    }
                    return $conn;
                }, $incoming);

                Configuracion::set('ext_dbs', json_encode(array_values($merged)), 'ext_db', 'Bases de datos externas para contexto del bot');
                $guardados++;
            }
        }

        foreach ($this->campos as $grupo => $info) {
            foreach ($info['claves'] as $clave => $meta) {
                $valor = $request->input($clave);

                if ($valor !== null && trim($valor) !== '') {
                    Configuracion::set(
                        clave:       $clave,
                        valor:       trim($valor),
                        grupo:       $grupo,
                        descripcion: $meta['label'],
                    );
                    $guardados++;
                }
            }
        }

        $msg = $guardados > 0
            ? "Se guardaron {$guardados} campo(s) correctamente."
            : 'No se realizaron cambios (todos los campos estaban vacíos).';

        return redirect()->route('configuracion.index')->with('success', $msg);
    }

    private function defaultBotPasosIA(): string
    {
        $pasos = [
            [
                'desde'       => 1,
                'hasta'       => 1,
                'nombre'      => 'Bienvenida',
                'instruccion' => 'Saluda cordialmente al usuario, presentate con el nombre del negocio y pregunta en que puedes ayudarle. No hagas mas de una pregunta a la vez.',
            ],
            [
                'desde'       => 2,
                'hasta'       => 3,
                'nombre'      => 'Identificacion de necesidad',
                'instruccion' => 'Escucha la necesidad del usuario y haz UNA pregunta de seguimiento para entender mejor su situacion. No repitas lo que ya te dijo.',
            ],
            [
                'desde'       => 4,
                'hasta'       => 6,
                'nombre'      => 'Resolucion',
                'instruccion' => 'Proporciona informacion concreta y util basada en lo que el usuario expreso. Si la solucion requiere atencion humana, indica como contactar con un asesor.',
            ],
            [
                'desde'       => 7,
                'hasta'       => 9999,
                'nombre'      => 'Cierre o derivacion',
                'instruccion' => 'Resume brevemente lo tratado y pregunta si hay algo mas en que puedas ayudar. Si llevas mas de 3 mensajes sin resolver, ofrece contacto directo con un humano.',
            ],
        ];

        return json_encode($pasos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '';
    }

    private function defaultBotFlujoPasos(): string
    {
        $flow = [
            'inicio' => 'menu',
            'fallback' => 'No entendi tu opcion. Escribe *menu* para ver las opciones disponibles.',
            'steps' => [
                'menu' => [
                    'mensaje' => "Hola, soy el asistente virtual.\n\nElige una opcion:\n1) Ventas\n2) Soporte\n3) Horarios",
                    'opciones' => [
                        '1|ventas|venta' => 'ventas',
                        '2|soporte|ayuda' => 'soporte',
                        '3|horarios|horario' => 'horarios',
                    ],
                ],
                'ventas' => [
                    'mensaje' => 'Perfecto. Un asesor de ventas te contacta en breve. Escribe menu para volver.',
                ],
                'soporte' => [
                    'mensaje' => 'Cuéntame tu problema tecnico en un mensaje y te ayudamos. Escribe menu para volver.',
                ],
                'horarios' => [
                    'mensaje' => 'Nuestro horario es de lunes a viernes de 9:00 a 18:00. Escribe menu para volver.',
                ],
            ],
        ];

        return json_encode($flow, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '';
    }

    /**
     * Limpia (borra) el valor de una clave específica.
     */
    public function limpiar(string $clave)
    {
        $clavesValidas = collect($this->campos)
            ->flatMap(fn($g) => array_keys($g['claves']))
            ->push('system_prompt', 'bot_ia_proveedor', 'ext_dbs', 'bot_prompt_verificacion',
                   'openai_whisper_activo', 'openai_imagen_activo', 'gemini_audio_activo', 'gemini_vision_activo')
            ->toArray();

        if (in_array($clave, $clavesValidas)) {
            Configuracion::clear($clave);
        }

        return redirect()->route('configuracion.index')
            ->with('success', "Clave «{$clave}» eliminada.");
    }

    /**
     * Prueba la conexión a la BD externa y devuelve las tablas/colecciones disponibles.
     * Solo prueba — NO guarda credenciales.
     * POST /configuracion/test-db → JSON
     */
    public function testExternalDb(Request $request): JsonResponse
    {
        $request->validate([
            'driver'   => ['required', 'in:mysql,pgsql,mongodb'],
            'host'     => ['required', 'string', 'max:255'],
            'port'     => ['nullable', 'numeric', 'min:1', 'max:65535'],
            'database' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
        ]);

        try {
            $servicio = new ExternalDbService();
            $servicio->conectar($request->only(['driver', 'host', 'port', 'database', 'username', 'password']));
            $tablas   = $servicio->listarTablas();
            $esquemas = $servicio->listarEsquemaCompleto();

            return response()->json([
                'success'  => true,
                'tablas'   => $tablas,
                'esquemas' => $esquemas,
                'mensaje'  => 'Conexión exitosa. Se encontraron ' . count($tablas) . ' tabla(s).',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
                'debug'   => basename($e->getFile()) . ':' . $e->getLine() . ' [' . get_class($e) . ']',
            ], 422);
        }
    }

    /**
     * Guarda un prompt con nombre para uso posterior.
     * POST /configuracion/prompts → JSON
     */
    public function storePrompt(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'    => ['required', 'string', 'max:100'],
            'contenido' => ['required', 'string', 'max:8000'],
        ]);

        $prompt = SavedPrompt::create([
            'nombre'    => trim($request->nombre),
            'contenido' => $request->contenido,
        ]);

        return response()->json(['success' => true, 'prompt' => $prompt]);
    }

    /**
     * Actualiza nombre y/o contenido de un prompt guardado.
     * PUT /configuracion/prompts/{id} → JSON
     */
    public function updatePrompt(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'nombre'    => ['required', 'string', 'max:100'],
            'contenido' => ['required', 'string', 'max:8000'],
        ]);

        $prompt = SavedPrompt::findOrFail($id);
        $prompt->update([
            'nombre'    => trim($request->nombre),
            'contenido' => $request->contenido,
        ]);

        return response()->json(['success' => true, 'prompt' => $prompt]);
    }

    /**
     * Elimina un prompt guardado.
     * DELETE /configuracion/prompts/{id} → JSON
     */
    public function destroyPrompt(int $id): JsonResponse
    {
        SavedPrompt::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Guarda la config de media adjunta de catálogos (bot_catalog_media).
     * POST /configuracion/catalog-media → JSON
     */
    public function saveCatalogMedia(Request $request): JsonResponse
    {
        $data = $request->validate([
            'config' => ['present'],
        ]);

        $clean = [];
        foreach ((array) ($data['config'] ?? []) as $slug => $mc) {
            // Normalizar campos de archivo (nuevo formato: array de {campo_slug, mediatype})
            $campos = [];
            foreach ($mc['campos'] ?? [] as $c) {
                if (empty($c['campo_slug'])) continue;
                $campos[] = [
                    'campo_slug' => (string) $c['campo_slug'],
                    'mediatype'  => in_array($c['mediatype'] ?? '', ['image', 'video', 'document', 'audio'])
                        ? $c['mediatype']
                        : 'image',
                    'view_once'  => !empty($c['view_once']),
                ];
            }
            $clean[(string) $slug] = [
                'activo'        => (bool) ($mc['activo'] ?? false),
                'campos'        => $campos,
                'caption_campo' => (string) ($mc['caption_campo'] ?? ''),
                'max_resultados'=> min(10, max(1, (int) ($mc['max_resultados'] ?? 3))),
            ];
        }

        Configuracion::set('bot_catalog_media', json_encode($clean), 'bot', 'Configuración de media adjunta de catálogos para el bot');

        return response()->json(['success' => true]);
    }

    /**
     * Guarda el pipeline de medios completo como JSON.
     * POST /configuracion/pipeline → JSON
     */
    public function savePipeline(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pipeline' => ['required', 'array'],
        ]);

        $allowed = ['image', 'audio', 'video', 'documento'];
        $clean   = array_intersect_key($data['pipeline'], array_flip($allowed));

        Configuracion::set('bot_media_pipeline', json_encode($clean), 'bot', 'Pipeline de procesamiento de medios entrantes');

        return response()->json(['success' => true]);
    }
}
