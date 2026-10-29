<div @class(['container mt-3',
'd-none' => !session()->has('success') && !session()->has('status') && !$errors->any() && !session()->has('error') ]) id="alert-container">
    @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            <button type="button" class="btn-close position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('status'))
        <div class="alert alert-success">
            {{ session('status') }}
            <button type="button" class="btn-close position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <a type="button" class="btn btn-link" href="{{ \App\Support\SubmissionGuide::url() ?? route('guides.data-ingestion').'#data-submission' }}">
                <i class="bi bi-question-circle me-2"></i>View Data Submission Guide
            </a>
            <button type="button" class="btn-close position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ( session()->has('error') )
        <div class="alert alert-danger">
            {{ session('error') }}
            <button type="button" class="btn-close position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    
</div>