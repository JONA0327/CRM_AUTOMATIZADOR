<?php

namespace App\Http\Controllers;

use App\Models\CatalogField;
use App\Models\CatalogModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogModuleController extends Controller
{
    // ── MÓDULOS ──────────────────────────────────────────────────────────────

    /**
     * GET /admin/modulos
     * - Navegador (HTML): devuelve la vista Blade del panel no-code
     * - AJAX (Accept: application/json): devuelve la lista de módulos con campos
     */
    public function index(Request $request): mixed
    {
        if (! $request->wantsJson()) {
            return view('admin.modulos.index');
        }

        $modulos = CatalogModule::with(['fields' => fn($q) => $q->orderBy('orden')])
            ->orderBy('orden')
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'nombre'       => $m->nombre,
                'slug'         => $m->slug,
                'icono'        => $m->icono,
                'color'        => $m->color,
                'activo'       => (bool) $m->activo,
                'orden'        => $m->orden,
                'campos_count' => $m->fields->count(),
                'campos'       => $m->fields->values(),
            ]);

        return response()->json($modulos);
    }

    /**
     * Crea un nuevo módulo.
     * POST /admin/modulos
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'icono'  => ['nullable', 'string', 'max:10'],
            'color'  => ['nullable', 'string', 'max:7'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $data['slug']  = CatalogModule::generarSlug($data['nombre']);
        $data['orden'] = CatalogModule::max('orden') + 1;
        $data['icono'] = $data['icono'] ?? '📋';
        $data['color'] = $data['color'] ?? '#6366f1';

        $modulo = CatalogModule::create($data);

        return response()->json($modulo->load('fields'), 201);
    }

    /**
     * Actualiza un módulo existente.
     * PUT /admin/modulos/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $modulo = CatalogModule::findOrFail($id);

        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:80'],
            'icono'  => ['nullable', 'string', 'max:10'],
            'color'  => ['nullable', 'string', 'max:7'],
            'activo' => ['nullable', 'boolean'],
            'orden'  => ['nullable', 'integer', 'min:0'],
        ]);

        $modulo->update($data);

        return response()->json($modulo->load('fields'));
    }

    /**
     * Elimina un módulo y todos sus campos y registros.
     * DELETE /admin/modulos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        CatalogModule::findOrFail($id)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Reordena todos los módulos.
     * POST /admin/modulos/reorder  { ids: [3,1,2] }
     */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $orden => $id) {
            CatalogModule::where('id', $id)->update(['orden' => $orden]);
        }

        return response()->json(['ok' => true]);
    }

    // ── CAMPOS ───────────────────────────────────────────────────────────────

    /**
     * Crea un campo en un módulo.
     * POST /admin/modulos/{id}/campos
     */
    public function storeField(Request $request, int $id): JsonResponse
    {
        $modulo = CatalogModule::findOrFail($id);

        $data = $request->validate([
            'nombre'          => ['required', 'string', 'max:80'],
            'tipo'            => ['required', 'in:' . implode(',', CatalogField::TIPOS)],
            'obligatorio'     => ['nullable', 'boolean'],
            'opciones'        => ['nullable', 'array'],        // array de strings para tipo=select
            'modulo_relacion' => ['nullable', 'string'],       // slug del módulo relacionado
            'meta'            => ['nullable', 'array'],        // config extra (file: accept, max_mb)
        ]);

        $data['module_id']  = $modulo->id;
        $data['slug']       = CatalogField::generarSlug($data['nombre'], $modulo->id);
        $data['orden']      = CatalogField::where('module_id', $modulo->id)->max('orden') + 1;
        $data['obligatorio'] = $data['obligatorio'] ?? false;

        $campo = CatalogField::create($data);

        return response()->json($campo, 201);
    }

    /**
     * Actualiza un campo.
     * PUT /admin/modulos/{id}/campos/{fid}
     */
    public function updateField(Request $request, int $id, int $fid): JsonResponse
    {
        $campo = CatalogField::where('module_id', $id)->findOrFail($fid);

        $data = $request->validate([
            'nombre'          => ['sometimes', 'string', 'max:80'],
            'tipo'            => ['sometimes', 'in:' . implode(',', CatalogField::TIPOS)],
            'obligatorio'     => ['nullable', 'boolean'],
            'opciones'        => ['nullable', 'array'],
            'modulo_relacion' => ['nullable', 'string'],
            'meta'            => ['nullable', 'array'],
            'orden'           => ['nullable', 'integer', 'min:0'],
        ]);

        $campo->update($data);

        return response()->json($campo);
    }

    /**
     * Elimina un campo.
     * DELETE /admin/modulos/{id}/campos/{fid}
     */
    public function destroyField(int $id, int $fid): JsonResponse
    {
        CatalogField::where('module_id', $id)->findOrFail($fid)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Reordena los campos de un módulo.
     * POST /admin/modulos/{id}/campos/reorder  { ids: [3,1,2] }
     */
    public function reorderFields(Request $request, int $id): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $orden => $fid) {
            CatalogField::where('module_id', $id)->where('id', $fid)->update(['orden' => $orden]);
        }

        return response()->json(['ok' => true]);
    }
}
