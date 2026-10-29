@extends('layout')

@section('title', 'Core Facility Registration')

@section('content')
<div class="container py-4">
	<div class="news-hero-band rounded-3 px-4 py-4 px-md-5 py-md-4 mb-4">
		<h1 class="text-white mb-2 fs-2">ROSSA Core Facility Registration Form</h1>
		<p class="lead mb-0 news-hero-subtitle">Tell us about your facility and the analyses it offers.</p>
	</div>

	<div class="card cf-card">
		<div class="card-body">
			@livewire('cf-create')
		</div>
	</div>
</div>
@endsection
