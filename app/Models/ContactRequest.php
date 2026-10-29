<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'message',
        'emailed',
        'emailed_at',
    ];

    protected $casts = [
        'emailed' => 'boolean',
        'emailed_at' => 'datetime',
    ];
}
