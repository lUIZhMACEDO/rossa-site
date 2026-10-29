@extends('layout')

@section('content')
<div class="py-5">
    <div class="container mt-4">

        <h1>ROSSA Office Hours</h1>
        <p class="text-muted">Join a live drop-in session to get help navigating the ROSSA data submission process.</p>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Info Box --}}
        <div class="alert alert-info mb-4">
            <strong>How it works:</strong> Office hours are held every Thursday from 2:00 – 3:00 PM Eastern Time.
            Register for an upcoming session below and you will receive a meeting link by email.
            Sessions are hosted via Microsoft Teams — no Microsoft account required to join.
        </div>

        {{-- Sessions --}}
        @forelse($sessions as $session)
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="mb-1">{{ $session->session_date->format('l, F j, Y') }}</h5>
                        <p class="text-muted mb-0">
                            {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} –
                            {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }} Eastern Time
                        </p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Host</small><br>
                        {{ $session->assigned_to ?? 'Dr. Peter Maye' }}
                    </div>
                    <div class="col-md-5 text-md-end mt-3 mt-md-0">

                        @if(in_array($session->id, $userRegistrations))
                            {{-- Already registered --}}
                            <span class="badge bg-success me-2">Registered</span>
                            <form method="POST"
                                  action="{{ route('office-hours.cancel', $session) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Cancel your registration for this session?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel Registration</button>
                            </form>
                        @else
                            {{-- Registration form --}}
                            <button class="btn btn-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#register-{{ $session->id }}">
                                Register
                            </button>
                        @endif

                    </div>
                </div>

                {{-- Collapsible registration form --}}
                @if(!in_array($session->id, $userRegistrations))
                <div class="collapse mt-3" id="register-{{ $session->id }}">
                    <form method="POST" action="{{ route('office-hours.register', $session) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">What do you need help with? <span class="text-muted">(optional)</span></label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="e.g. I am trying to submit microCT data and need help with the metadata form..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Confirm Registration</button>
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                data-bs-toggle="collapse"
                                data-bs-target="#register-{{ $session->id }}">Cancel</button>
                    </form>
                </div>
                @endif

            </div>
        </div>
        @empty
        <div class="alert alert-warning">
            No upcoming office hours sessions are currently scheduled. Please check back soon.
        </div>
        @endforelse

    </div>
</div>
@endsection