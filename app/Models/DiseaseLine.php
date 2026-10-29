<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiseaseLine extends Model
{
    protected $fillable = [
        'label',
        'iri',
        'description',
        'ontology',
        'short_form',
        'obo_id',
        'type',
        'xrefs',
    ];

    protected $casts = [
        'xrefs' => 'array',
    ];

    public function studies()
    {
        return $this->belongsToMany(Study::class)->withTimestamps();
    }
}
