<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
	@php
		$defaultDescription = 'The Rodent Open Science Skeletal Archive (ROSSA) is a comprehensive resource designed to advance skeletal biology research through open data sharing, collaborative analysis, and global networking. ROSSA brings together three essential components that address the critical needs of the skeletal research community: data preservation, discovery tools, and research infrastructure connectivity.';
		$pageTitle = trim($__env->yieldContent('title'));
		$documentTitle = $pageTitle !== '' ? $pageTitle.' | ROSSA' : 'ROSSA | Rodent Open Science Skeletal Archive';
		$pageDescription = trim($__env->yieldContent('description')) ?: $defaultDescription;
		$pageIsIndexable = app(\App\Services\SitemapBuilder::class)->isIndexable(request()->route()?->getName());
		if (request()->routeIs('search.index') && request()->filled('q')) {
			$pageIsIndexable = false;
		}
		$canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
		$openGraphType = trim($__env->yieldContent('og_type')) ?: 'website';
		$socialImage = trim($__env->yieldContent('social_image')) ?: asset('images/ROSSA-logo2.png');
	@endphp
	<meta name="robots" content="{{ $pageIsIndexable ? 'index, follow' : 'noindex, nofollow' }}">
	<meta name="description" content="{{ $pageDescription }}">
    <title>{{ $documentTitle }}</title>
	<link rel="canonical" href="{{ $canonicalUrl }}">


	{{-- <link rel="preload" href="{{ asset('fonts/Fira_Code/FiraCode-VariableFont_wght.woff2') }}" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="{{ asset('fonts/Work_Sans/WorkSans-VariableFont_wght.woff2') }}" as="font" type="font/woff2" crossorigin> --}}

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://unpkg.com/htmx.org@2.0.4" integrity="sha384-HGfztofotfshcF7+8n44JQL2oJmowVChPTg48S+jvZoztPfvwD79OC/LTtG6dMp+" crossorigin="anonymous"></script>
	<script src="https://unpkg.com/idiomorph@0.7.3" integrity="sha384-JcorokHTL/m+D6ZHe2+yFVQopVwZ+91GxAPDyEZ6/A/OEPGEx1+MeNSe2OGvoRS9" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/idiomorph@0.7.3/dist/idiomorph-ext.min.js" integrity="sha384-szktAZju9fwY15dZ6D2FKFN4eZoltuXiHStNDJWK9+FARrxJtquql828JzikODob" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	@vite(['resources/css/app.css','resources/js/app.js', 'resources/js/vendor/htmx-master/dist/htmx.min.js'])
	<meta name="htmx-config" content='{"scrollIntoViewOnBoost":false}'>
	@livewireStyles

	{{-- @if ( config('services.clarity.enable') )
		<script type="text/javascript">
			(function(c,l,a,r,i,t,y){
				c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
				t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
				y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
			})(window, document, "clarity", "script", "rjgofda3l8");
		</script>
	@endif --}}


	<!-- OG Meta Tags -->
	<meta property="og:site_name" content="ROSSA">
	<meta property="og:type" content="{{ $openGraphType }}">
	<meta property="og:title" content="{{ $documentTitle }}">
	<meta property="og:description" content="{{ $pageDescription }}">
	<meta property="og:url" content="{{ $canonicalUrl }}">
	<meta property="og:image" content="{{ $socialImage }}">
	<meta property="og:image:alt" content="Rodent Open Science Skeletal Archive">
	@if (trim($__env->yieldContent('published_time')) !== '')
		<meta property="article:published_time" content="{{ trim($__env->yieldContent('published_time')) }}">
	@endif
	@if (trim($__env->yieldContent('modified_time')) !== '')
		<meta property="article:modified_time" content="{{ trim($__env->yieldContent('modified_time')) }}">
	@endif
	<meta name="twitter:card" content="summary">
	<meta name="twitter:title" content="{{ $documentTitle }}">
	<meta name="twitter:description" content="{{ $pageDescription }}">
	<meta name="twitter:image" content="{{ $socialImage }}">


