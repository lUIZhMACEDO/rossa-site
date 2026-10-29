<?php

namespace App\Http\Middleware;

use App\Models\Study;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessStudy
{
    public function handle(Request $request, Closure $next): Response
    {
        $study = null;

        if ($request->route('study')) {
            $study = $request->route('study');
        } elseif ($request->has('id')) {
            $study = Study::find($request->route('id'), ['*']);
        }

        if ($study) {
            return $request->user()->canViewStudy($study)
                ? $next($request)
                : abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
