<?php

namespace App\Models;

use App\Enums\ApiTokenRequestStatus;
use App\Enums\InstitutionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenRequest extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'terms_accepted_at',
        'status',
        'category',
        'personal_access_token_id',
        'token_encrypted',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => ApiTokenRequestStatus::class,
        'category' => InstitutionType::class,
        'terms_accepted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function personalAccessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    public function hasToken(): bool
    {
        return $this->status === ApiTokenRequestStatus::Accepted
            && $this->personal_access_token_id !== null;
    }

    public function getPlainToken(): ?string
    {
        if ($this->token_encrypted === null) {
            return null;
        }

        return decrypt($this->token_encrypted);
    }
}
