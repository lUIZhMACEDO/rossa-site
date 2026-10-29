@section('title', 'Upload Data')
@section('guide-content')
    @include('guides.data-ingestion._05_2upload-steps')
@endsection
<x-layouts.submission :$study>
@php
    $phenotypeLocked = $study->isPhenotypeAnalysisLocked(auth()->user());
    $modalityDefault = config('modalities.default');
@endphp
<div class="py-0" @if($phenotypeLocked) data-input-locked @endif>

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-baseline gap-3 p-3 mb-4"
         style="background-color: #0D3B52; border-radius: 0.375rem;">
        <div>
            <h1 class="mb-1" style="color: #fff;">Upload spreadsheet data</h1>
            <p class="mb-0" style="color: #E6F1FB;">
            Select the appropriate phenotying analysis below to upload your spreadsheet data.
            </p>
        </div>
        <div class="font-monospace text-nowrap" style="font-size: .8125rem; color: #E6F1FB;">
            {{ $study->identifier }}
        </div>
    </div>

    {{-- Section 1: modality selection --}}
    <div class="d-flex align-items-baseline gap-2 mb-2">
        <span class="font-monospace" style="font-size: .9375rem; color: #2C2C2A;">01</span>
        <h2 class="h5 mb-0">Select modality to upload your spreadsheet(s)</h2>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-0 rounded overflow-hidden mb-5" style="border: 1px solid #5F5E5A;">
        @foreach ( $analysisTypes as $analysisType )
            <div class="col" @if(!$loop->last) style="border-right: 1px solid #5F5E5A;" @endif>
                @include('study.phenotype-analysis.partials.analysis-type-tile', [
                    'analysisType' => $analysisType,
                    'study'        => $study,
                    'locked'       => $phenotypeLocked,
                ])
            </div>
        @endforeach
    </div>

    {{-- Section 2: uploaded datasets, on a tinted panel --}}
    <div class="rounded p-3 p-md-4" style="background-color: #0D3B52;">

        <div class="d-flex align-items-baseline gap-2 mb-3">
            <span class="font-monospace" style="font-size: .9375rem; color: #E6F1FB;">02</span>
            <h2 class="h5 mb-0" style="color: #fff;">Uploaded spreadsheet datasets</h2>
            @if ( $study->analysisUploads->isNotEmpty() )
                <span style="font-size: .8125rem; color: #E6F1FB;">
                    {{ $study->analysisUploads->count() }}
                    {{ Str::plural('dataset', $study->analysisUploads->count()) }}
                </span>
            @endif
        </div>

        <div class="bg-white rounded p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Modality</th>
                            <th scope="col">Upload</th>
                            <th scope="col">Step</th>
                            <th scope="col" class="text-end">Samples Recorded</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ( $study->analysisUploads->isEmpty() )
                        <tr>
                            <td colspan="100%" class="text-center py-4" style="color: #2C2C2A;">
                                Choose a modality above to upload your first spreadsheet.
                            </td>
                        </tr>
                        @else
                            @foreach ( $groupedUploads as $group )
                                @php
                                    $modalityRowspan = $group['uploads']->count();
                                    $groupColor = config(
                                        'modalities.types.' . ($group['analysis_type_code'] ?? '') . '.color',
                                        $modalityDefault['color']
                                    );
                                @endphp
                                @foreach ( $group['uploads'] as $index => $analysisUpload )
                                    @php
                                        $uploadStepComplete = $analysisUpload->current_step !== null
                                            && in_array($analysisUpload->current_step, [
                                                App\Enums\UploadSteps::SCAN_DATASETS,
                                                App\Enums\UploadSteps::COMPLETE,
                                            ], true);
                                    @endphp
                                    <tr>
                                        @if ( $index === 0 )
                                            <td rowspan="{{ $modalityRowspan }}" class="align-middle border-end">
                                                <div class="d-flex align-items-start gap-2">
                                                    <span class="flex-shrink-0 mt-1"
                                                          style="display:inline-block; width:3px; height:1.1em; background:{{ $groupColor }};"
                                                          aria-hidden="true"></span>
                                                    <div>
                                                        <div class="fw-semibold text-nowrap">{{ $group['analysis_type'] }}</div>
                                                        <div style="color: #2C2C2A;">{{ $group['context_description'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                        <td>
                                            <div class="font-monospace" style="max-width: 300px; font-size: .875rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="{{ $analysisUpload->original_filename }}">
                                                {{ $analysisUpload->original_filename }}
                                            </div>
                                        </td>
                                        <td class="text-nowrap">
                                            @if($uploadStepComplete)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                            @endif
                                            {{ $uploadStepComplete
                                                ? App\Enums\UploadSteps::COMPLETE->label()
                                                : $analysisUpload->current_step->label() }}
                                        </td>
                                        <td class="text-end">{{ $analysisUpload->samples_count ?: '-' }}</td>
                                        <td class="text-end">
                                            <div class="btn-group gap-2">
                                                <form action="{{ route('upload.download', $analysisUpload) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Download Original Data File">
                                                        <i class="bi-download"></i>
                                                    </button>
                                                </form>
                                                <form method="GET"
                                                    action="{{ route('study.phenotype-analysis.upload', ['study' => $study->id, 'upload_id' => $analysisUpload->id]) }}"
                                                    class="d-inline">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                        title="Edit"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top">
                                                        <i class="bi-pencil"></i>
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('study.phenotype-analysis.destroy', ['study' => $study->id, 'upload_id' => $analysisUpload->id]) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this analysis upload?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top">
                                                        <i class="bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('study.scan-datasets.index', $study) }}" class="btn btn-lg btn-primary">Save &amp; Continue <i class="bi-arrow-right"></i></a>
    </div>

</div>
</x-layouts.submission>
