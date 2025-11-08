<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DiseaseAiService
{
    protected string $model;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Genera sugerencias de productos utilizando IA y realiza un fallback heurístico cuando no hay API.
     *
     * @param string $diseaseName
     * @param string|null $description
     * @param Collection<int, Product> $products
     * @param array $options
     * @return array<int, array{product_id:int, product_name:string, reason:string, analysis_points:array<int, string>, confidence:int}>
     */
    public function generateProductSuggestions(string $diseaseName, ?string $description, Collection $products, array $options = []): array
    {
        $options = array_merge([
            'limit' => 3,
            'threshold' => 0.35,
        ], $options);

        $payload = [
            'disease_name' => $diseaseName,
            'description' => $description,
            'limit' => (int) $options['limit'],
            'threshold' => (float) $options['threshold'],
        ];

        $productDataset = $products->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'country' => $product->country,
                'key_points' => $product->key_points ?: [],
                'information' => $product->information,
            ];
        })->values()->all();

        if ($this->apiKey) {
            $response = $this->callOpenAi($payload, $productDataset);

            if ($response) {
                return $this->mapAiResponseToProducts($response, $products);
            }
        }

        return $this->heuristicSuggestions($payload, $products);
    }

    /**
     * Genera información sobre la enfermedad utilizando IA con fallback.
     */
    public function generateDiseaseInformation(string $diseaseName, Collection $products, array $options = []): string
    {
        $options = array_merge([
            'focus' => null,
            'tone' => 'informative',
        ], $options);

        $productSummaries = $products->map(function (Product $product) {
            $highlights = implode('; ', Arr::wrap($product->key_points));

            return sprintf(
                '%s (%s) - País: %s. Puntos clave: %s. Información: %s',
                $product->name,
                $product->category,
                $product->country,
                $highlights ?: 'Sin puntos clave registrados',
                $product->information ?: 'Sin información adicional'
            );
        })->implode("\n- ");

        if ($this->apiKey) {
            $prompt = $this->buildInformationPrompt($diseaseName, $productSummaries, $options);

            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout(20)
                    ->post('https://api.openai.com/v1/responses', [
                        'model' => $this->model,
                        'input' => $prompt,
                        'max_output_tokens' => 400,
                        'temperature' => 0.4,
                    ]);

                if ($response->successful()) {
                    $content = data_get($response->json(), 'output.0.content.0.text');

                    if (is_string($content) && Str::of($content)->isNotEmpty()) {
                        return trim($content);
                    }
                }
            } catch (\Throwable $exception) {
                Log::warning('Error generating disease information with OpenAI', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $this->heuristicInformation($diseaseName, $products, $options);
    }

    protected function callOpenAi(array $payload, array $dataset): ?array
    {
        $instruction = $this->buildSuggestionPrompt($payload, $dataset);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $this->model,
                    'input' => $instruction,
                    'max_output_tokens' => 600,
                    'temperature' => 0.2,
                ]);

            if ($response->failed()) {
                Log::warning('OpenAI suggestion request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $content = data_get($response->json(), 'output.0.content.0.text');

            if (! is_string($content)) {
                return null;
            }

            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('OpenAI suggestion response was not valid JSON', [
                    'error' => json_last_error_msg(),
                    'content' => Str::limit($content, 500),
                ]);

                return null;
            }

            return $decoded;
        } catch (\Throwable $exception) {
            Log::warning('Error calling OpenAI for suggestions', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function buildSuggestionPrompt(array $payload, array $dataset): string
    {
        $datasetSummary = collect($dataset)->map(function (array $product) {
            $points = implode('; ', Arr::wrap($product['key_points'] ?? []));

            return sprintf(
                '%s | Categoría: %s | País: %s | Puntos clave: %s | Información: %s',
                $product['name'],
                $product['category'],
                $product['country'],
                $points ?: 'Sin puntos clave',
                $product['information'] ?: 'Sin información registrada'
            );
        })->implode("\n- ");

        $rules = <<<'RULES'
- Prioriza precisión clínica y coherencia con los puntos clave registrados.
- Nunca afirmes que un producto cura, sana ni garantices resultados.
- Enfoca la recomendación en apoyo, acompañamiento y refuerzo del bienestar.
- Si no encuentras una coincidencia sólida, responde con una lista vacía.
- Devuelve un JSON válido con la estructura:
{
  "recommendations": [
    {"product_id": 0, "reason": "", "analysis_points": ["", ""], "confidence": 0}
  ]
}
- La propiedad confidence debe ser un número entre 0 y 100 que refleje la fuerza de la coincidencia.
- Incluye solo los productos verdaderamente relevantes (1 a 3 máximo) y ordenados del más preciso al menos preciso.
RULES;

        return sprintf(
            "Genera sugerencias de productos 4Life para apoyar la condición denominada '%s'.\n" .
            "Descripción proporcionada: %s\n" .
            "Límite de sugerencias: %d\n" .
            "Base de datos de productos disponibles:\n- %s\n\n%s\n" .
            "Responde únicamente con el JSON solicitado sin texto adicional.",
            $payload['disease_name'],
            $payload['description'] ?: 'Sin descripción adicional',
            $payload['limit'],
            $datasetSummary,
            $rules
        );
    }

    protected function mapAiResponseToProducts(array $response, Collection $products): array
    {
        return collect(data_get($response, 'recommendations', []))
            ->map(fn ($item) => $this->mapSuggestionItem($item, $products))
            ->filter()
            ->values()
            ->all();
    }

    protected function mapSuggestionItem(array $item, Collection $products): ?array
    {
        $product = null;

        if ($productId = data_get($item, 'product_id')) {
            $product = $products->firstWhere('id', (int) $productId);
        }

        if (! $product && ($name = data_get($item, 'product_name'))) {
            $product = $products->first(function (Product $candidate) use ($name) {
                return Str::lower($candidate->name) === Str::lower($name);
            }) ?? $products->first(function (Product $candidate) use ($name) {
                return Str::of($candidate->name)->lower()->contains(Str::lower($name));
            });
        }

        if (! $product) {
            return null;
        }

        $confidence = (int) round(min(100, max(0, data_get($item, 'confidence', 0))));

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'reason' => data_get($item, 'reason', ''),
            'analysis_points' => Arr::wrap(data_get($item, 'analysis_points', [])),
            'confidence' => $confidence,
        ];
    }

    protected function heuristicSuggestions(array $payload, Collection $products): array
    {
        $limit = max(1, (int) $payload['limit']);
        $threshold = isset($payload['threshold']) ? max(0, min(1, (float) $payload['threshold'])) : 0.35;
        $keywords = $this->extractKeywords($payload['disease_name'], $payload['description'] ?? null);
        $keywordsLower = $keywords->map(fn ($keyword) => Str::lower($keyword));
        $diseasePhrase = Str::of($payload['disease_name'])->lower()->value();

        $scored = $products->map(function (Product $product) use ($keywordsLower, $diseasePhrase) {
            $infoText = Str::lower($product->information ?? '');
            $keyPoints = collect(Arr::wrap($product->key_points ?? []))
                ->map(fn ($point) => trim($point))
                ->filter();
            $keyPointsLower = $keyPoints->map(fn ($point) => Str::lower($point));

            $score = 0;
            $matchedPoints = [];

            foreach ($keywordsLower as $keyword) {
                if (Str::length($keyword) < 4) {
                    continue;
                }

                if (Str::contains(Str::lower($product->name), $keyword)) {
                    $score += 6;
                }

                if ($infoText !== '' && Str::contains($infoText, $keyword)) {
                    $score += 3.5;
                }

                foreach ($keyPointsLower as $index => $keyPoint) {
                    if (Str::contains($keyPoint, $keyword)) {
                        $score += 5.5;
                        $matchedPoints[] = $keyPoints[$index];
                    }
                }
            }

            if ($diseasePhrase !== '') {
                $referenceText = $keyPointsLower->implode(' ') . ' ' . $infoText;
                $similarityBoost = 0;

                if ($referenceText !== '') {
                    similar_text($diseasePhrase, $referenceText, $percentage);
                    $similarityBoost = $percentage / 18;
                }

                $score += $similarityBoost;
            }

            return [
                'product' => $product,
                'score' => $score,
                'matched_points' => collect($matchedPoints)->unique()->values()->all(),
            ];
        })->filter(fn (array $item) => $item['score'] > 0)->sortByDesc('score')->values();

        if ($scored->isEmpty()) {
            return [];
        }

        $maxScore = max(1, $scored->max('score'));

        $mapped = $scored
            ->take($limit)
            ->map(function (array $item) use ($maxScore) {
                /** @var Product $product */
                $product = $item['product'];
                $confidence = (int) round(($item['score'] / $maxScore) * 100);
                $matchedPoints = collect($item['matched_points']);
                $analysisPoints = $matchedPoints->take(3)->all();

                if (empty($analysisPoints)) {
                    $analysisPoints = array_slice(Arr::wrap($product->key_points ?? []), 0, 3);
                }

                $summaryFragments = [];

                if (! empty($analysisPoints)) {
                    $summaryFragments[] = 'puntos clave: ' . implode(', ', array_slice($analysisPoints, 0, 2));
                }

                if ($product->category) {
                    $summaryFragments[] = 'categoría ' . $product->category;
                }

                $summary = $summaryFragments ? implode(' y ', $summaryFragments) : 'su perfil nutracéutico';

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'reason' => sprintf('Coincidencia del %d%% basada en %s.', $confidence, $summary),
                    'analysis_points' => $analysisPoints,
                    'confidence' => $confidence,
                ];
            })
            ->values();

        $filtered = $mapped
            ->filter(fn (array $item) => ($item['confidence'] ?? 0) >= (int) round($threshold * 100))
            ->values();

        if ($filtered->isEmpty() && $mapped->isNotEmpty()) {
            return [$mapped->first()];
        }

        return $filtered->all();
    }

    protected function extractKeywords(string $diseaseName, ?string $description): Collection
    {
        $raw = trim($diseaseName . ' ' . ($description ?? ''));

        if ($raw === '') {
            return collect();
        }

        $tokens = preg_split('/[\s,.;:\\-]+/u', Str::lower($raw), -1, PREG_SPLIT_NO_EMPTY);

        return collect($tokens)
            ->map(fn (string $token) => Str::of($token)->replaceMatches('/[^a-z0-9áéíóúñü]/u', '')->value())
            ->filter(fn (?string $token) => $token && Str::length($token) >= 4)
            ->unique()
            ->values();
    }

    protected function buildInformationPrompt(string $diseaseName, string $productSummaries, array $options): string
    {
        $focus = $options['focus'] ? 'Aspectos a destacar: ' . $options['focus'] : 'Sin aspectos específicos a resaltar.';

        $rules = <<<'RULES'
- Mantén un tono profesional, empático e informativo.
- Describe cómo los productos apoyan o complementan el bienestar, evitando promesas de curación.
- Explica la lógica que conecta cada producto con la condición mencionada.
- Limita la longitud a 3-4 párrafos breves.
RULES;

        return sprintf(
            "Genera una descripción sobre la condición '%s' destacando cómo los siguientes productos de 4Life pueden apoyar a la pe" .
            "rsona sin afirmar curación ni resultados garantizados.\nProductos seleccionados:\n- %s\n%s\n%s",
            $diseaseName,
            $productSummaries,
            $focus,
            $rules
        );
    }

    protected function heuristicInformation(string $diseaseName, Collection $products, array $options): string
    {
        $intro = sprintf(
            'La condición %s requiere acompañamiento integral enfocado en hábitos saludables y soporte del sistema inmunológico y metabolico según corresponda.',
            Str::lower($diseaseName)
        );

        $body = $products->map(function (Product $product) {
            $points = implode('; ', Arr::wrap($product->key_points) ?: []);

            return sprintf(
                '- %s (%s, %s): se recomienda como apoyo gracias a sus características registradas: %s.',
                $product->name,
                $product->category,
                $product->country,
                $points ?: 'sin puntos clave disponibles'
            );
        })->implode(' ');

        $closing = 'Se recomienda complementar estas opciones con orientación profesional y hábitos consistentes. Estas sugerencias no sustituyen atención médica.';

        return trim($intro . ' ' . $body . ' ' . $closing);
    }
}
