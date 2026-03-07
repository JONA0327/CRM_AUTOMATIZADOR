<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MediaPipelineService
 *
 * Ejecuta pipelines de procesamiento de medios de forma agnóstica al canal.
 * Cada tipo de media (image, audio, video, documento) tiene una cadena de pasos.
 *
 * Pasos disponibles:
 *   vision        → imagen         → texto (descripción)
 *   ocr           → imagen         → texto (extracción de texto visible)
 *   transcribir   → audio/video    → texto (transcripción)
 *   resumir       → texto          → texto (resumen con LLM)
 *   generar_imagen→ texto          → URL/base64 de imagen (DALL-E / Imagen)
 *
 * Destinos de resultado:
 *   pasar_a_bot   → el texto resultante se entrega al LLM principal
 *   enviar_media  → la media generada se envía de vuelta al canal
 *   ambos         → envía media Y pasa descripción al bot
 *   enviar_texto  → envía el texto directamente sin pasar por el bot
 */
class MediaPipelineService
{
    // ── Config ────────────────────────────────────────────────────────────────

    private function pipeline(): array
    {
        $raw = Configuracion::get('bot_media_pipeline', '{}');
        return json_decode($raw, true) ?: [];
    }

    /**
     * Devuelve el handler configurado para un tipo de media.
     * Si no existe, devuelve null.
     */
    public function handler(string $tipo): ?array
    {
        $p = $this->pipeline();
        $h = $p[$tipo] ?? null;
        if (!$h || empty($h['activo'])) return null;
        return $h;
    }

    // ── Entrypoint principal ──────────────────────────────────────────────────

    /**
     * Procesa un medio y devuelve el resultado del pipeline.
     *
     * $media = [
     *   'tipo'     => 'image' | 'audio' | 'video' | 'documento',
     *   'base64'   => '...',
     *   'mimeType' => 'image/jpeg',
     *   'caption'  => '...',    // texto adicional del usuario (ej. caption de imagen)
     *   'filename' => '...',
     * ]
     *
     * Retorna:
     * [
     *   'ok'          => bool,
     *   'destino'     => 'pasar_a_bot'|'enviar_media'|'ambos'|'enviar_texto',
     *   'texto'       => string,   // texto para el bot / respuesta directa
     *   'texto_label' => string,   // versión con emoji para guardar en BD
     *   'media_url'   => string,   // URL de imagen generada (si aplica)
     *   'media_tipo'  => string,
     * ]
     */
    public function procesar(array $media): array
    {
        $tipo    = $media['tipo'];
        $handler = $this->handler($tipo);

        if (!$handler) {
            return ['ok' => false, 'razon' => 'sin_handler'];
        }

        $pasos   = $handler['pasos'] ?? [];
        $destino = $handler['destino'] ?? 'pasar_a_bot';

        // Estado mutable del pipeline (cada paso puede leer/escribir)
        $ctx = [
            'texto'     => $media['caption'] ?? '',
            'base64'    => $media['base64']   ?? '',
            'mimeType'  => $media['mimeType'] ?? '',
            'filename'  => $media['filename'] ?? '',
            'media_url' => '',
            'tipo'      => $tipo,
        ];

        foreach ($pasos as $paso) {
            $ctx = $this->ejecutarPaso($paso, $ctx);
            if ($ctx === null) {
                return ['ok' => false, 'razon' => 'paso_fallido'];
            }
        }

        $emoji = match($tipo) {
            'image'     => '🖼',
            'audio'     => '🎙',
            'video'     => '🎬',
            'documento' => '📄',
            default     => '📎',
        };

        return [
            'ok'          => true,
            'destino'     => $destino,
            'texto'       => $ctx['texto'],
            'texto_label' => $emoji . ' ' . $ctx['texto'],
            'media_url'   => $ctx['media_url'],
            'media_tipo'  => $tipo === 'image' ? 'image' : 'document',
        ];
    }

    // ── Ejecutar paso individual ──────────────────────────────────────────────

