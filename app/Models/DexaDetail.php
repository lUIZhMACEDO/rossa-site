<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DexaDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_upload_id',
        'dexa_instrument',
    ];

    public function analysisUpload()
    {
        return $this->belongsTo(AnalysisUpload::class, 'analysis_upload_id');
    }
}
