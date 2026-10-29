<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cell extends Model
{
    use HasFactory;

    protected $fillable = [
        'cell_name',
        'display_name',
        'cl_term_name',
        'cl_id',
        'ontology_status',
        'tooltip',
        'group',
    ];

    /**
     * Get the tags associated with this cell.
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}
