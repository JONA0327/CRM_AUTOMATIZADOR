<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\DiseaseAiService;
use Illuminate\Http\Request;

class DiseaseSuggestionController extends Controller
{
    public function suggestProducts(Request $request, DiseaseAiService $service)
    {
        $data = $request->validate([
            'disease_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'only_same_country' => 'boolean',
            'include_cross_country' => 'boolean',
            'limit' => 'nullable|integer|min:1|max:6',
        ]);

        $query = Product::query();

        if (! empty($data['country']) && ($data['only_same_country'] ?? false)) {
            $query->where('country', $data['country']);
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        $suggestions = $service->generateProductSuggestions(
            $data['disease_name'],
            $data['description'] ?? null,
            $products,
            [
                'target_country' => $data['country'] ?? null,
                'only_same_country' => (bool) ($data['only_same_country'] ?? false),
                'include_cross_country' => (bool) ($data['include_cross_country'] ?? true),
                'limit' => $data['limit'] ?? 3,
            ]
        );

        return response()->json([
            'data' => $suggestions,
        ]);
    }

    public function generateInformation(Request $request, DiseaseAiService $service)
    {
        $data = $request->validate([
            'disease_name' => 'required|string|max:255',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'focus' => 'nullable|string',
        ]);

        $products = Product::whereIn('id', $data['product_ids'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $information = $service->generateDiseaseInformation(
            $data['disease_name'],
            $products,
            [
                'focus' => $data['focus'] ?? null,
            ]
        );

        return response()->json([
            'data' => $information,
        ]);
    }
}
