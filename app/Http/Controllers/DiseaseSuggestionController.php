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
            'limit' => 'nullable|integer|min:1|max:6',
        ]);

        $products = Product::orderBy('category')->orderBy('name')->get();

        $suggestions = $service->generateProductSuggestions(
            $data['disease_name'],
            $data['description'] ?? null,
            $products,
            [
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
