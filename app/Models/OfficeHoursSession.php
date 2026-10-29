<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeHoursSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_date',
        'start_time',
        'end_time',
        'teams_link',
        'status',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function registrations()
    {
        return $this->hasMany(OfficeHoursRegistration::class, 'session_id');
    }

    public function isActive()
    {
        if ($this->trashed()) {
            return false;
        }

        return $this->status === 'active';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function registrationCount()
    {
        return $this->registrations()->whereNull('cancelled_at')->count();
    }
}
