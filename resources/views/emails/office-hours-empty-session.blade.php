@component('mail::message')
# {{ config('app.name') }} Office Hours - No Registrations for Tomorrow

Just a heads up — tomorrow's Office Hours session currently has no registrations.

**Date:** {{ $session->session_date->format('l, F j, Y') }}

**Time:** {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }} Eastern Time

You may want to cancel this session if you do not expect any last-minute signups.

@component('mail::button', ['url' => route('admin.office-hours.index')])
View Session
@endcomponent

— {{ config('app.name') }} System
@endcomponent
