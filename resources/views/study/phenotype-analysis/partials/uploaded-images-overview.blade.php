@php
    use App\Support\MediaUploadTable;

    $formatUserUpload = fn (?array $userUpload, string $field): string => MediaUploadTable::formatUserUpload($userUpload, $field);
@endphp

<div class="d-flex align-items-baseline gap-2 mb-2">
    <span class="font-monospace" style="font-size: .9375rem; color: #2C2C2A;">02</span>
    <h2 class="h5 mb-0">Already uploaded</h2>
</div>

<div class="row row-cols-1 row-cols-lg-2 g-0 rounded overflow-hidden mb-2" style="border: 1px solid #5F5E5A;">
    <div class="col" style="border-right: 1px solid #5F5E5A;">
        <div class="h-100 d-flex flex-column" style="border-top: 4px solid #378ADD;">
            <div class="px-3 pt-3 pb-2 d-flex align-items-baseline gap-2">
                <div class="fw-semibold lh-sm" style="font-size: .9375rem; color: #2C2C2A;">
                    Raw Micro-CT images
                </div>
                <span class="ms-auto font-monospace" style="font-size: .8125rem; color: #5F5E5A;">
                    {{ $mediaUploads->count() }}
                </span>
            </div>

            @forelse ($mediaUploads as $media)
                @php
                    $userUpload = is_array($media->metadata) ? ($media->metadata['user_upload'] ?? []) : [];
                    $metaParts = array_values(array_filter([
                        $formatUserUpload($userUpload, 'bone_type') !== '—' ? $formatUserUpload($userUpload, 'bone_type') : null,
                        $formatUserUpload($userUpload, 'scan_extent') !== '—' ? $formatUserUpload($userUpload, 'scan_extent') : null,
                        $media->sampleGroup?->label,
                    ]));
                @endphp
                <div class="px-3 py-2 border-top" style="border-color: #D3D1C7 !important;">
                    <div class="d-flex align-items-start gap-2">
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-truncate font-monospace" style="font-size: .8125rem; color: #2C2C2A;" title="{{ $media->original_filename }}">
                                {{ $media->original_filename }}
                            </div>
                            @if($metaParts)
                                <div class="text-truncate mt-1" style="font-size: .75rem; color: #5F5E5A;" title="{{ implode(' · ', $metaParts) }}">
                                    {{ implode(' · ', $metaParts) }}
                                </div>
                            @endif
                        </div>
                        <span @class([
                            'badge flex-shrink-0',
                            'text-bg-success' => $media->status === App\Enums\MediaUploadStatus::Complete,
                            'text-bg-warning' => $media->isInProgress() || $media->status === App\Enums\MediaUploadStatus::Scanning,
                            'text-bg-danger' => $media->status === App\Enums\MediaUploadStatus::Failed,
                            'text-bg-secondary' => ! $media->isInProgress() && $media->status !== App\Enums\MediaUploadStatus::Scanning && $media->status !== App\Enums\MediaUploadStatus::Complete && $media->status !== App\Enums\MediaUploadStatus::Failed,
                        ])>
                            {{ $media->status->label() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-3 py-4 border-top flex-grow-1 d-flex align-items-center justify-content-center" style="border-color: #D3D1C7 !important; color: #5F5E5A; font-size: .875rem;">
                    None uploaded yet
                </div>
            @endforelse
        </div>
    </div>

    <div class="col">
        <div class="h-100 d-flex flex-column" style="border-top: 4px solid #888780;">
            <div class="px-3 pt-3 pb-2 d-flex align-items-baseline gap-2">
                <div class="fw-semibold lh-sm" style="font-size: .9375rem; color: #2C2C2A;">
                    Representative figures
                </div>
                <span class="ms-auto font-monospace" style="font-size: .8125rem; color: #5F5E5A;">
                    {{ $studyFigures->count() }}
                </span>
            </div>

            @forelse ($studyFigures as $figure)
                <div class="px-3 py-2 border-top" style="border-color: #D3D1C7 !important;">
                    <div class="d-flex align-items-start gap-3">
                        <img src="{{ route('study.figures.show', [$study, $figure]) }}"
                             alt=""
                             class="flex-shrink-0 rounded"
                             style="width: 3.25rem; height: 2.5rem; object-fit: cover; background: #F5F4F0;">
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-truncate font-monospace" style="font-size: .8125rem; color: #2C2C2A;" title="{{ $figure->original_filename }}">
                                {{ $figure->original_filename }}
                            </div>
                            <div class="text-truncate mt-1" style="font-size: .75rem; color: #5F5E5A;" title="{{ $figure->caption }}">
                                {{ $figure->caption }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-3 py-4 border-top flex-grow-1 d-flex align-items-center justify-content-center" style="border-color: #D3D1C7 !important; color: #5F5E5A; font-size: .875rem;">
                    None uploaded yet
                </div>
            @endforelse
        </div>
    </div>
</div>
