<?php

namespace App\Console\Commands;

use App\Jobs\SendEmbargoReminderEmail;
use App\Models\Study;
use Illuminate\Console\Command;

class SendEmbargoReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'embargo:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue weekly embargo reminder emails for studies whose embargo period has ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting embargo reminder emails...');

        $sevenDaysAgo = now()->startOfDay()->subDays(SendEmbargoReminderEmail::REMINDER_INTERVAL_DAYS);

        $studies = Study::query()
            ->whereDate('embargo_reminder_date', '<=', now()->toDateString(), 'and')
            ->where(function ($query) use ($sevenDaysAgo) {
                $query->whereNull('embargo_reminder_sent_at')
                    ->orWhereDate('embargo_reminder_sent_at', '<=', $sevenDaysAgo->toDateString());
            })
            ->cursor();

        $count = 0;
        foreach ($studies as $study) {
            SendEmbargoReminderEmail::dispatch($study->id);
            $count++;
        }

        $this->info("Queued {$count} embargo reminder email jobs.");
    }
}
