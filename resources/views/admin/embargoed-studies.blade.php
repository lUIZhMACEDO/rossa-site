@extends('layout')

@section('content')
<div class="py-5">
    <div class="container mt-4">
        <h1>Embargo Admin</h1>
        <p class="text-muted">
            Keep track of studies with embargo reminders. 
        </p>
       
        <!-- Bootstrap Tabs -->
        <ul class="nav nav-tabs" id="ontologyAdminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="past-embargo-reminders-tab" data-bs-toggle="tab" data-bs-target="#past-embargo-reminders" 
                        type="button" role="tab" aria-controls="past-embargo-reminders" aria-selected="true">
                    Past Embargo Reminders
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="upcoming-embargo-reminders-tab" data-bs-toggle="tab" data-bs-target="#upcoming-embargo-reminders" 
                        type="button" role="tab" aria-controls="upcoming-embargo-reminders" aria-selected="false">
                    Upcoming Embargo Reminders
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Past Embargo Reminders Tab -->
            <div class="tab-pane fade show active" id="past-embargo-reminders" role="tabpanel" aria-labelledby="past-embargo-reminders-tab">
                <div class="mt-4">
                    <h4>Sent Reminders</h4>
                    @forelse ($past as $study)
                        <div class="card mb-3" style="border-left: 4px solid var(--bs-primary);">
                            <div class="card-body">
                                <h5 class="card-title">{{ $study->title }}</h5>
                                <p class="card-text">
                                    <strong>Identifier:</strong> {{ $study->identifier }}<br>
                                    <strong>Embargo Reminder Date:</strong> {{ $study->embargo_reminder_date->format('F j, Y') }}<br>
                                    <strong>Reminder Sent At:</strong> {{ $study->embargo_reminder_sent_at->format('F j, Y, g:i A') }}<br>
                                    <strong>User:</strong> {{ $study->user->email }}
                                </p>
                                @foreach ($study->investigators as $investigator)
                                    <p class="card-text">
                                        <strong>Investigator:</strong> {{ $investigator->first_name }} {{ $investigator->last_name }} ({{ $investigator->email }})
                                    </p>
                                @endforeach
                                <p class="small text-muted mb-2">
                                    Resending will send a fresh reminder to the study owner only. Last sent {{ $study->embargo_reminder_sent_at->diffForHumans() }}.
                                </p>
                                <form method="POST" action="{{ route('admin.embargo.resend', $study->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Are you sure you want to resend the embargo reminder? The study owner will be notified again.')">Resend Reminder</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p>No past embargo reminders.</p>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Embargo Reminders Tab -->
            <div class="tab-pane fade" id="upcoming-embargo-reminders" role="tabpanel" aria-labelledby="upcoming-embargo-reminders-tab">
                <div class="mt-4">
                    <h4>Upcoming Reminders</h4>
                    @forelse ($future as $study)
                        <div class="card mb-3" style="border-left: 4px solid var(--bs-primary);">
                            <div class="card-body">
                                <h5 class="card-title">{{ $study->title }}</h5>
                                <p class="card-text">
                                    <strong>Identifier:</strong> {{ $study->identifier }}<br>
                                    <strong>Embargo Reminder Date:</strong> {{ $study->embargo_reminder_date->format('F j, Y') }}<br>
                                    <strong>User:</strong> {{ $study->user->email }}
                                </p>
                                @foreach ($study->investigators as $investigator)
                                    <p class="card-text">
                                        <strong>Investigator:</strong> {{ $investigator->first_name }} {{ $investigator->last_name }} ({{ $investigator->email }})
                                    </p>
                                @endforeach
                                <div class="alert alert-info mt-3" role="alert">
                                    This study will receive an embargo reminder in {{ $study->embargo_reminder_date->diffForHumans() }}.
                                </div>
                            </div>
                        </div>
                    @empty
                        <p>No upcoming embargo reminders.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection