<?php

namespace App\Services\Collections;

use App\Models\Collection;
use App\Models\ComparisonGroup;
use App\Models\Study;
use Illuminate\Support\Facades\DB;

/**
 * Generates comparison groups for every study in a collection.
 *
 * Pairs each experimental sample group with the control group that shares its
 * split value (here: sex), producing one comparison per split. For Rowe that is
 * two per study: KO Male vs WT Male, KO Female vs WT Female.
 *
 * Order convention matches the existing manual studies: control first,
 * experimental second, stored as string IDs in sample_group_ids. Statistics
 * compute direction relative to that order, so getting it right matters.
 */
class ComparisonGroupGenerator
{
    public array $scheme = [
        // Which sample-group attribute pairs an experimental group with its
        // control. Groups sharing this value are compared.
        'pair_by' => 'sex',
    ];

    public function __construct(?array $scheme = null)
    {
        if ($scheme !== null) {
            $this->scheme = $scheme;
        }
    }

    public function generate(Collection $collection): array
    {
        $summary = [
            'studies_total' => 0,
            'studies_created' => 0,
            'studies_skipped' => 0,
            'comparisons_created' => 0,
            'problems' => [],
        ];

        $studies = $collection->studies()
            ->with(['sampleGroups.animalModel'])
            ->get();

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
                    $summary['comparisons_created'] += $result['created'];
                }
            }
        });

        return $summary;
    }

    private function generateForStudy(Study $study): array
    {
        // Skip a study that already has comparisons, so re-runs are safe.
        if ($study->comparisonGroups()->exists()) {
            return ['skipped' => true, 'error' => null, 'created' => 0];
        }

        $pairBy = $this->scheme['pair_by'];
        $groups = $study->sampleGroups;

        $created = 0;

        // For each distinct pairing value (Male, Female), find the one control
        // and one experimental group and compare them.
        foreach ($groups->pluck($pairBy)->unique() as $pairValue) {
            $inPair = $groups->where($pairBy, $pairValue);

            $control = $inPair->firstWhere('control', true);
            $experimental = $inPair->firstWhere('control', false);

            if (! $control || ! $experimental) {
                // A sex with only one side present — skip that pairing rather
                // than create a one-sided comparison.
                continue;
            }

            // comparison_groups has no name column; the label is derived from
            // the two groups it points at, so we only store the pair.
            // Control first, experimental second, as strings — matches the
            // existing manual convention exactly.
            ComparisonGroup::create([
                'study_id' => $study->id,
                'sample_group_ids' => [(string) $control->id, (string) $experimental->id],
            ]);

            $created++;
        }

        if ($created === 0) {
            return ['skipped' => false, 'error' => 'no complete control/experimental pairs found', 'created' => 0];
        }

        return ['skipped' => false, 'error' => null, 'created' => $created];
    }
}
