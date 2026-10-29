<div class="container">
    <div class="mb-3">
        <a href="{{ route('data.index') }}" class="small text-decoration-none">&larr; Back to Data Center</a>
    </div>

    <div class="d-flex flex-column mb-2">
        <h1 class="fw-bold">Parameter Data Browser</h1>
        <p class="text-muted">Explore parameter data in ROSSA</p>
    </div>

    <div class="d-flex">
        <div class="input-group mb-3 w-50 gap-3">
            <input
                type="text"
                class="form-control rounded"
                placeholder="Search parameter name, e.g. BV/TV, Tb.N, BMD..."
                aria-label="Search data"
                wire:model.live.debounce.300ms="query"
            >
            <button class="btn btn-outline-primary allow-rounded rounded" type="button" wire:click="applySearch">Search</button>
        </div>
        <div class="ms-auto text-muted">
            {{ $studies->total() }} studies found
        </div>
    </div>

    @include('datacenter.partials.browser_study_filters')

    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-0 pb-2 small text-uppercase text-secondary fw-semibold">Studies</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($studies as $study)
                    <tr>
                        <td class="p-0 border-0 @if (!$loop->last) pb-3 @endif">
                            @include('datacenter.partials.parameter_card', [
                                'study' => $study,
                                'measurements' => $measurementsByStudy->get($study->id, collect()),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>
                            <div class="alert alert-light border mb-0">
                                No studies found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <small class="text-muted">
            Showing {{ $studies->firstItem() ?? 0 }}-{{ $studies->lastItem() ?? 0 }} of {{ $studies->total() }}
        </small>
        {{ $studies->links() }}
    </div>
</div>
