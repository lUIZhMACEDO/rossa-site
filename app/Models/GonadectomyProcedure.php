<?php

namespace App\Models;

use App\Enums\GonadectomyType;
use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;

class GonadectomyProcedure extends Model
{
    use LocksWithStudy;

    protected $fillable = [
        'study_id',
        'type',
        'description',
        'age',
    ];

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function sampleGroups()
    {
        return $this->hasMany(SampleGroup::class);
    }

    public function getDisplayName()
    {
        return GonadectomyType::tryFrom($this->type)?->name() ?? 'Unknown';
    }

    public function getShortDisplayName()
    {
        return GonadectomyType::tryFrom($this->type)?->shortName() ?? 'Unknown';
    }
}
