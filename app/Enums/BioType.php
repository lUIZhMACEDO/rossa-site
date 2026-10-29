<?php

namespace App\Enums;

enum BioType: string
{
    case PROTEIN_CODING = 'protein_coding_gene';
    case MICRORNA = 'microrna';
    case LNCRNA = 'lncrna';
    case SNRNA = 'snrna';
    case ERNA = 'erna';
    case ANTISENSE = 'antisense';
    case OTHER = 'other';

    public function print(): string
    {
        return match ($this) {
            BioType::PROTEIN_CODING => 'Protein Coding',
            BioType::MICRORNA => 'MicroRNA',
            BioType::LNCRNA => 'Long Non-Coding RNA',
            BioType::SNRNA => 'Small Nuclear RNA',
            BioType::ERNA => 'Enhancer RNA',
            BioType::ANTISENSE => 'Antisense RNA',
            BioType::OTHER => 'Other',
        };
    }
}
