@extends('layout')

@section('content')
<div class="container py-5">
    <h1>Manage Core Facilities</h1>

    <div class="card mt-4" style="border-left: 4px solid var(--bs-primary);">
        
        <div class="card-body">
            <div class="card-title">
                <h3 class="">Core Facilities</h2>
            </div>
            @livewire('core-facility-table', ['myFacilities' => false])
        </div>
    </div>
</div>
@endsection