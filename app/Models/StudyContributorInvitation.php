<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * user_id is nullable for email-only invitations (made nullable by a raw ALTER TABLE).
 *
 * @property int|null $user_id
 */
class StudyContributorInvitation extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const ACCEPTED = 'accepted';

    public const DECLINED = 'declined';

    public const REVOKED = 'revoked';

    public const EXPIRED = 'expired';

    protected $fillable = ['study_id', 'user_id', 'invited_by_user_id', 'email', 'token_hash', 'status', 'expires_at', 'responded_at', 'revoked_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
