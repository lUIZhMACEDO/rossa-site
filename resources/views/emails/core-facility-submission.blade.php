@component('mail::message')
# Core Facility Submission Confirmation

Thank you for submitting your core facility for review.

**Core Facility Details:**
- **Name:** {{ $coreFacility->facility_name }}
- **Institution:** {{ $coreFacility->institution }}

Your submission is currently pending approval by our administrators.
We will notify you once your submission has been processed.

You can view your core facility submissions by clicking the button below:

@component('mail::button', ['url' => $callToAction])
View My Core Facilities
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent