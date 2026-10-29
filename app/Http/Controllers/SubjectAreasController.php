<?php

namespace App\Http\Controllers;

use App\Models\Study;

class SubjectAreasController extends Controller
{
    public function show(Study $study)
    {
        return view('study.subject-areas', compact('study'));
    }
}
