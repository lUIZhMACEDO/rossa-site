@php
    use App\Support\MediaUploadTable;

    $scanEditable = ! $study->isLockedForEditing(auth()->user())
        && ! $study->isPhenotypeAnalysisLocked(auth()->user());
    $imageType = $imageType ?? null;
    $studyFigures = $studyFigures ?? collect();
    $processingLevels = MediaUploadTable::processingLevels();
    $boneTypes = MediaUploadTable::boneTypes();
    $spinalRegions = MediaUploadTable::spinalRegions();
    $uploadLabels = MediaUploadTable::uploadLabels();
    $formatUserUpload = fn (?array $userUpload, string $field): string => MediaUploadTable::formatUserUpload($userUpload, $field);
@endphp

<div class="mb-4"
     @if($imageType === 'micro_ct')
     data-media-upload
     data-csrf="{{ csrf_token() }}"
     data-init-url="{{ route('study.media-uploads.store', $study) }}"
     data-chunk-url-template="{{ route('study.media-uploads.chunk', ['study' => $study, 'media' => '__MEDIA__', 'index' => '__INDEX__']) }}"
     data-complete-url-template="{{ route('study.media-uploads.complete', ['study' => $study, 'media' => '__MEDIA__']) }}"
     data-status-url-template="{{ route('study.media-uploads.show', ['study' => $study, 'media' => '__MEDIA__']) }}"
     data-download-url-template="{{ route('study.media-uploads.download', ['study' => $study, 'media' => '__MEDIA__']) }}"
     data-destroy-url-template="{{ route('study.media-uploads.destroy', ['study' => $study, 'media' => '__MEDIA__']) }}"
     data-row-url-template="{{ route('study.media-uploads.row', ['study' => $study, 'media' => '__MEDIA__']) }}"
     data-chunk-size="{{ $chunkSize }}"
     data-max-file-size="{{ $maxFileSize }}"
     data-allowed-extensions="{{ implode(',', $allowedExtensions) }}"
     data-scan-editable="{{ $scanEditable ? '1' : '0' }}"
     @endif>

    {{-- ============================================================
         Section 01: image type picker, or the form for the chosen type
         ============================================================ --}}

    @if(! $imageType)

        <div class="d-flex align-items-baseline gap-2 mb-2">
            <span class="font-monospace" style="font-size: .9375rem; color: #2C2C2A;">01</span>
            <h2 class="h5 mb-0">Select image type to upload</h2>
        </div>

        <p class="mb-3" style="font-size: .875rem; color: #2C2C2A;">
            Choose what you want to upload. Raw Micro-CT volumes go with your scan data;
            representative figures are publication-ready images with captions.
            Image upload is optional&mdash;use Save &amp; Continue below when you are done, or to skip this step.
        </p>

        <div class="row row-cols-1 row-cols-lg-2 g-0 rounded overflow-hidden mb-5" style="border: 1px solid #5F5E5A;">
            <div class="col" style="border-right: 1px solid #5F5E5A;">
                <div class="h-100 p-3 d-flex flex-column" style="border-top: 4px solid #378ADD;">
                    <div class="fw-semibold lh-sm mb-1" style="font-size: .9375rem; color: #2C2C2A;">
                        Raw Micro-CT images
                    </div>
                    <div class="mb-3" style="font-size: .8125rem; color: #2C2C2A;">
                        Original unprocessed scan volumes
                    </div>
                    <div class="mt-auto">
                        @if($scanEditable)
                            <a href="{{ route('study.scan-datasets.index', ['study' => $study, 'image_type' => 'micro_ct']) }}"
                               class="btn btn-sm btn-modality w-100">
                                <i class="bi-upload"></i> Upload
                            </a>
                        @else
                            <button type="button" class="btn btn-sm btn-modality w-100 disabled" disabled
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="This study is locked for editing.">
                                <i class="bi-upload"></i> Upload
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="h-100 p-3 d-flex flex-column" style="border-top: 4px solid #888780;">
                    <div class="fw-semibold lh-sm mb-1" style="font-size: .9375rem; color: #2C2C2A;">
                        Representative figures
                    </div>
                    <div class="mb-3" style="font-size: .8125rem; color: #2C2C2A;">
                        Publication-ready figure images with captions
                    </div>
                    <div class="mt-auto">
                        @if($scanEditable)
                            <a href="{{ route('study.scan-datasets.index', ['study' => $study, 'image_type' => 'figures']) }}"
                               class="btn btn-sm btn-modality w-100">
                                <i class="bi-upload"></i> Upload
                            </a>
                        @else
                            <button type="button" class="btn btn-sm btn-modality w-100 disabled" disabled
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="This study is locked for editing.">
                                <i class="bi-upload"></i> Upload
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @include('study.phenotype-analysis.partials.uploaded-images-overview', [
            'study' => $study,
            'mediaUploads' => $mediaUploads,
            'studyFigures' => $studyFigures,
        ])

    @elseif($imageType === 'micro_ct')

        <div class="d-flex align-items-baseline gap-2 mb-2">
            <span class="font-monospace" style="font-size: .9375rem; color: #2C2C2A;">01</span>
            <h2 class="h5 mb-0">Upload raw Micro-CT images</h2>
            <a href="{{ route('study.scan-datasets.index', $study) }}"
               class="ms-auto" style="font-size: .8125rem;">
                <i class="bi-arrow-left"></i> Choose a different image type
            </a>
        </div>

        <div class="rounded p-3 p-md-4 mb-5" style="border: 1px solid #5F5E5A; border-top: 4px solid #378ADD;">

            <p class="mb-4" style="font-size: .875rem; color: #2C2C2A; max-width: 65ch;">
                Prefer the original unprocessed scan volume when possible.
                See the <button type="button" class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#guideModal">Help Guide</button>
                for what to upload by scanner and processing level.
            </p>

            @if($scanEditable)
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <label for="upload-processing-level" class="form-label">Processing level <span class="text-danger">*</span></label>
                        <select id="upload-processing-level" class="form-select" required data-upload-field="processing_level">
                            <option value="">Select&hellip;</option>
                            <option value="original">Original, unprocessed scan volume</option>
                            <option value="cropped_roi">Cropped region of interest</option>
                            <option value="segmented">Segmented/processed volume</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <label for="upload-bone-type" class="form-label">Bone type <span class="text-danger">*</span></label>
                        <select id="upload-bone-type" class="form-select" required data-upload-field="bone_type">
                            <option value="">Select&hellip;</option>
                            <option value="femur">Femur</option>
                            <option value="tibia">Tibia</option>
                            <option value="vertebra">Vertebra</option>
                            <option value="humerus">Humerus</option>
                            <option value="radius">Radius</option>
                            <option value="ulna">Ulna</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <label for="upload-scan-extent" class="form-label">Scan extent <span class="text-danger">*</span></label>
                        <select id="upload-scan-extent" class="form-select" required data-upload-field="scan_extent">
                            <option value="">Select bone type first&hellip;</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <label for="upload-sample-group" class="form-label">Sample group <span class="text-danger">*</span></label>
                        <select id="upload-sample-group" class="form-select" required data-upload-field="sample_group_id">
                            <option value="">Select&hellip;</option>
                            @forelse ($sampleGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->label }}</option>
                            @empty
                                <option value="" disabled>No sample groups yet</option>
                            @endforelse
                        </select>
                        @if ($sampleGroups->isEmpty())
                            <div class="form-text text-warning">
                                Create a sample group before uploading images.
                            </div>
                        @endif
                    </div>
                </div>

                                <div class="row g-3 mb-4" id="upload-vertebra-fields" hidden>
                    <div class="col-md-6 col-xl-3">
                        <label for="upload-spinal-region" class="form-label">Spinal region <span class="text-danger">*</span></label>
                        <select id="upload-spinal-region" class="form-select" data-upload-field="spinal_region">
                            <option value="">Select&hellip;</option>
                            <option value="cervical">Cervical</option>
                            <option value="thoracic">Thoracic</option>
                            <option value="lumbar">Lumbar</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label for="upload-vertebral-levels" class="form-label">Vertebral level(s) included in scan <span class="text-danger">*</span></label>
                        <input type="text"
                               id="upload-vertebral-levels"
                               class="form-control"
                               data-upload-field="vertebral_levels"
                               placeholder="e.g. 5 or 3-5"
                               autocomplete="off">
                        <div class="form-text">Single level (5) or range (3-5). Valid for lumbar: 1&ndash;6.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4" id="upload-other-bone-fields" hidden>
                    <div class="col-md-6 col-xl-3">
                        <label for="upload-bone-type-other" class="form-label">Specify bone type <span class="text-danger">*</span></label>
                        <input type="text"
                               id="upload-bone-type-other"
                               class="form-control"
                               data-upload-field="bone_type_other"
                               placeholder="e.g. calvaria, mandible, scapula"
                               autocomplete="off">
                        <div class="form-text">Use the standard anatomical name.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4" id="upload-other-extent-fields" hidden>
                    <div class="col-md-6 col-xl-3">
                        <label for="upload-scan-extent-other" class="form-label">Specify scan extent <span class="text-danger">*</span></label>
                        <input type="text"
                               id="upload-scan-extent-other"
                               class="form-control"
                               data-upload-field="scan_extent_other"
                               placeholder="e.g. frontal bone, condylar region"
                               autocomplete="off">
                        <div class="form-text">Describe the region of the bone that was scanned.</div>
                    </div>
                </div>

                <div id="upload-form-error" class="alert alert-danger py-2 small" hidden></div>

                <label for="media-file-input" class="media-dropzone d-flex flex-column align-items-center justify-content-center gap-2 border border-2 border-dashed rounded p-4 text-center mb-0">
                    <input type="file"
                           id="media-file-input"
                           class="visually-hidden"
                           accept=".{{ implode(',.', $allowedExtensions) }}"
                           multiple>
                    <i class="bi bi-cloud-arrow-up media-dropzone-icon" aria-hidden="true"></i>
                    <div class="fw-semibold">Load files by sample group</div>
                    <div class="small">Files dropped or browsed here are assigned to the sample group selected above</div>
                    <div class="small media-dropzone-formats font-monospace">
                        .{{ implode(', .', $allowedExtensions) }}
                        &middot; up to {{ number_format($maxFileSize / (1024 * 1024 * 1024), 0) }} GB
                    </div>
                </label>
            @else
                <div class="alert alert-secondary mb-0">
                    File upload is unavailable while this study is locked.
                </div>
            @endif
        </div>

    @elseif($imageType === 'figures')

        <div class="d-flex align-items-baseline gap-2 mb-2">
            <span class="font-monospace" style="font-size: .9375rem; color: #2C2C2A;">01</span>
            <h2 class="h5 mb-0">Upload representative figures</h2>
            <a href="{{ route('study.scan-datasets.index', $study) }}"
               class="ms-auto" style="font-size: .8125rem;">
                <i class="bi-arrow-left"></i> Choose a different image type
            </a>
        </div>

        <div class="rounded p-3 p-md-4 mb-5" style="border: 1px solid #5F5E5A; border-top: 4px solid #888780;">
            <p class="mb-4" style="font-size: .875rem; color: #2C2C2A; max-width: 65ch;">
                Upload publication-ready figure images and a short caption describing each one.
                JPG, PNG, or WebP up to 10&nbsp;MB.
            </p>

            @if($scanEditable)
                <form method="POST"
                      action="{{ route('study.figures.store', $study) }}"
                      enctype="multipart/form-data"
                      class="row g-3 align-items-start">
                    @csrf
                    <div class="col-md-5">
                        <label for="figure-image" class="form-label">Image <span class="text-danger">*</span></label>
                        <input type="file"
                               id="figure-image"
                               name="image"
                               class="form-control @error('image') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               required>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="figure-caption" class="form-label">Caption <span class="text-danger">*</span></label>
                        <textarea id="figure-caption"
                                  name="caption"
                                  class="form-control @error('caption') is-invalid @enderror"
                                  rows="3"
                                  maxlength="500"
                                  required
                                  placeholder="Describe what this figure shows">{{ old('caption') }}</textarea>
                        @error('caption')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi-upload"></i> Upload
                        </button>
                    </div>
                </form>
            @else
                <div class="alert alert-secondary mb-0">
                    File upload is unavailable while this study is locked.
                </div>
            @endif
        </div>

        @include('study.phenotype-analysis.partials.study-figures-list', [
            'study' => $study,
            'studyFigures' => $studyFigures,
            'scanEditable' => $scanEditable,
            'sectionNumber' => '02',
        ])

    @endif

    @if($imageType === 'micro_ct')
    @if($scanEditable)
        <div id="media-upload-queue-wrap" class="mb-4" hidden>
            <h2 class="h5 mb-3">Uploads in progress</h2>
            <div id="media-upload-queue"></div>
        </div>
    @endif

    <script type="application/json" id="media-upload-labels">@json($uploadLabels)</script>

    {{-- ============================================================
         Section 02: uploaded images
         ============================================================ --}}

    @include('study.phenotype-analysis.partials.media-uploads-table', [
        'study' => $study,
        'mediaUploads' => $mediaUploads,
        'sectionNumber' => '02',
    ])

    <div class="modal fade" id="media-upload-navigation-modal" tabindex="-1" aria-labelledby="media-upload-navigation-modal-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="media-upload-navigation-modal-title">
                        Upload in progress
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Leaving this page will stop your current upload(s).</p>
                    <p class="mb-0">You can return later and use <strong>Resume</strong> to continue from where you left off.</p>
                </div>
                <div class="modal-footer d-block">
                    <p class="small text-muted mb-3">
                        <strong>Open in new tab</strong> keeps your upload running in this tab while you view the other page.
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-danger" id="media-upload-leave-page">Leave page</button>
                        <button type="button" class="btn btn-outline-primary" id="media-upload-open-new-tab">
                            Open in new tab
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
