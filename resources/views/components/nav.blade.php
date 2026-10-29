@if (Route::is('admin.*'))
<nav class="navbar navbar-expand-xl navbar-dark bg-dark" data-bs-theme="dark" aria-label="Administration">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarNav" aria-controls="adminNavbarNav" aria-expanded="false" aria-label="Toggle admin navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbarNav">
            <ul class="navbar-nav gap-xl-2">
                @canany(['manage-collections', 'manage-studies'])
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('admin/collections*', 'admin/studies*', 'admin/embargo*', 'admin/media-uploads*') ? 'active' : '' }}" href="#" id="adminDataDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Data
                        @if ((Gate::allows('manage-studies') ? ($pendingStudyReviewsCount ?? 0) : 0) > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ Gate::allows('manage-studies') ? ($pendingStudyReviewsCount ?? 0) : 0 }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminDataDropdown">
                        @can('manage-collections')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/collections*') ? 'active' : '' }}" href="{{ route('admin.collections') }}" @if (request()->is('admin/collections*')) aria-current="page" @endif>Collections</a>
                        </li>
                        @endcan
                        @can('manage-studies')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/studies*') ? 'active' : '' }}" href="{{ route('admin.studies') }}" @if (request()->is('admin/studies*')) aria-current="page" @endif>
                                Studies
                                @if(($pendingStudyReviewsCount ?? 0) > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $pendingStudyReviewsCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/embargo*') ? 'active' : '' }}" href="{{ route('admin.embargo.index') }}" @if (request()->is('admin/embargo*')) aria-current="page" @endif>Embargo</a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/media-uploads*') ? 'active' : '' }}" href="{{ route('admin.media-uploads.index') }}" @if (request()->is('admin/media-uploads*')) aria-current="page" @endif>Media Quarantine</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @can('manage-studies')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('admin/measurements*', 'admin/ontology*', 'admin/genes*') ? 'active' : '' }}" href="#" id="adminReferenceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Reference
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminReferenceDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ Route::is('admin.measurements*') ? 'active' : '' }}" href="{{ route('admin.measurements') }}" @if (Route::is('admin.measurements*')) aria-current="page" @endif>Measurements</a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/ontology*') ? 'active' : '' }}" href="{{ route('admin.ontology') }}" @if (request()->is('admin/ontology*')) aria-current="page" @endif>Ontologies</a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/genes*') ? 'active' : '' }}" href="{{ route('admin.genes.index') }}" @if (request()->is('admin/genes*')) aria-current="page" @endif>Genes</a>
                        </li>
                    </ul>
                </li>
                @endcan
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('admin/news*', 'admin/core-facilities*', 'admin/users*', 'admin/office-hours*') || Route::is('admin.user.*') ? 'active' : '' }}" href="#" id="adminManagementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Management
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminManagementDropdown">
                        @can('no-restriction')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ Route::is('admin.news.*') ? 'active' : '' }}" href="{{ route('admin.news.index') }}" @if (Route::is('admin.news.*')) aria-current="page" @endif>News</a>
                        </li>
                        @endcan
                        @can('manage-facilities')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/core-facilities') ? 'active' : '' }}" href="{{ route('admin.core-facilities') }}" @if (request()->is('admin/core-facilities')) aria-current="page" @endif>Core Facilities</a>
                        </li>
                        @endcan
                        @can('manage-users')
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/users*') || Route::is('admin.user.*') ? 'active' : '' }}" href="{{ route('admin.users') }}" @if (request()->is('admin/users*') || Route::is('admin.user.*')) aria-current="page" @endif>Users</a>
                        </li>
                        @endcan
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/office-hours*') ? 'active' : '' }}" href="{{ route('admin.office-hours.index') }}" @if (request()->is('admin/office-hours*')) aria-current="page" @endif>Office Hours</a>
                        </li>
                    </ul>
                </li>
                @can('manage-feedback')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('admin/feedback*', 'admin/contact-requests*', 'admin/api-token-requests*') ? 'active' : '' }}" href="#" id="adminInboxDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Inbox
                        @if ((($unresolvedFeedbackCount ?? 0) + ($pendingApiTokenRequestsCount ?? 0)) > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ ($unresolvedFeedbackCount ?? 0) + ($pendingApiTokenRequestsCount ?? 0) }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminInboxDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/feedback') ? 'active' : '' }}" href="{{ route('admin.feedback') }}" @if (request()->is('admin/feedback')) aria-current="page" @endif>
                                Feedback
                                @if(($unresolvedFeedbackCount ?? 0) > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $unresolvedFeedbackCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/contact-requests') ? 'active' : '' }}" href="{{ route('admin.contact-requests') }}" @if (request()->is('admin/contact-requests')) aria-current="page" @endif>Contact Requests</a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('admin/api-token-requests') ? 'active' : '' }}" href="{{ route('admin.api-token-requests') }}" @if (request()->is('admin/api-token-requests')) aria-current="page" @endif>
                                API Tokens
                                @if(($pendingApiTokenRequestsCount ?? 0) > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $pendingApiTokenRequestsCount }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.analytics') ? 'active' : '' }}" href="{{ route('admin.analytics') }}">Analytics</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                @if (Auth::check())
                    <x-nav.user-menu />
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register.show') }}">Create Account</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>
@else
<nav class="navbar navbar-expand-lg navbar-dark bg-dark " data-bs-theme="dark">
	<div class="container-fluid">
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto">
				@if (Auth::check())
					<x-nav.user-menu />
				@else
					<li class="nav-item">
						<a class="nav-link" href="{{ route('login') }}">Login</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('register.show') }}">Create Account</a>
					</li>
				@endif
			</ul>
		</div>
	</div>
