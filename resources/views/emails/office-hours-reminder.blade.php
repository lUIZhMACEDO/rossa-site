@component('mail::message')
# {{ config('app.name') }} Office Hours - Tomorrow's Session Summary

Here is a summary of registrations for tomorrow's Office Hours session.

**Date:** {{ $session->session_date->format('l, F j, Y') }}

**Time:** {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }} Eastern Time

**Total Registrations:** {{ $registrations->count() }}

@if($registrations->count() > 0)
**Registrants:**

@foreach($registrations as $registration)
- **{{ $registration->user->name }}** ({{ $registration->user->email }})@if($registration->notes) — {{ $registration->notes }}@endif
@endforeach
@endif

@component('mail::button', ['url' => route('admin.office-hours.index')])
View All Sessions
@endcomponent

— {{ config('app.name') }} System
@endcomponent
