<?php

namespace App\Http\Middleware;

use App\Models\Study;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudyEditable
{
    public function handle(Request $request, Closure $next): Response
    {
        $study = $request->route('study');

        if ($study instanceof Study && ! $request->user()->canEditStudy($study)) {
            abort(403, 'Unauthorized action.');
        }

        if ($study instanceof Study && $study->isLockedForEditing($request->user())) {
            return redirect()->route('study.summary.information', $study);
        }

        return $next($request);
    }
}
