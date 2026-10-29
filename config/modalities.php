<?php

/*
|--------------------------------------------------------------------------
| Modality display metadata
|--------------------------------------------------------------------------
|
| Keyed on analysis_types.code (NOT the id, which is not stable across
| environments). Used by the phenotype analysis page to give each modality
| a consistent short label and identifying color in both the selection
| tiles and the uploaded datasets table.
|
| Verify your codes with:
|   php artisan tinker
|   >>> App\Models\AnalysisType::pluck('code', 'name');
|
*/

return [

    'default' => [
        'abbr' => null,
        'color' => '#888780',
    ],

    'types' => [

        'micro_ct' => [
            'abbr' => 'uCT',
            'color' => '#378ADD',
        ],

        'dexa' => [
            'abbr' => 'DEXA',
            'color' => '#1D9E75',
        ],

        'bone_histo' => [
            'abbr' => 'BH',
            'color' => '#D4537E',
        ],

        'mechanical_testing' => [
            'abbr' => 'MECH',
            'color' => '#BA7517',
        ],

        'clinical_chemistry' => [
            'abbr' => 'CC',
            'color' => '#7F77DD',
        ],

    ],

];
