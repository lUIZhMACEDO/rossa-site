<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Investigator extends Model
{
    use HasFactory, LocksWithStudy;

    protected $fillable = [
        'study_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'department',
        'organization',
        'country_id',
        'state',
        'corresponding',
        'is_submitting',
        'is_contributing',
        'collection_id',

    ];

    protected $casts = [
        'corresponding' => 'boolean',
        'is_submitting' => 'boolean',
        'is_contributing' => 'boolean',
    ];

    /**
     * @return string[]
     */
    public static function labelsFromFlags(bool $isSubmitting, bool $corresponding, bool $isContributing = false): array
    {
        $labels = [];

        if ($isSubmitting) {
            $labels[] = 'Submitting';
        }

        if ($corresponding) {
            $labels[] = 'Corresponding';
        }

        if ($isContributing) {
            $labels[] = 'Contributing';
        }

        return $labels;
    }

    public static function roleSelectionError(bool $isSubmitting, bool $corresponding, bool $isContributing): ?string
    {
        if ($isContributing && ($isSubmitting || $corresponding)) {
            return 'Contributing must be selected on its own.';
        }

        if (! $isSubmitting && ! $corresponding && ! $isContributing) {
            return 'Select at least one investigator type.';
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function roleLabels(): array
    {
        return static::labelsFromFlags(
            (bool) $this->is_submitting,
            (bool) $this->corresponding,
            (bool) $this->is_contributing,
        );
    }

    public function isContributing(): bool
    {
        return (bool) $this->is_contributing;
    }

    protected static function booted(): void
    {
        static::saving(function (self $investigator) {
            $investigator->user_id = static::resolveLinkedUserId($investigator->email);
        });
    }

    public static function resolveLinkedUserId(?string $email): ?int
    {
        if ($email === null || trim($email) === '') {
            return null;
        }

        return User::query()
            ->where(DB::raw('LOWER(email)'), strtolower($email))
            ->value('id');
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
