<div>
    <p class="text-muted small">
        Creates sample groups for every study in this collection that does not already
        have them, from the scheme below. Studies that already have groups are skipped.
        Review or adjust the values before running.
    </p>

    {{-- Editable scheme --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <label class="form-label">Age (weeks)</label>
            <input type="number" class="form-control" wire:model.live="age">
        </div>
        <div class="col-md-4">
            <label class="form-label">Sexes</label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sex-m" value="M" wire:model.live="sexes">
                    <label class="form-check-label" for="sex-m">Male</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sex-f" value="F" wire:model.live="sexes">
                    <label class="form-check-label" for="sex-f">Female</label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Name Template</label>
            <input type="text" class="form-control" wire:model.live="nameTemplate">
            <div class="form-text">Placeholders: <code>{gene}</code>, <code>{group}</code>, <code>{sex}</code></div>
        </div>
    </div>

    <table class="table table-sm mb-3">
        <thead>
            <tr>
                <th>Group Label</th>
                <th>Animal Model</th>
                <th>Genotype</th>
                <th>Control</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $i => $group)
                <tr>
                    <td><input type="text" class="form-control form-control-sm" wire:model.live="groups.{{ $i }}.label"></td>
                    <td>
                        <select class="form-select form-select-sm" wire:model.live="groups.{{ $i }}.model">
                            <option value="experimental">Experimental (genetically modified)</option>
                            <option value="control">Control (wild type)</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm" wire:model.live="groups.{{ $i }}.genotype"></td>
                    <td>
                        <input type="checkbox" class="form-check-input" wire:model.live="groups.{{ $i }}.control">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Live preview --}}
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title">Preview: groups created per study</h6>
            <p class="text-muted small">Using <em>Adora2b</em> as an example gene. {{ count($this->preview) }} groups per study.</p>
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Sample Group</th>
                        <th>Model</th>
                        <th>Sex</th>
                        <th>Genotype</th>
                        <th>Age</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->preview as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="small">{{ $row['model'] }}</td>
                            <td>{{ $row['sex'] }}</td>
                            <td>{{ $row['genotype'] }}</td>
                            <td>{{ $row['age'] }}</td>
                            <td>
                                @if($row['control'])
                                    <span class="badge text-bg-info fw-normal">Control</span>
                                @else
                                    <span class="badge text-bg-dark fw-normal">Experimental</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
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
            Created groups for <strong>{{ $result['studies_created'] }}</strong>,
            skipped <strong>{{ $result['studies_skipped'] }}</strong> that already had them.
            <strong>{{ $result['groups_created'] }}</strong> sample groups created.

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
        Generate Sample Groups
    </button>
    <span wire:loading wire:target="runGeneration" class="ms-2 small text-muted">Generating...</span>
</div>
