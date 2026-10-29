<?php

namespace App\Console\Commands;

use App\Mail\CfRefreshEmail;
use App\Models\CoreFacility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CfSendRefreshEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:refresh-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch job to send expiration email to creator of core facility';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting refresh emails...');

        // get list of facilities to send refresh email to
        $facilities = CoreFacility::where('expires_at', '<=', now()->addDays(30)->startOfDay())
            ->where(function ($query) {
                $query->where('last_email_sent_at', '<', now()->subDays(7)->startOfDay())
                    ->orWhereNull('last_email_sent_at');
            })
            ->cursor();

        $count = 0;
        foreach ($facilities as $facility) {
            if ($facility->createdBy) {
                Mail::to($facility->createdBy->email)->queue(new CfRefreshEmail($facility->id));
                $facility->last_email_sent_at = now();
                $facility->save();
                $count++;
            }
        }

        $this->info("Sent {$count} refresh emails.");
    }
}
