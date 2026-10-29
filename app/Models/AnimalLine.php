<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;

class AnimalLine extends Model
{
    use LocksWithStudy;

    protected $fillable = [
        'label',
        'iri',
        'description',
        'ontology',
        'synonyms',
        'obo_id',
        'definition_citation',
        'species',
        'study_id',
    ];

    public $defaultStrains = [
        'C57BL/6',
        'C57BL/6J',
        'C57BL/6N',
        'BALB/c',
        'DBA/2J',
        '129S1/SvImJ',
        'FVB/NJ',
        'C3H/HeJ',
    ];

    protected $casts = [
        'synonyms' => 'array', // Cast synonyms to an array
        'definition_citation' => 'array',
    ];

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function animalModels()
    {
        return $this->hasMany(AnimalModel::class);
    }

    public function scopePreloadedResults($query)
    {
        return $query->whereIn('label', $this->defaultStrains);
    }
}