</head>
@if ( config('services.google.tag') )
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.tag') }}"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', '{{ config('services.google.tag') }}');
	</script>
@endif
<body hx-ext="true">
	<x-environment-banner />

    <header>
        @include('components.nav')
        {{-- @include('components.study-nav') --}}
    </header>

    <main>
		@if ( !isset($hideSessionAlerts) )
			<x-session-alerts />
		@endif

		<div>
			@yield('content')
		</div>


		@if ( Auth::check() && !isset($showFeedback) )
			<div class="container mt-4">
				<x-feedback-form class="border border-2 p-3 mb-4" />
			</div>
		@endif
    </main>
	<footer class="bg-dark text-white pt-4 pb-3 shadow-lg">
		<div class="container">
			<div class="row text-center text-md-start align-items-start">
				<div class="col-md-3">
					<h5 class="fw-bold">ROSSA</h5>
					<p class="small text-white-50">Rodent Open Science Skeletal Archive</p>
					<p class="small text-white-50">Funded by the National Institute of Arthritis and
						Musculoskeletal and Skin Diseases (NIAMS): R24AR084725</p>

				</div>
				<div class="col-md-3 mb-2">
					<h6 class="fw-bold text-uppercase mb-2">Submission Portal</h6>
					<ul class="list-unstyled">
						<li class="mb-2"><a href="{{ route('data-submission') }}" class="text-white-50 text-decoration-none">Submit Your Data</a></li>
						<li class="mb-2"><a href="{{ route('my-studies') }}" class="text-white-50 text-decoration-none">My Submissions</a></li>
						<li class="mb-2"><a href="{{ route('core-facility.create') }}" class="text-white-50 text-decoration-none">Register Core Facility</a></li>
					</ul>
				</div>
				<div class="col-md-3 mb-2">
					<h6 class="fw-bold text-uppercase mb-2">Search</h6>
					<ul class="list-unstyled">
						{{-- <li class="mb-2"><a href="{{ route('search-studies') }}" class="text-white-50 text-decoration-none" disabled>Studies</a></li> --}}
						<li class="mb-2"><a href="{{ route('search.index') }}"class="text-white-50 text-decoration-none">Studies</a></li>
						<li class="mb-2"><a href="{{ route('core-facility.home') }}" class="text-white-50 text-decoration-none">Core Facilities</a></li>
					</ul>
				</div>
				<div class="col-md-3 mb-2">
					<h6 class="fw-bold text-uppercase mb-2">About</h6>
					<ul class="list-unstyled">
						<li class="mb-2"><a href="{{ route('team') }}" class="text-white-50 text-decoration-none">Team Members</a></li>
						{{-- <li class="mb-2"><a href="{{ route('contact') }}" class="text-white-50 text-decoration-none">Contact Us</a></li> --}}
						<li class="mb-2"><a href="{{ route('about.terms-of-use') }}" class="text-white-50 text-decoration-none">Terms of Use</a></li>
						<li class="mb-2"><a href="{{ route('about') }}" class="text-white-50 text-decoration-none">About ROSSA</a></li>
						<li class="mb-2"><a href="{{ route('contact') }}" class="text-white-50 text-decoration-none">Contact Us</a></li>
					</ul>
				</div>
			</div>
			<hr class="border-secondary my-3">
			<div class="row">
				<small class="d-flex flex-column flex-md-row gap-2 align-items-center justify-content-center text-center text-white-50">
					<a class="text-white-50" target="_blank" href="https://uconn.edu/">&copy; University of Connecticut</a>
					<a class="text-white-50" target="_blank" href="https://uconn.edu/disclaimers-privacy-copyright/">Disclaimers, Privacy &amp; Copyright</a>
					<a class="text-white-50" target="_blank" href="https://accessibility.uconn.edu/">Accessibility</a>
				</small>
			</div>
		</div>
	</footer>
	@htmx
		@livewireScriptConfig
	@else
		@livewireScripts
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	@endhtmx

	@livewire('wire-elements-modal')
	@stack('scripts')
</body>
</html>
