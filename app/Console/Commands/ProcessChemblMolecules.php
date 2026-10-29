<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessChemblMolecules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rossa:process-molecules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load ChEMBL molecules into the database from CSV files in database/data/chembl_molecules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '128M');

        $dir = database_path('data/chembl_molecules');

        if (! is_dir($dir)) {
            $this->error('Directory not found: '.$dir);

            return;
        }

        $files = glob($dir.'/*.csv');

        if (empty($files)) {
            $this->error('No CSV files found in: '.$dir);

            return;
        }

        // Upsert data
        $this->info('Loading molecule data using upsert strategy (will update existing records)...');

        foreach ($files as $path) {
            $this->loadCsv($path);
        }
    }

    protected function loadCsv($filePath)
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $this->error('File not found or not readable: '.$filePath);

            return;
        }

        $this->info('Loading data from: '.$filePath);

        $header = null;
        $data_to_add = [];
        $withoutNameCount = 0;
        $totalInserted = 0;
        $batchSize = 1000; // Process in smaller batches

        // Read CSV line by line
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                if (! $header) {
                    $header = $data;

                    continue;
                }

                if (count($header) !== count($data)) {
                    continue;
                }

                $chembl_id = $data[0];
                $name = $data[1];

                if (! $name) {
                    $withoutNameCount++;

                    continue;
                }

                // Check if molecule already exists (individual query is more memory efficient)
                $exists = DB::table('molecules')->where('chembl_id', $chembl_id)->exists();

                if (! $exists) {
                    // Extract items from single quote array eg: "['a', 'b', 'c']" into $matches
                    preg_match_all("/'([^']*)'/", $data[27], $matches);

                    $synonym_set = array_unique(
                        array_map(
                            'strtolower',
                            // Filter out any synonyms that do not contain letters (some synonyms were just a single digit)
                            // [1] is without the inside quotes: ["'a'", "'b'", "'c'"] -> ["a", "b", "c"]
                            array_filter($matches[1], function ($str) {
                                return preg_match('/^[a-zA-Z]+$/', $str);
                            })
                        )
                    );

                    $synonyms_json = json_encode(array_values($synonym_set));
                    $timestamp = now();

                    $data_to_add[] = [
                        'chembl_id' => $chembl_id,
                        'pref_name' => $name,
                        'molecule_type' => $data[3],
                        'max_phase' => $data[4],
                        'formula' => $data[21],
                        'synonyms' => $synonyms_json,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                // Process batch when we reach the batch size
                if (count($data_to_add) >= $batchSize) {
                    $processed = $this->upsertBatch($data_to_add);
                    $totalInserted += $processed;
                    $data_to_add = []; // Clear the array to free memory

                    $this->info('Processed batch. Total inserted so far: '.$totalInserted);

                    // Force garbage collection to free memory
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }
            }
            fclose($handle);
        }

        // Process any remaining data
        if (! empty($data_to_add)) {
            $processed = $this->upsertBatch($data_to_add);
            $totalInserted += $processed;
        }

        $this->info('Finished processing file. Skipped '.$withoutNameCount.' entries without a name.');
        $this->info('Total inserted: '.$totalInserted.' items to the molecules table.');
    }

    /**
     * Insert a batch of data with proper chunking for MySQL parameter limits
     */
    protected function upsertBatch(array $data)
    {
        if (empty($data)) {
            return 0;
        }

        // MySQL parameter limit is 65535, calculate safe chunk size
        $columnsPerRow = count($data[0]);
        $maxRowsPerChunk = floor(65535 / $columnsPerRow) - 1;

        $chunks = array_chunk($data, $maxRowsPerChunk);
        $totalInserted = 0;

        foreach ($chunks as $chunk) {
            try {
                DB::table('molecules')->upsert(
                    $chunk,
                    ['chembl_id'],
                    ['pref_name', 'molecule_type', 'max_phase', 'formula', 'synonyms', 'updated_at']
                );
                $totalInserted += count($chunk);
            } catch (\Exception $e) {
                $this->error('Failed to insert batch: '.$e->getMessage());
                Log::error('Failed to insert molecules batch', [
                    'error' => $e->getMessage(),
                    'chunk_size' => count($chunk),
                ]);
            }
        }

        return $totalInserted;
    }
}
