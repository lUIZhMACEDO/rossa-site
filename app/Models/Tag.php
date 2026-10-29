<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, LocksWithStudy;

    protected $fillable = [
        'name',
        'description',
        'tab',
        'cell_id',
        'study_id',
    ];

    /**
     * Get the cell that this tag belongs to (optional relationship).
     */
    public function cell()
    {
        return $this->belongsTo(Cell::class);
    }

    /**
     * Get the study that this tag belongs to (for custom tags).
     */
    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * Get the studies associated with this tag.
     */
    public function studies()
    {
        return $this->belongsToMany(Study::class, 'study_tag')
            ->withTimestamps();
    }
}
