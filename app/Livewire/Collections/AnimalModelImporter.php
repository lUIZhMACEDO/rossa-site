<?php

namespace App\Livewire\Collections;

use App\Enums\PermissionType;
use App\Enums\StudyStatus;
use App\Models\AnimalModel;
use App\Models\Collection;
use App\Models\Gene;
use App\Models\Study;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class AnimalModelImporter extends Component
{
    use WithFileUploads;

    public Collection $collection;

    public $file;

    /**
     * Constants applied to every row. Pre-filled for the IMPC/KOMP knockout
     * screen; editable so a different collection can use different values.
     * animal_line_id is the background strain, shared by every line in the file.
     */
    public array $config = [
        'species' => 'mouse',
        'animal_line_id' => 54,                 // C57BL/6NTac, EFO:0020093
        'genetic_category_id' => 1,                  // Global Knockout/Knockin - Gene Targeting
        'genetic_method_id' => 1,                  // Knockout
        'functional_change' => 'loss_of_function',
        'source' => 'IMPC',
        'title_template' => 'Skeletal Phenotyping of {gene_symbol} Knockout Mice',

        // A wild-type control model is identical for every line, so it is
        // generated here rather than carried as repeated rows in the sheet.
        'create_wildtype' => true,
        'wildtype_name' => 'C57BL/6NTac',
        'wildtype_source' => 'Taconic',
    ];

    /** When false, rows whose sheet symbol differs from the database are held back. */
    public bool $acceptSymbolUpdates = true;

    public ?string $importError = null;

    public array $rows = [];

    public array $summary = [];

    public bool $hasRun = false;

    public bool $committed = false;

    /**
     * Spreadsheet header (normalized) => internal key.
     * Unlisted columns (Stock #, MMRRC, allele_accession_id) are ignored.
     */
    private array $headerMap = [
        'mgi' => 'mgi',
        'gene symbol' => 'gene_symbol',
        // The JAX export labels this column "Strain Name", but its contents are
        // the line designation (e.g. B6N(Cg)-Adora2b tm1.1(KOMP)Vlcg/J), which
        // belongs in animal_models.animal_model_name. The strain itself is the
        // constant animal_line_id below. Alternate headers accepted.
        'strain name' => 'animal_model_name',
        'line name' => 'animal_model_name',
        'animal model name' => 'animal_model_name',
        'gene name' => 'gene_name',
    ];

    public function mount(Collection $collection)
    {
        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);
        $this->collection = $collection;
    }

    public function dryRun()
    {
        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);

        $this->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $this->committed = false;
        $this->rows = $this->resolve($this->parseCsv($this->file->getRealPath()));
        $this->summary = $this->summarize($this->rows);
        $this->hasRun = true;
    }

    public function runImport()
    {
        Gate::authorize(PermissionType::MANAGE_COLLECTIONS->value);

        Log::info('runImport fired', [
            'hasRun' => $this->hasRun,
            'rows' => count($this->rows),
            'accept' => $this->acceptSymbolUpdates,
        ]);

        $this->importError = null;

        if (! $this->hasRun) {
            $this->importError = 'Run the check first.';

            return;
        }

        $importable = array_filter($this->rows, function ($r) {
            if (! $r['ok']) {
                return false;
            }
            if (! $this->acceptSymbolUpdates && ! empty($r['warnings'])) {
                return false;
            }

            return true;
        });

        if (empty($importable)) {
            $this->importError = 'Nothing to import. No rows passed the check.';

            return;
        }

        try {
            DB::transaction(function () use ($importable) {
                foreach ($importable as $row) {
                    $study = Study::create([
                        'collection_id' => $this->collection->id,
                        'user_id' => Auth::id(),
                        'title' => $this->buildTitle($row['resolved_as']),
                        'status' => StudyStatus::IN_PROGRESS,
                        'performs_genetic_modification' => true,
                        'performs_drug_treatment' => false,
                        'performs_gonadectomy' => false,
                    ]);

                    AnimalModel::create([
                        'study_id' => $study->id,
                        'species' => $this->config['species'],
                        'animal_line_id' => $this->config['animal_line_id'],
                        'animal_model_name' => $row['animal_model_name'],
                        'investigator' => $this->config['source'],
                        'genetic_category_id' => $this->config['genetic_category_id'],
                        'genetic_method_id' => $this->config['genetic_method_id'],
                        'gene_id' => $row['gene_id'],
                        'functional_change' => $this->config['functional_change'],
                    ]);

                    // Wild-type control model. No gene, category, or method, since
                    // nothing about it is genetically modified. Sample groups flagged
                    // as controls point at this rather than the knockout line.
                    if ($this->config['create_wildtype']) {
                        AnimalModel::create([
                            'study_id' => $study->id,
                            'species' => $this->config['species'],
                            'animal_line_id' => $this->config['animal_line_id'],
                            'animal_model_name' => $this->config['wildtype_name'],
                            'investigator' => $this->config['wildtype_source'],
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            // Surface the failure in the UI rather than dying silently.
            $this->importError = get_class($e).': '.$e->getMessage();
            report($e);

            return;
        }

        $this->committed = true;
        $this->hasRun = false;
        $this->rows = [];
        $this->file = null;

        session()->flash('success', count($importable).' studies created in this collection.');
    }

    // ---------------------------------------------------------------- parsing

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $headers = null;
        $rows = [];
        $lineNumber = 0;

        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($headers === null) {
                $headers = $this->mapHeaders($line);

                continue;
            }

            // skip fully blank lines
            if (count(array_filter($line, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = ['line' => $lineNumber];

            foreach ($headers as $index => $key) {
                if ($key !== null) {
                    $row[$key] = trim((string) ($line[$index] ?? ''));
                }
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function mapHeaders(array $line): array
    {
        $mapped = [];

        foreach ($line as $index => $raw) {
            // strip UTF-8 BOM from the first header if present
            $clean = strtolower(trim(preg_replace('/^\x{FEFF}/u', '', (string) $raw)));
            $mapped[$index] = $this->headerMap[$clean] ?? null;
        }

        return $mapped;
    }

    // ------------------------------------------------------------- resolution

    private function resolve(array $rows): array
    {
        $seenMgi = [];
        $resolved = [];

        // MGI IDs already imported into this collection, to catch a re-run
        $existing = AnimalModel::whereIn(
            'study_id',
            $this->collection->studies()->select('id')
        )->pluck('gene_id')->filter()->all();

        foreach ($rows as $row) {
            $mgi = $row['mgi'] ?? '';
            $symbol = $row['gene_symbol'] ?? '';
            $modelName = $row['animal_model_name'] ?? '';

            $errors = [];
            $warnings = [];
            $gene = null;

            if ($mgi === '') {
                $errors[] = 'MGI ID missing';
            } elseif (isset($seenMgi[$mgi])) {
                $errors[] = 'Duplicate MGI ID (also on line '.$seenMgi[$mgi].')';
            } else {
                $seenMgi[$mgi] = $row['line'];

                $gene = Gene::where('alliance_id', $mgi)->first();

                if (! $gene) {
                    $errors[] = 'MGI ID not found in genes table';
                } else {
                    if ($symbol !== '' && strcasecmp($gene->symbol, $symbol) !== 0) {
                        // Usually the sheet carries an older MGI symbol and the
                        // database has the current one. The MGI ID is authoritative,
                        // so this is informational rather than blocking.
                        $warnings[] = "Sheet says {$symbol}, database says {$gene->symbol}";
                    }
                    if (in_array($gene->id, $existing, true)) {
                        $errors[] = 'Already imported into this collection';
                    }
                }
            }

            if ($modelName === '') {
                $errors[] = 'Animal model name missing';
            }

            $resolved[] = [
                'line' => $row['line'],
                'mgi' => $mgi,
                'gene_symbol' => $symbol,
                'animal_model_name' => $modelName,
                'gene_id' => $gene?->id,
                'resolved_as' => $gene?->symbol,
                'errors' => $errors,
                'warnings' => $warnings,
                'ok' => empty($errors),
            ];
        }

        return $resolved;
    }

    private function summarize(array $rows): array
    {
        return [
            'total' => count($rows),
            'ok' => count(array_filter($rows, fn ($r) => $r['ok'])),
            'failed' => count(array_filter($rows, fn ($r) => ! $r['ok'])),
            'warned' => count(array_filter($rows, fn ($r) => $r['ok'] && ! empty($r['warnings']))),
            'clean' => count(array_filter($rows, fn ($r) => $r['ok'] && empty($r['warnings']))),
        ];
    }

    private function buildTitle(string $symbol): string
    {
        return str_replace('{gene_symbol}', $symbol, $this->config['title_template']);
    }

    public function render()
    {
        return view('livewire.collections.animal-model-importer');
    }
}
