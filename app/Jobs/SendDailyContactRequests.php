<?php

namespace App\Jobs;

use App\Mail\DailyContactRequestsMail;
use App\Models\ContactRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailyContactRequests implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all contact requests that haven't been emailed yet
        $requests = ContactRequest::where('emailed', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Only send email if there are requests
        if ($requests->count() > 0) {
            $email = config('mail.rossa_contact_email');

            if ($email) {
                Mail::to($email)->send(new DailyContactRequestsMail($requests));

                // Mark all requests as emailed
                ContactRequest::whereIn('id', $requests->pluck('id'))
                    ->update([
                        'emailed' => true,
                        'emailed_at' => now(),
                    ]);
            }
        }
    }
}
