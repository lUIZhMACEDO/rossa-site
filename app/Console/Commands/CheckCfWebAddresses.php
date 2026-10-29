<?php

namespace App\Console\Commands;

use App\Jobs\CfWebsiteHealthCheck;
use App\Models\CoreFacility;
use Illuminate\Console\Command;

class CheckCfWebAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:web-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Health check all Core Facility web addresses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting web address health check...');

        $facilities = CoreFacility::whereNotNull('web_address')->cursor();

        $count = 0;
        foreach ($facilities as $facility) {
            // dispatch job to queue
            CfWebsiteHealthCheck::dispatch($facility->id);
            $count++;
        }

        $this->info("Dispatched {$count} link health check jobs to the queue.");
        $this->info('Ensure your queue worker is running to process these jobs: `php artisan queue:work`');

        return Command::SUCCESS;
    }
}
