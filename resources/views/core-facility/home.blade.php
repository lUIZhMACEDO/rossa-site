@extends('layout')

@section('title', 'Register a Core Facility')
@section('description', 'Register your core facility in the ROSSA registry to increase visibility and collaboration.')

@section('content')
<div class="container py-4">
	<div class="news-hero-band rounded-3 px-4 py-4 px-md-5 py-md-4 mb-4">
		<h1 class="text-white mb-2 fs-2">Register Your Core Facility</h1>
		<p class="lead mb-0 news-hero-subtitle">Join the registry and connect your services with researchers who need them.</p>
	</div>

	<div class="mb-4">
		<p>
			As part of our mission to support and advance skeletal phenotyping in rodent models,
			we have created a registry for core facilities that offer these specialized services.
			By registering your core facility, you are joining a growing network dedicated to improving
			access, visibility, and collaboration across the research community. This registry not only
			helps researchers easily discover where skeletal phenotyping can be performed—it also acts
			as free promotion for your facility, helping to connect your services with those who need them.
			Whether you're looking to increase visibility or support the broader scientific mission,
			registering is a simple way to make a big impact.
		</p>

		<div class="d-flex flex-wrap gap-2">
			@if (Auth::check())
				<form action="{{ route('core-facility.create') }}" method="GET" class="d-inline-flex m-0">
					<button type="submit" class="btn btn-primary">Register Core Facility</button>
				</form>
			@else
				<a href="{{ route('core-facility.create') }}" class="btn btn-primary">Login to Register a Core Facility</a>
			@endif
			<a href="{{ route('core-facility.index') }}" class="btn btn-outline-primary">View Core Facilities</a>
		</div>
	</div>

	<div class="card cf-card">
		<div class="card-body">
			<h2 class="cf-card-title" id="my-facilities">Your Core Facilities</h2>
			@if(Auth::check())
				@livewire('core-facility-table', ['myFacilities' => true])
			@else
				<div class="alert alert-info cf-empty-state">
					Log in to view and manage your facilities. <a href="{{ route('core-facility.create') }}">Login</a>
				</div>
			@endif
		</div>
	</div>
</div>
@endsection
