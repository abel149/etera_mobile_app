<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServerErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        // Rename 'message' to 'errorMessage' to avoid conflict with $message property
        $this->data = [
            'errorMessage' => $data['message'] ?? 'N/A', // <-- key renamed
            'status'       => $data['status'] ?? 'N/A',
            'url'          => $data['url'] ?? 'N/A',
            'method'       => $data['method'] ?? 'N/A',
            'trace'        => $data['trace'] ?? 'No trace',
            'user'         => $data['user'] ?? ['email' => 'Guest'],
        ];
    }

    public function build()
    {
        return $this->subject('🚨 500 Error Detected')
                    ->view('emails.server-error')
                    ->with($this->data);
    }
}
