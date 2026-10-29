<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimalLine;
use App\Models\DiseaseLine;
use Illuminate\Http\Request;

class AdminOntologyController extends Controller
{
    public function adminAnimalOntology(Request $request)
    {
        // Handle Animal Lines
        $animalQuery = AnimalLine::query();
        if ($request->filled('animal_search')) {
            $search = $request->get('animal_search');
            $animalQuery->where(function ($q) use ($search) {
                $q->where('label', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%")
                    ->orWhere('iri', 'ILIKE', "%{$search}%")
                    ->orWhere('ontology', 'ILIKE', "%{$search}%");
            });
        }
        $animalLines = $animalQuery->orderBy('label')->paginate(20, ['*'], 'animal_page')->appends($request->query());

        // Return the view with the data
        return view('admin.ontology.animal-lines', compact('animalLines'));
    }

    public function adminDiseaseOntology(Request $request)
    {
        // Handle Disease Lines
        $diseaseQuery = DiseaseLine::query();
        if ($request->filled('disease_search')) {
            $search = $request->get('disease_search');
            $diseaseQuery->where(function ($q) use ($search) {
                $q->where('label', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%")
                    ->orWhere('iri', 'ILIKE', "%{$search}%")
                    ->orWhere('ontology', 'ILIKE', "%{$search}%")
                    ->orWhere('short_form', 'ILIKE', "%{$search}%");
            });
        }
        $diseaseLines = $diseaseQuery->orderBy('label')->paginate(20, ['*'], 'disease_page')->appends($request->query());

        // Return the view with the data
        return view('admin.ontology.disease-lines', compact('diseaseLines'));
    }
}
