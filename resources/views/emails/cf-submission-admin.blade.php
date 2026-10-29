@component('mail::message')
# New Core Facility Submission

A new core facility submission is awaiting review.

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

You can view review the submission by clicking the button below:

@component('mail::button', ['url' => $callToAction])
Review Submission
@endcomponent
@endcomponent