@section('title', 'Edit Animal Model')
@section('guide-content')
    @include('guides.data-ingestion._04_1animalexp-models')
@endsection
<x-layouts.submission :$study>

<div class="pt-0 pb-5" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Edit Animal Model</h1>
        <p class="mb-1" style="color: #E6F1FB; font-size: 1.1rem;">
            <span class="fw-semibold">Genetic Modification:</span>
            <span class="fw-bold" style="color: #E8C46A;">{{ $animalModel->geneticCategory?->name ?: 'No Genetic Modification' }}</span>
        </p>
        <p class="mb-0" style="color: #E6F1FB;">Make changes to your animal model using the form below and select <strong>Save &amp; Continue</strong></p>
    </div>

    <form action="{{ route('study.animal-experimentation.animal-models.update', ['study' => $study->id, 'animalModel' => $animalModel->id]) }}" method="POST" data-disable-onsubmit>
        @csrf
        @include('study.animal-experimentation.animal-models.forms.fields', [
            'animalModel' => $animalModel,
            'selectedGeneticCategory' => $animalModel->geneticCategory,
            'study' => $study,
        ])

        <button type="submit" class="btn btn-primary">Save &amp; Continue <i class="bi-arrow-right"></i></button>
    </form>
</div>
</x-layouts.submission>