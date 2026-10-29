<div>
    @foreach($study->gonadectomyProcedures as $procedure)
        <div class="mb-4 p-3 border rounded">
            <h5 class="mb-3">{{ $procedure->getDisplayName() }}</h5>
            <div>
                <p class="mb-0"><strong>Age at Procedure (weeks):</strong> {{ $procedure->age }}</p>
                @if ( $procedure->description )
                    <p><strong>Details:</strong> {{ $procedure->description }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>