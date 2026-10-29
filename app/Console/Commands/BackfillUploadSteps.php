<?php

namespace App\Console\Commands;

use App\Enums\UploadSteps;
use App\Models\AnalysisUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillUploadSteps extends Command
{
    protected $signature = 'uploads:backfill-steps {--dry-run : Show what would be updated without saving}';

    protected $description = 'Backfill current_step for existing analysis uploads based on their data state';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN - no changes will be saved');
        }

        $uploads = AnalysisUpload::all();
        $this->info("Processing {$uploads->count()} uploads...");

        $counts = [
            UploadSteps::PREPARE_UPLOAD->value => 0,
            UploadSteps::MAP_MEASUREMENTS->value => 0,
            UploadSteps::GROUP_SAMPLES->value => 0,
            UploadSteps::REVIEW->value => 0,
            UploadSteps::SCAN_DATASETS->value => 0,
            UploadSteps::COMPLETE->value => 0,
        ];

        foreach ($uploads as $upload) {
            $step = $this->determineStep($upload);
            $counts[$step->value]++;

            $this->line("Upload #{$upload->id}: {$step->label()}");

            if (! $dryRun) {
                $upload->current_step = $step;
                $upload->save();
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Step', 'Count'],
            [
                [UploadSteps::PREPARE_UPLOAD->label(), $counts[0]],
                [UploadSteps::MAP_MEASUREMENTS->label(), $counts[1]],
                [UploadSteps::GROUP_SAMPLES->label(), $counts[2]],
                [UploadSteps::REVIEW->label(), $counts[3]],
                [UploadSteps::SCAN_DATASETS->label(), $counts[4]],
                [UploadSteps::COMPLETE->label(), $counts[5]],
            ]
        );

        if ($dryRun) {
            $this->warn('No changes saved (dry run)');
        } else {
            $this->info('All uploads updated!');
        }

        return Command::SUCCESS;
    }

    // determine step based on old metrics for determining upload step
    private function determineStep(AnalysisUpload $upload): UploadSteps
    {
        // no file uploaded
        if (! $upload->path || ! Storage::exists($upload->path)) {
            return UploadSteps::PREPARE_UPLOAD;
        }

        // has file but no column mappings
        $hasColumnMappings = $upload->column_measurement_mappings
            && count($upload->column_measurement_mappings) > 0;
        if (! $hasColumnMappings) {
            return UploadSteps::MAP_MEASUREMENTS;
        }

        // has mappings but no samples (cached or saved)
        $hasSamples = $upload->group_cache || $upload->samples()->count() > 0;
        if (! $hasSamples) {
            return UploadSteps::GROUP_SAMPLES;
        }

        // has everything
        return UploadSteps::REVIEW;
    }
}
