<?php

namespace App\Mail;

use App\Models\StudyContributorInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudyContributorInvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StudyContributorInvitation $invitation, public string $token)
    {
        $this->invitation->loadMissing('study', 'inviter');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You have been invited to collaborate on a study');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.study-contributor-invitation', with: [
            'invitation' => $this->invitation,
            'acceptUrl' => route('study.invitations.show', [$this->invitation, $this->token]),
        ]);
    }

    public function attachments(): array
    {
        return [];
    }
}
