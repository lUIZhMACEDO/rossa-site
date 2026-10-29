<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Sample extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'analysis_upload_id',
        'label',
        'sample_group_id',
        'raw_upload_data',
        'row_index',
    ];

    protected $casts = [
        'data' => 'array',
        'deleted' => 'boolean',
        'row_index' => 'integer',
    ];

    public function analysisUpload()
    {
        return $this->belongsTo(AnalysisUpload::class);
    }

    public function sampleGroup(): BelongsTo
    {
        return $this->belongsTo(SampleGroup::class);
    }

    public function assignmentChipLabel(): string
    {
        $name = $this->label ?? 'Sample '.$this->id;
        $group = $this->sampleGroup?->label;

        return $group ? "{$name} ({$group})" : $name;
    }

    public function assignmentLabel(): string
    {
        $label = $this->assignmentChipLabel();
        $context = $this->analysisUpload?->getContextDescription(true);

        return $context ? "{$label} — {$context}" : $label;
    }

    public function study()
    {
        return $this->hasOneThrough(Study::class, AnalysisUpload::class, 'id', 'id', 'analysis_upload_id', 'study_id');
    }

    public function getSampleId()
    {
        return $this->measurementValues()
            ->whereHas('measurement', fn ($q) => $q->where('name', 'sample_identifier'))
            ->first()
            ->value;
    }

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }

    public function deleteUploads()
    {
        $this->uploads()->each(function ($upload) {
            if (Storage::exists($upload->file_path)) {
                Storage::delete($upload->file_path);
            }
            $upload->delete();
        });
    }
}
