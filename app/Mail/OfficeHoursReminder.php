<?php

namespace App\Mail;

use App\Models\OfficeHoursSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OfficeHoursReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OfficeHoursSession $session,
        public Collection $registrations
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ROSSA Office Hours - Tomorrow\'s Session Summary',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.office-hours-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
