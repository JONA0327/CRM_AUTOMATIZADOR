<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogRecord extends Model
{
    protected $fillable = [
        'module_id',
        'datos',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CatalogModule::class, 'module_id');
    }
}
