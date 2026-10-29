@component('mail::message')
# Study Requires Revision

Your study submission was reviewed and requires revision before approval.

**Study Details:**
- **Title:** {{ $study->title ?? 'Untitled Study' }}
- **Study ID:** {{ $study->identifier }}

@if($study->rejection_reason)
**Reviewer Feedback:**
{{ $study->rejection_reason }}
@endif

@component('mail::button', ['url' => $studyUrl])
Review Study
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent
