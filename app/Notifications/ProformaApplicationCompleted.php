<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\User;

class ProformaApplicationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $proforma;
    protected $selectedApplications;

    /**
     * Create a new notification instance.
     */
    public function __construct(Proforma $proforma, $selectedApplications = [])
    {
        $this->proforma = $proforma;
        $this->selectedApplications = $selectedApplications;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $message = (new MailMessage)
            ->subject("Proforma #{$this->proforma->file_number} Completed")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your proforma #{$this->proforma->file_number} has been completed.");

        if ($this->proforma->isEteraCheretaMode()) {
            $message->line("This was an Etera-Chereta proforma that has been automatically processed.");
        }

        if (!empty($this->selectedApplications)) {
            $message->line("Top applications have been selected based on lowest pricing.");
        }

        $message->line("Customer: {$this->proforma->customer_name}")
            ->line("Vehicle: {$this->proforma->brand->name} {$this->proforma->model} ({$this->proforma->year})")
            ->action('View Proforma Details', url("/proforma-details?proforma_id={$this->proforma->id}"))
            ->line('Thank you for using our platform!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'proforma_application_completed',
            'proforma_id' => $this->proforma->id,
            'proforma_file_number' => $this->proforma->file_number,
            'status' => $this->proforma->status,
            'is_etera_chereta' => $this->proforma->isEteraCheretaMode(),
            'customer_name' => $this->proforma->customer_name,
            'vehicle_info' => "{$this->proforma->brand->name} {$this->proforma->model} ({$this->proforma->year})",
            'selected_applications_count' => count($this->selectedApplications),
            'timestamp' => now()->toISOString(),
        ];
    }
} 