<div>
    <x-session-alerts />

    @if($importError)
        <div class="alert alert-danger">
            <strong>Import failed.</strong>
            <div class="small mt-1" style="word-break: break-word;">{{ $importError }}</div>
        </div>
    @endif

    @if($committed)
        <div class="alert alert-success">
            Import complete. The animal models and their studies now appear under this collection.
        </div>
    @endif

    {{-- Import configuration --}}
    <div class="mb-4">
        <h5>Import Settings</h5>
        <p class="text-muted small">
            These values are applied to every row. Only the gene and strain name come from the file.
        </p>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Species</label>
                <input type="text" class="form-control" wire:model="config.species">
            </div>
            <div class="col-md-3">
                <label class="form-label">Background Strain ID</label>
                <input type="number" class="form-control" wire:model="config.animal_line_id">
            </div>
            <div class="col-md-3">
                <label class="form-label">Genetic Category ID</label>
                <input type="number" class="form-control" wire:model="config.genetic_category_id">
            </div>
            <div class="col-md-3">
                <label class="form-label">Genetic Method ID</label>
                <input type="number" class="form-control" wire:model="config.genetic_method_id">
            </div>
            <div class="col-md-3">
                <label class="form-label">Functional Change</label>
                <input type="text" class="form-control" wire:model="config.functional_change">
            </div>
            <div class="col-md-3">
                <label class="form-label">Source</label>
                <input type="text" class="form-control" wire:model="config.source">
            </div>
            <div class="col-12">
                <hr class="my-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="create-wildtype"
                        wire:model.live="config.create_wildtype">
                    <label class="form-check-label" for="create-wildtype">
                        Also create a wild-type control animal model for each study
                    </label>
                    <div class="form-text">
                        Identical for every line, so it is generated rather than read from the file.
                        Control sample groups will point at this model.
                    </div>
                </div>
            </div>

            @if($config['create_wildtype'])
                <div class="col-md-3">
                    <label class="form-label">Wild-type Model Name</label>
                    <input type="text" class="form-control" wire:model="config.wildtype_name">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Wild-type Source</label>
                    <input type="text" class="form-control" wire:model="config.wildtype_source">
                </div>
            @endif

            <div class="col-md-6">
                <label class="form-label">Study Title Template</label>
                <input type="text" class="form-control" wire:model="config.title_template">
                <div class="form-text">Use <code>{gene_symbol}</code> as the placeholder.</div>
            </div>
        </div>
    </div>

    {{-- File upload --}}
    <div class="mb-4">
        <h5>Spreadsheet</h5>
        <p class="text-muted small">
            CSV export. Three columns are read, everything else is ignored:
            <code>MGI</code> resolves the gene, <code>Gene Symbol</code> is checked
            against it, and the column the JAX export heads <code>Strain Name</code>
            (or <code>Line Name</code>) holds the full line designation and becomes the
            Animal Model Name. The background strain is set once below, not per row.
        </p>
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <input type="file" class="form-control @error('file') is-invalid @enderror"
                    wire:model="file" accept=".csv,text/csv">
                @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div wire:loading wire:target="file" class="form-text">Uploading...</div>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-outline-primary" wire:click="dryRun"
                    wire:loading.attr="disabled" wire:target="dryRun">
                    Run Check
                </button>
                <span wire:loading wire:target="dryRun" class="ms-2 small text-muted">Checking...</span>
            </div>
        </div>
    </div>

    {{-- Dry run results --}}
    @if($hasRun)
        <div class="alert {{ $summary['failed'] === 0 ? 'alert-success' : 'alert-warning' }}">
            <strong>{{ $summary['total'] }}</strong> rows read.
            <strong>{{ $summary['ok'] }}</strong> ready to import.
            <strong>{{ $summary['failed'] }}</strong> skipped.
            @if(($summary['warned'] ?? 0) > 0)
                <strong>{{ $summary['warned'] }}</strong> resolved with an updated gene symbol.
            @endif
            <span class="d-block small mt-1">Nothing has been written to the database yet.</span>
        </div>

        @if($summary['failed'] > 0)
            <h6>Rows that will be skipped</h6>
            <table class="table table-sm mb-4">
                <thead>
                    <tr>
                        <th>Line</th>
                        <th>MGI</th>
                        <th>Gene Symbol</th>
                        <th>Animal Model Name</th>
                        <th>Problem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @if(! $row['ok'])
                            <tr>
                                <td>{{ $row['line'] }}</td>
                                <td>{{ $row['mgi'] ?: '-' }}</td>
                                <td>{{ $row['gene_symbol'] ?: '-' }}</td>
                                <td class="small">{{ $row['animal_model_name'] ?: '-' }}</td>
                                <td class="text-danger small">{{ implode('; ', $row['errors']) }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(($summary['warned'] ?? 0) > 0)
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">Gene symbol updates</h6>
                    <p class="text-muted small">
                        These MGI IDs resolved, but the sheet carries an older symbol than the
                        database. The MGI ID is the authoritative identifier, so the current
                        symbol is used for the study title.
                    </p>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>MGI</th>
                                <th>Sheet symbol</th>
                                <th>Current symbol</th>
                                <th>Study title if accepted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @if($row['ok'] && ! empty($row['warnings']))
                                    <tr>
                                        <td>{{ $row['line'] }}</td>
                                        <td>{{ $row['mgi'] }}</td>
                                        <td class="text-decoration-line-through text-muted">{{ $row['gene_symbol'] }}</td>
                                        <td class="fw-semibold">{{ $row['resolved_as'] }}</td>
                                        <td class="small">
                                            {{ str_replace('{gene_symbol}', $row['resolved_as'], $config['title_template']) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="accept-symbol-updates"
                            wire:model.live="acceptSymbolUpdates">
                        <label class="form-check-label" for="accept-symbol-updates">
                            Accept these {{ $summary['warned'] }} updates and import the rows
                        </label>
                    </div>
                    @unless($acceptSymbolUpdates)
                        <div class="form-text text-warning">
                            These rows will be held back. The other {{ $summary['clean'] }} still import.
                        </div>
                    @endunless
                </div>
            </div>
        @endif

        @if($summary['ok'] > 0)
            <details class="mb-4">
                <summary class="mb-2">Show the {{ $summary['ok'] }} rows that resolved</summary>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Line</th>
                            <th>MGI</th>
                            <th>Resolved Gene</th>
                            <th>Animal Model Name</th>
                            <th>Study Title</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @if($row['ok'])
                                <tr>
                                    <td>{{ $row['line'] }}</td>
                                    <td>{{ $row['mgi'] }}</td>
                                    <td>{{ $row['resolved_as'] }}</td>
                                    <td class="small">{{ $row['animal_model_name'] }}</td>
                                    <td class="small">
                                        {{ str_replace('{gene_symbol}', $row['resolved_as'], $config['title_template']) }}
                                        @if(! empty($row['warnings']))
                                            <span class="d-block text-warning">{{ implode('; ', $row['warnings']) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif

        @php $toImport = $acceptSymbolUpdates ? $summary['ok'] : $summary['clean']; @endphp

        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-primary"
                wire:click="runImport"
                wire:loading.attr="disabled"
                wire:target="runImport"
                @disabled($toImport === 0)>
                Import {{ $toImport }} Animal Models
            </button>
            <span wire:loading wire:target="runImport" class="small text-muted">Importing...</span>
            @if($summary['failed'] > 0)
                <span class="small text-muted">
                    Rows with problems will be skipped. Fix the file and run the check again to include them.
                </span>
            @endif
        </div>
    @endif
</div>
