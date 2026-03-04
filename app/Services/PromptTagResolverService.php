<?php

namespace App\Services;

use App\Models\CatalogModule;
use App\Models\CatalogRecord;
use App\Models\Configuracion;

/**
 * Resuelve etiquetas [TAG] del system_prompt reemplazándolas con
 * información real: esquemas de catálogos, BDs externas o instrucciones de API.
 *
 * Uso en BotController:
 *   $prompt = app(PromptTagResolverService::class)->resolve($prompt);
 */
class PromptTagResolverService
{
    // ─── Metadata estática de cada API ───────────────────────────────────────

    private const API_INFO = [
        'API_EVOLUTION' => [
            'label'  => 'Evolution API (WhatsApp)',
            'claves' => ['evolution_url', 'evolution_key'],
            'desc'   => <<<'TXT'
API de gestión de WhatsApp Business (Evolution API).
Endpoints disponibles:
  • Enviar texto:   POST {url}/message/sendText/{instance}
      body: { "number": "521XXXXXXXXXX@s.whatsapp.net", "text": "Hola" }
  • Enviar imagen:  POST {url}/message/sendMedia/{instance}
      body: { "number": "...", "mediatype": "image", "media": "<url_o_base64>", "caption": "..." }
  • Estado:         GET  {url}/instance/fetchInstances
  • Conectar QR:    GET  {url}/instance/connect/{instance}
TXT,
        ],
        'API_OPENAI' => [
            'label'  => 'ChatGPT (OpenAI)',
            'claves' => ['openai_key'],
            'desc'   => <<<'TXT'
API de OpenAI para generación de texto e imágenes.
Endpoints disponibles:
  • Chat completion: POST https://api.openai.com/v1/chat/completions
      body: { "model": "gpt-4o", "messages": [{"role":"user","content":"..."}] }
  • Modelos:         GET  https://api.openai.com/v1/models
  • Embeddings:      POST https://api.openai.com/v1/embeddings
Modelos recomendados: gpt-4o, gpt-4o-mini, gpt-4-turbo
TXT,
        ],
        'API_DEEPSEEK' => [
            'label'  => 'DeepSeek',
            'claves' => ['deepseek_key'],
            'desc'   => <<<'TXT'
API de DeepSeek para inferencia de lenguaje y código.
Endpoints disponibles:
  • Chat completion: POST https://api.deepseek.com/v1/chat/completions
      body: { "model": "deepseek-chat", "messages": [...] }
Modelos: deepseek-chat, deepseek-coder, deepseek-reasoner
TXT,
        ],
        'API_GEMINI' => [
            'label'  => 'Google Gemini',
            'claves' => ['gemini_key'],
            'desc'   => <<<'TXT'
API de Google Gemini para generación multimodal (texto, imágenes, audio).
Endpoints disponibles:
  • Generar contenido: POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
      body: { "contents": [{"parts": [{"text":"..."}]}] }
Modelos: gemini-1.5-pro, gemini-1.5-flash, gemini-2.0-flash
TXT,
        ],
        'API_GOOGLE' => [
            'label'  => 'Google Calendar & Drive',
            'claves' => ['google_client_id', 'google_client_secret'],
            'desc'   => <<<'TXT'
API de Google para Calendar y Drive (OAuth2).
Google Calendar:
  • Listar eventos:  GET  https://www.googleapis.com/calendar/v3/calendars/{calendarId}/events
  • Crear evento:    POST https://www.googleapis.com/calendar/v3/calendars/{calendarId}/events
      body: { "summary":"...", "start":{"dateTime":"..."}, "end":{"dateTime":"..."} }
  • Eliminar evento: DELETE https://www.googleapis.com/calendar/v3/calendars/{calendarId}/events/{eventId}
Google Drive:
  • Listar archivos: GET  https://www.googleapis.com/drive/v3/files
  • Subir archivo:   POST https://www.googleapis.com/upload/drive/v3/files
TXT,
        ],
        'API_ZOOM' => [
            'label'  => 'Zoom',
            'claves' => ['zoom_account_id'],
            'desc'   => <<<'TXT'
API de Zoom para reuniones y webinars.
Endpoints disponibles:
  • Crear reunión:   POST https://api.zoom.us/v2/users/{userId}/meetings
      body: { "topic":"...", "type":2, "start_time":"2024-01-01T10:00:00Z", "duration":60 }
  • Listar reuniones: GET https://api.zoom.us/v2/users/{userId}/meetings
  • Obtener reunión: GET https://api.zoom.us/v2/meetings/{meetingId}
  • Eliminar reunión: DELETE https://api.zoom.us/v2/meetings/{meetingId}
TXT,
        ],
        'API_ASSEMBLY' => [
            'label'  => 'AssemblyAI (Transcripción)',
            'claves' => ['assembly_key'],
            'desc'   => <<<'TXT'
API de AssemblyAI para transcripción de audio/voz a texto.
Endpoints disponibles:
  • Subir audio:         POST https://api.assemblyai.com/v2/upload
      body: <binary audio data>   → responde { "upload_url": "..." }
  • Solicitar transcripción: POST https://api.assemblyai.com/v2/transcript
      body: { "audio_url": "<upload_url>", "language_code": "es" }
  • Obtener resultado:   GET  https://api.assemblyai.com/v2/transcript/{id}
      Polling hasta que status === "completed"
TXT,
        ],
    ];

