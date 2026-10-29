<div>
    <p class="text-muted small">
        Creates comparison groups for every study that does not already have them,
        one per pairing value, each pairing the experimental group against its
        matching control. Studies that already have comparisons are skipped.
        Requires sample groups to exist first.
    </p>

    <div class="alert alert-light border small mb-3">
        Groups are paired by <strong>sex</strong>: each experimental group is compared
        against the control group of the same sex.<br>
        Order is fixed as <strong>control first, experimental second</strong>, matching
        the manual studies. Statistics compute direction relative to this order, so it
        is not made editable.
    </div>

    {{-- Live preview --}}
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title">Preview: comparisons created per study</h6>
            <p class="text-muted small">Using <em>Adora2b</em> as an example gene. {{ count($this->preview) }} comparisons per study.</p>
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Pairing</th>
                        <th>Control (first)</th>
                        <th>Experimental (second)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->preview as $row)
                        <tr>
                            <td>{{ $row['pair'] }}</td>
                            <td><span class="badge text-bg-info fw-normal">{{ $row['control'] }}</span></td>
                            <td><span class="badge text-bg-dark fw-normal">{{ $row['experimental'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No pairings for this setting.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($error)
        <div class="alert alert-danger">
            <strong>Generation failed.</strong>
            <div class="small mt-1" style="word-break: break-word;">{{ $error }}</div>
        </div>
    @endif

    @if($result)
        <div class="alert {{ empty($result['problems']) ? 'alert-success' : 'alert-warning' }}">
            Processed <strong>{{ $result['studies_total'] }}</strong> studies.
            Created comparisons for <strong>{{ $result['studies_created'] }}</strong>,
            skipped <strong>{{ $result['studies_skipped'] }}</strong> that already had them.
            <strong>{{ $result['comparisons_created'] }}</strong> comparisons created.

            @if(!empty($result['problems']))
                <div class="mt-2">
                    <strong>Problems:</strong>
                    <ul class="mb-0 small">
                        @foreach($result['problems'] as $problem)
                            <li>{{ $problem }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <button type="button" class="btn btn-primary"
        wire:click="runGeneration"
        wire:loading.attr="disabled"
        wire:target="runGeneration">
        Generate Comparison Groups
    </button>
    <span wire:loading wire:target="runGeneration" class="ms-2 small text-muted">Generating...</span>
</div>
