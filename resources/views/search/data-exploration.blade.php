@extends('layout')

@section('title', $study->title.' — Data Exploration')
@section('description', 'Explore skeletal phenotyping data for '.$study->identifier.' in the Rodent Open Science Skeletal Archive.')
@section('canonical', route('search.show', $study))

@section('content')
<div class="container py-5">
    @include('search.partials.report-header')

    <!-- Navigation Tabs -->
     @include('search.partials.report-tabs')

    <!-- Data Exploration Content -->
    <div class="card report-section mb-4">
        <div class="card-body">
            @livewire('data-exploration', ['study' => $study, 'publicAccess' => true], key('data-exploration-' . $study->id))
        </div>
    </div>
</div>
@endsection
