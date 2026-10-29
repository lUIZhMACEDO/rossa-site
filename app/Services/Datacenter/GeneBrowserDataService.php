<?php

namespace App\Services\Datacenter;

use App\Models\AnalysisType;
use App\Models\ComparisonGroup;
use App\Models\DescriptiveStatistic;
use App\Models\Measurement;
use App\Models\Region;
use App\Models\SampleGroup;
use App\Models\SpecimenType;
use App\Models\Study;
use Illuminate\Support\Collection;

class GeneBrowserDataService
{
    public function build(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        $comparisonGroups = ComparisonGroup::query()
            ->with([
                'study',
                'study.user',
            ])
            ->get();

        if ($comparisonGroups->isEmpty()) {
            return $this->emptyPayload($filters);
        }

        $allSampleGroupIds = $comparisonGroups
            ->flatMap(fn (ComparisonGroup $group) => $group->sample_group_ids ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($allSampleGroupIds->isEmpty()) {
            return $this->emptyPayload($filters);
        }

        $sampleGroups = SampleGroup::query()
            ->whereIn('id', $allSampleGroupIds)
            ->with([
                'animalModel.gene',
                'animalModel.geneticCategory',
                'animalModel.geneticMethod',
                'animalModel.animalLine',
            ])
            ->get()
            ->keyBy('id');

        $statistics = DescriptiveStatistic::query()
            ->whereIn('sample_group_id', $allSampleGroupIds)
            ->with(['measurement', 'analysisType'])
            ->get();

        if ($statistics->isEmpty()) {
            return $this->emptyPayload($filters);
        }

        $measurementIds = $statistics->pluck('measurement_id')->filter()->unique()->values();

        $measurements = Measurement::query()
            ->whereIn('id', $measurementIds)
            ->where('graph_exclude', false)
            ->where('public', true)
            ->get()
            ->keyBy('id');

        if ($measurements->isEmpty()) {
            return $this->emptyPayload($filters);
        }

        $analysisTypes = AnalysisType::query()->get()->keyBy('id');
        $regions = Region::query()->get()->keyBy('id');
        $specimenTypes = SpecimenType::query()->get()->keyBy('id');

        $statsBySampleGroupContext = [];
        foreach ($statistics as $stat) {
            if (! $measurements->has($stat->measurement_id)) {
                continue;
            }

            $sampleGroupId = (int) $stat->sample_group_id;
            $contextKey = (string) $stat->context_key;

            if (! isset($statsBySampleGroupContext[$sampleGroupId][$contextKey])) {
                $statsBySampleGroupContext[$sampleGroupId][$contextKey] = [
                    'context' => [
                        'analysis_type_id' => $stat->analysis_type_id,
                        'specimen_type_id' => $stat->specimen_type_id,
                        'analyte_category_id' => $stat->analyte_category_id,
                        'region_id' => $stat->region_id,
                        'test_method' => $stat->test_method,
                        'bone_tested' => $stat->bone_tested,
                    ],
                    'measurements' => [],
                ];
            }

            $statsBySampleGroupContext[$sampleGroupId][$contextKey]['measurements'][(int) $stat->measurement_id] = $stat->mean !== null
                ? (float) $stat->mean
                : null;
        }

        $rawRows = [];

        foreach ($comparisonGroups as $comparisonGroup) {
            $orderedSampleGroupIds = collect($comparisonGroup->sample_group_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();

            if ($orderedSampleGroupIds->count() < 2) {
                continue;
            }

            $controlSampleGroupId = (int) $orderedSampleGroupIds->first();
            $controlSampleGroup = $sampleGroups->get($controlSampleGroupId);

            if (! $controlSampleGroup) {
                continue;
            }

            $study = $comparisonGroup->study;
            if (! $study instanceof Study) {
                continue;
            }

            foreach ($orderedSampleGroupIds->slice(1) as $experimentalSampleGroupId) {
                $experimentalSampleGroup = $sampleGroups->get((int) $experimentalSampleGroupId);

                if (! $experimentalSampleGroup || ! $experimentalSampleGroup->animalModel?->gene) {
                    continue;
                }

                $controlContexts = array_keys($statsBySampleGroupContext[$controlSampleGroupId] ?? []);
                $experimentalContexts = array_keys($statsBySampleGroupContext[(int) $experimentalSampleGroupId] ?? []);
                $sharedContexts = array_values(array_intersect($controlContexts, $experimentalContexts));

                foreach ($sharedContexts as $contextKey) {
                    $controlContext = $statsBySampleGroupContext[$controlSampleGroupId][$contextKey];
                    $experimentalContext = $statsBySampleGroupContext[(int) $experimentalSampleGroupId][$contextKey];
                    $context = $experimentalContext['context'];

                    $normalizedValues = [];

                    foreach ($experimentalContext['measurements'] as $measurementId => $experimentalMean) {
                        $controlMean = $controlContext['measurements'][$measurementId] ?? null;

                        if ($experimentalMean === null || $controlMean === null || (float) $controlMean === 0.0) {
                            continue;
                        }

                        $normalizedValues[$measurementId] = self::calculatePercentChange(
                            (float) $experimentalMean,
                            (float) $controlMean
                        );
                    }

                    if ($normalizedValues === []) {
                        continue;
                    }

                    $analysisTypeId = (int) $context['analysis_type_id'];
                    $regionId = $context['region_id'] !== null ? (int) $context['region_id'] : null;
                    $specimenTypeId = $context['specimen_type_id'] !== null ? (int) $context['specimen_type_id'] : null;
                    $analysisType = $analysisTypes->get($analysisTypeId);

                    $rawRows[] = [
                        'comparison_group_id' => (int) $comparisonGroup->id,
                        'study_id' => (int) $study->id,
                        'study_identifier' => (string) $study->identifier,
                        'study_title' => (string) $study->title,
                        'study_url' => route('search.show', $study),
                        'context_key' => $contextKey,
                        'analysis_type_id' => $analysisTypeId,
                        'analysis_type_code' => (string) ($analysisType?->code ?? ''),
                        'analysis_type_name' => (string) ($analysisType?->name ?? 'Unknown'),
                        'specimen_type_id' => $specimenTypeId,
                        'specimen_type_label' => (string) ($specimenTypes->get($specimenTypeId)?->name ?? ''),
                        'region_id' => $regionId,
                        'region_label' => (string) ($regions->get($regionId)?->label ?? ''),
                        'test_method' => $context['test_method'],
                        'test_method_label' => $this->formatTestMethod($context['test_method']),
                        'bone_tested' => $context['bone_tested'],
                        'bone_site_label' => $this->resolveBoneSiteLabel(
                            $context['bone_tested'],
                            $regions->get($regionId)?->label,
                            $specimenTypes->get($specimenTypeId)?->name
                        ),
                        'gene_id' => (int) $experimentalSampleGroup->animalModel->gene->id,
                        'gene_symbol' => (string) $experimentalSampleGroup->animalModel->gene->symbol,
                        'gene_name' => (string) $experimentalSampleGroup->animalModel->gene->name,
                        'model_label' => $this->buildModelLabel($experimentalSampleGroup),
                        'sample_group_label' => (string) $experimentalSampleGroup->label,
                        'control_sample_group_label' => (string) $controlSampleGroup->label,
                        'sex' => $experimentalSampleGroup->sex,
                        'sex_label' => $this->formatSex($experimentalSampleGroup->sex),
                        'age' => $experimentalSampleGroup->age,
                        'age_bucket' => self::ageBucket($experimentalSampleGroup->age),
                        'measurement_values' => $normalizedValues,
                    ];
                }
            }
        }

        if ($rawRows === []) {
            return $this->emptyPayload($filters);
        }

        $availableAnalysisTypes = collect($rawRows)
            ->groupBy('analysis_type_code')
            ->map(function (Collection $rows, string $code) {
                $first = $rows->first();

                return [
                    'code' => $code,
                    'name' => $first['analysis_type_name'],
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        $selectedAnalysisType = $filters['selectedAnalysisType'];
        if ($selectedAnalysisType === 'all' || ! collect($availableAnalysisTypes)->pluck('code')->contains($selectedAnalysisType)) {
            $selectedAnalysisType = $availableAnalysisTypes[0]['code'] ?? 'all';
        }

        $analysisRows = collect($rawRows)
            ->when($selectedAnalysisType !== 'all', fn (Collection $rows) => $rows->where('analysis_type_code', $selectedAnalysisType))
            ->values();

        $testMethodOptions = $this->buildValueOptions($analysisRows, 'test_method', 'test_method_label');
        $selectedTestMethod = $this->normalizeOptionSelection($filters['selectedTestMethod'], collect($testMethodOptions)->pluck('value')->all());

        $boneSiteOptions = $this->buildLabelOptions($analysisRows, 'bone_site_label');
        $selectedBoneSite = $this->normalizeOptionSelection($filters['selectedBoneSite'], $boneSiteOptions);

        $baseFilteredRows = $analysisRows
            ->when($selectedTestMethod !== 'all', fn (Collection $rows) => $rows->where('test_method', $selectedTestMethod))
            ->when($selectedBoneSite !== 'all', fn (Collection $rows) => $rows->where('bone_site_label', $selectedBoneSite))
            ->when($filters['selectedSex'] !== 'all', fn (Collection $rows) => $rows->where('sex', $filters['selectedSex']))
            ->when($filters['selectedAgeBucket'] !== 'all', fn (Collection $rows) => $rows->where('age_bucket', $filters['selectedAgeBucket']))
            ->when($filters['query'] !== '', function (Collection $rows) use ($filters) {
                $needle = mb_strtolower($filters['query']);

                return $rows->filter(function (array $row) use ($needle) {
                    foreach ([
                        $row['gene_symbol'],
                        $row['gene_name'],
                        $row['study_identifier'],
                        $row['study_title'],
                        $row['model_label'],
                    ] as $field) {
                        if ($field !== null && str_contains(mb_strtolower((string) $field), $needle)) {
                            return true;
                        }
                    }

                    return false;
                });
            })
            ->values();

        $measurementOptions = $this->buildMeasurementOptions(
            $baseFilteredRows,
            $measurements,
            $selectedAnalysisType
        );

        $selectedXAxisMeasurementId = $this->normalizeMeasurementSelection(
            $filters['selectedXAxisMeasurementId'],
            $measurementOptions,
            0
        );
        $selectedYAxisMeasurementId = $this->normalizeMeasurementSelection(
            $filters['selectedYAxisMeasurementId'],
            $measurementOptions,
            1,
            $selectedXAxisMeasurementId
        );
        $selectedSizeMeasurementId = $this->normalizeSizeMeasurementSelection(
            $filters['selectedSizeMeasurementId'],
            $measurementOptions
        );

        $plottedRows = $baseFilteredRows
            ->filter(function (array $row) use ($selectedXAxisMeasurementId, $selectedYAxisMeasurementId) {
                return isset($row['measurement_values'][$selectedXAxisMeasurementId], $row['measurement_values'][$selectedYAxisMeasurementId]);
            })
            ->values();

        $scatterRows = $plottedRows->map(function (array $row) use ($selectedXAxisMeasurementId, $selectedYAxisMeasurementId, $selectedSizeMeasurementId, $measurements) {
            $xValue = (float) $row['measurement_values'][$selectedXAxisMeasurementId];
            $yValue = (float) $row['measurement_values'][$selectedYAxisMeasurementId];
            $sizeValue = $selectedSizeMeasurementId !== null
                ? ($row['measurement_values'][$selectedSizeMeasurementId] ?? null)
                : null;

            return [
                ...$row,
                'x_value' => $xValue,
                'y_value' => $yValue,
                'size_value' => $sizeValue !== null ? (float) $sizeValue : null,
                'x_label' => $measurements[$selectedXAxisMeasurementId]->display_name ?? 'X axis',
                'y_label' => $measurements[$selectedYAxisMeasurementId]->display_name ?? 'Y axis',
                'size_label' => $selectedSizeMeasurementId !== null ? ($measurements[$selectedSizeMeasurementId]->display_name ?? 'Size axis') : null,
            ];
        })->values();

        $summary = $this->buildSummary(
            $baseFilteredRows,
            $scatterRows,
            $selectedXAxisMeasurementId,
            $selectedYAxisMeasurementId
        );

        $plotPayload = $this->buildPlotPayload(
            $scatterRows,
            $selectedXAxisMeasurementId,
            $selectedYAxisMeasurementId,
            $selectedSizeMeasurementId,
            $measurements
        );

        return [
            'filters' => [
                ...$filters,
                'selectedAnalysisType' => $selectedAnalysisType,
                'selectedXAxisMeasurementId' => $selectedXAxisMeasurementId,
                'selectedYAxisMeasurementId' => $selectedYAxisMeasurementId,
                'selectedSizeMeasurementId' => $selectedSizeMeasurementId,
                'selectedTestMethod' => $selectedTestMethod,
                'selectedBoneSite' => $selectedBoneSite,
            ],
            'availableAnalysisTypes' => $availableAnalysisTypes,
            'measurementOptions' => $measurementOptions,
            'testMethodOptions' => $testMethodOptions,
            'boneSiteOptions' => $boneSiteOptions,
            'scatterRows' => $scatterRows->all(),
            'plotPayload' => $plotPayload,
            'summary' => $summary,
            'coverageMessage' => $this->buildCoverageMessage($summary, $measurements, $selectedXAxisMeasurementId, $selectedYAxisMeasurementId),
        ];
    }

    public static function calculatePercentChange(float $experimentalMean, float $controlMean): float
    {
        if ($controlMean === 0.0) {
            throw new \InvalidArgumentException('Control mean cannot be zero.');
        }

        return (($experimentalMean - $controlMean) / $controlMean) * 100;
    }

    public static function ageBucket(?int $age): string
    {
        if ($age === null) {
            return 'unknown';
        }

        if ($age <= 12) {
            return 'le_12';
        }

        if ($age <= 24) {
            return '13_24';
        }

        return 'gt_24';
    }

    public static function ageBucketLabel(?string $ageBucket): string
    {
        return match ($ageBucket) {
            'le_12' => '<= 12 wk',
            '13_24' => '13-24 wk',
            'gt_24' => '> 24 wk',
            default => 'Unknown',
        };
    }

    private function emptyPayload(array $filters): array
    {
        return [
            'filters' => [
                ...$filters,
                'selectedAnalysisType' => 'all',
                'selectedXAxisMeasurementId' => null,
                'selectedYAxisMeasurementId' => null,
                'selectedSizeMeasurementId' => null,
            ],
            'availableAnalysisTypes' => [],
            'measurementOptions' => [],
            'testMethodOptions' => [],
            'boneSiteOptions' => [],
            'scatterRows' => [],
            'plotPayload' => [
                'points' => [],
                'xAxisTitle' => 'X axis',
                'yAxisTitle' => 'Y axis',
                'sizeAxisTitle' => null,
            ],
            'summary' => [
                'eligibleComparisons' => 0,
                'plottedComparisons' => 0,
                'genesRepresented' => 0,
                'topXIncrease' => null,
                'topYIncrease' => null,
            ],
            'coverageMessage' => 'No comparison data is currently available for the selected filters.',
        ];
    }

    private function normalizeFilters(array $filters): array
    {
        return [
            'query' => trim((string) ($filters['query'] ?? '')),
            'selectedAnalysisType' => (string) ($filters['selectedAnalysisType'] ?? 'all'),
            'selectedXAxisMeasurementId' => $filters['selectedXAxisMeasurementId'] !== null ? (int) $filters['selectedXAxisMeasurementId'] : null,
            'selectedYAxisMeasurementId' => $filters['selectedYAxisMeasurementId'] !== null ? (int) $filters['selectedYAxisMeasurementId'] : null,
            'selectedSizeMeasurementId' => $filters['selectedSizeMeasurementId'] !== null && $filters['selectedSizeMeasurementId'] !== ''
                ? (int) $filters['selectedSizeMeasurementId']
                : null,
            'selectedTestMethod' => (string) ($filters['selectedTestMethod'] ?? 'all'),
            'selectedBoneSite' => (string) ($filters['selectedBoneSite'] ?? 'all'),
            'selectedSex' => (string) ($filters['selectedSex'] ?? 'all'),
            'selectedAgeBucket' => (string) ($filters['selectedAgeBucket'] ?? 'all'),
        ];
    }

    private function buildMeasurementOptions(Collection $rows, Collection $measurements, string $analysisTypeCode): array
    {
        $measurementIds = $rows
            ->flatMap(fn (array $row) => array_keys($row['measurement_values']))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn (int $id) => $measurements->has($id))
            ->filter(function (int $id) use ($measurements, $analysisTypeCode) {
                if ($analysisTypeCode === 'all') {
                    return true;
                }

                return $measurements[$id]->analysisType?->code === $analysisTypeCode;
            })
            ->values();

        return $measurementIds
            ->map(function (int $id) use ($measurements) {
                $measurement = $measurements[$id];

                return [
                    'id' => $id,
                    'label' => $measurement->display_name.($measurement->unit ? ' ('.$measurement->unit.')' : ''),
                    'display_name' => $measurement->display_name,
                    'unit' => $measurement->unit,
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function normalizeMeasurementSelection(?int $selectedId, array $measurementOptions, int $fallbackIndex = 0, ?int $disallowedId = null): ?int
    {
        $availableIds = collect($measurementOptions)
            ->pluck('id')
            ->reject(fn ($id) => $disallowedId !== null && (int) $id === $disallowedId)
            ->values();

        if ($availableIds->isEmpty()) {
            return null;
        }

        if ($selectedId !== null && $availableIds->contains($selectedId)) {
            return $selectedId;
        }

        return $availableIds->get($fallbackIndex, $availableIds->first());
    }

    private function normalizeSizeMeasurementSelection(?int $selectedId, array $measurementOptions): ?int
    {
        $availableIds = collect($measurementOptions)->pluck('id');

        if ($selectedId !== null && $availableIds->contains($selectedId)) {
            return $selectedId;
        }

        return null;
    }

    private function buildSummary(Collection $eligibleRows, Collection $plottedRows, ?int $xMeasurementId, ?int $yMeasurementId): array
    {
        return [
            'eligibleComparisons' => $eligibleRows->count(),
            'plottedComparisons' => $plottedRows->count(),
            'genesRepresented' => $plottedRows->pluck('gene_id')->unique()->count(),
            'topXIncrease' => $this->buildTopIncrease($plottedRows, 'x_value', $xMeasurementId),
            'topYIncrease' => $this->buildTopIncrease($plottedRows, 'y_value', $yMeasurementId),
        ];
    }

    private function buildTopIncrease(Collection $rows, string $valueKey, ?int $measurementId): ?array
    {
        if ($measurementId === null || $rows->isEmpty()) {
            return null;
        }

        $top = $rows->sortByDesc($valueKey)->first();

        if (! $top) {
            return null;
        }

        return [
            'gene_symbol' => $top['gene_symbol'],
            'value' => (float) $top[$valueKey],
        ];
    }

    private function buildPlotPayload(
        Collection $rows,
        ?int $xMeasurementId,
        ?int $yMeasurementId,
        ?int $sizeMeasurementId,
        Collection $measurements
    ): array {
        if ($xMeasurementId === null || $yMeasurementId === null) {
            return [
                'points' => [],
                'xAxisTitle' => 'X axis',
                'yAxisTitle' => 'Y axis',
                'sizeAxisTitle' => null,
            ];
        }

        $sizeValues = $rows
            ->pluck('size_value')
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => abs((float) $value))
            ->values();

        $minSize = $sizeValues->min() ?? 0.0;
        $maxSize = $sizeValues->max() ?? 0.0;

        $points = $rows->map(function (array $row) use ($sizeMeasurementId, $minSize, $maxSize) {
            $sizeValue = $row['size_value'];

            return [
                'x' => $row['x_value'],
                'y' => $row['y_value'],
                'r' => $this->scaleBubbleSize($sizeValue, $minSize, $maxSize),
                'quadrant' => $this->quadrantForPoint($row['x_value'], $row['y_value']),
                'gene_symbol' => $row['gene_symbol'],
                'model_label' => $row['model_label'],
                'study_identifier' => $row['study_identifier'],
                'study_url' => $row['study_url'],
                'sex_label' => $row['sex_label'],
                'age_label' => $row['age'] !== null ? $row['age'].' wk' : 'N/A',
                'bone_site_label' => $row['bone_site_label'] ?: 'N/A',
                'sample_group_label' => $row['sample_group_label'],
                'control_sample_group_label' => $row['control_sample_group_label'],
                'analysis_type_name' => $row['analysis_type_name'],
                'test_method_label' => $row['test_method_label'],
                'size_value' => $sizeMeasurementId !== null ? $sizeValue : null,
            ];
        })->values()->all();

        return [
            'points' => $points,
            'xAxisTitle' => $this->measurementLabel($measurements, $xMeasurementId),
            'yAxisTitle' => $this->measurementLabel($measurements, $yMeasurementId),
            'sizeAxisTitle' => $sizeMeasurementId !== null ? $this->measurementLabel($measurements, $sizeMeasurementId) : null,
        ];
    }

    private function measurementLabel(Collection $measurements, int $measurementId): string
    {
        $measurement = $measurements[$measurementId] ?? null;
        if (! $measurement) {
            return 'Measurement';
        }

        return $measurement->display_name.($measurement->unit ? ' ('.$measurement->unit.')' : '').' % change';
    }

    private function buildCoverageMessage(array $summary, Collection $measurements, ?int $xMeasurementId, ?int $yMeasurementId): string
    {
        if ($summary['eligibleComparisons'] === 0) {
            return 'No filtered comparisons are available for the current selection.';
        }

        if ($summary['plottedComparisons'] === 0 || $xMeasurementId === null || $yMeasurementId === null) {
            return 'The filtered comparisons do not have overlapping data for the selected axes.';
        }

        $xLabel = $measurements[$xMeasurementId]->display_name ?? 'X axis';
        $yLabel = $measurements[$yMeasurementId]->display_name ?? 'Y axis';

        return sprintf(
            '%d of %d filtered comparisons have data for both %s and %s.',
            $summary['plottedComparisons'],
            $summary['eligibleComparisons'],
            $xLabel,
            $yLabel
        );
    }

    private function buildValueOptions(Collection $rows, string $valueKey, string $labelKey): array
    {
        return $rows
            ->filter(fn (array $row) => ! empty($row[$valueKey]))
            ->map(fn (array $row) => [
                'value' => $row[$valueKey],
                'label' => $row[$labelKey] ?: $row[$valueKey],
            ])
            ->unique('value')
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function buildLabelOptions(Collection $rows, string $key): array
    {
        return $rows
            ->pluck($key)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function normalizeOptionSelection(string $selected, array $options): string
    {
        if ($selected === 'all' || in_array($selected, $options, true)) {
            return $selected;
        }

        return 'all';
    }

    private function resolveBoneSiteLabel(?string $boneTested, ?string $regionLabel, ?string $specimenTypeLabel): string
    {
        if ($boneTested) {
            return $this->formatBoneTested($boneTested);
        }

        if ($regionLabel) {
            return $regionLabel;
        }

        return $specimenTypeLabel ?? '';
    }

    private function formatBoneTested(?string $boneTested): string
    {
        if (! $boneTested) {
            return '';
        }

        return match ($boneTested) {
            'forelimb' => 'Forelimb (radius + ulna)',
            default => ucfirst(str_replace('_', ' ', $boneTested)),
        };
    }

    private function formatTestMethod(?string $testMethod): string
    {
        if (! $testMethod) {
            return '';
        }

        return match ($testMethod) {
            'bending_3pt' => '3-point bending',
            'bending_4pt' => '4-point bending',
            'torsion' => 'Torsion',
            'compression' => 'Compression',
            default => ucfirst(str_replace('_', ' ', $testMethod)),
        };
    }

    private function formatSex(?string $sex): string
    {
        return match ($sex) {
            'M' => 'Male',
            'F' => 'Female',
            default => 'Unknown',
        };
    }

    private function buildModelLabel(SampleGroup $sampleGroup): string
    {
        $animalModel = $sampleGroup->animalModel;
        if (! $animalModel) {
            return $sampleGroup->label ?: 'Unknown model';
        }

        $geneSymbol = $animalModel->gene?->symbol;
        $allele = $animalModel->geneticMethod?->allele_abbreviation ?: $animalModel->geneticMethod?->name;
        $category = $animalModel->geneticCategory?->name;

        $parts = array_filter([
            $geneSymbol,
            $allele,
            $category,
        ]);

        if ($parts !== []) {
            return implode(' ', $parts);
        }

        return $animalModel->animal_model_name
            ?: $animalModel->animalLine?->label
            ?: ($sampleGroup->label ?: 'Unknown model');
    }

    private function scaleBubbleSize(?float $sizeValue, float $minValue, float $maxValue): int
    {
        if ($sizeValue === null) {
            return 12;
        }

        $normalizedValue = abs($sizeValue);
        if ($maxValue <= $minValue) {
            return 14;
        }

        return (int) round(8 + (($normalizedValue - $minValue) / ($maxValue - $minValue)) * 14);
    }

    private function quadrantForPoint(float $xValue, float $yValue): string
    {
        if ($xValue >= 0 && $yValue >= 0) {
            return 'both_up';
        }

        if ($xValue < 0 && $yValue < 0) {
            return 'both_down';
        }

        if ($xValue < 0) {
            return 'x_down_y_up';
        }

        return 'x_up_y_down';
    }
}
