<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class SendToOwnerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $proforma;
    protected $ownerType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Proforma $proforma, string $ownerType = 'owner')
    {
        $this->proforma = $proforma;
        $this->ownerType = $ownerType;
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
        $roleName = ucfirst($this->ownerType);
        $route = $this->getRouteForRole($this->ownerType);
        
        return [
            'proforma_id' => $this->proforma->id,
            'file_number' => $this->proforma->file_number,
            'customer_name' => $this->proforma->customer_name,
            'license_plate' => $this->proforma->license_plate_number,
            'owner_type' => $this->ownerType,
            'message' => "Proforma #{$this->proforma->file_number} has been sent to you by admin for customer {$this->proforma->customer_name}",
            'type' => 'send_to_owner',
            'route' => $route,
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roleName = ucfirst($this->ownerType);
        
        return (new MailMessage)
            ->subject("Proforma #{$this->proforma->file_number} Sent to You")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A proforma has been sent to you by the admin.")
            ->line("File Number: #{$this->proforma->file_number}")
            ->line("Customer: {$this->proforma->customer_name}")
            ->line("License Plate: {$this->proforma->license_plate_number}")
            ->action('View Proforma', url($this->getRouteForRole($this->ownerType)))
            ->line("Please review the proforma details and take necessary action.");
    }

    /**
     * Get the appropriate route for the user role
     */
    private function getRouteForRole(string $role): string
    {
        switch ($role) {
            case 'garage':
                return '/garage/proformas';
            case 'insurance':
                return '/insurance/proformas';
            case 'business_owner':
                return '/business-owner/proformas';
            default:
                return '/proformas';
        }
    }
}
