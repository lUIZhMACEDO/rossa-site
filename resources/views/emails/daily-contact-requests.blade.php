<x-mail::message>
# ROSSA Contact Requests

You have received **{{ $requests->count() }}** new contact {{ $requests->count() === 1 ? 'request' : 'requests' }} since the last update.

---

@foreach($requests as $request)
## Request #{{ $request->id }}

**Name:** {{ $request->name }}  
**Email:** [{{ $request->email }}](mailto:{{ $request->email }})  
**Submitted:** {{ $request->created_at->format('F j, Y g:i A') }}

<x-mail::panel>
**Message:**

{{ $request->message }}
</x-mail::panel>

---

@endforeach

<small>This is an automated email from the ROSSA system. Contact requests are pooled and sent daily.</small>

</x-mail::message>
