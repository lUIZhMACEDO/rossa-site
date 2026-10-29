<?php

namespace App\Models;

use App\Enums\InstitutionType;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'department',
        'organization',
        'country_id',
        'state',
        'institution_type',
        'role_id',
        'disabled_at',
        'department',
        'organization',
        'country_id',
        'state',
        'institution_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'disabled_at' => 'datetime',
        'password' => 'hashed',
        'institution_type' => InstitutionType::class,
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function isDisabled(): bool
    {
        return ! is_null($this->disabled_at);
    }

    public function studies()
    {
        return $this->hasMany(Study::class);
    }

    public function contributedStudies(): BelongsToMany
    {
        return $this->belongsToMany(Study::class)->withTimestamps();
    }

    public function studyContributorInvitations(): HasMany
    {
        return $this->hasMany(StudyContributorInvitation::class);
    }

    public function coreFacilities()
    {
        return $this->hasMany(CoreFacility::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class);
    }

    public function apiTokenRequest(): HasOne
    {
        return $this->hasOne(ApiTokenRequest::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $fromParts = trim("{$this->first_name} {$this->last_name}");

        return $fromParts !== '' ? $fromParts : ($this->name ?? '');
    }

    public function hasEduEmail(): bool
    {
        return str_ends_with(strtolower($this->email), '.edu');
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    // Anyone with unrestricted access is considered an admin
    public function isAdmin()
    {
        return $this->hasPermission('no-restriction');
    }

    public function hasRole($role)
    {
        return $this->role && $this->role->name === $role;
    }

    // Check if user has a certain permission. no-restriction overrides all others
    public function hasPermission($permission)
    {
        return $this->role && (
            $this->role->permissions()->where('name', 'no-restriction')->exists() ||
            $this->role->permissions()->where('name', $permission)->exists()
        );
    }

    /**
     * True if this user is listed as a corresponding investigator on the study.
     * Matches on user_id, or on email for investigators added before this
     * user's account existed (user_id is only resolved when the row is saved).
     */
    public function isCorrespondingInvestigatorFor(Study $study): bool
    {
        return $study->investigators()
            ->where('corresponding', true)
            ->where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhereRaw('LOWER(email) = ?', [strtolower($this->email)]);
            })
            ->exists();
    }

    /**
     * Who can invite and remove people on a study:
     * the owner, a corresponding investigator, or a ROSSA admin.
     * Contributors (editors) can edit the form but not manage access.
     */
    public function canManageSharing(Study $study): bool
    {
        return $study->user_id === $this->id
            || $this->hasPermission('manage-studies')
            || $this->isCorrespondingInvestigatorFor($study);
    }

    /**
     * Who can submit a study: ROSSA staff, or a corresponding investigator.
     * Studies inside a collection keep the owner rule, because they are
     * submitted through the collection's own flow.
     */
    public function canSubmitStudy(Study $study): bool
    {
        if ($this->hasPermission('manage-studies')) {
            return true;
        }

        if ($study->collection_id !== null) {
            return $study->isOwner($this);
        }

        return $this->isCorrespondingInvestigatorFor($study);
    }

    public function canViewStudy(Study $study): bool
    {
        if ($study->user_id === $this->id) {
            return true;
        }

        if ($study->contributors()->whereKey($this->id)->exists()) {
            return true;
        }

        if ($this->isCorrespondingInvestigatorFor($study)) {
            return true;
        }

        if ($this->hasPermission('manage-studies')) {
            return true;
        }

        // open study view for users on collections
        return $study->collection !== null
            && $study->collection->canUserView($this);
    }

    public function canEditStudy(Study $study)
    {
        // Study owner can edit
        if ($study->user_id === $this->id) {
            return true;
        }

        if ($study->contributors()->whereKey($this->id)->exists()) {
            return true;
        }

        if ($this->isCorrespondingInvestigatorFor($study)) {
            return true;
        }

        // Users with 'manage-studies' permission can edit any study
        if ($this->hasPermission('manage-studies')) {
            return true;
        }

        // Otherwise, no permission to edit
        return false;
    }
}
