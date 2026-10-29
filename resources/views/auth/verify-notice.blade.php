@extends('layout')
@section('title', 'Verify Your Email Address')

@section('content')
<x-layouts.split-auth>
    @if ( Config::get('app.enable_registration') )
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="mb-4">Verify Your Email Address</h2>
                <p class="mb-4">Please check your email for a verification link. If you did not receive the email, you can request another one.</p>

                @if ( auth()->user()->hasVerifiedEmail() )
                    <div class="alert alert-success">
                        Your email has already been verified. You can now access all features of ROSSA.
                    </div>

                    <a href="{{ route('my-studies') }}" class="btn btn-primary w-100 mb-3">Go to Dashboard</a>
                @else
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 mb-3">Resend Verification Email</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</x-layouts.split-auth>
@endsection