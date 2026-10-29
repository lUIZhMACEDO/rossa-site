<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OlsApiService
{
    public function getMouseStrains()
    {
        $iri = 'http%253A%252F%252Fpurl.obolibrary.org%252Fobo%252FNCBITaxon_10090/descendants';

        $response = Http::withoutVerifying()->get("https://www.ebi.ac.uk/ols4/api/ontologies/efo/terms/{$iri}");

        if ($response->failed()) {
            dd('API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->json();
    }

    public function getRatStrains()
    {
        // Double-encoded IRI for RS_0000765 (Rat strains top class in RS ontology)
        $iri = rawurlencode(rawurlencode('http://purl.obolibrary.org/obo/RS_0000765'));

        $url = "https://www.ebi.ac.uk/ols4/api/ontologies/rs/terms/{$iri}/children";

        $response = Http::withoutVerifying()->get($url);

        if ($response->failed()) {
            \Log::error('Rat strain API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $data = $response->json();

        if (! isset($data['_embedded']['terms'])) {
            \Log::error('No terms found in rat API response', ['response' => $data]);

            return null;
        }

        return $data;
    }
}
