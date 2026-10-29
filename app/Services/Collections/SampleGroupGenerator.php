<?php

namespace App\Services\Collections;

use App\Models\Collection;
use App\Models\SampleGroup;
use App\Models\Study;
use Illuminate\Support\Facades\DB;

/**
 * Generates sample groups for every study in a collection from a scheme.
 *
 * Built for the Rowe/KOMP screen (KO vs WT, split by sex, homozygous, 12wk),
 * but the scheme is data rather than hardcoded logic so a future collection
 * can supply a different one. The generator only knows how to:
 *   - resolve which animal model is experimental (has a gene) vs control (none)
 *   - cross the group definitions with the split values
 *   - name each group from a template
 *   - skip a study that already has sample groups
 *
 * Anything genuinely collection-specific lives in the scheme, not here.
 */
class SampleGroupGenerator
{
    /**
     * Default scheme = Rowe/KOMP. Override per collection as needed.
     *
     * groups[].model: 'experimental' (animal model with a gene_id) or
     *                 'control' (animal model with null gene_id)
     * split_by:       attribute => list of values; the cross-product of
     *                 groups x splits is generated per study
     * name_template:  {gene}, {group}, plus any split key (e.g. {sex})
     */
    public array $scheme = [
        'age' => 12,
        'name_template' => '{gene} {group} {sex}',
        'split_by' => [
            'sex' => ['M', 'F'],
        ],
        'groups' => [
            ['label' => 'KO', 'model' => 'experimental', 'genotype' => '-/-', 'control' => false],
            ['label' => 'WT', 'model' => 'control',      'genotype' => '+/+', 'control' => true],
        ],
    ];

    public function __construct(?array $scheme = null)
    {
        if ($scheme !== null) {
            $this->scheme = $scheme;
        }
    }

    /**
     * Run across a collection. Returns a per-study summary.
     */
    public function generate(Collection $collection): array
    {
        $summary = [
            'studies_total' => 0,
            'studies_created' => 0,
            'studies_skipped' => 0,
            'groups_created' => 0,
            'problems' => [],
        ];

        $studies = $collection->studies()->with('animalModels')->get();
        $summary['studies_total'] = $studies->count();

        DB::transaction(function () use ($studies, &$summary) {
            foreach ($studies as $study) {
                $result = $this->generateForStudy($study);

                if ($result['skipped']) {
                    $summary['studies_skipped']++;
                } elseif ($result['error']) {
                    $summary['problems'][] = "{$study->identifier}: {$result['error']}";
                } else {
                    $summary['studies_created']++;
                    $summary['groups_created'] += $result['created'];
                }
            }
        });

        return $summary;
    }

    /**
     * @return array{skipped: bool, error: ?string, created: int}
     */
    private function generateForStudy(Study $study): array
    {
        // Skip a study that already has sample groups, so re-runs are safe.
        if ($study->sampleGroups()->exists()) {
            return ['skipped' => true, 'error' => null, 'created' => 0];
        }

        // Resolve the two roles from the study's animal models.
        $experimental = $study->animalModels->first(fn ($m) => $m->gene_id !== null);
        $control = $study->animalModels->first(fn ($m) => $m->gene_id === null);

        $models = ['experimental' => $experimental, 'control' => $control];

        // Verify every role the scheme needs is present before writing anything.
        foreach ($this->scheme['groups'] as $group) {
            if (empty($models[$group['model']])) {
                return [
                    'skipped' => false,
                    'error' => "no {$group['model']} animal model found",
                    'created' => 0,
                ];
            }
        }

        $created = 0;
        $geneSymbol = $experimental?->gene?->symbol ?? $study->title;

        // Cross groups with the split values (here: sex).
        [$splitKey, $splitValues] = $this->splitDimension();

        foreach ($this->scheme['groups'] as $group) {
            foreach ($splitValues as $splitValue) {
                SampleGroup::create([
                    'study_id' => $study->id,
                    'animal_model_id' => $models[$group['model']]->id,
                    'age' => $this->scheme['age'],
                    $splitKey => $splitValue,
                    'genotype' => $group['genotype'],
                    'control' => $group['control'],
                    'label' => $this->buildName($group['label'], $geneSymbol, $splitKey, $splitValue),
                ]);
                $created++;
            }
        }

        return ['skipped' => false, 'error' => null, 'created' => $created];
    }

    private function splitDimension(): array
    {
        $splitKey = array_key_first($this->scheme['split_by']);

        return [$splitKey, $this->scheme['split_by'][$splitKey]];
    }

    private function buildName(string $groupLabel, string $gene, string $splitKey, string $splitValue): string
    {
        // Map stored split slugs to readable names for the label (M -> Male).
        $readable = [
            'M' => 'Male',
            'F' => 'Female',
        ];

        return strtr($this->scheme['name_template'], [
            '{gene}' => $gene,
            '{group}' => $groupLabel,
            '{'.$splitKey.'}' => $readable[$splitValue] ?? $splitValue,
        ]);
    }
}
