@component('mail::message')
# Collection Awaiting Review

A collection has been submitted and is ready for administrative review in ROSSA.

**Title:** {{ $collection->title ?? 'Untitled Collection' }}

**Collection ID:** {{ $collection->identifier }}

**Studies submitted:** {{ $studyCount }}

@component('mail::button', ['url' => $adminCollectionStudiesUrl])
Review Collection
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent
