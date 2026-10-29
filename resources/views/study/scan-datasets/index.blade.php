@section('title', 'Image Upload')
@section('guide-content')
    @include('guides.data-ingestion._05_3image-upload')
@endsection
@section('guide-link', route('guides.data-ingestion').'#section-5-image-upload')
<x-layouts.submission :$study>
<div class="py-0" @if($study->isPhenotypeAnalysisLocked(auth()->user())) data-input-locked @endif>

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-baseline gap-3 p-3 mb-4"
         style="background-color: #0D3B52; border-radius: 0.375rem;">
        <div>
            <h1 class="mb-1" style="color: #fff;">Upload image data</h1>
            <p class="mb-0" style="color: #E6F1FB;">
                Upload image files and representative image data for this study.
                Image upload is optional; continue without files to mark this step complete.
            </p>
        </div>
        <div class="font-monospace text-nowrap" style="font-size: .8125rem; color: #E6F1FB;">
            {{ $study->identifier }}
        </div>
    </div>

    @php
        $incompleteUploads = $mediaUploads->filter(fn ($media) => ! $media->isComplete());
        $hasIncompleteUploads = $incompleteUploads->isNotEmpty();
    @endphp

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif($hasIncompleteUploads)
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            {{ $incompleteUploads->count() }}
            {{ $incompleteUploads->count() === 1 ? 'image upload is' : 'image uploads are' }}
            not complete (Pending, Uploading, Scanning, or Failed).
            Finish or delete {{ $incompleteUploads->count() === 1 ? 'it' : 'them' }} before you can continue.
            Failed or abandoned uploads must be deleted.
        </div>
    @endif

    @include('study.phenotype-analysis.partials.step-scan-datasets', [
        'study' => $study,
        'sampleGroups' => $sampleGroups,
        'mediaUploads' => $mediaUploads,
        'studyFigures' => $studyFigures,
        'chunkSize' => $chunkSize,
        'maxFileSize' => $maxFileSize,
        'allowedExtensions' => $allowedExtensions,
        'imageType' => $imageType,
    ])

    <div class="d-flex flex-column align-items-end gap-2 mt-4 pb-4">
        @if($hasIncompleteUploads)
            <p class="mb-0 small text-warning text-end">
                Delete or finish incomplete uploads before continuing.
            </p>
        @endif
        <form method="POST" action="{{ route('study.scan-datasets.continue', $study) }}">
            @csrf
            <button type="submit" class="btn btn-lg btn-primary">Save &amp; Continue <i class="bi-arrow-right"></i></button>
        </form>
    </div>
</div>
</x-layouts.submission>
