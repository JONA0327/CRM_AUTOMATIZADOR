<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Disease extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'category',
        'symptoms',
        'treatment',
        'prevention',
        'suggested',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
