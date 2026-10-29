@extends('layout')

@section('title', 'Team Members')
@section('description', 'Meet the dedicated researchers and developers behind ROSSA.')


@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-normal text-dark mb-1">Team Members</h1>
        <div class="mx-auto bg-dark" style="width: 60px; height: 1px;"></div>
        <p class="text-muted mt-4">Meet the dedicated researchers and developers behind ROSSA</p>
    </div>

    <!-- Skeletal Biology Team -->
    <div class="mb-5">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-dark rounded-circle p-2 me-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 17L12 22L22 17" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12L12 17L22 12" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2 class="mb-0 text-dark fw-normal">Skeletal Biology Team</h2>
        </div>

        <div class="row g-4">
            @foreach ($teamMembers['skeletal_biology'] as $member)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h5 class="card-title mb-0 fw-bold">{{ $member['name'] }}</h5>
                            </div>

                            <div class="mb-2">
                                <span class="badge bg-light text-dark small">{{ $member['institution'] }}</span>
                            </div>

                            <p class="text-muted mb-1 small">
                                <strong>Department:</strong> {{ $member['department'] }}
                            </p>
                            <p class="text-muted mb-0 small">
                                <strong>Location:</strong> {{ $member['location'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Computer Science Team -->
    <div class="mb-5">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-dark rounded-circle p-2 me-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="8" y1="21" x2="16" y2="21" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="17" x2="12" y2="21" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2 class="mb-0 text-dark fw-normal">Computer Science, Software & Infrastructure Team</h2>
        </div>

        <div class="row g-4">
            @foreach ($teamMembers['computer_science'] as $member)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h5 class="card-title mb-0 fw-bold">{{ $member['name'] }}</h5>
                            </div>

                            <div class="mb-2">
                                <span class="badge bg-light text-dark small">{{ $member['institution'] }}</span>
                            </div>

                            <p class="text-muted mb-1 small">
                                <strong>Department:</strong> {{ $member['department'] }}
                            </p>
                            <p class="text-muted mb-0 small">
                                <strong>Location:</strong> {{ $member['location'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

	<!-- Past Contributors -->
    <div class="mb-5">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-dark rounded-circle p-2 me-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M3 3v5h5" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M12 7v5l4 2" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
            </div>
            <h2 class="mb-0 text-dark fw-normal">Past Contributors</h2>
        </div>

        <div class="row g-4">
            @foreach ($teamMembers['past_contributors'] as $member)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h5 class="card-title mb-0 fw-bold">{{ $member['name'] }}</h5>
                            </div>

                            <div class="mb-2">
                                <span class="badge bg-light text-dark small">{{ $member['institution'] }}</span>
                            </div>

                            <p class="text-muted mb-1 small">
                                <strong>Department:</strong> {{ $member['department'] }}
                            </p>
                            <p class="text-muted mb-0 small">
                                <strong>Location:</strong> {{ $member['location'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

