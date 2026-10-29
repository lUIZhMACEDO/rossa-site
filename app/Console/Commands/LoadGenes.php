<?php

namespace App\Console\Commands;

use App\Models\Gene;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;

class LoadGenes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rossa:load-genes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load gene data from Alliance Gene Description files into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Set memory limit for this command
        ini_set('memory_limit', '128M');

        $files = [
            'https://fms.alliancegenome.org/download/GENE-DESCRIPTION-TSV_HUMAN.tsv.gz',
            'https://fms.alliancegenome.org/download/GENE-DESCRIPTION-TSV_MGI.tsv.gz',
            'https://fms.alliancegenome.org/download/GENE-DESCRIPTION-TSV_RGD.tsv.gz',
        ];

        // Ensure the gene data directory exists
        $geneDataDir = database_path('data/gene');
        if (! is_dir($geneDataDir)) {
            mkdir($geneDataDir, 0755, true);
        }

        // Using upsert strategy - no need to clear existing data
        $this->info('Loading gene data using upsert strategy (will update existing records)...');

        // Download files, extract, process data, and load into DB
        foreach ($files as $fileUrl) {
            $this->info('Processing file: '.$fileUrl);
            $filename = basename($fileUrl);
            $gzPath = $geneDataDir.'/'.$filename;
            $extractedPath = $geneDataDir.'/'.pathinfo($filename, PATHINFO_FILENAME);

            // Download the compressed file
            file_put_contents($gzPath, fopen($fileUrl, 'r'));
            $this->info('Downloaded to: '.$gzPath);

            // Extract the file
            $this->extractGzFile($gzPath, $extractedPath);
            $this->info('Extracted to: '.$extractedPath);

            // Remove the compressed file
            File::delete($gzPath);
            $this->info('Removed compressed file: '.$gzPath);
        }

        // Go through each extracted file and load data into db
        $finder = new Finder;
        $finder->files()->in($geneDataDir)->name(['*.tsv', '*.txt']);

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();
            $this->info('Loading data from: '.$filePath);
            $this->loadTsvData($filePath);
        }
    }

    /**
     * Extract a .gz file to the specified path
     */
    public function extractGzFile(string $gzPath, string $extractedPath): void
    {
        $gzFile = gzopen($gzPath, 'rb');
        $extractedFile = fopen($extractedPath, 'wb');

        if (! $gzFile || ! $extractedFile) {
            throw new \Exception("Failed to open files for extraction: $gzPath -> $extractedPath");
        }

        while (! gzeof($gzFile)) {
            fwrite($extractedFile, gzread($gzFile, 4096));
        }

        gzclose($gzFile);
        fclose($extractedFile);
    }

    public function loadTsvData($filePath)
    {
        // prevent memory buildup by disabling query logging
        DB::connection()->disableQueryLog();

        $data_to_add = [];
        $totalInserted = 0;
        $batchSize = 500;

        // create timestamp once
        $timestamp = now();

        // open file handle instead of loading entire file
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            $this->error('Failed to open file: '.$filePath);

            return;
        }

        // read file line by line
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode("\t", $line);
            if (count($parts) < 3) {
                continue;
            }

            $geneId = trim($parts[0]);
            $geneSymbol = trim($parts[1]);
            $geneDescription = trim($parts[2]);

            $data_to_add[] = [
                'alliance_id' => $geneId,
                'symbol' => $geneSymbol,
                'name' => $geneSymbol,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            // Process batch when we reach the batch size
            if (count($data_to_add) >= $batchSize) {
                $processed = $this->upsertBatch($data_to_add);
                $totalInserted += $processed;

                // free mem immediately
                unset($data_to_add);
                $data_to_add = [];

                if ($totalInserted % 2000 == 0) {
                    $this->info('Processed batch. Total processed so far: '.$totalInserted);
                }

                // Force garbage collection to free memory
                gc_collect_cycles();
            }
        }

        fclose($handle);

        // Process any remaining data
        if (! empty($data_to_add)) {
            $processed = $this->upsertBatch($data_to_add);
            $totalInserted += $processed;
        }

        $this->info('Finished processing file. Total processed: '.$totalInserted.' items to the genes table.');
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
                // Use upsert to handle both inserts and updates
                DB::table('genes')->upsert(
                    $chunk,
                    ['alliance_id'], // Unique identifier column
                    ['symbol', 'name', 'updated_at'] // Columns to update if record exists
                );
                $totalProcessed += count($chunk);
            } catch (\Exception $e) {
                $this->error('Failed to upsert batch: '.$e->getMessage());
                Log::error('Failed to upsert genes batch', [
                    'error' => $e->getMessage(),
                    'chunk_size' => count($chunk),
                ]);
            }
        }

        return $totalProcessed;
    }
}
