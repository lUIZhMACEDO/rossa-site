@section('title', 'Statistical Analysis')
<x-layouts.submission :$study>
<div class="py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item active" aria-current="page">Statistical Analysis</li>
            </ol>
        </nav>
        <h1>Statistical Analysis</h1>
        <p class="text-muted fst-italic">View and manage statistical analysis results for uploaded data.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Upload</th>
                    <th scope="col">Analysis Type</th>
                    <th scope="col">Date Uploaded</th>
                    <th scope="col" class="text-center">Samples</th>
                    <th scope="col" class="text-center">Groups</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if ($uploads->isEmpty())
                <tr>
                    <td colspan="100%" class="text-center text-muted">No analysis uploads found.</td>
                </tr>
                @else
                    @foreach ($uploads as $upload)
                        @php
                            $analysis = $upload->statisticalAnalysis;
                            $status = $analysis ? $analysis->status : 'not_run';
                            $isStale = $analysis && $analysis->isStale();
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('study.statistical-analysis.show', ['study' => $study, 'upload' => $upload]) }}">
                                    {{ $upload->original_filename }}
                                </a>
                            </td>
                            <td>{{ $upload->analysisType->name ?? '-' }}</td>
                            <td>{{ $upload->created_at->format('M d, Y') }}</td>
                            <td class="text-center">{{ $upload->samples_count }}</td>
                            <td class="text-center">
                                {{ $upload->samples->pluck('sampleGroup')->unique('id')->filter()->count() }}
                            </td>
                            <td>
                                @if ($isStale)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi-exclamation-triangle"></i> Stale
                                    </span>
                                @elseif ($status === 'completed')
                                    <span class="badge text-bg-success">
                                        <i class="bi-check-circle"></i> Complete
                                    </span>
                                @elseif ($status === 'processing')
                                    <span class="badge text-bg-primarydark">
                                        <i class="bi-hourglass-split"></i> Processing
                                    </span>
                                @elseif ($status === 'failed')
                                    <span class="badge text-bg-danger">
                                        <i class="bi-x-circle"></i> Failed
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        <i class="bi-dash-circle"></i> Not Run
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group gap-2">
                                    <a href="{{ route('study.statistical-analysis.show', ['study' => $study, 'upload' => $upload]) }}" 
                                       class="btn btn-primary"
                                       data-bs-toggle="tooltip"
                                       title="View">
                                        <i class="bi-eye"></i>
                                    </a>
                                    <form action="{{ route('study.statistical-analysis.recalculate', ['study' => $study, 'upload' => $upload]) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning"
                                                title="{{ $status === 'processing' ? 'Analysis is running' : 'Run or rerun analysis' }}"
                                                data-bs-toggle="tooltip">
                                            <i class="bi-arrow-clockwise"></i> 
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
</x-layouts.submission>

