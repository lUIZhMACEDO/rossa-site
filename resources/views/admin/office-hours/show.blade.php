@extends('layout')

@section('content')
<div class="py-5">
    <div class="container mt-4">

        <a href="{{ route('admin.office-hours.index') }}" class="btn btn-outline-secondary mb-4">&larr; Back to All Sessions</a>

        <h1>Office Hours Session</h1>
        <p class="text-muted">{{ $session->session_date->format('l, F j, Y') }}</p>

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

        <div class="row g-4">

            {{-- Session Details --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Session Details</h5>
                    </div>
                    <div class="card-body">
                        <dl class="mb-3">
                            <dt>Date</dt>
                            <dd>{{ $session->session_date->format('l, F j, Y') }}</dd>
                            <dt>Time</dt>
                            <dd>
                                {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} –
                                {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }} ET
                            </dd>
                            <dt>Status</dt>
                            <dd>
                                @if($session->isActive())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </dd>
                            <dt>Meeting Link</dt>
                            <dd><a href="{{ $session->teams_link }}" target="_blank">Join Meeting</a></dd>
                            <dt>Registrations</dt>
                            <dd>{{ $registrations->count() }}</dd>
                        </dl>

                        @if($session->isActive())
                        <form method="POST"
                              action="{{ route('admin.office-hours.cancel', $session) }}"
                              onsubmit="return confirm('Cancel this session and notify all registrants?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Cancel Session</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Update Form --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Update Session</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.office-hours.update', $session) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="form-label">Assigned To</label>
                                <input type="text" name="assigned_to" class="form-control"
                                       value="{{ $session->assigned_to ?? '' }}"
                                       placeholder="Dr. Peter Maye">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Internal Notes</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Notes visible only to admin...">{{ $session->notes ?? '' }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Registrants --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Registrants ({{ $registrations->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>What They Need Help With</th>
                                    <th>Registered At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registrations as $registration)
                                <tr>
                                    <td>{{ $registration->user->name }}</td>
                                    <td>{{ $registration->user->email }}</td>
                                    <td>{{ $registration->notes ?? '—' }}</td>
                                    <td>{{ $registration->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No registrations yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection