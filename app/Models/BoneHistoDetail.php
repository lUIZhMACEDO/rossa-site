<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoneHistoDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_upload_id',
        'measurement_software',
        'histological_approach',
    ];

    public function analysisUpload()
    {
        return $this->belongsTo(AnalysisUpload::class, 'analysis_upload_id');
    }
}
