@extends('study.summary.layout')

@section('tab-content')
    @if($uploads->isNotEmpty() || $mediaUploads->isNotEmpty())
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4">
            <p class="text-muted mb-0 small">
                Large downloads stream as an uncompressed zip so multi‑GB image files are not recompressed.
            </p>
            <a href="{{ route('study.summary.download-all', $study) }}"
               class="btn btn-primary flex-shrink-0 text-nowrap">
                <i class="bi bi-file-earmark-zip me-1"></i>
                Download all
                @if($downloadAllSize)
                    <span class="opacity-75">({{ $downloadAllSize }})</span>
                @endif
            </a>
        </div>
    @endif

    <h5 class="mb-3">Spreadsheet Data Files</h5>
    @forelse($uploads as $upload)
        <div class="d-flex align-items-center justify-content-between p-3 mb-3 border rounded bg-light">
            <div>
                <h6 class="mb-1">{{ $upload->original_filename }}</h6>
                @if($upload->analysis_type)
                    <small class="text-muted">{{ ucfirst($upload->analysis_type) }} Data</small>
                @endif
                <div class="mt-2">
                    <span class="badge bg-secondary me-1">{{ $upload->analysisType->name }}</span>
                    <span class="badge bg-secondary me-1">{{ $upload->getContextDescription() }}</span>
                </div>
            </div>
            <form action="{{ route('upload.download', $upload) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i>
                    Download
                </button>
            </form>
        </div>
    @empty
        <p class="text-muted mb-4">No spreadsheet data files available for download.</p>
    @endforelse

    <h5 class="mb-3 mt-4">Image Upload</h5>
    @forelse($mediaUploads as $media)
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3 p-3 mb-3 border rounded bg-light">
            <div class="w-100" style="min-width: 0;">
                <h6 class="mb-1 text-break">{{ $media->original_filename }}</h6>
                <small class="text-muted">
                    @if($media->sampleGroup)
                        Sample group: {{ $media->sampleGroup->label }} ·
                    @endif
                    @if($media->size)
                        {{ number_format($media->size / (1024 * 1024), 1) }} MB
                    @else
                        —
                    @endif
                </small>
                @include('study.partials.media-upload-metadata', ['media' => $media])
            </div>
            <a href="{{ route('study.media-uploads.download', [$study, $media]) }}"
               class="btn btn-primary flex-shrink-0 text-nowrap">
                <i class="bi bi-download me-1"></i>
                Download
            </a>
        </div>
    @empty
        <p class="text-muted mb-0">No images available for download.</p>
    @endforelse
@endsection
