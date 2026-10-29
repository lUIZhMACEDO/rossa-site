<?php

namespace App\Mail;

use App\Models\OfficeHoursSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficeHoursAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OfficeHoursSession $session,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ROSSA Office Hours - New Registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.office-hours-admin-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
