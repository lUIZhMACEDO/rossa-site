<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ChemblService
{
    public function getMoleculeDetails($chemblId)
    {
        $details = [];

        $molecule = Http::get("https://www.ebi.ac.uk/chembl/api/data/molecule/{$chemblId}.json")->json();

        $phaseMap = [
            0 => 'Preclinical',
            1 => 'Phase 1',
            2 => 'Phase 2',
            3 => 'Phase 3',
            4 => 'Approved',
        ];

        $maxPhase = (int) ($molecule['max_phase'] ?? -1);
        $phaseStatus = $phaseMap[$maxPhase] ?? 'Unknown';

        $details['molecule'] = $molecule;
        $details['molecule']['max_phase_label'] = $phaseStatus;  // ✅ Add this
        // $details['xrefs'] = $molecule['cross_references'] ?? [];
        $allXrefs = $molecule['cross_references'] ?? [];
        $uniChemXrefs = array_filter($allXrefs, function ($xref) {
            return isset($xref['xref_src_db']) && strtoupper($xref['xref_src_db']) === 'UNICHEM';
        });

        $details['xrefs'] = $uniChemXrefs;

        // $details['targets'] = $molecule['targets'] ?? [];
        // $details['pathways'] = $molecule['pathways'] ?? [];
        // $details['xrefs'] = $molecule['cross_sources'] ?? [];

        return $details;
    }

    public function getMechanisms($chemblId)
    {
        $response = Http::get("https://www.ebi.ac.uk/chembl/api/data/mechanism.json?molecule_chembl_id={$chemblId}");
        $mechanisms = $response->json()['mechanisms'] ?? [];

        foreach ($mechanisms as &$mech) {
            if (isset($mech['target_chembl_id'])) {
                $targetResp = Http::get("https://www.ebi.ac.uk/chembl/api/data/target/{$mech['target_chembl_id']}.json");
                if ($targetResp->successful()) {
                    $targetData = $targetResp->json();
                    $mech['target_pref_name'] = $targetData['pref_name'] ?? null;
                    $mech['target_type'] = $targetData['target_type'] ?? null;
                }
            }
        }

        return $mechanisms;
    }

    public function getUniChemCrossReferences(string $inchikey): array
    {
        if (! $inchikey) {
            return [];
        }

        $url = 'https://www.ebi.ac.uk/unichem/api/v1/connectivity';

        $response = Http::post($url, [
            'type' => 'inchikey',
            'compound' => $inchikey,
            'searchComponents' => true,
        ]);

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();

        $crossReferences = [];

        if (! empty($data['sources']) && is_array($data['sources'])) {
            foreach ($data['sources'] as $xref) {
                $crossReferences[] = [
                    'database' => $xref['longName'] ?? $xref['shortName'] ?? 'Unknown',
                    'compound_id' => $xref['compoundId'] ?? 'N/A',
                    'url' => $xref['url'] ?? '',
                ];
            }
        }

        return $crossReferences;
    }

    public function getUniChemSources(): Collection
    {
        $url = 'https://www.ebi.ac.uk/unichem/api/v1/sources/';
        $response = Http::get($url);

        if ($response->successful()) {
            // Key by src_id for easy lookup
            return collect($response->json())->keyBy('src_id');
        }

        return collect();
    }
}
