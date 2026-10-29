@component('mail::message')
# {{ config('app.name') }} Office Hours - Session Cancelled

Hi {{ $user->name }},

We regret to inform you that the following ROSSA Office Hours session has been cancelled.

**Date:** {{ $session->session_date->format('l, F j, Y') }}

**Time:** {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }} Eastern Time

We apologize for any inconvenience. Please check back for future sessions and register again when a new slot is available.

@component('mail::button', ['url' => route('office-hours.index')])
View Upcoming Sessions
@endcomponent

If you have any questions, contact us at [{{ config('mail.rossa_contact_email') }}](mailto:{{ config('mail.rossa_contact_email') }}).

— The {{ config('app.name') }} Team
@endcomponent
