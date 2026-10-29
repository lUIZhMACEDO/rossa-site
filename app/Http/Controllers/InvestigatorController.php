<?php

namespace App\Http\Controllers;

use App\Models\Study;
use Illuminate\Http\Request;

class InvestigatorController extends Controller
{
    public function show(Study $study)
    {
        return view('study.investigators', compact('study'));
    }

    public function store(Request $request, Study $study)
    {
        return redirect()
            ->route('study.animal-experimentation.setup', $study)
            ->with('success', 'Investigators updated successfully.');
    }
}
