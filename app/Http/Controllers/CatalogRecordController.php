<?php

namespace App\Http\Controllers;

use App\Models\CatalogField;
use App\Models\CatalogModule;
use App\Models\CatalogRecord;
use App\Models\Configuracion;
use App\Models\TenantInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogRecordController extends Controller
{
    /**
     * Lista los registros de un módulo con paginación.
     * GET /catalogo/{module}
     */
    public function index(Request $request, string $module)
    {
        $modulo  = CatalogModule::where('slug', $module)->firstOrFail();
        $fields  = $modulo->fields()->orderBy('orden')->get();
        $records = CatalogRecord::where('module_id', $modulo->id)
            ->latest()
            ->paginate(25);

        $tienePromptVerificacion = Configuracion::isConfigured('bot_prompt_verificacion');

        return view('catalogo.index', compact('modulo', 'fields', 'records', 'tienePromptVerificacion'));
    }

    /**
     * Crea un nuevo registro.
     * POST /catalogo/{module}
     */
    public function store(Request $request, string $module): JsonResponse
    {
        $modulo = CatalogModule::where('slug', $module)->firstOrFail();
        $datos  = $this->validarYLimpiar($request, $modulo);

        // Auto-generate all id-type fields
        foreach ($modulo->fields()->where('tipo', 'id')->get() as $idField) {
            $datos[$idField->slug] = $this->generarIdCampo($idField, $modulo);
        }

        $record = CatalogRecord::create([
            'module_id' => $modulo->id,
            'datos'     => $datos,
        ]);

        return response()->json($record, 201);
    }

    /**
     * Actualiza un registro existente.
     * PUT /catalogo/{module}/{id}
     */
    public function update(Request $request, string $module, int $id): JsonResponse
    {
        $modulo = CatalogModule::where('slug', $module)->firstOrFail();
        $record = CatalogRecord::where('module_id', $modulo->id)->findOrFail($id);
        $datos  = $this->validarYLimpiar($request, $modulo);

        // Preserve auto-generated id fields — never allow editing
        foreach ($modulo->fields()->where('tipo', 'id')->get() as $idField) {
            $datos[$idField->slug] = $record->datos[$idField->slug]
                ?? $this->generarIdCampo($idField, $modulo);
        }

        $record->update(['datos' => $datos]);

        return response()->json($record);
    }

    /**
     * Elimina un registro.
     * DELETE /catalogo/{module}/{id}
     */
    public function destroy(string $module, int $id): JsonResponse
    {
        $modulo = CatalogModule::where('slug', $module)->firstOrFail();
        CatalogRecord::where('module_id', $modulo->id)->findOrFail($id)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Devuelve opciones para campos de tipo "relation" (AJAX).
     * GET /catalogo/{module}/opciones-relation?modulo_relacion=agenda
     */
    public function opcionesRelation(Request $request, string $module): JsonResponse
    {
        $slugRelacion   = $request->query('modulo_relacion');
        $moduloRelacion = CatalogModule::where('slug', $slugRelacion)->firstOrFail();

        // Devuelve id + primer campo de texto para mostrar en el select
        $primerCampo = $moduloRelacion->fields()
            ->whereIn('tipo', ['text', 'email', 'phone'])
            ->orderBy('orden')
            ->first();

        $records = CatalogRecord::where('module_id', $moduloRelacion->id)
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn($r) => [
                'id'    => $r->id,
                'label' => $primerCampo
                    ? ($r->datos[$primerCampo->slug] ?? "Registro #{$r->id}")
                    : "Registro #{$r->id}",
            ]);

        return response()->json($records);
    }

    /**
     * Sube un archivo para un campo de tipo "file".
     * POST /catalogo/{module}/upload-file
     */
    public function uploadFile(Request $request, string $module): JsonResponse
    {
        $modulo    = CatalogModule::where('slug', $module)->firstOrFail();
        $fieldSlug = $request->input('field_slug');
        $field     = $modulo->fields()->where('slug', $fieldSlug)->first();

        $meta   = $field?->meta ?? [];
        $accept = $meta['accept'] ?? 'all';
        $maxMb  = max(1, (int) ($meta['max_mb'] ?? 10));

        $mimes = match ($accept) {
            'image' => 'jpg,jpeg,png,gif,webp,svg,bmp',
            'video' => 'mp4,mov,avi,webm,mkv',
            default => 'jpg,jpeg,png,gif,webp,svg,bmp,mp4,mov,avi,webm,mkv,pdf,doc,docx,xls,xlsx,csv,txt,zip',
        };

        $request->validate([
            'file' => ['required', 'file', 'max:' . ($maxMb * 1024), 'mimes:' . $mimes],
        ]);

        $tenantId = tenancy()->tenant->getTenantKey();
        $path = $request->file('file')->store("catalog/{$tenantId}/{$module}", 'public');

        return response()->json([
            'path' => $path,
            'url'  => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Envía un mensaje de verificación de WhatsApp al teléfono almacenado en el campo.
     * POST /catalogo/{module}/{id}/whatsapp-verify/{fieldSlug}
     */
    public function verificarWhatsapp(Request $request, string $module, int $id, string $fieldSlug): JsonResponse
    {
        $modulo = CatalogModule::where('slug', $module)->firstOrFail();
        $record = CatalogRecord::where('module_id', $modulo->id)->findOrFail($id);
        $modulo->fields()->where('slug', $fieldSlug)->where('tipo', 'phone')->firstOrFail();

        $phone = $record->datos[$fieldSlug] ?? null;
        if (! $phone) {
            return response()->json(['message' => 'El campo no tiene número de teléfono.'], 422);
        }

        $prompt = Configuracion::get('bot_prompt_verificacion');
        if (! $prompt) {
            return response()->json(['message' => 'No hay prompt de verificación configurado. Ve a Configuración > WhatsApp Verificación.'], 422);
        }

        $apiUrl = Configuracion::get('evolution_url');
        $apiKey = Configuracion::get('evolution_key');
        if (! $apiUrl || ! $apiKey) {
            return response()->json(['message' => 'Evolution API no configurada.'], 422);
        }

        // TenantInstance usa conexión central (protected $connection = 'mysql')
        $tenantId = tenancy()->tenant->getTenantKey();
        $instance = TenantInstance::where('tenant_id', $tenantId)->first();
        if (! $instance) {
            return response()->json(['message' => 'No hay instancia de WhatsApp registrada para este tenant.'], 422);
        }

        // Solo dígitos para Evolution API
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        try {
            $res = Http::withHeaders(['apikey' => $apiKey])
                ->post(rtrim($apiUrl, '/') . '/message/sendText/' . urlencode($instance->instance_name), [
                    'number'      => $cleanPhone,
                    'options'     => ['delay' => 1200],
                    'textMessage' => ['text' => $prompt],
                ]);

            if ($res->successful()) {
                return response()->json(['ok' => true, 'message' => 'Mensaje de verificación enviado.']);
            }

            return response()->json(['message' => 'Error al enviar: ' . $res->body()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Genera el valor para un campo de tipo "id" según su configuración en meta.
     */
    private function generarIdCampo(CatalogField $field, CatalogModule $modulo): string
    {
        $meta   = $field->meta ?? [];
        $tipoId = $meta['tipo_id'] ?? 'folio';

        switch ($tipoId) {
            case 'uuid':
                return (string) Str::uuid();

            case 'autoincrement':
                $max = 0;
                CatalogRecord::where('module_id', $modulo->id)->each(function ($r) use ($field, &$max) {
                    $val = $r->datos[$field->slug] ?? null;
                    if ($val !== null && preg_match('/(\d+)$/', (string) $val, $m)) {
                        $max = max($max, (int) $m[1]);
                    }
                });
                return (string) ($max + 1);

            case 'folio':
            default:
                $prefijo   = trim($meta['folio_prefijo']   ?? '');
                $separador = $meta['folio_separador'] ?? '-';
                $cifras    = max(1, (int) ($meta['folio_cifras'] ?? 4));

                // Find current max number used in this field
                $max = 0;
                CatalogRecord::where('module_id', $modulo->id)->each(function ($r) use ($field, &$max) {
                    $val = $r->datos[$field->slug] ?? null;
                    if ($val !== null && preg_match('/(\d+)$/', (string) $val, $m)) {
                        $max = max($max, (int) $m[1]);
                    }
                });

                $numero = str_pad($max + 1, $cifras, '0', STR_PAD_LEFT);

                return $prefijo !== ''
                    ? $prefijo . $separador . $numero
                    : $numero;
        }
    }

    private function validarYLimpiar(Request $request, CatalogModule $modulo): array
    {
        $fields = $modulo->fields()->orderBy('orden')->get();
        $rules  = [];

        foreach ($fields as $field) {
            $key      = "datos.{$field->slug}";
            $req      = $field->obligatorio ? 'required' : 'nullable';
            $multiple = $field->meta['multiple'] ?? false;

            switch ($field->tipo) {
                case 'number':
                    $rules[$key] = "$req|numeric";
                    break;
                case 'date':
                    $rules[$key] = "$req|date";
                    break;
                case 'email':
                    $rules[$key] = "$req|email|max:255";
                    break;
                case 'phone':
                    $rules[$key] = "$req|string|max:30";
                    break;
                case 'select':
                    $opts        = implode(',', $field->opciones ?? []);
                    $rules[$key] = "$req|string|in:$opts";
                    break;
                case 'multiselect':
                    $opts            = $field->opciones ?? [];
                    $rules[$key]     = "$req|array";
                    if (!empty($opts)) {
                        $rules["$key.*"] = 'nullable|string|in:' . implode(',', $opts);
                    } else {
                        $rules["$key.*"] = 'nullable|string|max:200';
                    }
                    break;
                case 'relation':
                    if ($multiple) {
                        $rules[$key]        = "$req|array";
                        $rules["$key.*"]    = 'nullable|integer';
                    } else {
                        $rules[$key] = "$req|integer";
                    }
                    break;
                case 'textarea':
                    $rules[$key] = "$req|string|max:5000";
                    break;
                case 'url':
                    $rules[$key] = "$req|string|url|max:2048";
                    break;
                case 'file':
                    $rules[$key] = "$req|string|max:500";
                    break;
                case 'tags':
                    $rules[$key]     = "$req|array";
                    $rules["$key.*"] = 'nullable|string|max:500';
                    break;
                case 'id':
                    // Auto-generated — never validated from form input
                    $rules[$key] = 'nullable|string|max:100';
                    break;
                default:
                    $rules[$key] = "$req|string|max:1000";
            }
        }

        $validated = $request->validate($rules);

        return $validated['datos'] ?? [];
    }
}
