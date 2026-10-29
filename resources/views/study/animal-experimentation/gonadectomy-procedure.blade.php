@section('title', 'Gonadectomy Procedure - Animal Experimentation')
@section('guide-content')
    @include('guides.data-ingestion._04_3animalexp-gonadectomy')
@endsection
<x-layouts.submission :$study>
    <div class="py-0" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
        <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
            <h1 class="mb-1" style="color: #fff;">Gonadectomy Procedure</h1>
            <p class="mb-0" style="color: #E6F1FB;">
                Please add all relevant gonadectomy procedures to your study.
            </p>
        </div>

        @livewire('animal-experimentation.gonadectomy-procedure.gp-table', ['study' => $study])

        <div class="d-flex justify-content-end mt-5">
            <a href="{{ $study->nextAnimalExperimentationStepAfter('study.animal-experimentation.gonadectomy-procedure') }}" class="btn btn-primary btn-lg">Save &amp; Continue <i class="bi-arrow-right"></i></a>
        </div>
    </div>
</x-layouts.submission>