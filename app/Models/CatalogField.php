<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CatalogField extends Model
{
    // Tipos de campo soportados
    public const TIPOS = ['text', 'number', 'date', 'select', 'multiselect', 'relation', 'email', 'phone', 'textarea', 'url', 'file', 'tags', 'id'];

    protected $fillable = [
        'module_id',
        'nombre',
        'slug',
        'tipo',
        'obligatorio',
        'opciones',
        'modulo_relacion',
        'meta',
        'orden',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'opciones'    => 'array',
        'meta'        => 'array',
        'orden'       => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CatalogModule::class, 'module_id');
    }

    public static function generarSlug(string $nombre, int $moduleId): string
    {
        $base = Str::slug($nombre, '_');
        $slug = $base;
        $i    = 2;

        while (static::where('module_id', $moduleId)->where('slug', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }

        return $slug;
    }
}
