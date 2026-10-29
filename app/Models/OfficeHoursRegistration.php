<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeHoursRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'notes',
        'cancelled_at',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(OfficeHoursSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCancelled()
    {
        return ! is_null($this->cancelled_at);
    }
}
