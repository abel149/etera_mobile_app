<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class InboxNotification extends Notification implements ShouldQueue
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
        $channels = ['database'];
        if (!empty($notifiable->device_token)) {
            $channels[] = FcmChannel::class;
        }
        return $channels;
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'New Proforma in Your Inbox',
            "Proforma #{$this->proforma->file_number} — {$this->proforma->customer_name}",
            [
                'type'        => 'inbox_notification',
                'proforma_id' => (string) $this->proforma->id,
            ],
        ];
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
            'brand_name' => $this->proforma->brand->name ?? 'Unknown Brand',
            'message' => "New proforma #{$this->proforma->file_number} available in your inbox for customer {$this->proforma->customer_name}",
            'type' => 'inbox_notification',
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Proforma #{$this->proforma->file_number} in Your Inbox")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A new proforma has been added to your inbox.")
            ->line("File Number: #{$this->proforma->file_number}")
            ->line("Customer: {$this->proforma->customer_name}")
            ->line("License Plate: {$this->proforma->license_plate_number}")
            ->line("Brand: " . ($this->proforma->brand->name ?? 'Unknown Brand'))
            ->action('View in Inbox', url('/spare-part-shops/inbox'))
            ->line('Please review and submit your quote as soon as possible!');
    }
}
