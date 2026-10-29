<?php

namespace App\Enums;

enum AnalysisTypeEnum: string
{
    case MICRO_CT = 'micro_ct';
    case DEXA = 'dexa';
    case BONE_HISTO = 'bone_histo';
    case MECHANICAL_TESTING = 'mechanical_testing';
    case CLINICAL_CHEMISTRY = 'clinical_chemistry';

    public function shorthand(): string
    {
        return match ($this) {
            self::MECHANICAL_TESTING => 'MECH',
            self::MICRO_CT => 'µCT',
            self::DEXA => 'DEXA',
            self::BONE_HISTO => 'BH',
            self::CLINICAL_CHEMISTRY => 'CC',
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::MECHANICAL_TESTING => 'Mechanical Strength Testing',
            self::MICRO_CT => 'Microcomputed Tomography (µCT)',
            self::DEXA => 'Dual-Energy X-Ray Absorptiometry (DEXA)',
            self::BONE_HISTO => 'Bone Histomorphometry',
            self::CLINICAL_CHEMISTRY => 'Clinical Chemistry',
        };
    }

    public function descriptions(): string
    {
        return match ($this) {
            self::MECHANICAL_TESTING => 'Analysis of bone strength and mechanical properties through various testing methods such as three-point bending, torsion, and compression tests.',
            self::MICRO_CT => 'High-resolution imaging technique used to visualize and quantify bone microarchitecture and density in three dimensions.',
            self::DEXA => 'Imaging method that measures bone mineral density (BMD) and body composition using low-dose X-rays.',
            self::BONE_HISTO => 'Microscopic analysis of bone tissue sections to evaluate bone formation, resorption, and overall histological structure.',
            self::CLINICAL_CHEMISTRY => 'Assessment of biochemical markers in blood or serum that reflect bone metabolism and health.',
        };
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->value,
            'shorthand' => $this->shorthand(),
            'description' => $this->descriptions(),
            'name' => $this->name(),
        ];
    }

    public static function getAllAsArray(): array
    {
        return array_map(fn ($case) => $case->toArray(), self::cases());
    }

    public static function getStandardTypes(): array
    {
        return array_filter(
            self::getAllAsArray(),
            fn ($type) => $type['slug'] !== 'other'
        );
    }
}
