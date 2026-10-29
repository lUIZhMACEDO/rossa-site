<?php

namespace App\Http\Controllers;

use App\Services\ChemblService;
use Illuminate\Http\Request;

class ChemblController extends Controller
{
    protected $chembl;

    public function __construct(ChemblService $chembl)
    {
        $this->chembl = $chembl;
    }

    /**
     * Search for molecules based on a query string.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $molecules = $query ? $this->chembl->searchMolecules($query) : [];

        return view('chembl.search', compact('molecules', 'query'));
    }

    public function showDetails($chemblId)
    {
        $details = $this->chembl->getMoleculeDetails($chemblId);
        $inchikey = $details['molecule']['molecule_structures']['standard_inchi_key'] ?? null;

        // dd($inchikey);

        $mechanisms = $this->chembl->getMechanisms($chemblId);
        // $unichemLinks = $this->chembl->getUniChemCrossReferences($inchikey);
        $crossReferences = $inchikey ? $this->chembl->getUniChemCrossReferences($inchikey) : [];
        // $crossReferences = $chembl->getUniChemCrossReferences($inchikey);

        // dd($crossReferences);
        $sourceMap = $this->chembl->getUniChemSources();

        foreach ($crossReferences as &$xref) {
            if (isset($xref['src_id']) && isset($sourceMap[$xref['src_id']])) {
                $srcId = $xref['src_id'];
                $xref['source_name'] = $sourceMap[$srcId]['src_short_name'] ?? 'Unknown Source';
            } else {
                $xref['source_name'] = 'Unknown Source';
            }
        }

        // dd([
        //     'details' => $details,
        //     'mechanisms' => $mechanisms,
        //     'crossReferences' => $crossReferences,
        //     'sourceMap' => $sourceMap->toArray(),
        // ]);

        return view('chembl.details', compact('details', 'mechanisms', 'crossReferences'));
    }

    public function showCrossReferences(Request $request)
    {
        $inchikey = $request->input('inchikey');

        $crossReferences = $this->drugService->getUniChemCrossReferences($inchikey);

        return view('cross-references', compact('crossReferences'));
    }
}
