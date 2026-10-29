@component('mail::message')
# ROSSA Office Hours - Registration Confirmed

Hi {{ $user->name }},

You are registered for the upcoming {{ config('app.name') }} Office Hours session. We look forward to seeing you!

**Date:** {{ $session->session_date->format('l, F j, Y') }}

**Time:** {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }} Eastern Time

**Host:** {{ $session->assigned_to ?? 'Dr. Peter Maye' }}

@if($session->notes)
**Notes:** {{ $session->notes }}
@endif

Use the button below to join the meeting at the scheduled time.

@component('mail::button', ['url' => $session->teams_link])
Join Meeting
@endcomponent

If you are unable to attend, please cancel your registration so others may use the slot.

If you have any questions, contact us at [{{ config('mail.rossa_contact_email') }}](mailto:{{ config('mail.rossa_contact_email') }}).

— The {{ config('app.name') }} Team
@endcomponent
