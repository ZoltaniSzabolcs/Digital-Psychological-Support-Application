<?php

namespace App\Mail;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CrisisAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alert;
    public $patient;

    /**
     * Create a new message instance.
     */
    public function __construct(Alert $alert, User $patient)
    {
        $this->alert = $alert;
        $this->patient = $patient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'URGENT: Crisis Alert Reported - ' . $this->patient->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
        // --- CHANGE THIS LINE ---
            view: 'emails.crisis_alert', // Was 'view.name'
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