    private function ejecutarPaso(array $paso, array $ctx): ?array
    {
        $tipo = $paso['tipo'] ?? '';

        return match($tipo) {
            'vision'         => $this->pasoVision($paso, $ctx),
            'ocr'            => $this->pasoOCR($paso, $ctx),
            'transcribir'    => $this->pasoTranscribir($paso, $ctx),
            'resumir'        => $this->pasoResumirTexto($paso, $ctx),
            'generar_imagen' => $this->pasoGenerarImagen($paso, $ctx),
            default          => $ctx, // paso desconocido → pasar sin cambios
        };
    }

    // ── PASO: Vision (imagen → texto) ────────────────────────────────────────

    private function pasoVision(array $paso, array $ctx): ?array
    {
        $proveedor = $paso['proveedor'] ?? 'auto';
        $prompt    = !empty($paso['prompt'])
            ? $paso['prompt']
            : 'Describe detalladamente el contenido de esta imagen en español. '
              . 'Si contiene texto, transcríbelo. Si el usuario preguntó algo, respóndelo.';

        if (!empty($ctx['texto'])) {
            $prompt .= ' El usuario también escribió: "' . $ctx['texto'] . '"';
        }

        $resultado = null;

        if ($proveedor === 'gemini' || ($proveedor === 'auto' && !Configuracion::get('openai_key'))) {
            $resultado = $this->visionGemini($ctx['base64'], $ctx['mimeType'], $prompt);
        }
        if ($resultado === null) {
            $resultado = $this->visionOpenAI($ctx['base64'], $ctx['mimeType'], $prompt);
        }
        if ($resultado === null && $proveedor !== 'openai') {
            $resultado = $this->visionGemini($ctx['base64'], $ctx['mimeType'], $prompt);
        }

        if ($resultado === null) return null;
        $ctx['texto'] = $resultado;
        return $ctx;
    }

    // ── PASO: OCR (imagen → texto extraído) ──────────────────────────────────

    private function pasoOCR(array $paso, array $ctx): ?array
    {
        $paso['prompt'] = !empty($paso['prompt'])
            ? $paso['prompt']
            : 'Extrae y transcribe exactamente todo el texto visible en esta imagen. '
              . 'Devuelve únicamente el texto extraído, sin comentarios adicionales.';
        return $this->pasoVision($paso, $ctx);
    }

    // ── PASO: Transcribir (audio/video → texto) ───────────────────────────────

    private function pasoTranscribir(array $paso, array $ctx): ?array
    {
        $proveedor = $paso['proveedor'] ?? 'auto';
        $resultado = null;

        $whisperActivo = Configuracion::get('openai_whisper_activo', '0') === '1';
        $geminiActivo  = Configuracion::get('gemini_audio_activo', '0') === '1';

        if (($proveedor === 'whisper' || $proveedor === 'auto') && $whisperActivo) {
            $resultado = $this->transcribirWhisper($ctx['base64'], $ctx['mimeType']);
        }
        if ($resultado === null && ($proveedor === 'gemini' || $proveedor === 'auto') && $geminiActivo) {
            $resultado = $this->transcribirGemini($ctx['base64'], $ctx['mimeType']);
        }

        if ($resultado === null) return null;
        $ctx['texto'] = $resultado;
        return $ctx;
    }

    // ── PASO: Resumir texto con LLM ──────────────────────────────────────────

    private function pasoResumirTexto(array $paso, array $ctx): ?array
    {
        if (empty($ctx['texto'])) return $ctx;

        $prompt    = $paso['prompt'] ?? 'Resume el siguiente texto de forma clara y concisa en español:';
        $proveedor = $paso['proveedor'] ?? 'auto';
        $apiKey    = Configuracion::get('openai_key');
        $model     = Configuracion::get('openai_model', 'gpt-4o-mini');

        if (($proveedor === 'gemini' || ($proveedor === 'auto' && !$apiKey))) {
            $resultado = $this->llamarGeminiTexto($prompt . "\n\n" . $ctx['texto']);
        } else {
            $resultado = $this->llamarOpenAITexto($prompt . "\n\n" . $ctx['texto'], $model);
        }

        if ($resultado === null) return $ctx; // si falla, seguir con texto original
        $ctx['texto'] = $resultado;
        return $ctx;
    }

    // ── PASO: Generar imagen con DALL-E ───────────────────────────────────────

    private function pasoGenerarImagen(array $paso, array $ctx): ?array
    {
        $apiKey = Configuracion::get('openai_key');
        if (!$apiKey) {
            Log::warning('[MediaPipeline] generar_imagen requiere OpenAI API Key');
            return null;
        }

        // Prompt base: puede ser override del paso o el texto que viene del paso anterior (vision)
        $promptBase = !empty($paso['prompt']) ? $paso['prompt'] : $ctx['texto'];
        if (empty(trim($promptBase))) {
            Log::warning('[MediaPipeline] generar_imagen sin prompt disponible');
            return null;
        }

        $size    = $paso['size']    ?? '1024x1024';
        $quality = $paso['quality'] ?? 'standard';
        $style   = $paso['style']   ?? 'vivid';
        $model   = $paso['model']   ?? 'dall-e-3';

        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
                'model'   => $model,
                'prompt'  => $promptBase,
                'n'       => 1,
                'size'    => $size,
                'quality' => $quality,
                'style'   => $style,
            ]);

            $url = data_get($res->json(), 'data.0.url');
            if (!$url) {
                Log::warning('[MediaPipeline] DALL-E no devolvió URL: ' . $res->body());
                return null;
            }

            $ctx['media_url'] = $url;
            // El texto para el bot describe qué se generó
            if (empty($ctx['texto'])) {
                $ctx['texto'] = 'Imagen generada: ' . $promptBase;
            }
            return $ctx;
        } catch (\Exception $e) {
            Log::warning('[MediaPipeline] Error DALL-E: ' . $e->getMessage());
            return null;
        }
    }

    // ── Providers: Vision ────────────────────────────────────────────────────

    private function visionOpenAI(string $base64, string $mimeType, string $prompt): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        $model  = Configuracion::get('openai_model', 'gpt-4o');
        if (!$apiKey) return null;

        try {
            $res = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => $model,
                    'messages' => [[
                        'role'    => 'user',
                        'content' => [
                            ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64}", 'detail' => 'auto']],
                            ['type' => 'text', 'text' => $prompt],
                        ],
                    ]],
                    'max_tokens' => 1000,
                ]);
            return data_get($res->json(), 'choices.0.message.content');
        } catch (\Exception $e) {
            Log::warning('[MediaPipeline] Vision OpenAI error: ' . $e->getMessage());
            return null;
        }
    }

    private function visionGemini(string $base64, string $mimeType, string $prompt): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');
        if (!$apiKey) return null;

        try {
            $res = Http::timeout(30)
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
            Log::warning('[MediaPipeline] Vision Gemini error: ' . $e->getMessage());
            return null;
        }
    }

    // ── Providers: Transcripción ─────────────────────────────────────────────

    private function transcribirWhisper(string $base64, string $mimeType): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        if (!$apiKey) return null;

        $content = base64_decode($base64);
        if (!$content) return null;

        $ext = match(true) {
            str_contains($mimeType, 'ogg')  => 'ogg',
            str_contains($mimeType, 'mp4')  => 'mp4',
            str_contains($mimeType, 'webm') => 'webm',
            str_contains($mimeType, 'wav')  => 'wav',
            default => 'mp3',
        };

        try {
            $res = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->timeout(30)
                ->attach('file', $content, "audio.{$ext}")
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model'    => 'whisper-1',
                    'language' => 'es',
                ]);
            return data_get($res->json(), 'text');
        } catch (\Exception $e) {
            Log::warning('[MediaPipeline] Whisper error: ' . $e->getMessage());
            return null;
        }
    }

    private function transcribirGemini(string $base64, string $mimeType): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');
        if (!$apiKey) return null;

        try {
            $res = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'parts' => [
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                            ['text' => 'Transcribe exactamente lo que dice este audio en español. Solo la transcripción, sin comentarios.'],
                        ],
                    ]],
                ]);
            return data_get($res->json(), 'candidates.0.content.parts.0.text');
        } catch (\Exception $e) {
            Log::warning('[MediaPipeline] Gemini transcripción error: ' . $e->getMessage());
            return null;
        }
    }

    // ── Providers: LLM texto ──────────────────────────────────────────────────

    private function llamarOpenAITexto(string $prompt, string $model): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        if (!$apiKey) return null;
        try {
            $res = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);
            return data_get($res->json(), 'choices.0.message.content');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function llamarGeminiTexto(string $prompt): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');
        if (!$apiKey) return null;
        try {
            $res = Http::timeout(20)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);
            return data_get($res->json(), 'candidates.0.content.parts.0.text');
        } catch (\Exception $e) {
            return null;
        }
    }

    // ── Helpers públicos ──────────────────────────────────────────────────────

    /**
     * Devuelve la lista de tipos de paso disponibles con metadatos para la UI.
     */
    public static function pasosDisponibles(): array
    {
        return [
            'vision'         => ['label' => 'Analizar imagen',      'icon' => '👁',  'input' => 'image',          'output' => 'texto',      'color' => 'indigo'],
            'ocr'            => ['label' => 'Extraer texto (OCR)',   'icon' => '📝',  'input' => 'image',          'output' => 'texto',      'color' => 'blue'],
            'transcribir'    => ['label' => 'Transcribir audio',     'icon' => '🎙',  'input' => 'audio|video',    'output' => 'texto',      'color' => 'teal'],
            'resumir'        => ['label' => 'Resumir con IA',        'icon' => '📋',  'input' => 'texto',          'output' => 'texto',      'color' => 'purple'],
            'generar_imagen' => ['label' => 'Generar imagen (DALL-E)','icon' => '🎨', 'input' => 'texto',          'output' => 'imagen',     'color' => 'orange'],
        ];
    }

    /**
     * Devuelve los destinos disponibles con metadatos.
     */
    public static function destinosDisponibles(): array
    {
        return [
            'pasar_a_bot'   => ['label' => 'Pasar texto al bot',           'icon' => '🤖', 'desc' => 'El texto procesado se envía al LLM y el bot responde'],
            'enviar_media'  => ['label' => 'Enviar media generada',        'icon' => '📤', 'desc' => 'Envía la imagen/archivo generado directamente al usuario'],
            'ambos'         => ['label' => 'Enviar media + respuesta bot', 'icon' => '✨', 'desc' => 'Envía la media y además el bot responde con texto'],
            'enviar_texto'  => ['label' => 'Enviar texto directo',         'icon' => '💬', 'desc' => 'Envía el texto procesado sin pasar por el bot'],
        ];
    }

    /**
     * Estructura base vacía de un pipeline.
     */
    public static function defaultPipeline(): array
    {
        return [
            'image'     => ['activo' => false, 'pasos' => [['tipo' => 'vision', 'proveedor' => 'auto', 'prompt' => '']], 'destino' => 'pasar_a_bot'],
            'audio'     => ['activo' => false, 'pasos' => [['tipo' => 'transcribir', 'proveedor' => 'auto', 'prompt' => '']], 'destino' => 'pasar_a_bot'],
            'video'     => ['activo' => false, 'pasos' => [['tipo' => 'transcribir', 'proveedor' => 'auto', 'prompt' => '']], 'destino' => 'pasar_a_bot'],
            'documento' => ['activo' => false, 'pasos' => [['tipo' => 'vision', 'proveedor' => 'auto', 'prompt' => '']], 'destino' => 'pasar_a_bot'],
        ];
    }
}
