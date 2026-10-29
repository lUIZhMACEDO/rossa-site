@extends('layout')

@section('title', $study->title.' — Download Data')
@section('description', 'Download original skeletal phenotyping data for '.$study->identifier.' from the Rodent Open Science Skeletal Archive.')
@section('canonical', route('search.show', $study))

@section('content')
<div class="container py-5">
    @include('search.partials.report-header')

    <!-- Navigation Tabs -->
    @include('search.partials.report-tabs')

    @if($uploads->isNotEmpty() || $mediaUploads->isNotEmpty())
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mt-4 mb-3">
            <p class="text-muted mb-0 small">
                Large downloads stream as an uncompressed zip so multi‑GB image files are not recompressed.
            </p>
            <a href="{{ route('search.data-download.all', $study) }}"
               class="btn btn-primary flex-shrink-0 text-nowrap">
                <i class="bi bi-file-earmark-zip me-1"></i>
                Download all
                @if($downloadAllSize)
                    <span class="opacity-75">({{ $downloadAllSize }})</span>
                @endif
            </a>
        </div>
    @endif

    <!-- Download Content -->
    <div class="card report-section mt-4 mb-4">
        <div class="card-header">
            <h4 class="mb-0">Download Original Data Files</h4>
        </div>
        <div class="card-body">
            @if($uploads->count() > 0)
                @foreach($uploads as $upload)
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 p-3 mb-3 border rounded bg-light">
                        <div class="w-100" style="min-width: 0;">
                            <h6 class="mb-1 text-break">{{ $upload->original_filename }}</h6>
                            @if($upload->analysis_type)
                                <small class="text-muted">{{ ucfirst($upload->analysis_type) }} Data</small>
                            @endif
                            <div class="mt-2">
                                @if($upload->analysisType)
                                    <span class="badge bg-secondary me-1">{{ $upload->analysisType->name }}</span>
                                @endif
                                @if($upload->specimenType)
                                    <span class="badge bg-secondary me-1">{{ $upload->specimenType->name }}</span>
                                @endif
                                @if($upload->region)
                                    <span class="badge bg-secondary me-1">{{ $upload->region->label }}</span>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('upload.download', $upload) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-download me-1"></i>
                                Download
                            </button>
                        </form>
                    </div>
                @endforeach
            @else
                <p class="text-muted mb-0">No data files are available for download for this study.</p>
            @endif
        </div>
    </div>

    <!-- Scan datasets (ISQ / DCM / AIM) -->
    <div class="card report-section mb-4">
        <div class="card-header">
            <h4 class="mb-0">Image Upload</h4>
        </div>
        <div class="card-body">
            @if($mediaUploads->count() > 0)
                @foreach($mediaUploads as $media)
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
                        <a href="{{ route('search.media-uploads.download', [$study, $media]) }}"
                           class="btn btn-primary flex-shrink-0 text-nowrap">
                            <i class="bi bi-download me-1"></i>
                            Download
                        </a>
                    </div>
                @endforeach
            @else
                <p class="text-muted mb-0">No images are available for download for this study.</p>
            @endif
        </div>
    </div>
</div>
@endsection
