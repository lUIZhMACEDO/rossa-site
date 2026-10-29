@php
    $meta  = config('modalities.types.' . $analysisType->code, config('modalities.default'));
    $color = $meta['color'] ?? config('modalities.default.color');
    $abbr  = $meta['abbr'] ?? null;
@endphp

<div class="h-100 p-3 d-flex flex-column" style="border-top: 3px solid {{ $color }};">
    <div class="fw-semibold lh-sm mb-1" style="font-size: .9375rem; color: #2C2C2A;">
        {{ $analysisType->name }}
    </div>

    @if($abbr)
        <div class="font-monospace mb-3" style="font-size: .8125rem; color: #2C2C2A;">{{ $abbr }}</div>
    @else
        <div class="mb-3"></div>
    @endif

    <div class="mt-auto">
        @if($locked)
            <button type="button" class="btn btn-sm btn-modality w-100 disabled" disabled
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="This study is locked for editing.">
                <i class="bi-upload"></i> Upload
            </button>
        @else
            <a href="{{ route('study.phenotype-analysis.upload', ['study' => $study->id, 'analysis_type_id' => $analysisType->id]) }}"
               class="btn btn-sm btn-modality w-100">
                <i class="bi-upload"></i> Upload
            </a>
        @endif
    </div>
</div>
