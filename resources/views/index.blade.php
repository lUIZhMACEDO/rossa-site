@extends('layout')

@section('content')
<div class="py-5">
    <div class="container">
        <h1>ROSSA</h1>
        <p>Welcome to the <strong>Rodent Open Science Skeletal Archive.</strong></p>

		<div class="mb-4">
			<form action="{{ route('study.store') }}" method="POST" class="d-inline">
				@csrf
				<button type="submit" class="btn btn-primary">Create Study</button>
			</form>
		</div>

        @if($studies->count() > 0)
			<h2>Your Studies</h2>
			<div class="list-group">
				@foreach($studies as $study)
					<div class="list-group-item">
						<h5>Study #{{ $study->id }}</h5>
						<div class="btn-group">
							<a href="{{ route('study.study-information', ['study' => $study->id]) }}" 
							class="btn btn-sm btn-outline-primary">
								Study Information
							</a>
							<a href="{{ route('study.investigators', ['study' => $study->id]) }}" 
							class="btn btn-sm btn-outline-primary">
								Investigators
							</a>
							<a href="{{ route('study.animal-experimentation', ['study' => $study->id]) }}" 
							class="btn btn-sm btn-outline-primary">
								Animal Experimentation
							</a>
							<a href="{{ route('study.sample-groups', ['study' => $study->id]) }}" 
							class="btn btn-sm btn-outline-primary">
								Sample Groups
							</a>
							<a href="{{ route('study.phenotype-analysis', ['study' => $study->id]) }}" 
							class="btn btn-sm btn-outline-primary">
								Phenotype Analysis
							</a>
						</div>
					</div>
				@endforeach
			</div>
		@else
			<div class="alert alert-info">
				No studies found. Click "Create New Study" to get started.
			</div>
		@endif
    </div>
</div>
@endsection