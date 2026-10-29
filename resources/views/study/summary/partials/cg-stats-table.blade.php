@php
    $uploadIds = $analysisUploadIds ?? collect();
    $filterContext = $filterContext ?? null;
    $cg = $comparisonGroup;
    $summaryDataService = app(\App\Services\StudySummaryDataService::class);
    $statsPayload = $summaryDataService->buildComparisonGroupStatsPayload($cg, $uploadIds, $filterContext);

    $sampleGroups = $statsPayload['sampleGroups'];
    $sampleGroupIds = $statsPayload['sampleGroupIds'];
    $inferentialStats = $statsPayload['inferentialStats'];
    $descriptiveStats = $statsPayload['descriptiveStats'];
    $measurements = $statsPayload['measurements'];
    $cgMeasurements = $statsPayload['cgMeasurements'] ?? [];
    $hasAnova = $statsPayload['hasAnova'];
    $hasTTest = $statsPayload['hasTTest'];
    $hasTukey = $statsPayload['hasTukey'];

    if ($measurements->isEmpty()) {
        echo view('study.summary.partials.no-comparison-measurements', [
            'study' => $study,
            'publicAccess' => $publicAccess ?? false,
            'modality' => $modality ?? 'measurement',
        ])->render();
        return;
    }
@endphp

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm mb-0">
        <thead class="table-light">
            <tr>
                <th>Variable</th>
                {{-- col per sample group --}}
                @foreach($sampleGroups as $sg)
                    <th class="text-center">
                        {{ $sg->label }}
                        <small class="text-muted d-block">(Mean ± SD)</small>
                    </th>
                @endforeach
                @if($hasTTest)
                    <th class="text-center">
                        T-test
                        <small class="text-muted d-block">(p-Value)</small>
                    </th>
                @endif
                @if($hasAnova)
                    <th class="text-center">
                        ANOVA
                        <small class="text-muted d-block">(p-Value)</small>
                    </th>
                @endif
                @if($hasTukey)
                    <th class="text-center">
                        Tukey HSD
                    </th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($measurements as $measurement)
                @php
                    // get descriptive stats for this measurement
                    $measurementDescStats = $descriptiveStats->get($measurement->id, collect());
                    // build columns for each sample group
                    $groupColumns = [];
                    foreach ($sampleGroups as $sg) {
                        $stat = $measurementDescStats->firstWhere('sample_group_id', $sg->id);
                        if ($stat && $stat->n > 0) {
                            // adaptive precision
                            $meanAbs = abs($stat->mean);
                            $sdAbs = abs($stat->sd);
                            
                            // use min of (mean, sd)
                            $minAbs = min($meanAbs, $sdAbs);
                            if ($minAbs < 0.01) {
                                $decimals = 4;
                            } elseif ($minAbs < 0.1) {
                                $decimals = 3;
                            } else {
                                $decimals = 2;
                            }
                            
                            $groupColumns[] = number_format($stat->mean, $decimals) . ' ± ' . number_format($stat->sd, $decimals);
                        } else {
                            $groupColumns[] = '<span class="text-muted">-</span>';
                        }
                    }

                    // get inferential stats for this measurement
                    $measurementInferentialStats = $inferentialStats->get($measurement->id, collect());
                    // find specific test types
                    $tTestStat = $measurementInferentialStats->firstWhere('test_type', 't_test');
                    $anovaStat = $measurementInferentialStats->firstWhere('test_type', 'anova');
                    $tukeyStat = $measurementInferentialStats->firstWhere('test_type', 'tukey_hsd');
                @endphp
                <tr>
                    <td class="fw-medium" 
                        wire:click="$dispatch('openMeasurementModal', { measurementId: {{ $measurement->id }}, cgMeasurements: {{ Js::from($cgMeasurements) }} })" 
                        style="cursor: pointer;" 
                        title="Click to view plot">
                        {{ $measurement->display_name }}{{ $measurement->unit ? ' ('.$measurement->unit.')' : '' }}
                    </td>
                    @foreach($groupColumns as $col)
                        <td class="text-center">{!! $col !!}</td>
                    @endforeach
                    @if($hasTTest)
                        <td class="text-center">
                            @if($tTestStat && $tTestStat->p_value !== null)
                                @php
                                    $pValue = $tTestStat->p_value;
                                    $formattedP = $pValue < 0.001 ? '<0.001' : number_format($pValue, 4);
                                    $isSignificant = $pValue < 0.05;
                                @endphp
                                <span class="@if($isSignificant) fw-bold text-primary @endif">
                                    {{ $formattedP }}@if($isSignificant)<sup>*</sup>@endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endif
                    @if($hasAnova)
                        <td class="text-center">
                            @if($anovaStat && $anovaStat->p_value !== null)
                                @php
                                    $pValue = $anovaStat->p_value;
                                    $formattedP = $pValue < 0.001 ? '<0.001' : number_format($pValue, 4);
                                    $isSignificant = $pValue < 0.05;
                                @endphp
                                <span class="@if($isSignificant) fw-bold text-primary @endif">
                                    {{ $formattedP }}@if($isSignificant)<sup>*</sup>@endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endif
                    @if($hasTukey)
                        <td class="text-center">
                            @if($tukeyStat && isset($tukeyStat->metadata['display_string']))
                                @php
                                    $displayStr = $tukeyStat->metadata['display_string'];
                                    $nsOrError = $displayStr === 'ns' || $displayStr === 'Error';
                                @endphp
                                <span class="@if($nsOrError) text-muted @endif">
                                    {{ $displayStr }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                @php
                    $testTypeColCount = ($hasTTest ? 1 : 0) + ($hasAnova ? 1 : 0) + ($hasTukey ? 1 : 0);
                    $totalCols = 2 + $sampleGroups->count() + $testTypeColCount;
                @endphp
                <tr>
                    <td colspan="{{ $totalCols }}" class="text-center text-muted py-3">
                        No measurements with statistics available for this comparison group.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
