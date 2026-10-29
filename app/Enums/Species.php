<?php

namespace App\Enums;

enum Species: string
{
    case MOUSE = 'mouse';
    case RAT = 'rat';

    public function print(): string
    {
        return match ($this) {
            Species::MOUSE => 'Mouse',
            Species::RAT => 'Rat',
        };
    }

    public function alliancePrefix(): string
    {
        return match ($this) {
            Species::MOUSE => 'MGI',
            Species::RAT => 'RGD',
        };
    }

    /**
     * @return list<string>
     */
    public function chromosomes(): array
    {
        $autosomes = match ($this) {
            Species::MOUSE => range(1, 19),
            Species::RAT => range(1, 20),
        };

        return array_map('strval', [...$autosomes, 'X', 'Y', 'MT']);
    }

    /**
     * @return list<string>
     */
    public function genomeAssemblies(): array
    {
        return match ($this) {
            Species::MOUSE => [
                'GRCm39 (C57BL/6J)',
                'GRCm38/mm10 (C57BL/6J)',
            ],
            Species::RAT => [
                'mRatBN7.2 (BN/NHsdMcwi)',
                'Rnor_6.0 (BN)',
            ],
        };
    }

    public static function tryFromAlliancePrefix(?string $prefix): ?self
    {
        return match ($prefix) {
            'MGI' => self::MOUSE,
            'RGD' => self::RAT,
            default => null,
        };
    }
}
