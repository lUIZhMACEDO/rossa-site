<?php

namespace App\Enums;

enum DkoParentModification: string
{
    case KNOCKOUT = 'knockout';
    case KNOCKIN = 'knockin';
    case SPONTANEOUS_MUTATION = 'spontaneous_mutation';
    case INSERTIONAL_MUTAGENESIS = 'insertional_mutagenesis';

    public function print(): string
    {
        return match ($this) {
            self::KNOCKOUT => 'Knockout (KO)',
            self::KNOCKIN => 'Knockin (KI)',
            self::SPONTANEOUS_MUTATION => 'Spontaneous Mutation',
            self::INSERTIONAL_MUTAGENESIS => 'Insertional Mutagenesis',
        };
    }
}
