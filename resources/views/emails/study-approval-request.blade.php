@component('mail::message')
# Study ready for your review

{{ $requesterName }} has asked you to review and submit a study in {{ config('app.name') }}. You are listed as a corresponding investigator.

**Title:** {{ $study->title ?? 'Untitled Study' }}

**Study ID:** {{ $study->identifier }}

As a corresponding investigator, you review the study and submit it to the curation team.

@if($hasAccount)
Sign in to your ROSSA account to review the study and submit it.
@else
You don't have a ROSSA account yet. First [create an account]({{ route('register.show') }}) using this email address (**{{ $recipientEmail }}**) and verify it. The study will then be available to you.
@endif

@component('mail::button', ['url' => $studyUrl])
Review study
@endcomponent

If you were not expecting this request, you can safely ignore this email.

Regards,<br>
{{ config('app.name') }}
@endcomponent