    // ─── Resolución del prompt ────────────────────────────────────────────────

    /**
     * Reemplaza todas las etiquetas [TAG] del prompt con su contenido real.
     */
    public function resolve(string $prompt): string
    {
        return preg_replace_callback(
            '/\[([A-Z][A-Z0-9_]{2,})\]/',
            fn(array $m) => $this->resolveTag($m[1]) ?? $m[0],
            $prompt
        );
    }

    /**
     * Devuelve todos los tags disponibles para el tenant actual,
     * con metadata para la UI (diagrama de nodos + paleta).
     *
     * @return array<array{tag: string, tipo: string, label: string, activo: bool, preview: string}>
     */
    public function availableTags(): array
    {
        $tags = [];

        // ── APIs ─────────────────────────────────────────────────────────────
        foreach (self::API_INFO as $tag => $info) {
            $activo = collect($info['claves'])
                ->some(fn($k) => Configuracion::isConfigured($k));

            $tags[] = [
                'tag'     => $tag,
                'tipo'    => 'api',
                'label'   => $info['label'],
                'activo'  => $activo,
                'preview' => trim(substr($info['desc'], 0, 90)) . '…',
            ];
        }

        // ── Catálogos activos ─────────────────────────────────────────────────
        try {
            $modulos = CatalogModule::where('activo', true)->orderBy('orden')->get();
            foreach ($modulos as $mod) {
                $tagName = 'CATALOGO_' . strtoupper(preg_replace('/[^A-Z0-9]/i', '_', $mod->slug));
                $campos  = $mod->fields()->orderBy('orden')->pluck('nombre')->join(', ');
                $tags[]  = [
                    'tag'     => $tagName,
                    'tipo'    => 'catalogo',
                    'label'   => ($mod->icono ? $mod->icono . ' ' : '') . $mod->nombre,
                    'activo'  => true,
                    'preview' => 'Campos: ' . ($campos ?: 'sin campos aún'),
                ];
            }
        } catch (\Exception) {
            // El tenant puede no tener todavía las tablas de catálogos
        }

        // ── BDs externas ──────────────────────────────────────────────────────
        try {
            $extDbs = json_decode(Configuracion::get('ext_dbs', '[]'), true) ?? [];
            foreach ($extDbs as $db) {
                $nombre         = $db['nombre'] ?? ($db['id'] ?? 'bd');
                $tagName        = 'DB_EXT_' . strtoupper(preg_replace('/[^A-Z0-9]/i', '_', $nombre));
                $tablasLista    = $db['tablas'] ?? [];
                $tablasColumnas = $db['tablas_columnas'] ?? [];

                // Contar columnas seleccionadas en total
                $totalCols = 0;
                foreach ($tablasColumnas as $cols) {
                    $totalCols += is_array($cols) ? count($cols) : 0;
                }

                $tablaStr = implode(', ', $tablasLista);
                $preview  = 'BD ' . ($db['driver'] ?? 'mysql') . ' — Tablas: ' . (($tablaStr) ?: 'sin tablas');
                if ($totalCols > 0) {
                    $preview .= " ({$totalCols} campos seleccionados)";
                }

                $tags[] = [
                    'tag'     => $tagName,
                    'tipo'    => 'db_ext',
                    'label'   => $nombre,
                    'activo'  => true,
                    'preview' => $preview,
                ];
            }
        } catch (\Exception) {}

        return $tags;
    }

