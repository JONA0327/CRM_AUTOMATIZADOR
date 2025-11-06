<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'information_mode',
        'information',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function recommendations()
    {
        return $this->hasMany(DiseaseProductRecommendation::class);
    }

    public function scopeByCountry($query, ?string $country)
    {
        if ($country) {
            return $query->where('country', $country);
        }

        return $query;
    }

    public function getManualRecommendationsAttribute()
    {
        return $this->recommendations->where('recommendation_type', 'manual');
    }

    public function getAiRecommendationsAttribute()
    {
        return $this->recommendations->where('recommendation_type', 'ai');
    }
}
