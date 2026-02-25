<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'available',
        'suggested',
        'video',
    ];

    protected $casts = [
        'available' => 'boolean',
        'price'     => 'decimal:2',
    ];

    protected $appends = ['image_url', 'video_url', 'video_es_archivo'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    /** Devuelve la URL reproducible del video (sea externo o subido). */
    public function getVideoUrlAttribute(): ?string
    {
        if (! $this->video) return null;
        return str_starts_with($this->video, 'http') ? $this->video : Storage::url($this->video);
    }

    /** True si el video está almacenado localmente (no es una URL externa). */
    public function getVideoEsArchivoAttribute(): bool
    {
        return $this->video && ! str_starts_with($this->video, 'http');
    }
}
