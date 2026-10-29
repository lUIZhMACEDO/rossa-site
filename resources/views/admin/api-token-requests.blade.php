@extends('layout')

@section('title', 'API Token Requests')

@section('content')
<div class="container py-5">
    <h1>API Token Requests</h1>
    <p class="text-muted">Review and manage API token access requests</p>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mt-4 shadow-sm" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body">
            @livewire('admin.api-token-requests-table')
        </div>
    </div>
</div>
@endsection
