<x-mail::message>
# Daily Study Submission Digest

{{ config('app.name') }} received **{{ $studyCount }}** new {{ $studyCount === 1 ? 'study submission' : 'study submissions' }} since {{ $digestLabel }}.

<x-mail::button :url="$adminStudiesUrl">
View Submitted Studies
</x-mail::button>

@foreach($studies as $study)
- **{{ $study->identifier }}** — {{ $study->title ?? 'Untitled Study' }}
@endforeach

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
