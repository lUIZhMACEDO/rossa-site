@extends('layout')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Under Construction</h1>
    <p>This feature is currently under development. Please check back later.</p>

    <div class="text-center mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-primary btn-lg">Go Back</a>
    </div>

</div>
@endsection