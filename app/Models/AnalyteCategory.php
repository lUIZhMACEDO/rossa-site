<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'specimen_type_id',
    ];

    public function specimenType()
    {
        return $this->belongsTo(SpecimenType::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class, 'analyte_category_id');
    }
}
