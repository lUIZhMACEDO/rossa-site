@props([
    'isSubmitting' => false,
    'isCorresponding' => false,
    'isContributing' => false,
    'showSubmitting' => true,
    'submittingModel' => null,
    'correspondingModel',
    'contributingModel',
    'submittingId' => 'isSubmitting',
    'correspondingId' => 'isCorresponding',
    'contributingId' => 'isContributing',
    'error' => null,
])

@php
    $isSubmitting = filter_var($isSubmitting, FILTER_VALIDATE_BOOLEAN);
    $isCorresponding = filter_var($isCorresponding, FILTER_VALIDATE_BOOLEAN);
    $isContributing = filter_var($isContributing, FILTER_VALIDATE_BOOLEAN);
    $labels = \App\Models\Investigator::labelsFromFlags($isSubmitting, $isCorresponding, $isContributing);
@endphp

<div {{ $attributes->class('mb-4 border rounded bg-light p-3') }}>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <label class="form-label required fw-semibold mb-0">Investigator Type</label>
        <div class="d-flex align-items-center gap-2">
            <span class="text-secondary small">Recorded as</span>
            @if($labels === [])
                <span class="text-secondary small">Select a role</span>
            @else
                <x-investigator-role-badges :labels="$labels" />
            @endif
        </div>
    </div>

    <p class="text-secondary small mb-3">
        {{ $requiredNote ?? 'A corresponding investigator is required before studies in this collection can be submitted. Mark anyone else as Contributing. Contributing is selected on its own.' }}
    </p>

    <div class="row g-3">
        <div class="col-md-6">
            <div @class([
                'form-check border rounded p-3 h-100',
                'border-primary bg-primary-subtle' => $isCorresponding,
                'bg-white' => ! $isCorresponding,
            ])>
                <input class="form-check-input border-primary" type="checkbox" id="{{ $correspondingId }}" wire:model.live="{{ $correspondingModel }}">
                <label class="form-check-label" for="{{ $correspondingId }}">
                    <span class="fw-semibold">Corresponding Investigator</span>
                    <span class="d-block text-secondary small mt-1">Manuscript contact. More than one person can have this role.</span>
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div @class([
                'form-check border rounded p-3 h-100',
                'border-primary bg-primary-subtle' => $isContributing,
                'bg-white' => ! $isContributing,
            ])>
                <input class="form-check-input border-primary" type="checkbox" id="{{ $contributingId }}" wire:model.live="{{ $contributingModel }}">
                <label class="form-check-label" for="{{ $contributingId }}">
                    <span class="fw-semibold">Contributing Investigator</span>
                    <span class="d-block text-secondary small mt-1">Other investigators. Select this role on its own.</span>
                </label>
            </div>
        </div>
    </div>

    @if($error)
        <span class="text-danger d-block mt-2">{{ $error }}</span>
    @endif
</div>