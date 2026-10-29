@extends('layout')

@section('content')
<div class="py-5">
    <div class="container">
        <h1>Users</h1>
        <p class="text-muted">ROSSA Users and Submitters</p>

        <div class="card mt-4 shadow-sm" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body">
                @livewire('admin.users-table')
            </div>
        </div>
    </div>
</div>
@endsection
