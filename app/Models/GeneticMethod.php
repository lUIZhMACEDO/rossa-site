<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneticMethod extends Model
{
    protected $fillable = ['name', 'allele_abbreviation', 'description', 'genetic_category_id'];

    public function geneticCategory()
    {
        return $this->belongsTo(GeneticCategory::class);
    }

    public function animalModels()
    {
        return $this->hasMany(AnimalModel::class);
    }
}
