@section('title', 'Other Interventions - Animal Experimentation')
@section('guide-content')
    {{-- TODO: add a guide partial if one is wanted, matching the drug/gonadectomy pattern,
         e.g. @include('guides.data-ingestion._04_4animalexp-other') --}}
@endsection
<x-layouts.submission :$study>
<div class="py-0">
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Other Intervention Plans</h1>
        <p class="mb-0" style="color: #E6F1FB;">
            Create intervention plans for your study, typically at least one test plan and one matched control plan. Each plan consists of one or more intervention events.
        </p>
    </div>

    <livewire:other-intervention-plans-manager :study="$study" />

    <div class="d-flex justify-content-end mt-5">
        <a href="{{ $study->nextAnimalExperimentationStepAfter('study.animal-experimentation.other-intervention') }}" class="btn btn-primary btn-lg">
            Save &amp; Continue <i class="bi-arrow-right"></i>
        </a>
    </div>

</div>
</x-layouts.submission>
