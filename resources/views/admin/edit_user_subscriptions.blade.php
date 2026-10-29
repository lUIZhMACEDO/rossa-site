@extends('layout')

@section('content')
<div class="py-5">
    <div class="container">
        <h1>Edit User Subscriptions</h1>
        <p class="text-muted">Modify subscriptions for user: {{ $user->name }} (ID: {{ $user->id }})</p>

        <livewire:edit-user-subscriptions :user="$user" />
    </div>
</div>
@endsection
