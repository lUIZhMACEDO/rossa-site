<?php

namespace App\Enums;

enum StudyType: string
{
    case SINGLE = 'single_drug_testing';
    case COMBINATION = 'combination_therapy';
    case SEQUENTIAL = 'sequential_therapy';

    public function print(): string
    {
        return match ($this) {
            StudyType::SINGLE => 'Single Drug Testing',
            StudyType::COMBINATION => 'Combination Therapy',
            StudyType::SEQUENTIAL => 'Sequential Therapy',
        };
    }
}
