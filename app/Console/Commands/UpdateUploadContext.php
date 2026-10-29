<?php

namespace App\Console\Commands;

use App\Enums\UploadSteps;
use App\Models\AnalysisUpload;
use Illuminate\Console\Command;

class UpdateUploadContext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:update-context';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the context of mst uploads, and refresh other completed uploads.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get all completed uploads
        $uploads = AnalysisUpload::where('current_step', UploadSteps::COMPLETE)->get();

        $this->info("Found {$uploads->count()} completed uploads to process");

        $mechanicalCount = 0;
        $nonMechanicalCount = 0;

        foreach ($uploads as $upload) {
            $upload->load(['analysisType', 'mechanicalTestingDetails']);

            // use withoutlocking bypass to update uploads
            AnalysisUpload::withoutLocking(function () use ($upload, &$mechanicalCount, &$nonMechanicalCount) {
                if ($upload->analysisType && $upload->analysisType->code === 'mechanical_testing' && $upload->mechanicalTestingDetails) {
                    // populate new fields for mst
                    $upload->test_method = $upload->mechanicalTestingDetails->test_method;
                    $upload->bone_tested = $upload->mechanicalTestingDetails->bone_tested;
                    $upload->save();
                    $mechanicalCount++;

                    $this->info(" - Updated mechanical testing upload #{$upload->id}: {$upload->test_method} on {$upload->bone_tested}");
                } else {
                    // force stat refresh
                    $upload->touch();
                    $nonMechanicalCount++;
                    $this->info(" - Refreshed non-mechanical upload #{$upload->id}");
                }
            });
        }

        $this->info("\n=== Summary ===");
        $this->info("Mechanical testing uploads updated: {$mechanicalCount}");
        $this->info("Non-mechanical uploads refreshed: {$nonMechanicalCount}");
        $this->info("Total: {$uploads->count()}");

        return 0;
    }
}
