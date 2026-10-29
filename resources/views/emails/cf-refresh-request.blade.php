@component('mail::message')
# Review your Core Facility

Your core facility was last updated {{ $coreFacility->last_refreshed_at->diffForHumans() }}.<br>
Please review the following information

**Core Facility Details:**
- **Name:** {{ $coreFacility->facility_name }}
- **Institution:** {{ $coreFacility->institution }}
- **Web Address:** {{ $coreFacility->web_address }}
- **Contact Name:** {{ $coreFacility->contact_name }}
- **Contact Email:** {{ $coreFacility->contact_email }}
- **Analysis Types:**
@foreach($coreFacility->analysis_types as $analysisType)
  - {{ $analysisType['name'] }}
@endforeach

If the above information is correct, please click the button below to confirm.

@component('mail::button', ['url' => $refreshUrl])
Confirm and Refresh
@endcomponent

Otherwise, visit the [Core Facility homepage]({{ $homeUrl }}) to find your facility and update its details.

Regards,<br>
{{ config('app.name') }}
@endcomponent