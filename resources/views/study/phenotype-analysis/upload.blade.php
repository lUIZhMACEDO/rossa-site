@section('title', 'Upload Analysis')
@section('guide-content')
    @include('guides.data-ingestion._05_2upload-steps')
@endsection
<x-layouts.submission :$study>
<div class="pt-0 pb-5" @if($study->isPhenotypeAnalysisLocked(auth()->user())) data-input-locked @endif>

    <div class="p-3 mb-3" style="background-color: #0D3B52; border-radius: 0.375rem;">
    <h1 class="mb-2" style="color: #fff;">Upload Analysis</h1>
       @if (isset($analysisType) && $analysisType)
        <p class="mb-2" style="color: #E6F1FB;">
            <span class="fw-semibold">Selected Analysis Type:</span> <span class="fw-bold" style="color: #E8C46A;">{{ $analysisType->name }}</span>
        </p>
    @endif
    <a href="{{ route('study.phenotype-analysis', ['study' => $study->id]) }}" class="btn btn-sm btn-light">
        <i class="bi-arrow-left"></i> Back to Selection
    </a>
    </div>

    @php
        $colors = ['primary', 'success', 'danger', 'warning', 'secondary', 'info', 'dark'];
    @endphp

    @if (!isset($analysisType) || !$analysisType)
        {{-- Analysis Type Selection for New Uploads --}}
        <form action="{{ route('study.phenotype-analysis.upload', ['study' => $study->id]) }}" class="row justify-content-center">
            @foreach( $analysisTypes as $analysisType )
                <div class="col-md-4 mb-4 d-flex align-items-stretch justify-content-center">
                    @include('study.phenotype-analysis.partials.analysis-type-card', [
                        'analysisType' => $analysisType,
                        'color' => $colors[$loop->index % count($colors)],
                    ])
                </div>
            @endforeach
        </form>
    @else

        <div class="d-flex align-items-center flex-wrap gap-2 mb-4">
            <span class="fw-semibold text-muted me-2">Upload Steps:</span>

            @php
                $stepList = [
                    App\Enums\UploadSteps::PREPARE_UPLOAD,
                    App\Enums\UploadSteps::MAP_MEASUREMENTS,
                    App\Enums\UploadSteps::GROUP_SAMPLES,
                    App\Enums\UploadSteps::REVIEW,
                ];
                $stepLabels = [
                    App\Enums\UploadSteps::PREPARE_UPLOAD->value => 'Upload Spreadsheet',
                ];
            @endphp

            @foreach ($stepList as $index => $stepOption)
                @if ($index > 0)
                    <i class="bi-arrow-right text-muted"></i>
                @endif

                @php
                    $label = $stepLabels[$stepOption->value] ?? $stepOption->label();
                    $isCurrent = $step === $stepOption;
                    $isReachable = $maxStep->value >= $stepOption->value;
                @endphp

               @if ($isCurrent)
                    {{-- current step: never a link --}}
                    <span class="badge rounded-pill px-3 py-2" 
                        style="background-color: #0D3B52; color: #fff; font-weight: normal; font-size: .875rem;">
                        {{ $label }}
                    </span>
                @elseif ($isReachable)
                    <a href="{{ route('study.phenotype-analysis.upload', ['study' => $study->id, 'upload_id' => $upload->id, 'step' => $stepOption->value]) }}"
                    class="badge rounded-pill px-3 py-2 text-decoration-none"
                    style="background-color: #fff; color: #0D3B52; border: 1px solid #1A7DB5; font-weight: normal; font-size: .875rem;">
                        {{ $label }}
                    </a>
                @else
                    <span class="badge rounded-pill px-3 py-2" 
                        style="background-color: #f1f3f5; color: #adb5bd; border: 1px solid #e9ecef; font-weight: normal; font-size: .875rem;">
                        {{ $label }}
                    </span>
                @endif
            @endforeach
        </div>

        @include('study.phenotype-analysis.partials.upload-summary', [
            'upload' => $upload,
            'analysisType' => $analysisType,
        ])

        {{-- validation errors (filter out row_error.* which are shown inline on rows) --}}
        @php
            $displayErrors = collect($errors->keys())
                ->filter(fn($key) => !str_starts_with($key, 'row_error.'))
                ->flatMap(fn($key) => $errors->get($key))
                ->unique()
                ->values();
        @endphp
        @if ($displayErrors->isNotEmpty())
            <div class="alert alert-danger alert-dismissible fade show mt-3">
                <h6 class="alert-heading fw-bold mb-2"><i class="bi-exclamation-triangle-fill me-2"></i>Please correct the following errors:</h6>
                <ul class="mb-0">
                    @foreach ($displayErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            // determine form action based on step
            $formAction = match($step) {
                App\Enums\UploadSteps::PREPARE_UPLOAD => $upload->exists 
                    ? route('study.phenotype-analysis.update.prepare', ['study' => $study->id, 'upload_id' => $upload->id])
                    : route('study.phenotype-analysis.store', ['study' => $study->id]),
                App\Enums\UploadSteps::MAP_MEASUREMENTS => route('study.phenotype-analysis.update.measurement-mappings', ['study' => $study->id, 'upload_id' => $upload->id]),
                App\Enums\UploadSteps::GROUP_SAMPLES => route('study.phenotype-analysis.update.group-samples', ['study' => $study->id, 'upload_id' => $upload->id]),
                App\Enums\UploadSteps::REVIEW => route('study.phenotype-analysis.upload', ['study' => $study->id, 'upload_id' => $upload->id]),
                default => $upload->exists 
                    ? route('study.phenotype-analysis.update.prepare', ['study' => $study->id, 'upload_id' => $upload->id])
                    : route('study.phenotype-analysis.store', ['study' => $study->id]),
            };
            
            $bodyWeightMeasurementId = ($step === App\Enums\UploadSteps::MAP_MEASUREMENTS && $upload->body_weight_included === true)
                ? \App\Models\Measurement::where('name', 'weight')->value('id')
                : null;
        @endphp
        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="d-inline" id="updateUploadForm">
            @csrf
            <input type="hidden" name="upload_id" value="{{ $upload->id }}"/>
            @if ($step !== App\Enums\UploadSteps::PREPARE_UPLOAD)
                <div class="d-flex justify-content-between align-items-end gap-2 {{ $step === App\Enums\UploadSteps::GROUP_SAMPLES ? '' : 'my-4' }}">
                    <div class="d-flex gap-2 mb-2">
                        @if ( $step === App\Enums\UploadSteps::REVIEW )
                            <button type="button" class="btn btn-secondary btn-sm" 
                                    onmousedown="startScrolling(-300)" 
                                    onmouseup="stopScrolling()" 
                                    onmouseleave="stopScrolling()"
                                    onclick="document.querySelector('.table-responsive').scrollBy({left: -300, behavior: 'smooth'})">
                                <i class="bi-chevron-left"></i> Scroll Left
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" 
                                    onmousedown="startScrolling(300)" 
                                    onmouseup="stopScrolling()" 
                                    onmouseleave="stopScrolling()"
                                    onclick="document.querySelector('.table-responsive').scrollBy({left: 300, behavior: 'smooth'})">
                                Scroll Right <i class="bi-chevron-right"></i>
                            </button>
                            <script>
                                let scrollInterval;
        
                                function startScrolling(direction) {
                                    scrollInterval = setInterval(() => {
                                        document.querySelector('.table-responsive').scrollBy({left: direction, behavior: 'instant'});
                                    }, 100);
                                }
        
                                function stopScrolling() {
                                    clearInterval(scrollInterval);
                                }
                            </script>
                        @endif
                    </div>
                    
                    @if ( $step === App\Enums\UploadSteps::MAP_MEASUREMENTS )
                        <button type="button" class="btn btn-primary" id="saveMappingsBtn"
                            data-body-weight-id="{{ $bodyWeightMeasurementId ?? '' }}">Save Column Mappings & Continue</button>
                    @elseif ( $step === App\Enums\UploadSteps::GROUP_SAMPLES )
                        <button type="button" class="btn btn-primary d-none" id="saveGroupingsBtn">Save Groupings & Review Samples</button>
                    @elseif ( $step === App\Enums\UploadSteps::REVIEW )
                        @if ( $upload->group_cache === null )
                            <a href="{{ route('study.phenotype-analysis', ['study' => $study->id]) }}" class="btn btn-primary">No Changes Detected - Back to Uploads</a>
                        @else
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#finalizeUploadModal">Finalize Upload</button>
                        @endif
                    @endif
                </div>
            @endif

            <div class="mt-4">
                @if ( $step === App\Enums\UploadSteps::REVIEW )
                    @include('study.phenotype-analysis.partials.step-review', [
                        'upload' => $upload
                    ])
                @elseif ( $step === App\Enums\UploadSteps::GROUP_SAMPLES )
                    @include('study.phenotype-analysis.partials.step-group-samples', [
                        'upload' => $upload,
                        'study' => $study,
                    ])
                @elseif ( $step === App\Enums\UploadSteps::MAP_MEASUREMENTS )
                    @include('study.phenotype-analysis.partials.step-map-measurements', [
                        'upload' => $upload
                    ])
                @else
                    {{-- PREPARE_UPLOAD step (default) --}}
                    @if ($analysisType->code === "mechanical_testing")
                        <div class="mb-4 mt-n3">
                            @includeIf('study.phenotype-analysis.forms.mechanical_testing', [
                                'upload' => $upload,
                                'analysisType' => $analysisType,
                            ])
                        </div>
                    @else
                        @include('study.phenotype-analysis.partials.step-prepare-upload', [
                            'upload' => $upload,
                            'analysisType' => $analysisType,
                        ])
                        @if ($upload->exists && $upload->path && ($upload->column_measurement_mappings || $upload->samples_count > 0))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updatePrepareModal">
                                Update Upload
                            </button>
                        @else
                            <button type="submit" class="btn btn-primary">
                                {{ $upload->exists ? 'Save & Continue' : 'Upload & Continue' }}
                            </button>
                        @endif
                    @endif
                @endif
            </div>
        </form>
    @endif
</div>

{{-- Update Prepare Configuration Warning Modal --}}
<div class="modal fade" id="updatePrepareModal" tabindex="-1" aria-labelledby="updatePrepareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePrepareModalLabel">Update Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to continue?</p>
                <p class="mb-0">Changing the specimen type, region, or uploading a new file will <strong>clear all existing column mappings and sample groupings</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning" form="updateUploadForm">
                    <i class="bi-arrow-repeat me-1"></i> Update & Clear Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Body Weight Not Mapped Warning Modal --}}
