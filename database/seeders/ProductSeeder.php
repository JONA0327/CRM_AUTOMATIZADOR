<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'category' => 'Inmunológicos',
                'name' => 'Transfer Factor Plus',
                'key_points' => json_encode([
                    'Fortalece el sistema inmunológico',
                    'Contiene factores de transferencia',
                    'Mejora la respuesta inmune natural'
                ]),
                'information' => 'Transfer Factor Plus es un suplemento avanzado que ayuda a fortalecer y educar el sistema inmunológico mediante factores de transferencia naturales.',
                'disease' => 'Inmunidad comprometida',
                'country' => 'Estados Unidos',
                'dosage' => json_encode([
                    'adults' => '1-2 cápsulas al día',
                    'children' => '1 cápsula al día',
                    'frequency' => 'Con las comidas'
                ]),
                'image' => null,
                'video' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Digestivos',
                'name' => 'PRO-TF',
                'key_points' => json_encode([
                    'Proteína completa de alta calidad',
                    'Contiene factores de transferencia',
                    'Ideal para recuperación muscular'
                ]),
                'information' => 'PRO-TF combina proteína de suero de leche premium con factores de transferencia para optimizar la salud muscular e inmunológica.',
                'disease' => 'Deficiencia proteica',
                'country' => 'México',
                'dosage' => json_encode([
                    'adults' => '1 medida (30g) al día',
                    'athletes' => '2 medidas al día',
                    'frequency' => 'Post-entrenamiento o entre comidas'
                ]),
                'image' => null,
                'video' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Cardiovasculares',
                'name' => 'CardioRite',
                'key_points' => json_encode([
                    'Apoya la salud cardiovascular',
                    'Contiene CoQ10 y antioxidantes',
                    'Mejora la circulación sanguínea'
                ]),
                'information' => 'CardioRite es una fórmula especializada que combina nutrientes esenciales para mantener un corazón saludable y una circulación óptima.',
                'disease' => 'Problemas cardiovasculares',
                'country' => 'Colombia',
                'dosage' => json_encode([
                    'adults' => '2 cápsulas al día',
                    'seniors' => '3 cápsulas al día',
                    'frequency' => 'Con las comidas principales'
                ]),
                'image' => null,
                'video' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Antioxidantes',
                'name' => 'Belle Vie',
                'key_points' => json_encode([
                    'Potente antioxidante natural',
                    'Protege contra el envejecimiento',
                    'Mejora la salud de la piel'
                ]),
                'information' => 'Belle Vie es un suplemento antioxidante avanzado que ayuda a combatir los radicales libres y mantener una apariencia juvenil.',
                'disease' => 'Envejecimiento prematuro',
                'country' => 'Brasil',
                'dosage' => json_encode([
                    'adults' => '2 cápsulas al día',
                    'women' => '3 cápsulas al día',
                    'frequency' => 'En ayunas y antes de dormir'
                ]),
                'image' => null,
                'video' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Digestivos',
                'name' => 'Digestive Formula',
                'key_points' => json_encode([
                    'Mejora la digestión',
                    'Contiene enzimas naturales',
                    'Reduce la inflamación intestinal'
                ]),
                'information' => 'Digestive Formula es una mezcla de enzimas digestivas y probióticos que ayuda a optimizar la salud intestinal.',
                'disease' => 'Problemas digestivos',
                'country' => 'Argentina',
                'dosage' => json_encode([
                    'adults' => '1-2 cápsulas antes de cada comida',
                    'children' => '1 cápsula antes de comidas',
                    'frequency' => 'Antes de las comidas principales'
                ]),
                'image' => null,
                'video' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Inmunológicos',
                'name' => 'Transfer Factor Core',
                'key_points' => json_encode([
                    'Fórmula básica de factores de transferencia',
                    'Soporte inmunológico diario',
                    'Fácil absorción'
                ]),
                'information' => 'Transfer Factor Core proporciona soporte inmunológico básico mediante factores de transferencia para uso diario.',
                'disease' => 'Inmunidad baja',
                'country' => 'Chile',
                'dosage' => json_encode([
                    'adults' => '1 cápsula al día',
                    'maintenance' => '1 cápsula cada 2 días',
                    'frequency' => 'Con el desayuno'
                ]),
                'image' => null,
                'video' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}