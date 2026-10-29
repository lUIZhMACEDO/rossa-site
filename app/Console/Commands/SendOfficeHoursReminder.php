<?php

namespace App\Console\Commands;

use App\Mail\OfficeHoursReminder;
use App\Models\OfficeHoursSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOfficeHoursReminder extends Command
{
    protected $signature = 'office-hours:remind';

    protected $description = 'Send admin a summary of tomorrow\'s office hours registrations';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $session = OfficeHoursSession::where('session_date', $tomorrow)
            ->where('status', 'active')
            ->first();

        if (! $session) {
            $this->info('No active session tomorrow. Skipping reminder.');

            return;
        }

        $registrations = $session->registrations()
            ->whereNull('cancelled_at')
            ->with('user')
            ->get();

        if ($registrations->count() === 0) {
            $this->info('No registrations for tomorrow. Skipping reminder (empty session check handles this).');

            return;
        }

        try {
            Mail::to(config('mail.rossa_admin_mass_email'))
                ->send(new OfficeHoursReminder($session, $registrations));
            $this->info('Reminder sent successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to send office hours reminder: '.$e->getMessage());
            $this->error('Failed to send reminder: '.$e->getMessage());
        }
    }
}
