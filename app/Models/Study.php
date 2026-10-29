<?php

namespace App\Models;

use App\Enums\StudyStatus;
use App\Enums\UploadSteps;
use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Study extends Model
{
    use HasFactory, LocksWithStudy, SoftDeletes;

    protected $fillable = [
        'identifier', // ROSSA ID
        'title',
        'summary',
        'funding_sources',
        'conflicts',
        'completion_date',
        'is_published',
        'doi',
        'pubmed_id',
        'publication_plan',
        'embargo_months',
        'embargo_start_date',
        'embargo_reminder_date',
        'embargo_reminder_sent_at',
        'subject_areas',
        'user_id',
        'reviewer_id',
        'reviewed_at',
        'rejection_reason',
        'approved_at',
        'submitted_at',
        'subscribers_notified_at',
        'withdrawn_at',
        'status',
        'collection_id',

        // animal experimentation
        'performs_genetic_modification',
        'performs_drug_treatment',
        'performs_gonadectomy',
        'skips_image_data',
        'performs_other_intervention',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'embargo_start_date' => 'date',
        'embargo_reminder_date' => 'date',
        'embargo_reminder_sent_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'subscribers_notified_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'is_published' => 'boolean',
        'embargo_months' => 'integer',
        'subject_areas' => 'json',
        'status' => StudyStatus::class,

        // animal experimentation
        'performs_genetic_modification' => 'boolean',
        'performs_drug_treatment' => 'boolean',
        'performs_gonadectomy' => 'boolean',
        'skips_image_data' => 'boolean',
        'performs_other_intervention' => 'boolean',
    ];

    public function scopeApproved($query)
    {
        return $query->where('status', StudyStatus::APPROVED);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->approved()->nonEmbargoed()->where(function ($q) {
            $q->whereNull('collection_id')
                ->orWhereHas('collection', fn ($collection) => $collection->where('status', 'approved'));
        });
    }

    public function isPubliclyVisible(): bool
    {
        if ($this->status !== StudyStatus::APPROVED || $this->isEmbargoed()) {
            return false;
        }

        if ($this->collection_id === null) {
            return true;
        }

        return $this->collection?->status === 'approved';
    }

    public function scopeDeepSearch($queryBuilder, $searchTerm)
    {
        return $queryBuilder->where(function ($q2) use ($searchTerm) {
            // Search basic study fields
            $q2->where('identifier', 'iLIKE', "%{$searchTerm}%")
                ->orWhere('title', 'iLIKE', "%{$searchTerm}%")
                ->orWhere('summary', 'iLIKE', "%{$searchTerm}%")
                ->orWhere('doi', 'iLIKE', "%{$searchTerm}%")
                ->orWhere('pubmed_id', 'iLIKE', "%{$searchTerm}%")

                // Search in subject_areas JSON field
                ->orWhereJsonContains('subject_areas', $searchTerm)

                // Search in investigators
                ->orWhereHas('investigators', function ($q3) use ($searchTerm) {
                    $q3->where('first_name', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'iLIKE', "%{$searchTerm}%");
                })

                // Collection-inherited metadata. Studies imported as part of a
                // collection hold null in their own summary column and have no
                // investigator rows of their own, so reach through to the parent
                // collection for the same fields searched above.
                ->orWhereHas('collection', function ($q3) use ($searchTerm) {
                    $q3->where('identifier', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('title', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('summary', 'iLIKE', "%{$searchTerm}%");
                })

                ->orWhereHas('collection.investigators', function ($q3) use ($searchTerm) {
                    $q3->where('first_name', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'iLIKE', "%{$searchTerm}%");
                })

                ->orWhereHas('collection.publications', function ($q3) use ($searchTerm) {
                    $q3->where('title', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('doi', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('pubmed_id', 'iLIKE', "%{$searchTerm}%");
                })

                // Search in animal strains (animal lines)
                ->orWhereHas('animalModels.animalLine', function ($q3) use ($searchTerm) {
                    $q3->where('label', 'iLIKE', "%{$searchTerm}%");
                })

                // Search in animal model metadata
                ->orWhereHas('animalModels', function ($q3) use ($searchTerm) {
                    $q3->where('animal_model_name', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('animal_model_description', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('investigator', 'iLIKE', "%{$searchTerm}%");
                })

                // Search in animal model categories
                ->orWhereHas('animalModels.geneticCategory', function ($q3) use ($searchTerm) {
                    $q3->where('name', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'iLIKE', "%{$searchTerm}%");
                })

                // Search in genes
                ->orWhereHas('animalModels.gene', function ($q3) use ($searchTerm) {
                    $q3->where('symbol', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('name', 'iLIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('animalModels.targetGenes', function ($q3) use ($searchTerm) {
                    $q3->where('symbol', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('name', 'iLIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('animalModels.parents.gene', function ($q3) use ($searchTerm) {
                    $q3->where('symbol', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('name', 'iLIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('animalModels.parents.animalLine', function ($q3) use ($searchTerm) {
                    $q3->where('label', 'iLIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('animalModels.parents', function ($q3) use ($searchTerm) {
                    $q3->where('investigator', 'iLIKE', "%{$searchTerm}%");
                })

                // Search in diseases
                ->orWhereHas('diseaseLines', function ($q3) use ($searchTerm) {
                    $q3->where('label', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'iLIKE', "%{$searchTerm}%");
                })

                // Search in molecules (drugs)
                ->orWhereHas('drugAdministrationPlans.events.molecule', function ($q3) use ($searchTerm) {
                    $q3->where('pref_name', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('chembl_id', 'iLIKE', "%{$searchTerm}%");
                })
                // Search in analysis modality (e.g. micro CT, DEXA)
                ->orWhereHas('analysisUploads.analysisType', function ($q3) use ($searchTerm) {
                    $micrctTerms = ['microct', 'micro-ct', 'micro ct', 'uct', 'µct', 'ct'];
                    $q3->where('name', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere('code', 'iLIKE', "%{$searchTerm}%")
                        ->orWhere(function ($q4) use ($searchTerm, $micrctTerms) {
                            if (in_array(strtolower($searchTerm), $micrctTerms, true)) {
                                $q4->where('code', 'micro_ct');
                            }
                        });
                })

                // Search in sample group sex
                ->orWhereHas('sampleGroups', function ($q3) use ($searchTerm) {
                    $sexMap = ['male' => 'M', 'female' => 'F', 'm' => 'M', 'f' => 'F'];
                    $normalized = $sexMap[strtolower(trim($searchTerm))] ?? null;

                    if ($normalized) {
                        $q3->where('sex', $normalized);
                    } else {
                        $q3->where('sex', 'iLIKE', "%{$searchTerm}%");
                    }
                })

                // Search in tags
                ->orWhereHas('tags', function ($q3) use ($searchTerm) {
                    $q3->where('name', 'iLIKE', "%{$searchTerm}%");
                });
        });
    }

    public function scopeEmbargoed($query)
    {
        return $query->whereNotNull('embargo_start_date')
            ->whereNotNull('publication_plan');
    }

    public function scopeNonEmbargoed($query)
    {
        return $query->whereNull('embargo_start_date')
            ->whereNull('publication_plan');
    }

    public function isEmbargoed(): bool
    {
        return $this->embargo_start_date && $this->publication_plan !== null;
    }

    /**
     * Calculate the embargo reminder date based on embargo_start_date and embargo_months.
     * Returns null if the calculated date would be in the past or if required fields are missing.
     */
    public function calculateEmbargoReminderDate(): ?Carbon
    {
        if (! $this->embargo_start_date || ! $this->embargo_months) {
            return null;
        }

        $reminderDate = $this->embargo_start_date->copy()->addMonths($this->embargo_months);

        // Don't set reminder date if it's in the past
        if ($reminderDate->isPast()) {
            return null;
        }

        return $reminderDate;
    }

    protected static function boot()
    {
        // TODO: Check if this is the best way to generate the identifier
        parent::boot();

        static::creating(function ($study) {
            $study->identifier = static::generateIdentifier();
        });
    }

    protected static function generateIdentifier()
    {
        $datetime = now()->format('Ymd');  // Format: 20240311
        $random = strtoupper(substr(uniqid(), -4));  // 4 random characters

        return "ROSSA-{$datetime}-{$random}";
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * @return HasMany<SampleGroup, $this>
     */
    public function sampleGroups(): HasMany
    {
        return $this->hasMany(SampleGroup::class);
    }

    public function analysisUploads()
    {
        return $this->hasMany(AnalysisUpload::class);
    }

    public function mediaUploads()
    {
        return $this->hasMany(MediaUpload::class);
    }

    public function studyFigures()
    {
        return $this->hasMany(StudyFigure::class);
    }

    /**
     * Bytes attributed to this study (media, figures, and spreadsheet uploads).
     */
    public function storageBytesUsed(): int
    {
        return (int) $this->mediaUploads()->sum('size')
            + (int) $this->studyFigures()->sum('size')
            + (int) $this->analysisUploads()->sum('size');
    }

    public function canStoreBytes(int $additionalBytes): bool
    {
        $limit = (int) config('media_upload.max_study_bytes');

        if ($limit <= 0) {
            return true;
        }

        return ($this->storageBytesUsed() + max(0, $additionalBytes)) <= $limit;
    }

    public function comparisonGroups()
    {
        return $this->hasMany(ComparisonGroup::class);
    }

    public function inducibleDrugTreatments()
    {
        return $this->hasMany(InducibleDrugTreatment::class);
    }

    /**
     * @return HasMany<Investigator, $this>
     */
    public function investigators(): HasMany
    {
        return $this->hasMany(Investigator::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function contributorInvitations(): HasMany
    {
        return $this->hasMany(StudyContributorInvitation::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /**
     * Everyone working on this study: the owner, listed investigators, and contributors.
     */
    public function teamEmails(): \Illuminate\Support\Collection
    {
        return collect([$this->user?->email])
            ->merge($this->investigators->pluck('email'))
            ->merge($this->contributors->pluck('email'))
            ->filter()
            ->map(fn ($email) => strtolower(trim($email)))
            ->unique()
            ->values();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isLockedForEditing(?User $user = null): bool
    {
        if ($user !== null) {
            if ($user->hasPermission('manage-studies')) {
                return false;
            }

            if (
                $this->status === StudyStatus::APPROVED &&
                $this->isEmbargoed() &&
                $user->canEditStudy($this)
            ) {
                return false;
            }
        }

        return in_array($this->status, [StudyStatus::PENDING, StudyStatus::APPROVED], true);
    }

    /**
     * Phenotype analysis is always locked for non-admins when PENDING or APPROVED,
     * even if the study is embargoed and the user is the owner.
     */
    public function isPhenotypeAnalysisLocked(?User $user = null): bool
    {
        if ($user !== null && $user->hasPermission('manage-studies')) {
            return false;
        }

        return in_array($this->status, [StudyStatus::PENDING, StudyStatus::APPROVED], true);
    }

    public function scopeAbandonedDrafts($query, ?Carbon $cutoff = null)
    {
        return $query
            ->where('status', StudyStatus::IN_PROGRESS)
            ->where('updated_at', '<=', $cutoff ?? now()->subDays(90));
    }

    public function isAbandonedDraft(int $days = 90): bool
    {
        return $this->status === StudyStatus::IN_PROGRESS
            && $this->updated_at !== null
            && $this->updated_at->lte(now()->subDays($days));
    }

    protected function getStudyForLock(): ?self
    {
        return $this;
    }

    /**
     * @return BelongsToMany<DiseaseLine, $this>
     */
    public function diseaseLines(): BelongsToMany
    {
        return $this->belongsToMany(DiseaseLine::class, 'disease_line_study')
            ->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'study_tag')
            ->withTimestamps();
    }

    public function hasSubjectAreaContent(): bool
    {
        $hasTags = $this->relationLoaded('tags')
            ? $this->tags->isNotEmpty()
            : $this->tags()->exists();

        if ($hasTags) {
            return true;
        }

        return $this->relationLoaded('diseaseLines')
            ? $this->diseaseLines->isNotEmpty()
            : $this->diseaseLines()->exists();
    }

    /**
     * @return HasMany<AnimalModel, $this>
     */
    public function animalModels(): HasMany
    {
        return $this->hasMany(AnimalModel::class);
    }

    public function gonadectomyProcedures()
    {
        return $this->hasMany(GonadectomyProcedure::class);
    }

    public function drugAdministrationPlans(): HasMany
    {
        return $this->hasMany(DrugAdministrationPlan::class)->orderBy('id');
    }

    /**
     * Submission Validation Helpers
     * In general, these return:
     * 0 = No progress
     * 1 = Partial progress
     * 2 = Complete
     */

    /**
     * This should return 0 for no progress, 1 for partial progress, and 2 for complete.
     */
    public function filledFieldProgress(array $fields, bool $needsOne = false)
    {
        $filledCount = 0;
        foreach ($fields as $field) {
            if (! empty($this->$field)) {
                $filledCount++;
            }
        }

        if ($needsOne) {
            return $filledCount > 0 ? 2 : 0;
        }

        if ($filledCount === 0) {
            return 0; // No progress
        } elseif ($filledCount < count($fields)) {
            return 1; // Partial progress
        } else {
            return 2; // Complete
        }
    }

    /**
     * Study Information Tab Progress
     */
    public function progressStudyInformation()
    {
        $baseFields = [
            'title',
            'summary',
            'completion_date',
        ];

        $baseProgress = $this->filledFieldProgress($baseFields);

        // is_published is required before we can evaluate branch-specific requirements
        // if (is_null($this->is_published)) {
        //     return $baseProgress === 0 ? 0 : 1;
        // }

        $requiredFields = [];
        if ($this->is_published === true) {
            $requiredFields[] = 'doi';
            $requiredFields[] = 'pubmed_id';
        } elseif ($this->publication_plan == 'different_journal') {
            $requiredFields[] = 'embargo_start_date';
            $requiredFields[] = 'embargo_reminder_date';
        }

        $conditionalProgress = $this->filledFieldProgress($requiredFields);

        if ($baseProgress === 2 && ($conditionalProgress === 2 || empty($requiredFields))) {
            return 2;
        }

        return ($baseProgress > 0 || $conditionalProgress > 0) ? 1 : 0;
    }

    /**
     * Subject Areas Tab Progress
     */
    public function progressSubjectAreas()
    {
        return 2;
    }

    /**
     * Investigators Tab Progress
     */
    public function progressInvestigators()
    {
        if ($this->collection_id !== null) {
            $investigators = $this->collection?->investigators ?? collect();

            if ($investigators->isEmpty()) {
                return 1;
            }

            return $investigators->contains(fn ($i) => $i->corresponding) ? 2 : 1;
        }

        $investigators = $this->getRelationValue('investigators');

        if ($investigators->isEmpty()) {
            return 1;
        }

        return $investigators->contains(fn ($i) => $i->corresponding) ? 2 : 1;
    }

    /**
     * Animal Experimentation Setup Tab Progress
     */
    public function progressAnimalExperimentation()
    {
        $requiredFields = [
            'performs_genetic_modification',
            'performs_drug_treatment',
            'performs_gonadectomy',
        ];

        return $this->filledFieldProgress($requiredFields, true);
    }

    /**
     * Animal Models Tab Progress
     */
    public function progressAnimalModels()
    {
        if ($this->animalModels()->count() > 0) {
            return 2; // Complete
        }

        return 0; // No progress
    }

    /**
     * Drug Treatments Tab Progress
     */
    public function progressDrugTreatments()
    {
        if (! $this->performs_drug_treatment) {
            return 2; // Not applicable, consider complete
        }

        if ($this->drugAdministrationPlans()->count() > 0) {
            return 2; // Complete
        }

        return 0; // No progress
    }

    /**
     * Gonadectomy Procedures Tab Progress
     */
    public function progressGonadectomyProcedures()
    {
        if (! $this->performs_gonadectomy) {
            return 2; // Not applicable, consider complete
        }

        if ($this->gonadectomyProcedures()->count() > 0) {
            return 2; // Complete
        }

        return 0; // No progress
    }

    /**
     * Other Interventions Tab Progress
     */
    public function progressOtherInterventions()
    {
        if (! $this->performs_other_intervention) {
            return 2; // Not applicable, consider complete
        }

        if ($this->otherInterventionPlans()->count() > 1) {
            return 2; // Complete: at least a test plan and a control plan
        }

        if ($this->otherInterventionPlans()->count() > 0) {
            return 1; // Partial: one plan exists, likely still needs its control
        }

        return 0; // No progress
    }

    /**
     * Other Interventions
     */
    public function otherInterventionPlans(): HasMany
    {
        return $this->hasMany(OtherInterventionPlan::class);
    }

    /**
     * Sample Groups Tab Progress
     */
    public function progressSampleGroups()
    {
        if ($this->sampleGroups()->count() > 1) {
            return 2; // Complete
        }

        if ($this->sampleGroups()->count() > 0) {
            return 1; // Partial progress
        }

        return 0; // No progress
    }

    /**
     * Comparison Groups Tab Progress
     */
    public function progressComparisonGroups()
    {
        if ($this->comparisonGroups()->count() > 0) {
            return 2; // Complete
        }

        return 1;

    }

    /**
     * Analysis Uploads Tab Progress
     */
    public function progressAnalysisUploads()
    {
        if ($this->analysisUploads()->count() > 0) {
            foreach ($this->analysisUploads as $analysisUpload) {
                if (! in_array($analysisUpload->current_step, [
                    UploadSteps::COMPLETE,
                    UploadSteps::SCAN_DATASETS,
                ], true)) {
                    return 1; // Partial progress
                }
            }

            return 2; // Complete
        }

        return 0; // No progress
    }

    /**
     * Image Data (scan datasets) Tab Progress
     *
     * Complete when Micro-CT uploads are finished and/or representative
     * figures exist, or when the user continues without uploading
     * (skips_image_data).
     */
    public function progressImageData()
    {
        $hasIncompleteMedia = false;
        $hasCompleteMedia = false;

        if ($this->mediaUploads()->count() > 0) {
            foreach ($this->mediaUploads as $mediaUpload) {
                if (! $mediaUpload->isComplete()) {
                    $hasIncompleteMedia = true;
                } else {
                    $hasCompleteMedia = true;
                }
            }
        }

        if ($hasIncompleteMedia) {
            return 1;
        }

        if ($hasCompleteMedia || $this->studyFigures()->exists()) {
            return 2;
        }

        return $this->skips_image_data ? 2 : 0;
    }

    /**
     * Get total progress across steps
     */
    public function progressCounts()
    {
        $steps = [
            $this->progressStudyInformation(),
            $this->progressSubjectAreas(),
            $this->progressInvestigators(),
            $this->progressAnimalExperimentation(),
            $this->progressAnimalModels(),
            $this->progressDrugTreatments(),
            $this->progressGonadectomyProcedures(),
            $this->progressOtherInterventions(),
            $this->progressSampleGroups(),
            $this->progressComparisonGroups(),
            $this->progressAnalysisUploads(),
            $this->progressImageData(),
        ];

        $totalSteps = count($steps);
        $completedSteps = count(array_filter($steps, fn ($step) => $step === 2));

        return [
            'total' => $totalSteps,
            'completed' => $completedSteps,
        ];
    }

    public function progressAllAnimalExperimentation()
    {
        $steps = [
            $this->progressAnimalExperimentation(),
            $this->progressAnimalModels(),
            $this->progressDrugTreatments(),
            $this->progressGonadectomyProcedures(),
            $this->progressOtherInterventions(),
        ];

        $totalSteps = count($steps);
        $completedSteps = count(array_filter($steps, fn ($step) => $step === 2));

        return $totalSteps === $completedSteps ? 2 : ($completedSteps > 0 ? 1 : 0);
    }

    public function progressAllPhenotypeAnalysis()
    {
        $steps = [
            $this->progressSampleGroups(),
            $this->progressAnalysisUploads(),
            $this->progressImageData(),
        ];

        $totalSteps = count($steps);
        $completedSteps = count(array_filter($steps, fn ($step) => $step === 2));

        return $totalSteps === $completedSteps ? 2 : ($completedSteps > 0 ? 1 : 0);
    }

    public function isComplete(): bool
    {
        $progress = $this->progressCounts();

        return $progress['total'] > 0 && $progress['total'] === $progress['completed'];
    }

    public function doiLink(?string $url = null)
    {
        $doiUrl = $url ?? $this->doi;
        if (! $doiUrl) {
            return null;
        }

        return 'https://doi.org/'.$doiUrl;
    }

    public function pubmedLink(?string $url = null)
    {
        $pubmedUrl = $url ?? $this->pubmed_id;
        if (! $pubmedUrl) {
            return null;
        }

        return 'https://pubmed.ncbi.nlm.nih.gov/'.$pubmedUrl;
    }

    // get list of incomplete requirements for study submission
    public function getIncompleteRequirements(): array
    {
        $errors = [];

        // Study Information
        if ($this->progressStudyInformation() !== 2) {
            $errors[] = [
                'message' => 'Study Information: Complete all required fields (title, summary, completion date, and publication details)',
                'route' => route('study.study-information', $this->id),
            ];
        }

        // Subject Areas
        // Skipped for collection studies — collections don't currently carry
        // subject areas, and whether they should is still an open question.
        if ($this->collection_id === null && $this->progressSubjectAreas() !== 2) {
            $errors[] = [
                'message' => 'Subject Areas: Add at least one subject area or disease/condition',
                'route' => route('study.subject-areas', $this->id),
            ];
        }

        // Investigators
        if ($this->progressInvestigators() !== 2) {
            $errors[] = [
                'message' => $this->collection_id !== null
                    ? 'Investigators: Add at least one corresponding investigator on the collection'
                    : 'Investigators: Add at least one corresponding investigator',
                'route' => route('study.investigators', $this->id),
            ];
        }

        // Animal Experimentation Setup
        // if ($this->progressAnimalExperimentation() !== 2) {
        //     $errors[] = [
        //         'message' => 'Complete Animal Experimentation setup (genetic modification, drug treatment, and gonadectomy options)',
        //         'route' => route('study.animal-experimentation.setup', $this->id)
        //     ];
        // }

        // Animal Models
        if ($this->progressAnimalModels() !== 2) {
            $errors[] = [
                'message' => 'Animal Models: Add at least one animal model',
                'route' => route('study.animal-experimentation.animal-models', $this->id),
            ];
        }

        // Drug Treatments (if applicable)
        if ($this->progressDrugTreatments() !== 2) {
            $errors[] = [
                'message' => 'Drug Treatments: Add at least one drug treatment plan (or remove drug treatment option from Animal Experimentation setup)',
                'route' => route('study.animal-experimentation.drug-treatment', $this->id),
            ];
        }

        // Gonadectomy Procedures (if applicable)
        if ($this->progressGonadectomyProcedures() !== 2) {
            $errors[] = [
                'message' => 'Gonadectomy Procedure: Add at least one gonadectomy procedure (or remove gonadectomy option from Animal Experimentation setup)',
                'route' => route('study.animal-experimentation.gonadectomy-procedure', $this->id),
            ];
        }

        // Other Interventions (if applicable)
        if ($this->progressOtherInterventions() !== 2) {
            $errors[] = [
                'message' => 'Other Interventions: Add at least one intervention plan (or remove the option from Animal Experimentation setup)',
                'route' => route('study.animal-experimentation.other-intervention', $this->id),
            ];
        }

        // Sample Groups
        if ($this->progressSampleGroups() !== 2) {
            $errors[] = [
                'message' => 'Sample Groups: Add at least two sample groups',
                'route' => route('study.sample-groups', $this->id),
            ];
        }

        // Comparison Groups
        if ($this->progressComparisonGroups() !== 2) {
            $errors[] = [
                'message' => 'Comparison Groups: Add at least one comparison group',
                'route' => route('study.comparison-groups.index', $this->id),
            ];
        }

        // Analysis Uploads
        $uploadProgress = $this->progressAnalysisUploads();
        if ($uploadProgress !== 2) {
            if ($uploadProgress === 0) {
                $errors[] = [
                    'message' => 'Phenotype Analysis: Upload and complete mapping for at least one phenotype analysis',
                    'route' => route('study.phenotype-analysis', $this->id),
                ];
            } else {
                $errors[] = [
                    'message' => 'Phenotype Analysis: Ensure all uploads are completed',
                    'route' => route('study.phenotype-analysis', $this->id),
                ];
            }
        }

        // Image data: visit & continue, or finish uploads
        $imageProgress = $this->progressImageData();
        if ($imageProgress !== 2) {
            if ($imageProgress === 1) {
                $errors[] = [
                    'message' => 'Upload Image Data: Finish or delete incomplete image uploads (failed/abandoned must be deleted)',
                    'route' => route('study.scan-datasets.index', $this->id),
                ];
            } else {
                $errors[] = [
                    'message' => 'Upload Image Data: Upload image files, or open this step and continue',
                    'route' => route('study.scan-datasets.index', $this->id),
                ];
            }
        }

        return $errors;
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Collection inheritance
    |--------------------------------------------------------------------------
    | When a study belongs to a collection, shared metadata lives on the
    | collection rather than being duplicated onto every study row. These
    | accessors read the study's own value first and fall back to the parent
    | collection, so views and reports need no collection-specific logic.
    */

    public function getSummaryAttribute($value)
    {
        return $value ?? $this->collection?->summary;
    }

    public function getFundingSourcesAttribute($value)
    {
        return $value ?? $this->collection?->funding_sources;
    }

    public function getConflictsAttribute($value)
    {
        return $value ?? $this->collection?->conflicts;
    }

    public function getCompletionDateAttribute($value)
    {
        // A mutator bypasses the model's date cast, so parse explicitly here.
        if ($value !== null) {
            return Carbon::parse($value);
        }

        return $this->collection?->completion_date;
    }

    public function getInvestigatorsAttribute()
    {
        // getRelationValue() calls the investigators() relationship method, not
        // this accessor, so there is no recursion. Eager loading still works.
        $own = $this->getRelationValue('investigators');

        if ($own && $own->isNotEmpty()) {
            return $own;
        }

        return $this->collection?->investigators ?? $own;
    }

    /**
     * Ordered animal-experimentation steps for "Save & Continue" navigation.
     * Route name => the boolean flag that gates it, or null if always enabled.
     */
    protected function animalExperimentationStepOrder(): array
    {
        return [
            'study.animal-experimentation.animal-models' => null,
            'study.animal-experimentation.drug-treatment' => 'performs_drug_treatment',
            'study.animal-experimentation.gonadectomy-procedure' => 'performs_gonadectomy',
            'study.animal-experimentation.other-intervention' => 'performs_other_intervention',
        ];
    }

    /**
     * Given the route name of the step currently being viewed, return the URL
     * of the next enabled step in sequence, or Sample Groups once the list is
     * exhausted. Callers don't need to know what comes after them, or re-check
     * every flag from the top, they just say who they are.
     */
    public function nextAnimalExperimentationStepAfter(string $currentRouteName): string
    {
        $steps = $this->animalExperimentationStepOrder();
        $found = false;

        foreach ($steps as $routeName => $flag) {
            if (! $found) {
                if ($routeName === $currentRouteName) {
                    $found = true;
                }

                continue;
            }

            if ($flag === null || $this->{$flag}) {
                return route($routeName, $this);
            }
        }

        return route('study.sample-groups', $this);
    }
}
