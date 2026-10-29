@php
    $userUpload = is_array($media->metadata) ? ($media->metadata['user_upload'] ?? []) : [];
    $boneTypeValue = $userUpload['bone_type'] ?? '';
    $extentOptions = $boneTypeValue === 'vertebra'
        ? array_merge($vertebraExtents, ['other' => 'Other'])
        : ($boneTypeValue ? array_merge($longBoneExtents, ['other' => 'Other']) : []);
    $metadataFormId = 'media-user-upload-'.$media->id;
@endphp
<tr data-media-id="{{ $media->id }}"
    data-original-filename="{{ $media->original_filename }}"
    data-sample-group-id="{{ $media->sample_group_id }}">
    <td>
        <div class="text-truncate font-monospace" style="max-width: 28rem; font-size: .875rem;" title="{{ $media->original_filename }}">
            {{ $media->original_filename }}
        </div>
        @if($scanEditable)
            <form id="{{ $metadataFormId }}"
                  hx-patch="{{ route('study.media-uploads.update-user-upload', [$study, $media]) }}"
                  hx-swap="none"
                  hx-sync="this:abort"
                  hx-trigger="change delay:200ms from:closest tr target:.media-user-upload-field, focusout delay:200ms from:closest tr target:.media-user-upload-field, submit"
                  novalidate
                  data-media-metadata-form
                  class="d-none">
                @csrf
                @method('PATCH')
                <input type="hidden" name="user_upload[kind]" value="raw_micro_ct">
            </form>
        @endif
    </td>
    <td data-media-status-cell>
        <span @class([
            'badge',
            'text-bg-success' => $media->status === App\Enums\MediaUploadStatus::Complete,
            'text-bg-warning' => $media->isInProgress() || $media->status === App\Enums\MediaUploadStatus::Scanning,
            'text-bg-danger' => $media->status === App\Enums\MediaUploadStatus::Failed,
            'text-bg-secondary' => ! $media->isInProgress() && $media->status !== App\Enums\MediaUploadStatus::Scanning && $media->status !== App\Enums\MediaUploadStatus::Complete && $media->status !== App\Enums\MediaUploadStatus::Failed,
        ])>
            {{ $media->status->label() }}
        </span>
        @if($media->isInProgress() && $media->size)
            <span class="small ms-1" data-media-upload-pct>
                {{ number_format(($media->bytes_received / max($media->size, 1)) * 100, 0) }}%
            </span>
        @endif
        @if($media->malware_scan_message && $media->status === App\Enums\MediaUploadStatus::Failed)
            <div class="small">{{ $media->malware_scan_message }}</div>
        @endif
    </td>
    <td>
        @if($scanEditable)
            <select class="form-select form-select-sm media-user-upload-field"
                    name="user_upload[processing_level]"
                    form="{{ $metadataFormId }}"
                    data-media-bone-dependent
                    aria-label="Processing level"
                    style="min-width: 12rem;">
                @foreach ($processingLevels as $value => $label)
                    <option value="{{ $value }}" @selected(($userUpload['processing_level'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        @else
            <span style="font-size: .875rem;">{{ $formatUserUpload($userUpload, 'processing_level') }}</span>
        @endif
    </td>
    <td>
        @if($scanEditable)
            <div data-user-upload-cell="bone_type">
                <select class="form-select form-select-sm media-user-upload-field"
                        name="user_upload[bone_type]"
                        form="{{ $metadataFormId }}"
                        data-media-bone-type
                        aria-label="Bone type">
                    @foreach ($boneTypes as $value => $label)
                        <option value="{{ $value }}" @selected($boneTypeValue === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text"
                       class="form-control form-control-sm mt-1 media-user-upload-field"
                       name="user_upload[bone_type_other]"
                       form="{{ $metadataFormId }}"
                       data-user-upload-extra="bone_type_other"
                       value="{{ $userUpload['bone_type_other'] ?? '' }}"
                       placeholder="e.g. calvaria, mandible, scapula"
                       autocomplete="off"
                       @if($boneTypeValue !== 'other') hidden @endif
                       aria-label="Specify bone type">
                <div data-user-upload-extra="vertebra" @if($boneTypeValue !== 'vertebra') hidden @endif>
                    <select class="form-select form-select-sm mt-1 media-user-upload-field"
                            name="user_upload[spinal_region]"
                            form="{{ $metadataFormId }}"
                            aria-label="Spinal region">
                        <option value="">Select&hellip;</option>
                        @foreach ($spinalRegions as $value => $label)
                            <option value="{{ $value }}" @selected(($userUpload['spinal_region'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text"
                           class="form-control form-control-sm mt-1 media-user-upload-field"
                           name="user_upload[vertebral_levels]"
                           form="{{ $metadataFormId }}"
                           value="{{ $userUpload['vertebral_levels'] ?? '' }}"
                           placeholder="e.g. 5 or 3-5"
                           autocomplete="off"
                           aria-label="Vertebral level(s)">
                </div>
            </div>
        @else
            <span style="font-size: .875rem;">{{ $formatUserUpload($userUpload, 'bone_type') }}</span>
        @endif
    </td>
    <td data-media-scan-extent-cell>
        @if($scanEditable)
            <div data-user-upload-cell="scan_extent">
                <select class="form-select form-select-sm media-user-upload-field"
                        name="user_upload[scan_extent]"
                        form="{{ $metadataFormId }}"
                        data-media-scan-extent
                        aria-label="Scan extent"
                        style="min-width: 10rem;">
                    @forelse ($extentOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($userUpload['scan_extent'] ?? '') === $value)>{{ $label }}</option>
                    @empty
                        <option value="">Select bone type first&hellip;</option>
                    @endforelse
                </select>
                <input type="text"
                       class="form-control form-control-sm mt-1 media-user-upload-field"
                       name="user_upload[scan_extent_other]"
                       form="{{ $metadataFormId }}"
                       data-user-upload-extra="scan_extent_other"
                       value="{{ $userUpload['scan_extent_other'] ?? '' }}"
                       placeholder="e.g. frontal bone, condylar region"
                       autocomplete="off"
                       @if(($userUpload['scan_extent'] ?? '') !== 'other') hidden @endif
                       aria-label="Specify scan extent">
            </div>
        @else
            <span style="font-size: .875rem;">{{ $formatUserUpload($userUpload, 'scan_extent') }}</span>
        @endif
    </td>
    <td>
        @if($scanEditable && $sampleGroups->isNotEmpty())
            <form hx-patch="{{ route('study.media-uploads.assign-sample-group', [$study, $media]) }}"
                  hx-swap="none"
                  hx-trigger="change"
                  hx-sync="this:abort"
                  novalidate
                  data-media-sample-group-form>
                @csrf
                @method('PATCH')
                <select class="form-select form-select-sm"
                        name="sample_group_id"
                        aria-label="Sample group">
                    <option value="">Select sample group&hellip;</option>
                    @foreach ($sampleGroups as $group)
                        <option value="{{ $group->id }}" @selected((int) $media->sample_group_id === (int) $group->id)>
                            {{ $group->label }}
                        </option>
                    @endforeach
                </select>
            </form>
        @elseif($media->sampleGroup)
            <span style="font-size: .875rem;">{{ $media->sampleGroup->label }}</span>
        @else
            <span class="text-muted" style="font-size: .875rem;">Unassigned</span>
        @endif
    </td>
    <td class="text-end" data-media-actions-cell>
        <div class="btn-group gap-1">
            @if($media->isDownloadable())
                <a href="{{ route('study.media-uploads.download', [$study, $media]) }}"
                   class="btn btn-sm btn-outline-secondary"
                   title="Download">
                    <i class="bi-download"></i>
                </a>
            @elseif($media->isInProgress() && $scanEditable)
                <button type="button"
                        class="btn btn-sm btn-primary"
                        data-resume-upload
                        data-media-id="{{ $media->id }}"
                        data-filename="{{ $media->original_filename }}"
                        data-size="{{ $media->size }}"
                        title="Resume (re-select the same file)">
                    Resume
                </button>
            @endif
            @if($scanEditable)
                <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        data-delete-media
                        data-delete-url="{{ route('study.media-uploads.destroy', [$study, $media]) }}"
                        title="Delete">
                    <i class="bi-trash"></i>
                </button>
            @endif
        </div>
    </td>
</tr>
