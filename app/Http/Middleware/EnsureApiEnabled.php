<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiEnabled
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('api.enabled')) {
            return $next($request);
        }

        return response()->json([
            'message' => config('api.disabled_message'),
        ], 503);
    }
}
