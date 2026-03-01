<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CatalogModule extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'icono',
        'color',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function fields(): HasMany
    {
        return $this->hasMany(CatalogField::class, 'module_id')->orderBy('orden');
    }

    public function records(): HasMany
    {
        return $this->hasMany(CatalogRecord::class, 'module_id')->latest();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Genera slug único a partir del nombre.
     */
    public static function generarSlug(string $nombre): string
    {
        $base = Str::slug($nombre, '_');
        $slug = $base;
        $i    = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }

        return $slug;
    }
}
