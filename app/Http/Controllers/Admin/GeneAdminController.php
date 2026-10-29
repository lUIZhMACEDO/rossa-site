<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gene;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneAdminController extends Controller
{
    /**
     * Display the gene admin page
     */
    public function index(Request $request)
    {
        $query = Gene::query();

        // Handle search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('symbol', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%")
                    ->orWhere('alliance_id', 'ILIKE', "%{$search}%")
                    ->orWhere('species_name', 'ILIKE', "%{$search}%");
            });
        }

        $genes = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.genes.index', compact('genes'));
    }

    /**
     * Test creating/updating a gene by Alliance ID
     */
    public function testByAllianceId(Request $request)
    {
        $request->validate([
            'alliance_id' => 'required|string',
        ]);

        try {
            $gene = Gene::updateOrCreateFromAlliance($request->alliance_id);

            if ($gene) {
                return redirect()->route('admin.genes.show', $gene)->with('success', "Gene '{$gene->symbol}' created/updated successfully!");
            } else {
                return redirect()->back()->with('error', 'Gene not found in Alliance database.');
            }
        } catch (\Exception $e) {
            Log::error('Gene creation by Alliance ID failed', [
                'alliance_id' => $request->alliance_id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Test creating/updating a gene by symbol and species
     */
    public function testBySymbolAndSpecies(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'species' => 'required|string|in:MGI,RGD,HGNC',
        ]);

        try {
            $gene = Gene::updateOrCreateBySymbolAndSpecies($request->symbol, $request->species);

            if ($gene) {
                return redirect()->route('admin.genes.show', $gene)->with('success', "Gene '{$gene->symbol}' created/updated successfully!");
            } else {
                return redirect()->back()->with('error', 'Gene not found for the specified symbol and species.');
            }
        } catch (\Exception $e) {
            Log::error('Gene creation by symbol and species failed', [
                'symbol' => $request->symbol,
                'species' => $request->species,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Test creating/updating a mouse or rat gene by symbol
     */
    public function testMouseOrRatGene(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
        ]);

        try {
            $gene = Gene::updateOrCreateMouseOrRatGene($request->symbol);

            if ($gene) {
                return redirect()->route('admin.genes.show', $gene)->with('success', "Gene '{$gene->symbol}' created/updated successfully!");
            } else {
                return redirect()->back()->with('error', 'Mouse or rat gene not found for the specified symbol.');
            }
        } catch (\Exception $e) {
            Log::error('Mouse/rat gene creation failed', [
                'symbol' => $request->symbol,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Test refreshing a gene from Alliance API
     */
    public function testRefresh(Request $request, Gene $gene)
    {
        try {
            if ($gene->refreshFromAlliance()) {
                return redirect()->back()->with('success', "Gene '{$gene->symbol}' refreshed successfully!");
            } else {
                return redirect()->back()->with('error', 'Failed to refresh gene data from Alliance API.');
            }
        } catch (\Exception $e) {
            Log::error('Gene refresh failed', [
                'gene_id' => $gene->id,
                'alliance_id' => $gene->alliance_id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Show gene details
     */
    public function show(Gene $gene)
    {
        return view('admin.genes.show', compact('gene'));
    }

    /**
     * Delete a gene
     */
    public function destroy(Gene $gene)
    {
        try {
            $symbol = $gene->symbol;
            $gene->delete();

            return redirect()->route('admin.genes.index')->with('success', "Gene '{$symbol}' deleted successfully!");
        } catch (\Exception $e) {
            Log::error('Gene deletion failed', [
                'gene_id' => $gene->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'An error occurred while deleting the gene.');
        }
    }
}
