@section('title', 'Genetic Modification - Animal Experimentation')
@section('guide-content')
    @include('guides.data-ingestion._04_2animalexp-drug')
@endsection
<x-layouts.submission :$study>
<div class="py-0">
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Drug Administration Plans</h1>
        <p class="mb-0" style="color: #E6F1FB;">
            Create a drug administration plan(s) for your study. Each plan consists of one or more administration events.
        </p>
    </div>

    <livewire:drug-administration-plans-manager :study="$study" />

    <div class="d-flex justify-content-end mt-5">
        <a href="{{ $study->nextAnimalExperimentationStepAfter('study.animal-experimentation.drug-treatment') }}" class="btn btn-primary btn-lg">Save &amp; Continue <i class="bi-arrow-right"></i></a>
    </div>

</div>
</x-layouts.submission>