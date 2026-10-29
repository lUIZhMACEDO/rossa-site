<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * Get User
     *
     * Returns basic profile information for the user associated with the
     * provided Bearer token. Use this to verify your token is valid and
     * to retrieve your account details.
     *
     * Requires a valid Sanctum API token in the `Authorization: Bearer` header.
     *
     * @tags Auth
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at?->toDateTimeString(),
        ]);
    }
}
