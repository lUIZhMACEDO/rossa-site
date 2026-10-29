<?php

namespace App\Enums;

enum SubscriptionType: string
{
    case STUDY_SUBMITTED = 'notify_on_submissions';
    case NEWSLETTER = 'newsletter';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
