<?php

namespace App\Models;

use App\Services\ChemblService;
use Illuminate\Database\Eloquent\Model;

class Molecule extends Model
{
    protected $fillable = [
        'chembl_id',
        'pref_name',
        'molecule_type',
        'max_phase',
        'formula',
        'data',
        'is_custom',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function updateOrCreateFromChembl(string $chemblId): self
    {
        $chemblService = new ChemblService;
        $moleculeData = $chemblService->getMoleculeDetails($chemblId);

        return self::updateOrCreate(
            ['chembl_id' => $chemblId],
            [
                'pref_name' => $moleculeData['molecule']['pref_name'] ?? null,
                'molecule_type' => $moleculeData['molecule']['molecule_type'] ?? null,
                'max_phase' => $moleculeData['molecule']['max_phase'] ?? null,
                'formula' => $moleculeData['molecule']['molecule_properties']['full_molformula'] ?? null,
                'data' => $moleculeData,
            ]
        );
    }
}
