<?php

namespace App\Enums;

enum CreSystem: string
{
    case TRANSGENE = 'transgene';
    case TAMOXIFEN = 'tamoxifen';
    case DOXYCYCLINE = 'doxycycline';

    public function print(): string
    {
        return match ($this) {
            self::TRANSGENE => 'Transgene Regulated Non-Inducible',
            self::TAMOXIFEN => 'Tamoxifen Inducible',
            self::DOXYCYCLINE => 'Doxycycline Regulated',
        };
    }
}
