<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'abbreviation',
    ];

    public function analysisUploads()
    {
        return $this->hasMany(AnalysisUpload::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }

    public function scopeWithoutOther($query)
    {
        return $query->where('code', '!=', 'other');
    }
}
