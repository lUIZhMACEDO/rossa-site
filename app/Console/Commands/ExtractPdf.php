<?php

namespace App\Console\Commands;

use App\Enums\AiExtractionStatus;
use App\Jobs\ExtractStudyFromPdf;
use App\Models\AiExtraction;
use App\Models\User;
use App\Services\Ai\PaperExtractionService;
use App\Services\Ai\StudyExtractionSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo / dev tool for the AI parser.
 *
 *   php artisan rossa:extract-pdf path/to/paper.pdf
 *       -> prints the JSON draft (no database writes)
 *
 *   php artisan rossa:extract-pdf path/to/paper.pdf --save --user=1
 *       -> stores the PDF, creates an ai_extractions row and runs the job
 */
class ExtractPdf extends Command
{
    protected $signature = 'rossa:extract-pdf
        {path : Path to a paper PDF}
        {--save : Store the PDF and save the draft to the ai_extractions table}
        {--user= : User ID that owns the draft (required with --save)}
        {--out= : Also write the JSON draft to this file}';

    protected $description = 'Run the AI parser on a paper PDF and output ROSSA form fields as JSON';

    public function handle(PaperExtractionService $service): int
    {
        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $bytes = file_get_contents($path);

        if ($this->option('save')) {
            return $this->saveAndQueue($path, $bytes);
        }

        $this->info('Sending PDF to Claude...');
        $result = $service->extract($bytes);

        $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->line($json);

        if ($out = $this->option('out')) {
            file_put_contents($out, $json);
            $this->info("Saved to {$out}");
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. %d investigators found. Tokens in/out: %s/%s',
            count($result['extracted']['investigators'] ?? []),
            $result['usage']['input_tokens'] ?? '?',
            $result['usage']['output_tokens'] ?? '?',
        ));

        return self::SUCCESS;
    }

    private function saveAndQueue(string $path, string $bytes): int
    {
        $user = User::find($this->option('user'));
        if (! $user) {
            $this->error('--save needs a valid --user=<id>.');

            return self::FAILURE;
        }

        $storedPath = 'ai-extractions/'.Str::uuid().'.pdf';
        Storage::disk('local')->put($storedPath, $bytes);

        $extraction = AiExtraction::create([
            'user_id' => $user->id,
            'disk' => 'local',
            'pdf_path' => $storedPath,
            'original_filename' => basename($path),
            'status' => AiExtractionStatus::Queued,
            'schema_version' => StudyExtractionSchema::VERSION,
        ]);

        // Run inline so the demo shows the result immediately.
        ExtractStudyFromPdf::dispatchSync($extraction);
        $extraction->refresh();

        $this->info("ai_extractions #{$extraction->id}: {$extraction->status->value}");
        if ($extraction->error) {
            $this->error($extraction->error);

            return self::FAILURE;
        }

        $this->line(json_encode($extraction->extracted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
