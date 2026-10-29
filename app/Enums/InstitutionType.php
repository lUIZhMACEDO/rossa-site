<?php

namespace App\Enums;

enum InstitutionType: string
{
    case AcademicNonprofit = 'academic_nonprofit';
    case CommercialForProfit = 'commercial_for_profit';

    // public limits
    public const PUBLIC_REQUESTS_PER_MINUTE = 30;

    public const PUBLIC_PER_PAGE_LIMIT = 25;

    public function label(): string
    {
        return match ($this) {
            self::AcademicNonprofit => 'Academic / nonprofit',
            self::CommercialForProfit => 'Commercial / for-profit',
        };
    }

    public function whoItsFor(): string
    {
        return match ($this) {
            self::AcademicNonprofit => 'Academic, nonprofit, and research use',
            self::CommercialForProfit => 'Commercial or for-profit use',
        };
    }

    public function usageLabel(): string
    {
        return match ($this) {
            self::AcademicNonprofit => 'Unlimited',
            self::CommercialForProfit => 'Moderate',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AcademicNonprofit => 'bg-primary',
            self::CommercialForProfit => 'bg-warning text-dark',
        };
    }

    public function usageBadgeClass(): string
    {
        return $this->badgeClass();
    }

    public function perPageLimit(): int
    {
        return match ($this) {
            self::AcademicNonprofit => PHP_INT_MAX,
            self::CommercialForProfit => 50,
        };
    }

    public function requestsPerMinute(): ?int
    {
        return match ($this) {
            self::AcademicNonprofit => null,
            self::CommercialForProfit => 60,
        };
    }
}
