<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name',
        'key_points',
        'information',
        'image',
        'image_name',
        'video',
        'video_name',
        'disease',
        'country',
        'dosage',
        'is_active'
    ];

    protected $casts = [
        'key_points' => 'array',
        'dosage' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Catálogo de productos disponibles
     */
    public static function getProductCatalog()
    {
        return [
            '4Life Transfer Factor' => [
                'TF Plus Tri-Factor Formula',
                'TF Avanzado Tri-Factor',
                'TF RioVida Stix Tri-Factor',
                'TF RioVida Liquido Tri-Factor',
                'TF RioVida BURST Tri-Factor',
                'TF BCV – Cardio',
                'TF Belle Vie',
                'TF Classic',
                'TF Collagen',
                'TF FeelRite',
                'TF Glucoach',
                'TF Glutamine Prime',
                'TF Immune Boost',
                'TF Immune Spray',
                'TF KBU',
                'TF Lung',
                'TF MalePro',
                'TF Masticable',
                'TF Metabolite',
                'TF Recall',
                'TF Reflexion',
                'TF Renewal',
                'TF Rite Start Kids & Teens',
                'TF Rite Start Men',
                'TF Rite Start Women',
                'TF Sleep Rite',
                'TF Vista'
            ],
            '4Life Elements' => ['Gold Factor', 'Zinc Factor'],
            '4Life Transform' => [
                'TF Burn',
                'TF Man',
                'TF Woman',
                'Pro TF Proteína Hidrolizada',
                'TF PreZoom',
                'TF ReNuvo',
                'TF ShapeRite'
            ],
            'Enummi Cuidado Personal' => [
                'Enummi Cuidado Personal',
                'Enummi Desodorante',
                'Enummi Jabón de Manos',
                'Enummi Loción Corporal',
                'Enummi Pasta Dental'
            ],
            'Äkwä Cuidado de Piel' => [
                'äKwä Fist Wave',
                'äKwä Lavapure',
                'äKwä Glacier Glow',
                'äKwä Precious Pool',
                'äKwä Royal Bath',
                'äKwä Ripple Refine',
                'äKwä RainBurst',
                'äKwä Life C'
            ],
            'Bienestar Fundamental' => [
                'Ácidos Grasos Esenciales',
                'BioGenistein Ultra',
                'Cal Mag Complex',
                'Calostro Fortificado',
                'Fibro AMJ',
                'Flex 4Life',
                'Gurmar',
                'Inner Sun',
                'Life C',
                'Multiplex',
                'Músculo Skeletal',
                'PBGS+ Antioxidante',
                'Stress Formula'
            ],
            'Energía & Nutrición' => ['Energy Go Stix', 'Nutra Start Blue'],
            'Digest 4Life' => [
                'Alove Vera',
                'Enzimas Digestivas',
                'Fibre System Plus',
                'Phytolax',
                'Pre/O Biotics',
                'Super Detox',
                'Tea 4Life'
            ]
        ];
    }

    /**
     * Países de América
     */
    public static function getAmericanCountries()
    {
        return [
            'Argentina',
            'Bolivia',
            'Brasil',
            'Canadá',
            'Chile',
            'Colombia',
            'Costa Rica',
            'Cuba',
            'Ecuador',
            'El Salvador',
            'Estados Unidos',
            'Guatemala',
            'Honduras',
            'México',
            'Nicaragua',
            'Panamá',
            'Paraguay',
            'Perú',
            'República Dominicana',
            'Uruguay',
            'Venezuela'
        ];
    }

    /**
     * Obtener imagen como data URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return 'data:image/jpeg;base64,' . $this->image;
        }
        return null;
    }

    /**
     * Obtener video como data URL
     */
    public function getVideoUrlAttribute()
    {
        if ($this->video) {
            return 'data:video/mp4;base64,' . $this->video;
        }
        return null;
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
