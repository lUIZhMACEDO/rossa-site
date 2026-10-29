@if ( $animalModel->intercross )
    <div class="border border-2 mb-3 p-3 d-flex">
        <div class="flex-grow-1">
            <p class="mb-1"><strong>{{ $animalModel->intercross->animal_model_name }}</strong></p>
            <p class="mb-1">{{ $animalModel->intercross->investigator ?: '- No Investigator -' }} | {{ $animalModel->intercross->animalLine->label }}</p>
        </div>
        <div class="ms-3">
            <a href="{{ route('study.animal-experimentation.animal-models.intercross', [$study, $animalModel]) }}" class="btn btn-sm btn-primary ms-auto align-self-start">Edit Intercross Animal</a>
        </div>
    </div>
@else
    <p class="fst-italic small">No intercross animal model has been created for this animal model.</p>

    <input type="submit" name="intercross" value="Save & Add Intercross Animal" class="btn btn-secondary">
@endif