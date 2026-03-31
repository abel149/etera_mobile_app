<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;
use App\Models\ProformaApplication;

class ProformaApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $proforma;
    protected $application;
    protected $currentCount;
    protected $totalRequired;

    /**
     * Create a new notification instance.
     */
    public function __construct(Proforma $proforma, ProformaApplication $application, int $currentCount, int $totalRequired)
    {
        $this->proforma = $proforma;
        $this->application = $application;
        $this->currentCount = $currentCount;
        $this->totalRequired = $totalRequired;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $progress = $this->totalRequired > 0
            ? "{$this->currentCount}/{$this->totalRequired}"
            : $this->currentCount;

        $message = (new MailMessage)
            ->subject("Proforma #{$this->proforma->file_number} – Applications ({$progress})")
            ->greeting("Hello!")
            ->line("Proforma #{$this->proforma->file_number} has received {$progress} applications so far.");

        if ($this->totalRequired > 0) {
            $remaining = $this->totalRequired - $this->currentCount;
            if ($remaining > 0) {
                $message->line("Progress: {$this->currentCount} of {$this->totalRequired} submitted. {$remaining} more needed.");
            } else {
                $message->line("All {$this->totalRequired} required applications have been submitted!");
            }
        }

        $message->line('You can log in to your account to review the applications and request close when ready.')
            ->action('Log In to etera', url('/login'))
            ->line('Thank you for using etera!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'proforma_application_submitted',
            'title' => "Applications ({$this->currentCount}/{$this->totalRequired})",
            'message' => "Proforma #{$this->proforma->file_number} has received {$this->currentCount} of {$this->totalRequired} applications. Log in to review and request close.",
            'proforma_id' => $this->proforma->id,
            'proforma_file_number' => $this->proforma->file_number,
            'current_count' => $this->currentCount,
            'total_required' => $this->totalRequired,
            'timestamp' => now()->toISOString(),
        ];
    }
}
