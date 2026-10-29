@section('title', 'Subject Areas')
@section('guide-content')
    @include('guides.data-ingestion._02_subject-areas')
@endsection
<x-layouts.submission :$study>
 {{-- Page header --}}
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Subject Areas</h1>
        <p class="mb-0" style="color: #E6F1FB;">
            Filling out the Subject Areas section, while optional, takes little time and will make your study more discoverable in the data center. 
        </p>
    </div>
    <div class="py-0">
    <div class="card mb-4" style="border-left: 4px solid #0D3B52; border-radius: 0.375rem; overflow: hidden;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h1 class="mb-0 h3">Subject Areas</h1>
        </div>

        <div class="card-body">
            <livewire:subject-area-tabs :study="$study" />
        </div>
    </div>
</div>
</x-layouts.submission>