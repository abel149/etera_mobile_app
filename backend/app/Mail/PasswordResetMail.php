<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;
    public string $userName;

    public function __construct(string $resetUrl, string $userName = 'User')
    {
        $this->resetUrl = $resetUrl;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password - ETERA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset',
            with: [
                'resetUrl' => $this->resetUrl,
                'userName' => $this->userName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