    // ─── Resolución interna por tipo ─────────────────────────────────────────

    private function resolveTag(string $tag): ?string
    {
        // API estática
        if (isset(self::API_INFO[$tag])) {
            return "\n\n### " . self::API_INFO[$tag]['label'] . " ###\n"
                . trim(self::API_INFO[$tag]['desc']) . "\n";
        }

        // Catálogo del tenant
        if (str_starts_with($tag, 'CATALOGO_')) {
            return $this->resolveCatalogTag(substr($tag, 9));
        }

        // BD externa
        if (str_starts_with($tag, 'DB_EXT_')) {
            return $this->resolveExtDbTag($tag);
        }

        return null;
    }

    private function resolveCatalogTag(string $suffix): ?string
    {
        try {
            foreach (CatalogModule::all() as $mod) {
                $candidate = strtoupper(preg_replace('/[^A-Z0-9]/i', '_', $mod->slug));
                if ($candidate !== $suffix) {
                    continue;
                }

                $campos = $mod->fields()->orderBy('orden')->get();
                $lines  = ["### CATÁLOGO: {$mod->nombre} (módulo del sistema) ###"];
                $lines[] = "Campos disponibles:";

                foreach ($campos as $c) {
                    $extra = '';
                    if (in_array($c->tipo, ['select', 'multiselect']) && !empty($c->opciones)) {
                        $extra = ' [opciones: ' . implode(', ', $c->opciones) . ']';
                    } elseif ($c->tipo === 'relation' && $c->modulo_relacion) {
                        $extra = " [relacionado con módulo: {$c->modulo_relacion}]";
                    } elseif ($c->tipo === 'id') {
                        $tipoId = $c->meta['tipo_id'] ?? 'folio';
                        $extra  = " [ID automático, formato: {$tipoId}]";
                    }
                    $oblig   = $c->obligatorio ? ' *obligatorio*' : '';
                    $lines[] = "  • {$c->nombre} (tipo: {$c->tipo}){$extra}{$oblig}";
                }

                $count   = CatalogRecord::where('module_id', $mod->id)->count();
                $lines[] = "Total de registros actuales: {$count}";

                return implode("\n", $lines) . "\n";
            }
        } catch (\Exception) {}

        return null;
    }

    private function resolveExtDbTag(string $tag): ?string
    {
        try {
            $extDbs = json_decode(Configuracion::get('ext_dbs', '[]'), true) ?? [];
            foreach ($extDbs as $db) {
                $nombre    = $db['nombre'] ?? ($db['id'] ?? '');
                $candidate = 'DB_EXT_' . strtoupper(preg_replace('/[^A-Z0-9]/i', '_', $nombre));
                if ($candidate !== $tag) {
                    continue;
                }

                $tablas         = $db['tablas'] ?? [];
                $tablasColumnas = $db['tablas_columnas'] ?? [];
                $driver         = $db['driver']   ?? 'mysql';
                $host           = $db['host']     ?? '';
                $dbName         = $db['database'] ?? '';

                $lines = ["### BD EXTERNA: {$nombre} ({$driver} @ {$host}/{$dbName}) ###"];

                if (empty($tablas)) {
                    $lines[] = "Sin tablas seleccionadas para contexto del bot.";
                    $lines[] = "Ve a Configuración → Bases de Datos y selecciona las tablas que el bot debe conocer.";
                } else {
                    $lines[] = "Tablas disponibles para consulta:";
                    foreach ($tablas as $t) {
                        $cols    = isset($tablasColumnas[$t]) && is_array($tablasColumnas[$t]) ? $tablasColumnas[$t] : [];
                        $colStr  = !empty($cols) ? ' — columnas: ' . implode(', ', $cols) : '';
                        $lines[] = "  • {$t}{$colStr}";
                    }
                    $lines[] = "Usa los datos de estas tablas para responder preguntas del usuario.";
                }

                return implode("\n", $lines) . "\n";
            }
        } catch (\Exception) {}

        return null;
    }
}
