<?php

namespace App\Enums;

enum CoreFacilityStatus: string
{
    // CF statusworkflow
    // 1. onSubmission -> pending DONE
    // 2. onAccept -> active (12 months expiration) DONE
    // 3. onReject -> rejected (with reason) DONE
    // 4. 11 months after acceptance -> refresh information/expiration email
    // 5. 12 months after acceptance -> expired
    // 6. onEdit/Renew -> if expired ? accepted : original status (reset expiration, refresh)
    // 7. onRefresh -> accepted (reset expiration, refresh)
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::EXPIRED => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::EXPIRED => 'secondary',
        };
    }

    public function action(): string
    {
        return match ($this) {
            self::PENDING => 'mark as pending',
            self::ACCEPTED => 'accept',
            self::REJECTED => 'reject',
            // no actions for expired
        };
    }

    public function nextStatuses(): array
    {
        return match ($this) {
            // must go through review
            self::PENDING => [self::ACCEPTED, self::REJECTED],
            // can be sent for review, expire, or archive if replaced
            self::ACCEPTED => [self::PENDING, self::EXPIRED],
            // can be adjusted for review
            self::REJECTED => [self::PENDING],
            // archive if replaced
            self::EXPIRED => [self::ACCEPTED],
        };
    }
}
