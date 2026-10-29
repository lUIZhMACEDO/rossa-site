<?php

namespace App\Enums;

enum GonadectomyType: string
{
    case OVX = 'ovx';
    case ORX = 'orx';
    case SHAM_CONTROL = 'sham_control';
    case NO_SURGERY_CONTROL = 'no_surgery_control';

    public function name(): string
    {
        return match ($this) {
            self::OVX => 'Ovaryectomy (OVX)',
            self::ORX => 'Orchiectomy (ORX)',
            self::SHAM_CONTROL => 'Sham control',
            self::NO_SURGERY_CONTROL => 'No Surgery control',
        };
    }

    public function shortName(): string
    {
        return match ($this) {
            self::OVX => 'OVX',
            self::ORX => 'ORX',
            self::SHAM_CONTROL => 'Sham',
            self::NO_SURGERY_CONTROL => 'No Surgery',
        };
    }

    public static function implodeValues(): string
    {
        return implode(',', array_map(fn ($case) => $case->value, self::cases()));
    }
}
