@extends('layout')

@section('title', 'Quarantined Media Uploads')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Quarantined Media Uploads</h1>
                <p class="text-muted mb-0">Failed malware scans — infected files and scan errors</p>
            </div>
        </div>

        <div class="row mb-4 g-2">
            <div class="col-md-8">
                <form method="GET" action="{{ route('admin.media-uploads.index') }}" class="d-flex gap-2 flex-wrap">
                    <select name="status" class="form-select" style="max-width: 10rem;">
                        <option value="">All statuses</option>
                        <option value="infected" @selected(request('status') === 'infected')>Infected</option>
                        <option value="error" @selected(request('status') === 'error')>Error</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                    @if(request('status'))
                        <a href="{{ route('admin.media-uploads.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <p class="text-muted small mb-2"><strong>{{ $mediaUploads->total() }}</strong> quarantined upload{{ $mediaUploads->total() === 1 ? '' : 's' }}</p>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                @if($mediaUploads->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>File</th>
                                    <th>Study</th>
                                    <th>Uploaded by</th>
                                    <th>Scan</th>
                                    <th>Reason</th>
                                    <th>Size</th>
                                    <th>Scanned</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mediaUploads as $media)
                                    <tr>
                                        <td>
                                            <div class="text-truncate" style="max-width: 16rem;" title="{{ $media->original_filename }}">
                                                {{ $media->original_filename }}
                                            </div>
                                            <small class="text-muted">#{{ $media->id }}</small>
                                        </td>
                                        <td>
                                            @if($media->study)
                                                <a href="{{ route('admin.study.edit', $media->study_id) }}">
                                                    {{ $media->study->identifier ?? 'Study #'.$media->study_id }}
                                                </a>
                                                <div class="small text-muted text-truncate" style="max-width: 14rem;" title="{{ $media->study->title }}">
                                                    {{ $media->study->title }}
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($media->study?->user)
                                                <div>{{ $media->study->user->name }}</div>
                                                <p>{{ $media->study->user->email }}</p>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($media->malware_scan_status === 'infected')
                                                <span class="badge text-bg-danger">Infected</span>
                                            @else
                                                <span class="badge text-bg-warning">Error</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width: 18rem;" class="small">
                                                {{ $media->malware_scan_message ?: '—' }}
                                            </div>
                                        </td>
                                        <td class="text-nowrap small">
                                            {{ number_format($media->size / (1024 * 1024), 1) }} MB
                                        </td>
                                        <td class="text-nowrap">
                                            @if($media->malware_scanned_at)
                                                <small class="text-muted">
                                                    {{ $media->malware_scanned_at->format('M d, Y') }}<br>
                                                    {{ $media->malware_scanned_at->format('g:i A') }}
                                                </small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted mb-0">No quarantined media uploads found.</p>
                    </div>
                @endif
            </div>
        </div>

        @if($mediaUploads->hasPages())
            <div class="mt-4">
                {{ $mediaUploads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
