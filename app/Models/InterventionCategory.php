<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterventionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sort_order',
    ];

    /**
     * @return HasMany<InterventionSpecificType, $this>
     */
    public function specificTypes(): HasMany
    {
        return $this->hasMany(InterventionSpecificType::class)->orderBy('sort_order');
    }
}
