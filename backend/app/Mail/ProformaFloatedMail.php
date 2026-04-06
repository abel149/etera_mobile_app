<?php

namespace App\Mail;

use App\Models\Proforma;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProformaFloatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Proforma $proforma;

    /**
     * Create a new message instance.
     */
    public function __construct(Proforma $proforma)
    {
        $this->proforma = $proforma;
    }

    /**
     * Email subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Proforma #' . $this->proforma->file_number . ' Published',
        );
    }

    /**
     * Email view
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.proforma_floated',
            with: [
                'proforma' => $this->proforma,
            ],
        );
    }

    /**
     * Attachments
     */
    public function attachments(): array
    {
        return [];
    }
}
