<?php

namespace App\Http\Controllers;

use App\Enums\ApiTokenRequestStatus;
use App\Models\ApiTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTokenRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Verify your email before requesting an API token.',
            ], 403);
        }

        if (ApiTokenRequest::where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You have already submitted an API token request.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        ApiTokenRequest::create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'terms_accepted_at' => now(),
            'status' => ApiTokenRequestStatus::Pending,
        ]);

        return response()->json([
            'message' => 'Your API token request has been submitted. An administrator will review it shortly.',
        ]);
    }
}