@if (isset($bodyWeightMeasurementId))
    <div class="modal fade" id="bodyWeightWarningModal" tabindex="-1" aria-labelledby="bodyWeightWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bodyWeightWarningModalLabel">
                        <i class="bi-exclamation-triangle-fill text-warning me-2"></i>Body Weight Not Mapped
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    You indicated that body weight is included in this upload, but no column has been mapped to <strong>Body Weight</strong>.
                    Click <strong>Go Back</strong> to map a column, or <strong>Continue Anyway</strong> to proceed without it.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                    <button type="button" class="btn btn-primary" id="confirmSkipBodyWeightBtn">Continue Anyway</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Unselected Samples Warning Modal --}}
<div class="modal fade" id="unselectedSamplesModal" tabindex="-1" aria-labelledby="unselectedSamplesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unselectedSamplesModalLabel">
                    <i class="bi-exclamation-triangle-fill text-warning me-2"></i>Unassigned Samples
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span id="unselectedSamplesCount"></span> sample(s) have not been assigned to a sample group and will be excluded from the analysis.
                Click <strong>Go Back</strong> to assign them, or <strong>Continue Anyway</strong> to proceed without them.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                <button type="button" class="btn btn-primary" id="confirmUnselectedSamplesBtn">Continue Anyway</button>
            </div>
        </div>
    </div>
</div>

{{-- Finalize Upload Confirmation Modal --}}
<div class="modal fade" id="finalizeUploadModal" tabindex="-1" aria-labelledby="finalizeUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizeUploadModalLabel">Finalize Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to finalize this upload?</p>
                @if($analysisType->code === 'micro_ct')
                    <p class="mb-0">This will create samples for this file. You can then optionally upload scan images from the Image Upload tab.</p>
                @else
                    <p class="mb-0">This will create samples for this file and cannot be undone.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                @if($upload->exists)
                    <form action="{{ route('study.phenotype-analysis.update.finalize', ['study' => $study->id, 'upload_id' => $upload->id]) }}" method="POST" class="d-inline" id="finalizeUploadForm">
                        @csrf
                        <input type="hidden" name="finalize" value="1" />
                        <button type="submit" class="btn btn-primary">Confirm & Finalize</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
</x-layouts.submission>
