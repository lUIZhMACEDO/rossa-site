@section('title', 'Drug Administration Plan - Animal Experimentation')
@section('guide-content')
    @include('guides.data-ingestion._04_2animalexp-drug')
@endsection
<x-layouts.submission :$study>
<div class="py-0">
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">
            @if ($planId)
                Edit Drug Administration Plan
            @else
                Create Drug Administration Plan
            @endif
        </h1>
        <p class="mb-0" style="color: #E6F1FB;">
            Create a drug administration plan for your study.
        </p>
    </div>

    <livewire:modals.drug-administration-plan-form
        :studyId="$study->id"
        :planId="$planId"
        :key="'plan-page-'.($planId ?? 'new')"
    />
</div>
</x-layouts.submission>