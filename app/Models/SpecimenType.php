<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecimenType extends Model
{
    protected $fillable = [
        'name',
        'specimen_category',
    ];

    public function analyteCategories()
    {
        return $this->hasMany(AnalyteCategory::class);
    }

    public function analysisUploads()
    {
        return $this->hasMany(AnalysisUpload::class);
    }
}
