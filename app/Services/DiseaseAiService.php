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
     * @return array{same_country: array<int, array>, cross_country: array<int, array>}
     */
    public function generateProductSuggestions(string $diseaseName, ?string $description, Collection $products, array $options = []): array
    {
        $options = array_merge([
            'target_country' => null,
            'only_same_country' => false,
            'include_cross_country' => true,
            'limit' => 3,
        ], $options);

        $payload = [
            'disease_name' => $diseaseName,
            'description' => $description,
            'target_country' => $options['target_country'],
            'only_same_country' => (bool) $options['only_same_country'],
            'include_cross_country' => (bool) $options['include_cross_country'],
            'limit' => (int) $options['limit'],
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
  "same_country": [
    {"product_name": "", "reason": "", "analysis_points": ["", ""]}
  ],
  "cross_country": [
    {"product_name": "", "reason": "", "analysis_points": ["", ""], "country": ""}
  ]
}
- Incluye productos de otros países solo si include_cross_country es verdadero.
- Los productos de otros países deben explicitar por qué pueden ser útiles y deben considerarse como sugerencias sujetas a aprobación.
RULES;

        return sprintf(
            "Genera sugerencias de productos 4Life para apoyar la condición denominada '%s'.\n" .
            "Descripción proporcionada: %s\n" .
            "País objetivo: %s\n" .
            "¿Solo mismo país?: %s\n" .
            "¿Incluir otros países?: %s\n" .
            "Límite de sugerencias por grupo: %d\n" .
            "Base de datos de productos disponibles:\n- %s\n\n%s\n" .
            "Responde únicamente con el JSON solicitado sin texto adicional.",
            $payload['disease_name'],
            $payload['description'] ?: 'Sin descripción adicional',
            $payload['target_country'] ?: 'Sin país especificado',
            $payload['only_same_country'] ? 'Sí' : 'No',
            $payload['include_cross_country'] ? 'Sí' : 'No',
            $payload['limit'],
            $datasetSummary,
            $rules
        );
    }

    protected function mapAiResponseToProducts(array $response, Collection $products): array
    {
        $sameCountry = collect(data_get($response, 'same_country', []))
            ->map(fn ($item) => $this->mapSuggestionItem($item, $products, false))
            ->filter()
            ->values()
            ->all();

        $crossCountry = collect(data_get($response, 'cross_country', []))
            ->map(fn ($item) => $this->mapSuggestionItem($item, $products, true))
            ->filter()
            ->values()
            ->all();

        return [
            'same_country' => $sameCountry,
            'cross_country' => $crossCountry,
        ];
    }

    protected function mapSuggestionItem(array $item, Collection $products, bool $isCrossCountry): ?array
    {
        $name = data_get($item, 'product_name');

        if (! $name) {
            return null;
        }

        $product = $products->first(function (Product $product) use ($name) {
            return Str::lower($product->name) === Str::lower($name);
        }) ?? $products->first(function (Product $product) use ($name) {
            return Str::of($product->name)->lower()->contains(Str::lower($name));
        });

        if (! $product) {
            return null;
        }

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'country' => $product->country,
            'reason' => data_get($item, 'reason', ''),
            'analysis_points' => Arr::wrap(data_get($item, 'analysis_points', [])),
            'is_cross_country' => $isCrossCountry,
        ];
    }

    protected function heuristicSuggestions(array $payload, Collection $products): array
    {
        $limit = (int) $payload['limit'];
        $country = $payload['target_country'];
        $onlySameCountry = (bool) $payload['only_same_country'];
        $includeCrossCountry = (bool) $payload['include_cross_country'];
        $description = Str::lower($payload['description'] ?? '');
        $keywords = collect(Str::of($payload['disease_name'])->lower()->explode(' '))
            ->merge(Str::of($description)->explode(' '))
            ->filter()
            ->unique();

        $scored = $products->map(function (Product $product) use ($keywords) {
            $haystack = Str::of(
                strtolower(($product->information ?? '') . ' ' . implode(' ', Arr::wrap($product->key_points) ?: []))
            );

            $score = $keywords->reduce(function ($carry, $keyword) use ($haystack) {
                if (strlen($keyword) < 4) {
                    return $carry;
                }

                return $haystack->contains($keyword) ? $carry + 1 : $carry;
            }, 0);

            return [
                'product' => $product,
                'score' => $score,
            ];
        })->sortByDesc('score')->values();

        $sameCountry = $scored->filter(function (array $item) use ($country) {
            return $country ? $item['product']->country === $country : true;
        })->take($limit)->map(function (array $item) {
            return [
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'country' => $item['product']->country,
                'reason' => 'Sugerencia basada en coincidencias de palabras clave con la información registrada.',
                'analysis_points' => Arr::wrap(array_slice($item['product']->key_points ?? [], 0, 2)),
                'is_cross_country' => false,
            ];
        })->values()->all();

        $crossCountry = [];

        if (! $onlySameCountry && $includeCrossCountry) {
            $crossCountry = $scored->reject(function (array $item) use ($country) {
                return $country ? $item['product']->country === $country : false;
            })->take($limit)->map(function (array $item) {
                return [
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'country' => $item['product']->country,
                    'reason' => 'Propuesta alternativa basada en atributos similares aunque el país sea distinto. Requiere aprobación manual.',
                    'analysis_points' => Arr::wrap(array_slice($item['product']->key_points ?? [], 0, 2)),
                    'is_cross_country' => true,
                ];
            })->values()->all();
        }

        return [
            'same_country' => $sameCountry,
            'cross_country' => $crossCountry,
        ];
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
