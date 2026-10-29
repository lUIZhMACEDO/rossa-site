<?php

namespace App\Models;

use App\Services\AllianceApiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Gene extends Model
{
    protected $fillable = [
        'alliance_id',
        'symbol',
        'name',
        'species_taxonId',
        'species_name',
        'data',
        'geneSynopsis',
        'biotype',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public function animalModels(): HasMany
    {
        return $this->hasMany(AnimalModel::class);
    }

    public function studies(): HasManyThrough
    {
        return $this->hasManyThrough(
            Study::class,
            AnimalModel::class,
            'gene_id',
            'id',
            'id',
            'study_id'
        );
    }

    /**
     * Update or create a gene from Alliance API data
     */
    public static function updateOrCreateFromAlliance(string $allianceId): ?self
    {
        $apiService = new AllianceApiService;
        $geneData = $apiService->getGeneDetails($allianceId);

        if (! $geneData) {
            return null;
        }

        return self::updateOrCreate(
            ['alliance_id' => $allianceId],
            self::mapAllianceDataToModel($geneData)
        );
    }

    /**
     * Update or create a gene by searching with symbol and species
     */
    public static function updateOrCreateBySymbolAndSpecies(string $symbol, string $speciesPrefix): ?self
    {
        $apiService = new AllianceApiService;
        $geneData = $apiService->findGeneBySymbolAndSpecies($symbol, $speciesPrefix);

        if (! $geneData) {
            return null;
        }

        return self::updateOrCreate(
            ['alliance_id' => $geneData['id']],
            self::mapAllianceDataToModel($geneData)
        );
    }

    /**
     * Update or create a gene by searching for mouse or rat genes
     */
    public static function updateOrCreateMouseOrRatGene(string $symbol): ?self
    {
        $apiService = new AllianceApiService;
        $geneData = $apiService->findMouseOrRatGene($symbol);

        if (! $geneData) {
            return null;
        }

        return self::updateOrCreate(
            ['alliance_id' => $geneData['id']],
            self::mapAllianceDataToModel($geneData)
        );
    }

    /**
     * Map Alliance API data to model attributes
     */
    private static function mapAllianceDataToModel(array $geneData): array
    {
        return [
            'symbol' => $geneData['gene']['geneSymbol']['formatText'] ?? null,
            'name' => $geneData['gene']['geneFullName']['formatText'] ?? null,
            'species_taxonId' => $geneData['gene']['taxon']['curie'] ?? null,
            'species_name' => $geneData['gene']['taxon']['name'] ?? null,
            'data' => $geneData,
            'geneSynopsis' => $geneData['gene']['relatedNotes'][0]['freeText'] ?? null,
            'biotype' => $geneData['gene']['geneType']['name'] ?? null,
        ];
    }

    /**
     * Search genes by symbol prefix
     */
    public static function searchBySymbol(string $symbol, ?string $species = null)
    {
        $like_lower = strtolower($symbol).'%';

        return self::whereRaw('LOWER(symbol) LIKE ?', [$like_lower])
            ->orderByRaw('LOWER(symbol)')
            ->limit(20)
            ->get()
            ->filter(function ($gene) use ($species) {
                if ($species) {
                    return str_starts_with($gene['alliance_id'], $species.':');
                }

                return true;
            })
            ->toArray();
    }

    /**
     * Refresh gene data from Alliance API
     */
    public function refreshFromAlliance(): bool
    {
        $apiService = new AllianceApiService;
        $geneData = $apiService->getGeneDetails($this->alliance_id);

        if (! $geneData) {
            return false;
        }

        $this->fill(self::mapAllianceDataToModel($geneData));

        return $this->save();
    }

    /**
     * Get the Alliance URL for this gene
     */
    public function getAllianceUrl(): string
    {
        return 'https://www.alliancegenome.org/gene/'.urlencode($this->alliance_id);
    }

    /**
     * Check if this gene is a mouse gene
     */
    public function isMouse(): bool
    {
        return str_starts_with($this->alliance_id, 'MGI:');
    }

    /**
     * Check if this gene is a rat gene
     */
    public function isRat(): bool
    {
        return str_starts_with($this->alliance_id, 'RGD:');
    }

    /**
     * Check if this gene is a human gene
     */
    public function isHuman(): bool
    {
        return str_starts_with($this->alliance_id, 'HGNC:');
    }

    /**
     * Get synonyms from the data field
     */
    public function getSynonyms(): array
    {
        return $this->data['synonyms'] ?? [];
    }

    /**
     * Get genome locations from the data field
     */
    public function getGenomeLocations(): array
    {
        return $this->data['genomeLocations'] ?? [];
    }
}
