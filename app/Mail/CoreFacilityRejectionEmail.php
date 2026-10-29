<?php

namespace App\Mail;

use App\Models\CoreFacility;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoreFacilityRejectionEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected CoreFacility $coreFacility;

    public string $callToAction;

    /**
     * Create a new message instance.
     */
    public function __construct(int $coreFacilityId)
    {
        $this->coreFacility = CoreFacility::findOrFail($coreFacilityId);
        $queryParams = ['statuses' => [$this->coreFacility->status->value]];
        $queryParams['sort'] = 'expires_at';
        $queryParams['direction'] = 'desc';
        $queryParams['search'] = $this->coreFacility->cf_id;
        $this->callToAction = route('core-facility.home').'?'.http_build_query($queryParams).'#my-facilities';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Core Facility Requires Revision',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.core-facility-rejection',
            with: [
                'coreFacility' => $this->coreFacility,
                'callToAction' => $this->callToAction,
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
