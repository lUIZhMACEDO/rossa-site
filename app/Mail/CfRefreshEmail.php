<?php

namespace App\Mail;

use App\Models\CoreFacility;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CfRefreshEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected CoreFacility $coreFacility;

    public string $homeUrl;

    public string $refreshUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(int $coreFacilityId)
    {
        $this->coreFacility = CoreFacility::findOrFail($coreFacilityId);
        $this->homeUrl = route('core-facility.home');
        $this->refreshUrl = URL::temporarySignedRoute(
            'core-facility.refresh',
            now()->addDays(7),
            ['facility' => $this->coreFacility->id]
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Review your Core Facility - {$this->coreFacility->facility_name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cf-refresh-request',
            with: [
                'coreFacility' => $this->coreFacility,
                'homeUrl' => $this->homeUrl,
                'refreshUrl' => $this->refreshUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
