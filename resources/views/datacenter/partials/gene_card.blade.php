@php
    $study = $animalModel->study;
    $gene = $animalModel->gene;

    $analysisTypes = $study->analysisUploads
        ->pluck('analysisType.name')
        ->filter()
        ->unique()
        ->values();

    $boneSites = $study->analysisUploads
        ->map(function ($upload) {
            if ($upload->region?->label) {
                return $upload->region->label;
            }

            if ($upload->mechanicalTestingDetails?->bone_tested) {
                return $upload->formatBoneTested($upload->mechanicalTestingDetails->bone_tested);
            }

            if ($upload->bone_tested) {
                return $upload->formatBoneTested($upload->bone_tested);
            }

            return $upload->specimenType?->name;
        })
        ->filter()
        ->unique()
        ->values();

    $sampleGroups = $study->sampleGroups
        ->where('animal_model_id', $animalModel->id)
        ->values();

    $ages = $sampleGroups
        ->pluck('age')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $sexes = $sampleGroups
        ->pluck('sex')
        ->filter()
        ->map(fn ($sex) => match ($sex) {
            'M' => 'Male',
            'F' => 'Female',
            default => $sex,
        })
        ->unique()
        ->values();

    $summary = $study->summary ?: 'No study summary available.';
    $geneLabel = $gene?->symbol ?? $gene?->name ?? 'Unknown gene';
    $methodLabel = $animalModel->geneticMethod?->allele_abbreviation
        ?: $animalModel->geneticMethod?->name
        ?: null;
    $title = trim($geneLabel . ' ' . $methodLabel);
    $category = $animalModel->geneticCategory?->name ?: 'No Genetic Modification';
    $author = $study->user?->name ?: 'Unknown author';
    $date = $study->completion_date?->format('Y') ?: optional($study->created_at)->format('Y');
@endphp

<div class="card shadow-sm" style="border-left: 4px solid var(--bs-primary);">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h3 class="h5 mb-0 fw-bold">
                        <a href="{{ route('search.show', ['study' => $study, 'return' => request()->fullUrl()]) }}" class="text-decoration-none">
                            {{ trim($title) }} &mdash; {{ $category }}
                        </a>
                    </h3>
                </div>

                <div class="text-secondary small mb-2">
                    {{ $study->identifier }}
                    @if ($author)
                        &middot; {{ $author }}
                    @endif
                    @if ($date)
                        &middot; {{ $date }}
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if ($animalModel->animalLine?->label)
                        <span class="badge rounded-pill bg-light text-dark border">{{ $animalModel->animalLine->label }}</span>
                    @endif

                    @foreach ($analysisTypes as $analysisType)
                        <span class="badge rounded-pill bg-info-subtle text-info-emphasis border">{{ $analysisType }}</span>
                    @endforeach
                </div>
            </div>

            <div class="d-flex flex-row align-items-end gap-2">
                <a href="{{ route('search.show', ['study' => $study, 'return' => request()->fullUrl()]) }}" class="btn btn-outline-primary btn-sm">
                    View Study
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
                {{-- <a href="{{ route('datacenter.gene', $gene) }}" class="text-decoration-none d-inline-flex flex-column align-items-center lh-1">
                    <i class="bi bi-box-arrow-up-right text-info"></i>
                    <span class="small text-info mt-1">Gene</span>
                </a> --}}
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="small text-uppercase text-secondary fw-semibold">Bone sites</div>
                <div class="fw-medium text-break">
                    {{ $boneSites->isNotEmpty() ? $boneSites->join(', ') : 'N/A' }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="small text-uppercase text-secondary fw-semibold">Animal age</div>
                <div class="fw-medium">
                    {{ $ages->isNotEmpty() ? $ages->map(fn ($age) => $age . ' wk')->join(', ') : 'N/A' }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="small text-uppercase text-secondary fw-semibold">Sex / background</div>
                <div class="fw-medium text-break">
                    {{ $sexes->isNotEmpty() ? $sexes->join(', ') : 'N/A' }}
                    @if ($animalModel->animalLine?->label)
                        <span class="text-muted">&middot; {{ $animalModel->animalLine->label }}</span>
                    @endif
                </div>
            </div>
        </div>

        <p class="mb-0 text-muted">{{ \Illuminate\Support\Str::limit($summary, 220) }}</p>
    </div>
</div>
