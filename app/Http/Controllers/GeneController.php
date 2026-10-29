<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeneController extends Controller
{
    public function show(Request $request, $symbol)
    {
        // Step 1: Call autocomplete API
        $response = Http::get('https://www.alliancegenome.org/api/search_autocomplete', [
            'q' => $symbol,
            'category' => 'gene',
        ]);

        $results = $response->json('results');

        if (empty($results)) {
            return response()->json(['error' => 'Gene not found'], 404);
        }

        // Step 2: Filter for mouse or rat IDs (MGI or RGD)
        $gene = collect($results)->first(fn ($item) => str_starts_with($item['primaryKey'], 'MGI:') ||
            str_starts_with($item['primaryKey'], 'RGD:')
        );

        if (! $gene) {
            return response()->json(['error' => 'Gene not found for mouse or rat'], 404);
        }

        // Step 3: Fetch detailed gene info
        $id = urlencode($gene['primaryKey']);
        $detailsResponse = Http::get("https://www.alliancegenome.org/api/gene/{$id}");

        if (! $detailsResponse->successful()) {
            return response()->json(['error' => 'Failed to fetch gene details'], 500);
        }

        $geneDetails = $detailsResponse->json();

        // Return full details JSON
        return response()->json($geneDetails);
    }

    public function search(Request $request)
    {
        $geneDetails = null;

        if ($request->isMethod('post')) {
            $symbol = $request->input('symbol');
            $species = $request->input('species'); // MGI, RGD, etc.

            $response = Http::get('https://www.alliancegenome.org/api/search_autocomplete', [
                'q' => $symbol,
                'category' => 'gene',
            ]);

            $results = $response->json('results');

            if (empty($results)) {
                return view('gene_search')->withErrors(['Gene not found']);
            }

            $filtered = collect($results)->first(fn ($item) => str_starts_with($item['primaryKey'], $species)
            );

            if (! $filtered) {
                return view('gene_search')->withErrors(['No match for selected species']);
            }

            $id = urlencode($filtered['primaryKey']);
            $detailsResponse = Http::get("https://www.alliancegenome.org/api/gene/{$id}");

            if (! $detailsResponse->successful()) {
                return view('gene_search')->withErrors(['Failed to fetch gene details']);
            }

            $geneDetails = $detailsResponse->json();
        }

        return view('gene_search', compact('geneDetails'));
    }
}
