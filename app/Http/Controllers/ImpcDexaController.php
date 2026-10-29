<?php

namespace App\Http\Controllers;

use App\Models\ImpcDexa;

class ImpcDexaController extends Controller
{
    public function index()
    {
        $impcDexa = ImpcDexa::orderBy('specimen_id')
            ->paginate(50);

        return view('impc_dexa.index', compact('impcDexa'));
    }
}
