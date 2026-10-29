@component('mail::message')
# New Office Hours Registration

A new user has registered for an upcoming ROSSA Office Hours session.

**User:** {{ $user->name }} ({{ $user->email }})

**Date:** {{ $session->session_date->format('l, F j, Y') }}

**Time:** {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }} Eastern Time

**Total Registrations:** {{ $session->registrationCount() }}

@if($user->notes ?? false)
**What they need help with:** {{ $user->notes }}
@endif

@component('mail::button', ['url' => route('admin.office-hours.show', $session)])
View Session
@endcomponent

— {{ config('app.name') }} System
@endcomponent
