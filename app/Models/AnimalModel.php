<?php

namespace App\Models;

use App\Enums\CreSystem;
use App\Enums\LocusIdentityType;
use App\Enums\Species;
use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class AnimalModel extends Model
{
    use LocksWithStudy;

    protected $fillable = [
        'study_id',
        'species',
        'animal_line_id',
        'animal_model_name',
        'animal_model_description',
        'functional_outcome_description',
        'locus_identity_type',
        'genomic_chromosome',
        'genomic_start',
        'genomic_end',
        'genomic_assembly',
        'genomic_region_description',
        'genotype_options',
        'investigator',
        'genetic_category_id',
        'genetic_method_id',
        'gene_id',
        'promoter_gene_id',
        'promoter_other',
        'functional_change',
        'tissue_lineage_specificity',
        'gene_product',
        'locus_name_safe_harbor',
        'cre_system_used',
        'cre_animal_model_name',
        'cre_animal_line_id',
        'cre_investigator',
        'cre_regulation_type',
        'intercrossed_from_id',
        'transgene_abbreviation',
        'transgene_biotype',
    ];

    protected $casts = [
        'genotype_options' => 'array',
        'genomic_start' => 'integer',
        'genomic_end' => 'integer',
    ];

    public const DKO_ZYGOSITIES = ['+/+', '+/-', '-/-'];

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * @return BelongsTo<AnimalLine, $this>
     */
    public function animalLine(): BelongsTo
    {
        return $this->belongsTo(AnimalLine::class);
    }

    /**
     * @return HasOne<InducibleDrugTreatment, $this>
     */
    public function inducibleDrugTreatment(): HasOne
    {
        return $this->hasOne(InducibleDrugTreatment::class);
    }

    /**
     * @return BelongsTo<GeneticCategory, $this>
     */
    public function geneticCategory(): BelongsTo
    {
        return $this->belongsTo(GeneticCategory::class);
    }

    /**
     * @return BelongsTo<GeneticMethod, $this>
     */
    public function geneticMethod(): BelongsTo
    {
        return $this->belongsTo(GeneticMethod::class);
    }

    /**
     * @return BelongsTo<Gene, $this>
     */
    public function gene(): BelongsTo
    {
        return $this->belongsTo(Gene::class);
    }

    public function promoterGene()
    {
        return $this->belongsTo(Gene::class, 'promoter_gene_id');
    }

    /**
     * @return BelongsToMany<Gene, $this>
     */
    public function targetGenes(): BelongsToMany
    {
        return $this->belongsToMany(Gene::class, 'animal_model_genes')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /**
     * @return HasMany<AnimalModelParent, $this>
     */
    public function parents(): HasMany
    {
        return $this->hasMany(AnimalModelParent::class)->orderBy('slot');
    }

    public function parentA()
    {
        return $this->hasOne(AnimalModelParent::class)->where('slot', 'a');
    }

    public function parentB()
    {
        return $this->hasOne(AnimalModelParent::class)->where('slot', 'b');
    }

    public function sampleGroups()
    {
        return $this->hasMany(SampleGroup::class);
    }

    public function creAnimalLine()
    {
        return $this->belongsTo(AnimalLine::class, 'cre_animal_line_id');
    }

    /**
     * To be used when trying to find the animal model that this one was intercrossed with.
     */
    public function intercrossedFrom()
    {
        return $this->belongsTo(AnimalModel::class, 'intercrossed_from_id');
    }

    /**
     * To be used when trying to determine if this is a support animal model, i.e. based on another.
     */
    public function intercross()
    {
        return $this->hasOne(AnimalModel::class, 'intercrossed_from_id');
    }

    /**
     * Helper functions
     */
    public function canBeDrugInduced()
    {
        return in_array($this->cre_system_used, [CreSystem::TAMOXIFEN->value, CreSystem::DOXYCYCLINE->value])
            || $this->geneticCategory?->can_cre
            || $this->geneticCategory?->can_drug_induce;
    }

    public function selectOptionLabel()
    {
        $name = $this->animal_model_name;
        if ($name) {
            return $name;
        }

        $name = $this->experimentalStrainLabel();

        if (! $this->geneticCategory) {
            $name .= ' Wild Type';
        }

        return $name;
    }

    public function isCustomAnimalModel(): bool
    {
        return $this->geneticCategory?->template === 'custom_animal_model';
    }

    public function isDoubleKnockout(): bool
    {
        return $this->geneticCategory?->template === 'double_knockout';
    }

    public function usesCustomGenotypes(): bool
    {
        return $this->isCustomAnimalModel() || $this->isDoubleKnockout();
    }

    public function genotypeOptionList(): array
    {
        $options = $this->genotype_options ?? [];
        if (! is_array($options)) {
            return [];
        }

        return array_values(array_filter($options, fn ($label) => is_string($label) && trim($label) !== ''));
    }

    public function experimentalStrainLabel(): string
    {
        if ($this->isDoubleKnockout()) {
            $parentA = $this->relationLoaded('parents')
                ? $this->parents->firstWhere('slot', 'a')
                : $this->parentA;
            $parentB = $this->relationLoaded('parents')
                ? $this->parents->firstWhere('slot', 'b')
                : $this->parentB;

            $parentA = $parentA?->animalLine?->label;
            $parentB = $parentB?->animalLine?->label;

            if ($parentA && $parentB) {
                return $parentA === $parentB ? $parentA : $parentA.' × '.$parentB;
            }

            return $parentA ?: ($parentB ?: '');
        }

        return $this->animalLine?->label ?? '';
    }

    /**
     * Genes attached via the custom pivot, DKO parents, or the legacy single gene_id.
     *
     * @return Collection<int, Gene>
     */
    public function attachedGenes()
    {
        $genes = $this->targetGenes;

        if ($this->relationLoaded('parents')) {
            foreach ($this->parents as $parent) {
                if ($parent->gene) {
                    $genes = $genes->push($parent->gene);
                }
            }
        } else {
            $parentGenes = $this->parents()->with('gene')->get()->pluck('gene')->filter();
            $genes = $genes->concat($parentGenes);
        }

        if ($this->gene && $genes->where('id', $this->gene_id)->isEmpty()) {
            $genes = $genes->prepend($this->gene);
        }

        return $genes->unique('id')->values();
    }

    public function sampleGroupGenotypeChoices(): array
    {
        if ($this->usesCustomGenotypes()) {
            $options = $this->genotypeOptionList();
            if (count($options) > 0) {
                return $options;
            }
        }

        return [];
    }

    public function genomicRegionSummary(): ?string
    {
        if ($this->locus_identity_type !== LocusIdentityType::GENOMIC_REGION->value) {
            return null;
        }

        $parts = array_filter([
            $this->genomic_chromosome ? 'Chr '.$this->genomic_chromosome : null,
            ($this->genomic_start && $this->genomic_end)
                ? number_format($this->genomic_start).'–'.number_format($this->genomic_end)
                : null,
            $this->genomic_assembly,
        ]);

        $summary = implode(', ', $parts);
        if ($this->genomic_region_description) {
            $summary = trim($summary.($summary ? ' — ' : '').$this->genomic_region_description);
        }

        return $summary ?: null;
    }

    public function speciesEnum(): ?Species
    {
        return $this->species ? Species::tryFrom($this->species) : null;
    }

    public function syncTargetGenes(array $geneIds): void
    {
        $geneIds = array_values(array_unique(array_filter($geneIds)));
        $sync = [];
        foreach ($geneIds as $index => $geneId) {
            $sync[$geneId] = ['position' => $index + 1];
        }
        $this->targetGenes()->sync($sync);
        $this->gene_id = $geneIds[0] ?? null;
        $this->save();
    }

    public function clearTargetGenes(): void
    {
        $this->targetGenes()->detach();
        $this->gene_id = null;
        $this->save();
    }

    public function clearGenomicRegion(): void
    {
        $this->forceFill([
            'genomic_chromosome' => null,
            'genomic_start' => null,
            'genomic_end' => null,
            'genomic_assembly' => null,
            'genomic_region_description' => null,
        ])->save();
    }

    public static function dkoCombinationHint(string $zygA, string $zygB): string
    {
        return match (true) {
            $zygA === '-/-' && $zygB === '-/-' => 'double mutant',
            $zygA === '+/+' && $zygB === '+/+' => 'double WT control',
            $zygA === '-/-' && $zygB === '+/+' => 'Gene A single mutant',
            $zygA === '+/+' && $zygB === '-/-' => 'Gene B single mutant',
            default => '',
        };
    }

    public static function dkoCombinationLabel(?string $symbolA, string $zygA, ?string $symbolB, string $zygB): string
    {
        $geneA = $symbolA ?: 'GeneA';
        $geneB = $symbolB ?: 'GeneB';

        return $geneA.$zygA.' ; '.$geneB.$zygB;
    }

    /**
     * @return list<array{label: string, hint: string, zygA: string, zygB: string}>
     */
    public static function dkoGeneratedCombinations(array $zygositiesA, array $zygositiesB, ?string $symbolA, ?string $symbolB): array
    {
        $combinations = [];
        foreach ($zygositiesA as $zygA) {
            foreach ($zygositiesB as $zygB) {
                $combinations[] = [
                    'label' => self::dkoCombinationLabel($symbolA, $zygA, $symbolB, $zygB),
                    'hint' => self::dkoCombinationHint($zygA, $zygB),
                    'zygA' => $zygA,
                    'zygB' => $zygB,
                ];
            }
        }

        return $combinations;
    }
}
