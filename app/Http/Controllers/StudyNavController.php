<?php

namespace App\Http\Controllers;

use App\Models\Study;
use Illuminate\Http\Request;

class StudyNavController extends Controller
{
    // TODO: Look at this file
    public function share(Request $request): void
    {
        $studyRoutes = [
            'study.study-information',
            'study.investigators',
            'study.animal-experimentation',
            'study.sample-groups',
            'study.phenotype-analysis',
        ];

        if ($request->route() && in_array($request->route()->getName(), $studyRoutes)) {
            $studyId = $request->route('study');
            $study = Study::find($studyId);
            view()->share('study', $study);
        }
    }
}
