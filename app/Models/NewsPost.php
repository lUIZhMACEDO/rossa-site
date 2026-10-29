<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'excerpt',
        'link_url',
        'link_label',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
