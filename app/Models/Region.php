<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'code',
        'label',
    ];

    public function measurements()
    {
        return $this->belongsToMany(Measurement::class);
    }

    public function analysisUploads()
    {
        return $this->hasMany(AnalysisUpload::class);
    }
}
