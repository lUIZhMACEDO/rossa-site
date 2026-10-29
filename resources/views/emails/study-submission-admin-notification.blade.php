@component('mail::message')
# Study Awaiting Review

A study has been submitted and is ready for administrative review in ROSSA.

**Title:** {{ $study->title ?? 'Untitled Study' }}

**Study ID:** {{ $study->identifier }}

**Submitted By:** {{ ($study->submittedBy ?? $study->user)->display_name ?: ($study->submittedBy ?? $study->user)->email }}

@component('mail::button', ['url' => $adminStudiesUrl])
Review Study
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent
