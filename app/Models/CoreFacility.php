<?php

namespace App\Models;

use App\Enums\CoreFacilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CoreFacility extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'cf_id',
        'user_id',
        'status',
        'reviewed_at',
        'reviewer_id',
        'expires_at',
        'rejection_reason',
        'web_status',
        'web_status_desc',
        'web_status_error',
        'web_checked_at',
        'last_email_sent_at',
        'last_refreshed_at',
    ];

    protected $visible = [
        'facility_name',
        'web_address',
        'institution',
        'contact_name',
        'contact_email',
        'secondary_contact_name',
        'secondary_contact_email',
        'analysis_types',
    ];

    protected $casts = [
        'status' => CoreFacilityStatus::class,
        'reviewed_at' => 'datetime',
        'last_refreshed_at' => 'datetime',
        'expires_at' => 'datetime',
        'web_checked_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'analysis_types' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        // fires in model creation
        static::creating(function (CoreFacility $facility) {
            if (empty($facility->cf_id)) {
                $facility->cf_id = self::generateCfId();
            }
        });
    }

    public static function generateCfId(): string
    {
        do {
            $id = 'CF-'.Str::lower(Str::random(6));
        } while (self::where('cf_id', $id)->exists());

        return $id;
    }

    // relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id')->select('id', 'name', 'email');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewer_id')->select('id', 'name');
    }

    public function getWebStatusColor(): string
    {
        return match ((int) $this->web_status) {
            200, 201, 204 => 'success',
            301, 302, 307 => 'warning',
            400, 401, 403, 404 => 'danger',
            500, 502, 503, 504 => 'danger',
            0 => 'secondary', // connection failed
            -1 => 'dark', // system error
            default => 'secondary',
        };
    }
}