</nav>

	<nav class="navbar navbar-expand-lg navbar-light bg-white pb-2 shadow-sm rossa-main-navbar" aria-label="Main navigation">
		<div class="container">
			<a class="navbar-brand" href="/">
				<img src="{{ asset('images/ROSSA-logo3.png') }}" alt="ROSSA Logo" class="img-fluid">
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarNav" aria-controls="mainNavbarNav" aria-expanded="false" aria-label="Toggle main navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="mainNavbarNav">
			<ul class="navbar-nav ms-auto">
				<li class="nav-item">
					<a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
				</li>

				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle {{ request()->is('search') || request()->is('api-docs') ? 'active' : '' }}" href="#" id="browseDataDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						Browse Data
					</a>
					<ul class="dropdown-menu" aria-labelledby="browseDataDropdown">
						<li>
							<a class="dropdown-item" href="{{ route('search.index') }}">Search Data</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('api-docs') }}">API</a>
						</li>
					</ul>
				</li>
				@if (config('app.enable_data_browser'))
					<li class="nav-item">
						<a class="nav-link {{ request()->is('data') ? 'active' : '' }}" href="{{ route('data.index') }}">Data Center</a>
					</li>
				@endif
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle {{ request()->is('data-submission') || request()->is('study*') ? 'active' : '' }}" href="#" id="submissionPortalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						Submission Portal
					</a>
					<ul class="dropdown-menu" aria-labelledby="submissionPortalDropdown">
						<li>
							<a class="dropdown-item" href="{{ route('data-submission') }}">
								Submit Your Data
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('measurement-parameters') }}">
								Supported Measurement Parameters
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('guides.data-ingestion') }}">
								Help Guide
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('office-hours.index') }}">
								Office Hours
							</a>
						</li>
						@auth
							<li>
								<a class="dropdown-item" href="{{ route('my-studies') }}">My Submissions</a>
							</li>
						@endauth
					</ul>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle {{ request()->is('core-facility*') ? 'active' : '' }}" href="#" id="coreFacilityDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						Core Facility Registry
					</a>
					<ul class="dropdown-menu" aria-labelledby="coreFacilityDropdown">
						<li>
							<a class="dropdown-item" href="{{ route('core-facility.index') }}">
								View{{ Gate::check('manage-facilities') ? '/Approve' : '' }} Facilities
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('core-facility.home') }}">
								Register Your Core Facility
							</a>
						</li>
					</ul>
				</li>
				<li class="nav-item">
					<a class="nav-link {{ request()->is('grant-resources') ? 'active' : '' }}" href="{{ route('grant-resources') }}">Grant Resources</a>
				</li>
				{{-- <li class="nav-item">
					<a class="nav-link {{ request()->is('search-studies') ? 'active' : '' }}" href="{{ route('search-studies') }}">Search Studies</a>
				</li> --}}
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle {{ (request()->is('team') || request()->is('contact') || request()->is('news*')) ? 'active' : '' }}" href="#" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						About
					</a>
					<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aboutDropdown">
						<li>
							<a class="dropdown-item" href="{{ route('news.index') }}">
								News
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('about') }}">
								About ROSSA
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('about.mission-statement') }}">
								Mission Statement
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('about.terms-of-use') }}">
								Terms of Use
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('team') }}">
								Team Members
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="{{ route('contact') }}">
								Contact Us
							</a>
						</li>
					</ul>
				</li>
			</ul>
			</div>
		</div>
	</nav>
@endif
