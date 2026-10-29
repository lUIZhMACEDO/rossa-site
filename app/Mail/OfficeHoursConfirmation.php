<?php

namespace App\Mail;

use App\Models\OfficeHoursSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficeHoursConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OfficeHoursSession $session,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ROSSA Office Hours - Registration Confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.office-hours-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
