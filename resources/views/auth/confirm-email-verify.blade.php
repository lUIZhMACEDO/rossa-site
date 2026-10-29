@extends('layout')

@section('title', 'Confirm Email Verification')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Confirm your email</h1>
                    
                    <p class="text-muted">
                        Click the button below to verify your email address for
                        <strong>{{ auth()->user()->email }}</strong>.
                    </p>

                    <div class="d-flex justify-content-center">
                        <form method="POST" action="{{ $verifyUrl }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Verify email address
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
