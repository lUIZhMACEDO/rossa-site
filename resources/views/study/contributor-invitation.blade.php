@extends('layout')

@section('title', 'Study Collaboration Invitation')

@section('content')
    <div class="container py-5" style="max-width: 720px;">
        <div class="card shadow-sm" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body p-4">
                <h1 class="h3">Study collaboration invitation</h1>
                <p>{{ $invitation->inviter->display_name ?: $invitation->inviter->email }} invited you to collaborate on this study.</p>

                <dl class="row mb-4">
                    <dt class="col-sm-3">Study</dt>
                    <dd class="col-sm-9">{{ $invitation->study->title ?: 'Untitled Study' }}</dd>
                    <dt class="col-sm-3">Access</dt>
                    <dd class="col-sm-9">Contributors can manage the study information and uploaded data.</dd>
                    <dt class="col-sm-3">Expires</dt>
                    <dd class="col-sm-9">{{ $invitation->expires_at->toFormattedDateString() }}</dd>
                </dl>

                <form action="{{ route('study.invitations.respond', [$invitation, $token]) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <button class="btn btn-primary" name="response" value="accept" type="submit">Accept invitation</button>
                    <button class="btn btn-outline-secondary" name="response" value="decline" type="submit">Decline</button>
                </form>
            </div>
        </div>
    </div>
@endsection
