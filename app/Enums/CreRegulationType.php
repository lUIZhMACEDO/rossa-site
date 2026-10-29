<?php

namespace App\Enums;

enum CreRegulationType: string
{
    case CRE_ER = 'cre_er';
    case CRE_ERT = 'cre_ert';
    case CRE_ERT2 = 'cre_ert2';
    case TET_ON = 'tet_on';
    case TET_OFF = 'tet_off';

    public function print(): string
    {
        return match ($this) {
            self::CRE_ER => 'Cre ER Positive (CreER+)',
            self::CRE_ERT => 'Cre ERT Positive (CreERT+)',
            self::CRE_ERT2 => 'Cre ERT2 Positive (CreERT2+)',
            self::TET_ON => 'Tet-On rtTA (Tet-On)',
            self::TET_OFF => 'Tet-Off tTA (Tet-Off)',
        };
    }

    public static function getOptionsForCreSystem(CreSystem $system): array
    {
        return match ($system) {
            CreSystem::TAMOXIFEN => [
                self::CRE_ER,
                self::CRE_ERT,
                self::CRE_ERT2,
            ],
            CreSystem::DOXYCYCLINE => [
                self::TET_ON,
                self::TET_OFF,
            ],
            default => [],
        };
    }
}
