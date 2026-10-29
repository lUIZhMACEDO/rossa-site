@section('title', 'Other Interventions - Animal Experimentation')
@section('guide-content')
    {{-- TODO: same as other-intervention.blade.php --}}
@endsection
<x-layouts.submission :$study>
<div class="py-0">
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">{{ $planId ? 'Edit' : 'New' }} Intervention Plan</h1>
    </div>

    <livewire:other-intervention-plan-form :study="$study" :plan-id="$planId" />
</div>
</x-layouts.submission>
