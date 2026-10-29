<?php

namespace Database\Seeders;

use App\Models\AnimalLine;
use App\Services\OlsApiService;
use Illuminate\Database\Seeder;

class OlsSeeder extends Seeder
{
    public function run(): void
    {
        $ols = new OlsApiService;

        $mouseStrainData = $ols->getMouseStrains();
        $ratStrainData = $ols->getRatStrains();

        $this->storeTerms($mouseStrainData, 'mouse');
        $this->storeTerms($ratStrainData, 'rat');
    }

    private function storeTerms(array $data, string $species): void
    {
        foreach ($data['_embedded']['terms'] ?? [] as $item) {
            \Log::info('About to save animal line:', [
                'iri' => $item['iri'] ?? 'MISSING',
                'synonyms' => $item['synonyms'] ?? 'MISSING',
                'definition_citation' => $item['annotation']['definition_citation'] ?? 'MISSING',
            ]);

            AnimalLine::updateOrCreate(
                ['iri' => $item['iri']],
                [
                    'label' => $item['label'],
                    'description' => $this->cleanDescription($item['description'] ?? null),
                    'ontology' => $item['ontology_name'] ?? 'efo',
                    'synonyms' => $item['synonyms'] ?? [],
                    'obo_id' => $item['obo_id'] ?? null,
                    'definition_citation' => $item['annotation']['definition_citation'] ?? [],
                    'species' => $species,
                ]
            );
        }
    }

    private function cleanDescription($description)
    {
        $maxLength = 500;

        if (is_string($description)) {
            return mb_substr($description, 0, $maxLength);
        }

        if (is_array($description)) {
            $joined = implode('; ', array_filter($description, 'is_string'));
            if (! empty($joined)) {
                return mb_substr($joined, 0, $maxLength);
            }
        }

        return 'Not provided';
    }
}
