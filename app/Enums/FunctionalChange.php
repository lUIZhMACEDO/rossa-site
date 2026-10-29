<?php

namespace App\Enums;

enum FunctionalChange: string
{
    case LOSS_OF_FUNCTION = 'loss_of_function';
    case GAIN_OF_FUNCTION = 'gain_of_function';
    case HYPOMORPHIC_MUTANT = 'hypomorphic_mutant';
    case DOMINANT_NEGATIVE = 'dominant_negative';
    case CELL_ABLATION = 'cell_ablation';
    case KNOCK_DOWN = 'knock_down';
    case UNKNOWN = 'unknown';

    public function print(): string
    {
        return match ($this) {
            FunctionalChange::LOSS_OF_FUNCTION => 'Loss of Function',
            FunctionalChange::GAIN_OF_FUNCTION => 'Gain of Function',
            FunctionalChange::HYPOMORPHIC_MUTANT => 'Hypomorphic Mutant',
            FunctionalChange::DOMINANT_NEGATIVE => 'Dominant Negative',
            FunctionalChange::CELL_ABLATION => 'Cell Ablation',
            FunctionalChange::KNOCK_DOWN => 'Knock Down',
            FunctionalChange::UNKNOWN => 'Unknown',
        };
    }

    /**
     * Get functional change options for general use
     */
    public static function getGeneralOptions(): array
    {
        return [
            self::LOSS_OF_FUNCTION,
            self::GAIN_OF_FUNCTION,
            self::HYPOMORPHIC_MUTANT,
            self::DOMINANT_NEGATIVE,
            self::UNKNOWN,
        ];
    }

    /**
     * Get functional change options for transgene context
     */
    public static function getTransgeneOptions(): array
    {
        return [
            self::GAIN_OF_FUNCTION,
            self::DOMINANT_NEGATIVE,
            self::CELL_ABLATION,
            self::KNOCK_DOWN,
            self::UNKNOWN,
        ];
    }

    /**
     * Check if this functional change is valid for general use
     */
    public function isValidForGeneral(): bool
    {
        return in_array($this, [
            self::LOSS_OF_FUNCTION,
            self::GAIN_OF_FUNCTION,
            self::HYPOMORPHIC_MUTANT,
            self::DOMINANT_NEGATIVE,
            self::UNKNOWN,
        ]);
    }

    /**
     * Check if this functional change is valid for transgene context
     */
    public function isValidForTransgene(): bool
    {
        return in_array($this, [
            self::GAIN_OF_FUNCTION,
            self::DOMINANT_NEGATIVE,
            self::CELL_ABLATION,
            self::KNOCK_DOWN,
            self::UNKNOWN,
        ]);
    }
}
