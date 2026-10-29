@extends('layout')

@section('title', 'Login')

@section('content')
<x-layouts.split-auth>
    <div class="card shadow-sm mb-3 overflow-hidden" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body px-4 pt-4 pb-0">
            <h1 class="h2 mb-0">Login</h1>
        </div>

        <form method="POST" action="{{ route('login.authenticate') }}">
            @csrf
            <input type="hidden" name="redirect" value="{{ $redirect ?? '/' }}">

            <div class="px-4 py-3">
                <div class="mb-3">
                    <label for="email" class="form-label required">Email address</label>
                    <input type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus>
                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label required">Password</label>
                    <input type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required>
                    @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </div>
        </form>

        <div class="text-center px-4 py-3 bg-light border-top">
            <small class="text-muted">
                <a href="{{ route('register.show', ['redirect' => $redirect ?? '/']) }}" class="text-decoration-none">Register a New Account</a>
                <span class="mx-2">&bull;</span>
                <a href="{{ route('forgot-password.show') }}" class="text-decoration-none">Forgot Password</a>
            </small>
        </div>
    </div>
</x-layouts.split-auth>
@endsection