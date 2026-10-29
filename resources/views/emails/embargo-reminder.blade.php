@component('mail::message')
# Embargo Period Reached

Your embargo period has been reached for study **{{ $study->identifier }}**.

**Study Title:** {{ $study->title }}

Please update your study to verify publication status or extend the embargo period if needed.

@component('mail::button', ['url' => $studyUrl])
Update Study
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent
