<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class ProformaClosed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $proforma;

    /**
     * Create a new notification instance.
     */
    public function __construct(Proforma $proforma)
    {
        $this->proforma = $proforma;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'proforma_id' => $this->proforma->id,
            'file_number' => $this->proforma->file_number,
            'customer_name' => $this->proforma->customer_name,
            'license_plate' => $this->proforma->license_plate_number,
            'status' => $this->proforma->status,
            'message' => "Proforma #{$this->proforma->file_number} has been closed for customer {$this->proforma->customer_name}",
            'type' => 'proforma_closed',
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Proforma #{$this->proforma->file_number} Closed")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Proforma #{$this->proforma->file_number} has been closed.")
            ->line("Customer: {$this->proforma->customer_name}")
            ->line("License Plate: {$this->proforma->license_plate_number}")
            ->line("Status: {$this->proforma->status}")
            ->action('View Proforma Details', url("/admin/proformas/{$this->proforma->id}/details"))
            ->line('Thank you for using our system!');
    }
}
