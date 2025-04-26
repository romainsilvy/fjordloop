<?php

namespace App\Mail;

use App\Models\Travel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TravelInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public User $sender;
    public Travel $travel;
    public string $invitationToken;

    public function __construct(User $sender, Travel $travel, string $invitationToken)
    {
        $this->sender = $sender;
        $this->travel = $travel;
        $this->invitationToken = $invitationToken;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Travel Invitation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.travel.invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
