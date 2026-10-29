<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate Bearer tokens when present without requiring auth.
 * Lets public API routes apply token-based rate limits and pagination caps.
 */
class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() && Auth::guard('sanctum')->user()) {
            Auth::shouldUse('sanctum');
        }

        return $next($request);
    }
}
