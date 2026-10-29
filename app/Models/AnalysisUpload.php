<?php

namespace App\Models;

use App\Enums\UploadSteps;
use App\Services\AnalysisUploadService;
use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AnalysisUpload extends Model
{
    use HasFactory, LocksWithStudy;

    public function isStudyLocked(): bool
    {
        $study = $this->getStudyForLock();

        return $study?->isPhenotypeAnalysisLocked(auth()->user()) ?? false;
    }

    public AnalysisUploadService $uploadService;

    protected $fillable = [
        'study_id',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'analysis_type_id',
        'specimen_type_id',
        'analyte_category_id',
        'compartment',
        'headers',
        'header_row_index',
        'column_measurement_mappings',
        'sample_group_ids',
        'description',
        'region_id',
        'test_method',
        'bone_tested',
        'data_hash',
        'group_cache', // Poorly named but this holds temporary data while the user is advancing to the review step.
        'current_step',
        'body_weight_included',
    ];

    protected $casts = [
        'headers' => 'array',
        'column_measurement_mappings' => 'array',
        'sample_group_ids' => 'array',
        'group_cache' => 'array',
        'current_step' => UploadSteps::class,
        'body_weight_included' => 'boolean',
    ];

    /**
     * @return BelongsTo<AnalysisType, $this>
     */
    public function analysisType(): BelongsTo
    {
        return $this->belongsTo(AnalysisType::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function specimenType()
    {
        return $this->belongsTo(SpecimenType::class);
    }

    public function analyteCategory()
    {
        return $this->belongsTo(AnalyteCategory::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasOne<MechanicalTestingDetail, $this>
     */
    public function mechanicalTestingDetails(): HasOne
    {
        return $this->hasOne(MechanicalTestingDetail::class);
    }

    public function microCtDetails()
    {
        return $this->hasOne(MicroCtDetail::class);
    }

    public function dexaDetails()
    {
        return $this->hasOne(DexaDetail::class);
    }

    public function boneHistoDetails()
    {
        return $this->hasOne(BoneHistoDetail::class);
    }

    public function samples()
    {
        return $this->hasMany(Sample::class);
    }

    public function isComplete(): bool
    {
        return in_array($this->current_step, [
            UploadSteps::COMPLETE,
            UploadSteps::SCAN_DATASETS,
        ], true);
    }

    public function measurements()
    {
        return $this->hasManyThrough(
            Measurement::class,
            AnalysisType::class,
            'id',
            'analysis_type_id',
            'analysis_type_id',
            'id'
        );
    }

    public function uploadService()
    {
        if (! isset($this->uploadService)) {
            $this->uploadService = new AnalysisUploadService($this);
        }

        return $this->uploadService;
    }

    // Rehydrate cached samples (and their measurement values) from the JSON group_cache column
    public function restoreGroupCacheSamples()
    {
        $cache = collect($this->group_cache ?? []);
        $sampleGroups = SampleGroup::whereIn('id', $cache->pluck('sample_group_id')->filter()->unique())->get()->keyBy('id');

        return $cache->map(function ($data) use ($sampleGroups) {
            $sample = new Sample($data);
            $sample->exists = false;

            $measurementValues = collect($data['measurement_values'] ?? $data['measurementValues'] ?? [])
                ->map(function ($mvData) {
                    $mv = new MeasurementValue($mvData);
                    $mv->exists = false;

                    return $mv;
                });

            $sample->setRelation('measurementValues', $measurementValues);

            if (isset($data['sample_group_id'])) {
                $sample->setRelation('sampleGroup', $sampleGroups->get($data['sample_group_id']));
            }

            return $sample;
        });
    }

    public function fileContents()
    {
        if (! $this->path || ! \Storage::exists($this->path)) {
            return null;
        }

        return \Storage::get($this->path);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($analysisUpload) {
            $analysisUpload->samples()->delete();
            $analysisUpload->mechanicalTestingDetails()->delete();
            $analysisUpload->microCtDetails()->delete();
            $analysisUpload->dexaDetails()->delete();
            $analysisUpload->boneHistoDetails()->delete();

            // Delete the uploaded file from storage
            if ($analysisUpload->path && \Storage::exists($analysisUpload->path)) {
                \Storage::delete($analysisUpload->path);
            }
        });
    }

    // return context for upload
    public function getContext(): array
    {
        $context = [
            'analysis_type_id' => $this->analysis_type_id,
            'specimen_type_id' => $this->specimen_type_id,
            'analyte_category_id' => $this->analyte_category_id,
            'region_id' => $this->region_id,
            'test_method' => $this->test_method,
            'bone_tested' => $this->bone_tested,
        ];

        return $context;
    }

    /**
     * Structured context parts for display, e.g.
     * [['label' => 'Bone', 'value' => 'Femur'], ['label' => 'Region', 'value' => 'Cortical']]
     * Lets each view style labels/values independently (bold labels, custom
     * spacing) instead of parsing a flat string.
     */
    public function getContextParts(bool $withAnalysisType = false): array
    {
        $parts = [];

        if ($withAnalysisType) {
            $parts[] = ['label' => 'Analysis Type', 'value' => $this->analysisType->name];
        }

        if ($this->specimenType) {
            $parts[] = ['label' => 'Bone', 'value' => $this->specimenType->name];
        }

        if ($this->analyteCategory) {
            $parts[] = ['label' => 'Analyte', 'value' => $this->analyteCategory->name];
        }

        if ($this->region) {
            $parts[] = ['label' => 'Region', 'value' => $this->region->label];
        }

        if ($this->mechanicalTestingDetails) {
            if ($this->mechanicalTestingDetails->test_method) {
                $parts[] = ['label' => 'Test Method', 'value' => $this->formatTestMethod($this->mechanicalTestingDetails->test_method)];
            }

            if ($this->mechanicalTestingDetails->bone_tested) {
                $parts[] = ['label' => 'Bone', 'value' => $this->formatBoneTested($this->mechanicalTestingDetails->bone_tested)];
            }

            if ($this->mechanicalTestingDetails->orientation) {
                $parts[] = ['label' => 'Orientation', 'value' => $this->formatOrientation($this->mechanicalTestingDetails)];
            }
        }

        return $parts;
    }

    /**
     * Human-readable description of this upload's context, e.g.
     * "Analysis Type: Microcomputed Tomography (µCT), Bone: Femur, Region: Cortical"
     * (with $withAnalysisType) or "Bone: Femur, Region: Cortical" (without).
     * Built from getContextParts() — kept as a flat string for any existing
     * caller that just wants text, rather than styled label/value pairs.
     *
     * $asSlug still returns the original unlabeled, underscore-joined machine
     * key (e.g. "femur_cortical") — unchanged, since that value may be used
     * elsewhere as an identifier rather than for display.
     */
    public function getContextDescription(bool $withAnalysisType = false, bool $asSlug = false): string
    {
        if ($asSlug) {
            $parts = [];

            if ($withAnalysisType) {
                $parts[] = $this->analysisType->abbreviation;
            }

            if ($this->specimenType) {
                $parts[] = $this->specimenType->name;
            }

            if ($this->analyteCategory) {
                $parts[] = $this->analyteCategory->slug;
            }

            if ($this->region) {
                $parts[] = $this->region->code;
            }

            if ($this->mechanicalTestingDetails) {
                if ($this->mechanicalTestingDetails->test_method) {
                    $parts[] = $this->mechanicalTestingDetails->test_method;
                }

                if ($this->mechanicalTestingDetails->bone_tested) {
                    $parts[] = $this->mechanicalTestingDetails->bone_tested;
                }

                if ($this->mechanicalTestingDetails->orientation) {
                    $parts[] = $this->mechanicalTestingDetails->orientation;
                }
            }

            return strtolower(str_replace([' ', '-'], '_', implode('_', $parts)));
        }

        return collect($this->getContextParts($withAnalysisType))
            ->map(fn ($part) => $part['label'].': '.$part['value'])
            ->implode(', ');
    }

    public function formatTestMethod(string $testMethod): string
    {
        $testMethodMap = [
            'bending_3pt' => '3-point bending',
            'bending_4pt' => '4-point bending',
            'torsion' => 'Torsion',
            'compression' => 'Compression',
        ];

        return $testMethodMap[$testMethod] ?? ucfirst(str_replace('_', ' ', $testMethod));
    }

    public function formatBoneTested(string $boneTested): string
    {
        $specialCases = [
            'forelimb' => 'Forelimb (radius + ulna)',
        ];

        if (isset($specialCases[$boneTested])) {
            return $specialCases[$boneTested];
        }

        return ucfirst($boneTested);
    }

    public function formatOrientation($mechanicalTestingDetails): string
    {
        $orientation = $mechanicalTestingDetails->orientation;

        // for compression tests, add off axis angle
        if ($mechanicalTestingDetails->test_method === 'compression') {
            if ($orientation === 'off-axis' && $mechanicalTestingDetails->off_axis_angle) {
                return "Off-axis ({$mechanicalTestingDetails->off_axis_angle}°)";
            }

            return ucfirst($orientation);
        }

        // for bending tests
        if ($orientation === 'other' && $mechanicalTestingDetails->orientation_other) {
            return ucfirst($mechanicalTestingDetails->orientation_other);
        }

        return ucfirst($orientation);
    }
}
