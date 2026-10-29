<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneticCategory extends Model
{
    protected $fillable = ['name', 'template', 'description', 'can_cre', 'can_drug_induce'];

    public function geneticMethods()
    {
        return $this->hasMany(GeneticMethod::class);
    }
}
