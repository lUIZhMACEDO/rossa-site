<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionSpecificType extends Model
{
    use HasFactory;

    protected $fillable = [
        'intervention_category_id',
        'name',
        'description',
        'sort_order',
        'is_other',
    ];

    protected $casts = [
        'is_other' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InterventionCategory::class, 'intervention_category_id');
    }
}
