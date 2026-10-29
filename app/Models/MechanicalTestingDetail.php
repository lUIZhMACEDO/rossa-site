<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MechanicalTestingDetail extends Model
{
    use HasFactory, LocksWithStudy;

    protected $fillable = [
        'study_id',
        'analysis_upload_id',
        'test_method',
        'orientation',
        'gage_length_torsion',
        'created_at',
        'updated_at',
        'bone_tested',
        'support_span_3pt',
        'support_span_4pt',
        'loading_span_4pt',
        'gage_length_compression',
        'preload_value',
        'preload_unit',
        'off_axis_angle',
        // 'anatomical_reference', // deprecated... should column at some point (feedback #67)
        'orientation_other',
        'storage_medium',
        'storage_medium_other',
        'test_conditioning',
        'test_conditioning_other',
        'storage_temperature',
        'storage_temperature_other',
        'testing_temperature',
        'testing_temperature_other',
        'use_preload',
        'loading_rate_bending',
        'loading_rate_compression',
        'loading_rate_compression_unit',
        'loading_rate_torsion',
        'loading_rate_torsion_unit',
    ];

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function analysisUpload()
    {
        return $this->belongsTo(AnalysisUpload::class, 'analysis_upload_id');
    }
}
