<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

// https://mondo.monarchinitiative.org/pages/download/
// https://purl.obolibrary.org/obo/mondo.json

class LoadDiseases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rossa:load-diseases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load disease data from Alliance Disease Description files into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '128M');

        $file = 'https://purl.obolibrary.org/obo/mondo.json';

        // Ensure the disease data directory exists
        $diseaseDataDir = database_path('data/disease_lines');
        if (! is_dir($diseaseDataDir)) {
            mkdir($diseaseDataDir, 0755, true);
        }

        // Upsert data
        $this->info('Loading disease data using upsert strategy (will update existing records)...');

        // download file,
        $filename = basename($file);
        $filePath = $diseaseDataDir.'/'.$filename;
        file_put_contents($filePath, fopen($file, 'r'));

        $this->loadJsonData($filePath);
    }

    public function loadJsonData($filePath)
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $this->error('File not found or not readable: '.$filePath);

            return;
        }

        $this->info('Loading data from: '.$filePath);

        // prevent memory buildup by disabling query logging
        DB::connection()->disableQueryLog();

        // use streaming JSON parser to avoid loading entire file into memory
        // decoder returns arrays instead of objects
        $nodes = Items::fromFile($filePath, [
            'pointer' => '/graphs/0/nodes',
            'decoder' => new ExtJsonDecoder(true),
        ]);

        $data_to_add = [];
        $totalInserted = 0;
        $batchSize = 500;

        // create timestamp once
        $timestamp = now();

        foreach ($nodes as $node) {
            if (! isset($node['id']) || ! isset($node['lbl'])) {
                continue;
            }

            $id = $node['id'];
            $id_sections = explode('/', $id);
            $ending = end($id_sections);
            // We only want MONDO diseases (are the other ones relevant? UBERON, GO, IAO, etc.)
            if (! str_starts_with($ending, 'MONDO')) {
                continue;
            }

            $mondo_id_short_form = $ending;

            // MONDO_1012559 => MONDO:1012559
            $mongo_obo_id = str_replace('_', ':', $ending);

            $data_to_add[] = [
                'iri' => $id,
                'obo_id' => $mongo_obo_id,
                'short_form' => $mondo_id_short_form,
                'label' => $node['lbl'],
                'ontology' => 'mondo',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if (count($data_to_add) >= $batchSize) {
                $processed = $this->upsertBatch($data_to_add);
                $totalInserted += $processed;

                // explicitly unset to free mem immediately
                unset($data_to_add);
                $data_to_add = [];

                if ($totalInserted % 2500 == 0) {
                    $this->info("Inserted batch. Total inserted: $totalInserted");
                }

                // force garbage collection to free mem
                gc_collect_cycles();
            }
        }

        // Insert any remaining data
        if (! empty($data_to_add)) {
            $inserted = $this->upsertBatch($data_to_add);
            $totalInserted += $inserted;
        }

        $this->info('Finished processing file. Total inserted: '.$totalInserted.' items to the disease_lines table.');
    }

    protected function upsertBatch(array $data)
    {
        if (empty($data)) {
            return 0;
        }

        // MySQL parameter limit is 65535, calculate safe chunk size
        $columnsPerRow = count($data[0]);
        $maxRowsPerChunk = floor(65535 / $columnsPerRow) - 1;

        $chunks = array_chunk($data, $maxRowsPerChunk);
        $totalProcessed = 0;

        foreach ($chunks as $chunk) {
            try {
                DB::table('disease_lines')->upsert(
                    $chunk,
                    ['obo_id'], // unique identifier column
                    ['label', 'iri', 'ontology', 'short_form', 'updated_at'] // Columns to update if record exists
                );
                $totalProcessed += count($chunk);
            } catch (\Exception $e) {
                $this->error('Failed to insert batch: '.$e->getMessage());
                Log::error('Failed to insert disease_lines batch', [
                    'error' => $e->getMessage(),
                    'chunk_size' => count($chunk),
                ]);
            }
        }

        return $totalProcessed;
    }
}
