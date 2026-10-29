<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback',
        'user_id',
        'ip_address',
        'user_agent',
        'previous_url',
        'resolved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
