<?php

namespace App\Services;

class CreOptionsService
{
    public function get_options()
    {
        return [
            'transgene_regulated_non_inducible' => 'Transgene Regulated Non-Inducible',
            'tamoxifen_inducible' => 'Tamoxifen Inducible',
            'doxycycline_regulated' => 'Doxycycline Regulated',
        ];
    }
}
