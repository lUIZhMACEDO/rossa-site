@section('title', 'Study Report')
@section('guide-content')
    @include('guides.data-ingestion._06_report')
@endsection
<x-layouts.submission :$study>
    <div class="py-4">
        <div class="mb-4">
            <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </nav>
            <h1 class="mb-4">Study Report</h1>
        </div>
        <div class="mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <!-- tab navigation -->
                    <ul class="nav nav-tabs report-tabs border-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('study.summary.information', $study) }}"
                               class="nav-link {{ request()->routeIs('study.summary.information') ? 'active' : '' }}">
                                Information
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('study.summary.exploration', $study) }}"
                               class="nav-link {{ request()->routeIs('study.summary.exploration') ? 'active' : '' }}">
                                Data Exploration
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('study.summary.download', $study) }}"
                               class="nav-link {{ request()->routeIs('study.summary.download') ? 'active' : '' }}">
                                Download Original Data
                            </a>
                        </li>
                        @if (Auth::user()->can('manage-studies'))
                            <li class="nav-item" role="presentation">
                                <a href="{{ route('admin.study.edit', $study) }}"
                                   class="nav-link {{ request()->routeIs('admin.study.edit') ? 'active' : '' }}">
                                    Admin Actions
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    @yield('tab-content')
                </div>
            </div>
        </div>
        <form action="{{ route('study.submission', $study) }}" method="GET">
            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg">Go To Submission <i class="bi-arrow-right"></i></button>
            </div>
        </form>
    </div>
</x-layouts.submission>
