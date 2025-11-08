<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\DiseaseProductRecommendation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiseaseController extends Controller
{
    public function index(Request $request)
    {
        $diseases = Schema::hasTable('diseases')
            ? Disease::with(['recommendations.product'])->orderBy('name')->get()
            : collect();

        $products = Schema::hasTable('products')
            ? Product::orderBy('category')->orderBy('name')->get()
            : collect();

        $diseasesByCountry = $diseases->groupBy('country')->sortKeys();
        $countries = $diseases->pluck('country')->unique()->values()->sort()->all();
        $availableCountries = Schema::hasTable('products')
            ? Product::select('country')->distinct()->orderBy('country')->pluck('country')->all()
            : [];

        return view('diseases.index', [
            'diseasesByCountry' => $diseasesByCountry,
            'countries' => $countries,
            'availableCountries' => $availableCountries,
            'products' => $products,
        ]);
    }

    public function list(Request $request)
    {
        $country = $request->query('country');

        if (! Schema::hasTable('diseases')) {
            return response()->json([
                'data' => [],
            ]);
        }

        $diseases = Disease::with(['recommendations.product'])
            ->byCountry($country)
            ->orderBy('name')
            ->get()
            ->map(function (Disease $disease) {
                return [
                    'id' => $disease->id,
                    'name' => $disease->name,
                    'country' => $disease->country,
                    'information_mode' => $disease->information_mode,
                    'information' => $disease->information,
                    'manual_count' => $disease->manual_recommendations->count(),
                    'ai_count' => $disease->ai_recommendations->count(),
                ];
            });

        return response()->json([
            'data' => $diseases,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        $disease = DB::transaction(function () use ($data) {
            /** @var Disease $disease */
            $disease = Disease::create(Arr::only($data, ['name', 'country', 'information_mode', 'information', 'metadata']));

            $this->syncRecommendations($disease, $data);

            return $disease->load('recommendations.product');
        });

        return response()->json([
            'message' => 'Índice creado correctamente.',
            'data' => $this->formatDiseaseResource($disease),
        ], Response::HTTP_CREATED);
    }

    public function show(Disease $disease)
    {
        $disease->load('recommendations.product');

        return response()->json([
            'data' => $this->formatDiseaseResource($disease),
        ]);
    }

    public function update(Request $request, Disease $disease)
    {
        $data = $this->validateRequest($request, $disease->id);

        $disease = DB::transaction(function () use ($disease, $data) {
            $disease->update(Arr::only($data, ['name', 'country', 'information_mode', 'information', 'metadata']));

            $this->syncRecommendations($disease, $data);

            return $disease->load('recommendations.product');
        });

        return response()->json([
            'message' => 'Índice actualizado correctamente.',
            'data' => $this->formatDiseaseResource($disease),
        ]);
    }

    public function destroy(Disease $disease)
    {
        $disease->delete();

        return response()->json([
            'message' => 'Índice eliminado correctamente.',
        ]);
    }

    public function approveSuggestion(DiseaseProductRecommendation $suggestion)
    {
        if (! $suggestion->is_cross_country) {
            return response()->json([
                'message' => 'La sugerencia seleccionada no requiere aprobación.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $suggestion->update(['is_approved' => true]);

        return response()->json([
            'message' => 'Sugerencia aprobada correctamente.',
        ]);
    }

    protected function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'information_mode' => 'required|in:manual,ai',
            'information' => 'nullable|string',
            'metadata' => 'nullable|array',
            'manual_recommendations' => 'array',
            'manual_recommendations.*.product_id' => 'required|exists:products,id',
            'manual_recommendations.*.reasoning' => 'required|string',
            'ai_recommendations' => 'array',
            'ai_recommendations.*.product_id' => 'required|exists:products,id',
            'ai_recommendations.*.reasoning' => 'required|string',
            'ai_recommendations.*.is_cross_country' => 'boolean',
            'ai_recommendations.*.is_approved' => 'boolean',
            'ai_recommendations.*.analysis_points' => 'array',
        ]);
    }

    protected function syncRecommendations(Disease $disease, array $data): void
    {
        $disease->recommendations()->delete();

        $recommendations = collect();

        foreach (Arr::get($data, 'manual_recommendations', []) as $item) {
            $recommendations->push([
                'product_id' => $item['product_id'],
                'recommendation_type' => 'manual',
                'is_cross_country' => false,
                'is_approved' => true,
                'reasoning' => $item['reasoning'],
                'analysis' => null,
            ]);
        }

        foreach (Arr::get($data, 'ai_recommendations', []) as $item) {
            $analysis = null;

            if (! empty($item['analysis_points']) && is_array($item['analysis_points'])) {
                $analysis = ['analysis_points' => $item['analysis_points']];
            }

            $recommendations->push([
                'product_id' => $item['product_id'],
                'recommendation_type' => 'ai',
                'is_cross_country' => (bool) ($item['is_cross_country'] ?? false),
                'is_approved' => (bool) ($item['is_approved'] ?? ! ($item['is_cross_country'] ?? false)),
                'reasoning' => $item['reasoning'],
                'analysis' => $analysis,
            ]);
        }

        if ($recommendations->isNotEmpty()) {
            $disease->recommendations()->createMany($recommendations->all());
        }
    }

    protected function formatDiseaseResource(Disease $disease): array
    {
        return [
            'id' => $disease->id,
            'name' => $disease->name,
            'country' => $disease->country,
            'information_mode' => $disease->information_mode,
            'information' => $disease->information,
            'metadata' => $disease->metadata,
            'recommendations' => $disease->recommendations->map(function (DiseaseProductRecommendation $recommendation) {
                return [
                    'id' => $recommendation->id,
                    'product' => [
                        'id' => $recommendation->product_id,
                        'name' => $recommendation->product?->name,
                        'country' => $recommendation->product?->country,
                        'category' => $recommendation->product?->category,
                    ],
                    'type' => $recommendation->recommendation_type,
                    'is_cross_country' => $recommendation->is_cross_country,
                    'is_approved' => $recommendation->is_approved,
                    'reasoning' => $recommendation->reasoning,
                    'analysis' => $recommendation->analysis,
                ];
            })->values()->all(),
        ];
    }
}
