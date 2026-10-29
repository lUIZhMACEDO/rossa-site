<?php

namespace App\Services;

use App\Enums\ApiTokenRequestStatus;
use App\Enums\InstitutionType;
use App\Models\ApiTokenRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApiTokenRequestService
{
    public function accept(ApiTokenRequest $submission, User $reviewer, InstitutionType $category): void
    {
        if ($submission->status !== ApiTokenRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be accepted.',
            ]);
        }

        $user = $submission->user;
        $user->tokens()->delete();

        $accessToken = $user->createToken('ROSSA API', [$category->value]);
        $plainToken = $accessToken->plainTextToken;

        $submission->update([
            'status' => ApiTokenRequestStatus::Accepted,
            'category' => $category,
            'personal_access_token_id' => $accessToken->accessToken->id,
            'token_encrypted' => encrypt($plainToken),
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    public function reject(ApiTokenRequest $submission, User $reviewer): void
    {
        if ($submission->status !== ApiTokenRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be rejected.',
            ]);
        }

        $this->revokeToken($submission);

        $submission->update([
            'status' => ApiTokenRequestStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    public function disable(ApiTokenRequest $submission, User $reviewer): void
    {
        if ($submission->status !== ApiTokenRequestStatus::Accepted) {
            throw ValidationException::withMessages([
                'status' => 'Only accepted tokens can be disabled.',
            ]);
        }

        $this->revokeToken($submission);

        $submission->update([
            'status' => ApiTokenRequestStatus::Disabled,
            'category' => null,
            'personal_access_token_id' => null,
            'token_encrypted' => null,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    private function revokeToken(ApiTokenRequest $submission): void
    {
        if ($submission->personal_access_token_id) {
            $submission->personalAccessToken?->delete();
        }

        $submission->user->tokens()->delete();
    }
}
