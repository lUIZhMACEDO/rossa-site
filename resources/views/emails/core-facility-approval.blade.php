@component('mail::message')
# Core Facility Approved

Your core facility submission has been approved.

**Core Facility Details:**
- **Name:** {{ $coreFacility->facility_name }}
- **Institution:** {{ $coreFacility->institution }}

Your core facility has been accepted and is now listed in our directory.

@component('mail::button', ['url' => $callToAction])
View My Core Facilities
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent