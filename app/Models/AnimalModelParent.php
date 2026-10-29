<?php

namespace App\Models;

use App\Enums\DkoParentModification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalModelParent extends Model
{
    protected $fillable = [
        'animal_model_id',
        'slot',
        'animal_line_id',
        'investigator',
        'gene_id',
        'modification_type',
        'included_zygosities',
    ];

    protected $casts = [
        'included_zygosities' => 'array',
    ];

    /**
     * @return BelongsTo<AnimalModel, $this>
     */
    public function animalModel(): BelongsTo
    {
        return $this->belongsTo(AnimalModel::class);
    }

    /**
     * @return BelongsTo<AnimalLine, $this>
     */
    public function animalLine(): BelongsTo
    {
        return $this->belongsTo(AnimalLine::class);
    }

    /**
     * @return BelongsTo<Gene, $this>
     */
    public function gene(): BelongsTo
    {
        return $this->belongsTo(Gene::class);
    }

    public function modificationLabel(): ?string
    {
        if (! $this->modification_type) {
            return null;
        }

        $enum = DkoParentModification::tryFrom($this->modification_type);

        return $enum?->print() ?? $this->modification_type;
    }
}
