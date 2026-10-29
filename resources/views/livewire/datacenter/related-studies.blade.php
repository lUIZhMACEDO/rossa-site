<div class="card mt-4" style="border-left: 4px solid var(--bs-primary);">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between">
            <div class="card-title fw-semibold">Related Studies</div>
            <div class="d-flex gap-2">
                <input
                    type="text"
                    class="form-control rounded"
                    placeholder="Filter studies..."
                    aria-label="Search data"
                    aria-describedby="button-search"
                    wire:model.live.debounce.300ms="filter"
                >
            </div>
        </div>
        @if ($studies->isEmpty())
            <div class="p-3 text-muted">No studies are linked to this gene yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ROSSA ID</th>
                            <th>Title</th>
                            <th class="text-nowrap">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($studies as $study)
                            <tr>
                                <td>
                                    <a href="{{ route('search.show', $study) }}">{{ $study->identifier }}</a>
                                </td>
                                <td>{{ $study->title }}</td>
                                <td class="text-nowrap">{{ optional($study->created_at)->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
        