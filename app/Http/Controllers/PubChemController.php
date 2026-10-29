<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class PubChemController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->input('query'));
        $encodedQuery = urlencode($query);

        // 1. Try exact name lookup for CID
        $exactResponse = Http::get("https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/name/{$encodedQuery}/cids/JSON");

        if ($exactResponse->ok() && ! empty($exactResponse->json('IdentifierList.CID'))) {
            // Got exact matches, limit to top 10
            $cids = array_slice($exactResponse->json('IdentifierList.CID'), 0, 10);
        } else {
            // 2. Fallback: Use NCBI ESearch text search for broader matches
            $esearchUrl = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pccompound&term={$encodedQuery}&retmax=10&retmode=json";
            $esearchResponse = Http::get($esearchUrl);

            if ($esearchResponse->ok()) {
                $cids = $esearchResponse->json('esearchresult.idlist', []);
            } else {
                $cids = [];
            }
        }

        if (empty($cids)) {
            return back()->with('error', "No compounds found for '{$query}'")->withInput();
        }

        // Fetch compound names (primary synonyms) for each CID
        $compounds = [];
        foreach ($cids as $cid) {
            $synResponse = Http::get("https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/synonyms/JSON");
            $synonyms = $synResponse->json('InformationList.Information.0.Synonym', []);
            $primaryName = $synonyms[0] ?? "CID {$cid}";
            $compounds[] = ['cid' => $cid, 'name' => $primaryName];
        }

        return view('pubchem', compact('compounds', 'query'));
    }

    public function showCompound($cid)
    {
        // Get summary data
        $propertyResponse = Http::get("https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/property/IUPACName,MolecularFormula,MolecularWeight/JSON");
        $props = $propertyResponse['PropertyTable']['Properties'][0] ?? [];

        // Get synonyms
        $synonyms = [];
        $synResponse = Http::get("https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/synonyms/JSON");
        if ($synResponse->ok()) {
            $synonyms = $synResponse['InformationList']['Information'][0]['Synonym'] ?? [];
        }
        $primary_name = $synonyms[0] ?? "CID {$cid}";

        // Get Description
        $response = Http::get("https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/description/XML");
        $xml = new SimpleXMLElement($response->body());

        $descriptions = [];
        foreach ($xml->Information as $info) {
            $source = (string) $info->SourceName;
            $text = (string) $info->Description;
            $descriptions[$source] = $text;
        }

        // Get cross-references
        $viewResponse = Http::get("https://pubchem.ncbi.nlm.nih.gov/rest/pug_view/data/compound/{$cid}/JSON");
        $sections = $viewResponse['Record']['Section'] ?? [];

        $cas = $this->findSectionValueWithUrl($sections, 'CAS');
        $drugbank = $this->findSectionValueWithUrl($sections, 'DrugBank ID');
        $hmdb = $this->findSectionValueWithUrl($sections, 'HMDB ID');
        $kegg = $this->findSectionValueWithUrl($sections, 'KEGG ID');

        $data = [
            'cid' => $cid,
            'primary_name' => $primary_name,
            'iupac_name' => $props['IUPACName'] ?? null,
            'molecular_formula' => $props['MolecularFormula'] ?? null,
            'molecular_weight' => $props['MolecularWeight'] ?? null,
            'synonyms' => $synonyms,
            'description_sources' => $descriptions,
            'cross_references' => [
                'CAS' => $cas,
                'DrugBank' => $drugbank,
                'HMDB' => $hmdb,
                'KEGG' => $kegg,
            ],
        ];

        return view('pubchem', ['data' => $data, 'input_name' => $primary_name]);
    }

    public function index()
    {
        return view('pubchem');
    }

    public function submit(Request $request)
    {
        $name = $request->input('chemical_name');

        return redirect()->route('pubchem.search', ['query' => $name]);
    }

    private function findSectionValueWithUrl($sections, $targetHeading)
    {
        foreach ($sections as $section) {
            if (($section['TOCHeading'] ?? '') === $targetHeading) {
                $info = $section['Information'][0] ?? null;
                if (! empty($info['Value']['StringWithMarkup'][0]['String'])) {
                    return [
                        'id' => $info['Value']['StringWithMarkup'][0]['String'],
                        'url' => $info['URL'] ?? null,
                    ];
                }
            }

            if (! empty($section['Section'])) {
                $result = $this->findSectionValueWithUrl($section['Section'], $targetHeading);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }
}
