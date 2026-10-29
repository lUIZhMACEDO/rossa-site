@extends('layout')

@section('title', 'Core Facility Registry')
@section('description', 'Browse registered core facilities that provide rodent skeletal phenotyping services.')

@section('content')
<div class="container py-4">
	<div class="news-hero-band rounded-3 px-4 py-4 px-md-5 py-md-4 mb-4">
		<h1 class="text-white mb-2 fs-2">ROSSA Core Facility Registry</h1>
		<p class="lead mb-0 news-hero-subtitle">Find core facilities that offer rodent skeletal phenotyping services.</p>
	</div>

	<div class="card cf-card">
		<div class="card-body">
			<h2 class="cf-card-title">Core Facilities</h2>
			@livewire('core-facility-table')
		</div>
	</div>
</div>
@endsection
