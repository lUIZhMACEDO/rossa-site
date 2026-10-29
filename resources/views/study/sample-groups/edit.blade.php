@section('title', 'Sample Groups')
@section('guide-content')
    @include('guides.data-ingestion._05_1upload-groups')
@endsection
<x-layouts.submission :$study>

<div class="pt-0 pb-5">
    <div class="p-3 mb-3" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Sample Group Builder</h1>
        <p class="mb-0" style="color: #E6F1FB;">
            Edit your sample group in the form below and save your corrected sample group.
        </p>
    </div>

    <div class="alert mb-4 d-flex align-items-center" role="alert" style="background-color: rgba(26, 125, 181, 0.08); border-left: 4px solid #1A7DB5; color: #14273f;">
        <i class="bi bi-info-circle-fill me-2" style="color: #1A7DB5; font-size: 1.5rem;"></i>
        <span><strong>Note:</strong> The tree diagram below shows the sample groups you generated and you can use edit or delete to make corrections.</span>
    </div>

    <div>
        <div class="row">
            <div class="col-12">
                @include('study.sample-groups.partials.tree', ['study' => $study])
            </div>
        </div>
    </div>

    <div class="card mt-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h2 class="mb-0 h5">Edit Sample Group</h2>
        </div>
        <div class="card-body" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
            <form method="POST" action="{{ route('study.sample-groups.update', $study) }}">
                @csrf
                <input type="hidden" name="sample_group_id" value="{{ $sampleGroup->id }}">
                @include('study.sample-groups.forms.fields', ['study' => $study, 'selectedAnimalModel' => $selectedAnimalModel ?? null, 'selectedAnimalModel2' => $selectedAnimalModel2 ?? null, 'sampleGroup' => $sampleGroup])

                <button type="submit" class="btn btn-primary">Save Sample Group</button>
            </form>
        </div>
    </div>
</div>
</x-layouts.submission>