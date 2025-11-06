<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiseaseProductRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'disease_id',
        'product_id',
        'recommendation_type',
        'is_cross_country',
        'is_approved',
        'reasoning',
        'analysis',
    ];

    protected $casts = [
        'is_cross_country' => 'boolean',
        'is_approved' => 'boolean',
        'analysis' => 'array',
    ];

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
