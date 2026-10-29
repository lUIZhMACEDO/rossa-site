<?php

namespace App\Http\Controllers\Api;

use App\Enums\InstitutionType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    /**
     * Resolve a safe per_page value capped by the caller's access tier.
     * Token ability determines the cap; public callers (no token) get the lowest cap.
     */
    protected function resolvePerPage(Request $request, int $default = 25): int
    {
        $max = InstitutionType::PUBLIC_PER_PAGE_LIMIT;

        foreach (InstitutionType::cases() as $type) {
            if ($request->user()?->tokenCan($type->value)) {
                $max = $type->perPageLimit();
                break;
            }
        }

        return min((int) $request->input('per_page', $default), $max);
    }

    // stub response for empty endpoints
    protected function notImplemented(string $hint = ''): JsonResponse
    {
        return response()->json([
            'data' => [],
            'message' => 'This endpoint is planned but not yet implemented.'.($hint ? ' '.$hint : ''),
        ], 200);
    }
}
