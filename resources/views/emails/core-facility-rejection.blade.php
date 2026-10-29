@component('mail::message')
# Core Facility Requires Revision

Your core facility submission has been reviewed and requires revision before approval.

**Core Facility Details:**
- **Name:** {{ $coreFacility->facility_name }}
- **Institution:** {{ $coreFacility->institution }}

@if($coreFacility->rejection_reason)
**Reviewer Feedback:**

> _{{ $coreFacility->rejection_reason }}_
@endif

@component('mail::button', ['url' => $callToAction])
Review Core Facility
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent