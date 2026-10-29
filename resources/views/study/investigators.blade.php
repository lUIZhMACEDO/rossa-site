@section('title', 'Investigators')
@section('guide-content')
    @include('guides.data-ingestion._03_investigators')
@endsection
<x-layouts.submission :$study>
<div class="py-0">
    <livewire:investigator-form :study="$study" />
</div>
</x-layouts.submission>