<?php

use App\Services\StudySummaryDataService;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

/**
 * @param  array<int, array{groupId: int, label: string, sampleLabel: string, value: float}>  $rows
 */
function measurementValuesForPlotOrdering(array $rows): Collection
{
    return collect($rows)->map(function (array $row) {
        $sampleGroup = (object) ['label' => $row['label']];
        $sample = (object) [
            'sample_group_id' => $row['groupId'],
            'label' => $row['sampleLabel'],
            'sampleGroup' => $sampleGroup,
        ];

        return (object) [
            'value' => $row['value'],
            'sample' => $sample,
        ];
    });
}

function plotSeriesFromMeasurementValues(Collection $measurementValues, array $preferredSampleGroupIds = []): array
{
    $method = new ReflectionMethod(StudySummaryDataService::class, 'plotSeriesFromMeasurementValues');
    $method->setAccessible(true);

    return $method->invoke(app(StudySummaryDataService::class), $measurementValues, $preferredSampleGroupIds);
}

it('orders plot series by preferred sample group ids regardless of row order', function () {
    $values = measurementValuesForPlotOrdering([
        ['groupId' => 20, 'label' => 'Female', 'sampleLabel' => 'F1', 'value' => 1.0],
        ['groupId' => 10, 'label' => 'Male', 'sampleLabel' => 'M1', 'value' => 2.0],
        ['groupId' => 20, 'label' => 'Female', 'sampleLabel' => 'F2', 'value' => 3.0],
    ]);

    $series = plotSeriesFromMeasurementValues($values, [10, 20]);

    expect(array_column($series, 'sample_group_id'))->toBe([10, 20])
        ->and(array_column($series, 'label'))->toBe(['Male', 'Female']);
});

it('keeps preferred order when measurement rows arrive in reverse group order', function () {
    $reversed = measurementValuesForPlotOrdering([
        ['groupId' => 20, 'label' => 'Female', 'sampleLabel' => 'F1', 'value' => 1.0],
        ['groupId' => 10, 'label' => 'Male', 'sampleLabel' => 'M1', 'value' => 2.0],
    ]);
    $forward = measurementValuesForPlotOrdering([
        ['groupId' => 10, 'label' => 'Male', 'sampleLabel' => 'M1', 'value' => 2.0],
        ['groupId' => 20, 'label' => 'Female', 'sampleLabel' => 'F1', 'value' => 1.0],
    ]);

    $preferred = [10, 20];

    expect(array_column(plotSeriesFromMeasurementValues($reversed, $preferred), 'sample_group_id'))
        ->toBe(array_column(plotSeriesFromMeasurementValues($forward, $preferred), 'sample_group_id'));
});
