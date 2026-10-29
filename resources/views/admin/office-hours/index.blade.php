@extends('layout')

@section('content')
<div class="py-5">
    <div class="container mt-4">
        <h1>Office Hours Admin</h1>
        <p class="text-muted">Manage ROSSA Office Hours sessions and registrations</p>

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

        {{-- Create New Session Form --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Create New Session</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.office-hours.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="session_date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="14:00" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="15:00" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Meeting Link</label>
                            <input type="url" name="teams_link" class="form-control" placeholder="https://..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Assigned To (optional)</label>
                            <input type="text" name="assigned_to" class="form-control" placeholder="Dr. Peter Maye">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes (optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="Internal notes...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Create Session</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sessions Table --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Sessions</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Host</th>
                            <th>Registrations</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td>{{ $session->session_date->format('D, M j, Y') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} –
                                {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
                            </td>
                            <td>{{ $session->assigned_to ?? 'Dr. Peter Maye' }}</td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $session->active_registrations_count }}
                                </span>
                            </td>
                            <td class="fw-normal">
                                @if($session->isActive())
                                    <span class="badge bg-success fw-normal">Active</span>
                                @elseif($session->trashed())
                                    <span class="badge bg-secondary fw-normal">Expired/Deleted</span>
                                @else
                                    <span class="badge bg-danger fw-normal">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.office-hours.show', $session) }}"
                                   class="btn btn-sm btn-outline-primary">View</a>

                                @if($session->isActive())
                                <form method="POST"
                                      action="{{ route('admin.office-hours.cancel', $session) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Cancel this session and notify all registrants?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No sessions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-3">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection