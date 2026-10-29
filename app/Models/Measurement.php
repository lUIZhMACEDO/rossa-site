<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Measurement extends Model
{
    use LocksWithStudy;

    protected $fillable = [
        'name',
        'display_name',
        'unit',
        'analysis_type_id',
        'synonyms',
        'study_id',
        'specimen_type_id',
        'graph_exclude',
        'analyte_category_id',
    ];

    protected $casts = [
        'synonyms' => 'array',
    ];

    public function analyteCategory()
    {
        return $this->belongsTo(AnalyteCategory::class);
    }

    public function specimenType()
    {
        return $this->belongsTo(SpecimenType::class);
    }

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }

    public function analysisType()
    {
        return $this->belongsTo(AnalysisType::class);
    }

    public function regions()
    {
        return $this->belongsToMany(Region::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function matchesSynonym($text)
    {
        // Check synonyms
        return collect($this->synonyms)->contains(function ($synonym) use ($text) {
            return Str::lower($synonym) === Str::lower($text);
        });
    }

    /**
     * Define scopes for common queries.
     */
    public function scopeOfficial($query)
    {
        return $query->where('study_id', null);
    }

    public static function sampleIdentifier()
    {
        return self::firstWhere('name', 'sample_identifier');
    }

    public static function generateUniqueName($baseName)
    {
        $name = $baseName;
        $counter = 1;

        while (self::where('name', $name)->exists()) {
            $name = $baseName.'_'.$counter;
            $counter++;
        }

        return $name;
    }
}
