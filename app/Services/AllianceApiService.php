<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AllianceApiService
{
    private const BASE_URL = 'https://www.alliancegenome.org/api';

    private const AUTOCOMPLETE_ENDPOINT = '/search_autocomplete';

    private const GENE_ENDPOINT = '/gene';

    /**
     * Search for genes using autocomplete API
     */
    public function searchGenes(string $query, ?string $species = null, bool $limitSpecies = true): array
    {
        try {
            $response = Http::get(self::BASE_URL.self::AUTOCOMPLETE_ENDPOINT, [
                'q' => $query,
                'category' => 'gene',
            ]);

            if (! $response->successful()) {
                Log::error('Alliance API search failed', [
                    'query' => $query,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [];
            }

            if ($limitSpecies || $species) {
                $results = [];
                if ($species) {
                    $results = collect($response->json('results', []))
                        ->filter(fn ($item) => str_starts_with($item['primaryKey'], $species)
                        )
                        ->values()
                        ->all();
                } else {
                    $results = collect($response->json('results', []))
                        ->filter(fn ($item) => str_starts_with($item['primaryKey'], 'RGD:') ||
                            str_starts_with($item['primaryKey'], 'MGI:') ||
                            str_starts_with($item['primaryKey'], 'HGNC:')
                        )
                        ->values()
                        ->all();
                }

                return $results;
            }

            return $response->json('results', []);
        } catch (\Exception $e) {
            Log::error('Alliance API search exception', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get detailed gene information by Alliance ID
     */
    public function getGeneDetails(string $allianceId): ?array
    {
        try {
            $encodedId = urlencode($allianceId);
            $response = Http::get(self::BASE_URL.self::GENE_ENDPOINT."/{$encodedId}");

            if (! $response->successful()) {
                Log::error('Alliance API gene details failed', [
                    'alliance_id' => $allianceId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Alliance API gene details exception', [
                'alliance_id' => $allianceId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find gene by symbol and species prefix (MGI, RGD, HGNC)
     */
    public function findGeneBySymbolAndSpecies(string $symbol, string $speciesPrefix): ?array
    {
        $results = $this->searchGenes($symbol);

        if (empty($results)) {
            return null;
        }

        // Filter for the specified species
        $filteredGene = collect($results)->first(fn ($item) => str_starts_with($item['primaryKey'], $speciesPrefix.':')
        );

        if (! $filteredGene) {
            return null;
        }

        // Get detailed information
        return $this->getGeneDetails($filteredGene['primaryKey']);
    }

    /**
     * Find mouse or rat gene by symbol
     */
    public function findMouseOrRatGene(string $symbol): ?array
    {
        $results = $this->searchGenes($symbol);

        if (empty($results)) {
            return null;
        }

        // Filter for mouse (MGI) or rat (RGD) IDs
        $filteredGene = collect($results)->first(fn ($item) => str_starts_with($item['primaryKey'], 'MGI:') ||
            str_starts_with($item['primaryKey'], 'RGD:')
        );

        if (! $filteredGene) {
            return null;
        }

        // Get detailed information
        return $this->getGeneDetails($filteredGene['primaryKey']);
    }
}
