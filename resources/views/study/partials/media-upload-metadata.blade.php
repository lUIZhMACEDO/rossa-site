@php
    $userUpload = $media->metadata['user_upload'] ?? [];
@endphp

@if($userUpload || $media->sampleGroup)
    <dl class="row small text-break mt-2 mb-0">
        @foreach(['processing_level' => 'Processing level', 'bone_type' => 'Bone type', 'scan_extent' => 'Scan extent'] as $field => $label)
            @if(!empty($userUpload[$field]))
                <dt class="col-sm-4">{{ $label }}</dt>
                <dd class="col-sm-8 mb-1">{{ \App\Support\MediaUploadTable::formatUserUpload($userUpload, $field) }}</dd>
            @endif
        @endforeach
        @if($media->sampleGroup)
            <dt class="col-sm-4">Sample group</dt>
            <dd class="col-sm-8 mb-1">{{ $media->sampleGroup->label }}</dd>
        @endif
    </dl>
@endif
