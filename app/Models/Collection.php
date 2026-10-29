<?php

namespace App\Models;

use App\Enums\PermissionType;
use App\Enums\StudyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'identifier',
        'title',
        'summary',
        'funding_sources',
        'conflicts',
        'completion_date',
        'user_id',
        'status',
        'default_body_weight_included',
        'default_voxel_size',
        'default_scan_mode',
        'default_scanner',
        'default_xray_tube_potential',
        'default_integration_time',
        'default_analytics_software',
        'default_dexa_instrument',
        'default_measurement_software',
        'default_histological_approach',
    ];

    protected $casts = [
        'completion_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($collection) {
            if (empty($collection->identifier)) {
                $collection->identifier = static::generateIdentifier();
            }
        });
    }

    protected static function generateIdentifier()
    {
        $datetime = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));

        return "ROSSA-C-{$datetime}-{$random}";
    }

    public function studies(): HasMany
    {
        return $this->hasMany(Study::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investigators(): HasMany
    {
        return $this->hasMany(Investigator::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(CollectionPublication::class);
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        return $query->whereHas('investigators', function ($investigators) use ($user) {
            $investigators
                ->where('corresponding', true)
                ->where(function ($matches) use ($user) {
                    $matches->where('user_id', $user->id)
                        ->orWhere(function ($fallback) use ($user) {
                            $fallback->whereNull('user_id')
                                ->whereRaw('LOWER(email) = ?', [strtolower($user->email)]);
                        });
                });
        });
    }

    public function hasCorrespondingUser(User $user): bool
    {
        return static::query()
            ->whereKey($this->getKey())
            ->visibleToUser($user)
            ->exists();
    }

    public function canUserView(User $user): bool
    {
        if ($user->hasPermission(PermissionType::MANAGE_COLLECTIONS->value)) {
            return true;
        }

        if (! $this->hasCorrespondingUser($user)) {
            return false;
        }

        return $user->hasPermission(PermissionType::VIEW_OWN_COLLECTIONS->value)
            || $user->hasPermission(PermissionType::EDIT_OWN_COLLECTIONS->value)
            || $user->hasPermission(PermissionType::SUBMIT_OWN_COLLECTIONS->value);
    }

    public function scopeWithStudyStatusCounts(Builder $query): Builder
    {
        return $query->withCount([
            'studies',
            'studies as studies_in_progress_count' => fn (Builder $q) => $q->where('status', StudyStatus::IN_PROGRESS),
            'studies as studies_pending_count' => fn (Builder $q) => $q->where('status', StudyStatus::PENDING),
            'studies as studies_approved_count' => fn (Builder $q) => $q->where('status', StudyStatus::APPROVED),
            'studies as studies_rejected_count' => fn (Builder $q) => $q->where('status', StudyStatus::REJECTED),
        ]);
    }

    public function canUserEditMetadata(User $user): bool
    {
        if ($user->hasPermission(PermissionType::MANAGE_COLLECTIONS->value)) {
            return true;
        }

        return $this->status === 'draft'
            && $user->hasPermission(PermissionType::EDIT_OWN_COLLECTIONS->value)
            && $this->hasCorrespondingUser($user);
    }

    public function canUserSubmit(User $user): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        if ($user->hasPermission(PermissionType::MANAGE_COLLECTIONS->value)) {
            return true;
        }

        return $user->hasPermission(PermissionType::SUBMIT_OWN_COLLECTIONS->value)
            && $this->hasCorrespondingUser($user);
    }
}
