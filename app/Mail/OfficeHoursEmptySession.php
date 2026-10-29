<?php

namespace App\Mail;

use App\Models\OfficeHoursSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficeHoursEmptySession extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OfficeHoursSession $session
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ROSSA Office Hours - No Registrations for Tomorrow\'s Session',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.office-hours-empty-session',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
