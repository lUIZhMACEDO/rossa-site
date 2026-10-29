@extends('layout')

@section('content')
<div class="py-5">
    <div class="container">
        <h1>Edit User Role</h1>
        <p class="text-muted">Modify role for user: {{ $user->name }} (ID: {{ $user->id }})</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <livewire:edit-user-role :user="$user" />
    </div>
</div>
@endsection