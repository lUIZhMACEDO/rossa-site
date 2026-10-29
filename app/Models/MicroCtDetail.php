<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroCtDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_upload_id',
        'voxel_size',
        'scan_mode',
        'scanner',
        'xray_tube_potential',
        'integration_time',
        'analytics_software',
    ];

    protected $casts = [
        'voxel_size' => 'float',
        'xray_tube_potential' => 'float',
        'integration_time' => 'float',
    ];

    public function analysisUpload()
    {
        return $this->belongsTo(AnalysisUpload::class, 'analysis_upload_id');
    }
}
