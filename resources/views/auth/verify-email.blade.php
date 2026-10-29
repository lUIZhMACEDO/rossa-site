@extends('layout')

@section('title', 'Verify Email')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Verify your email</h1>
                    <p class="text-muted">
                        Please verify your email address.
                        Check your inbox for a verification link, then confirm on the page we send you.
                    </p>

                    <div class="d-flex justify-content-center">
                        <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Resend verification email
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection