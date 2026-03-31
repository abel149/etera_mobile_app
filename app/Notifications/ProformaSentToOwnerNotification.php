<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class ProformaSentToOwnerNotification extends Notification
{
    use Queueable;

    protected $proforma;

    public function __construct(Proforma $proforma)
    {
        $this->proforma = $proforma;
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'proforma_id' => $this->proforma->id,
            'file_number' => $this->proforma->file_number,
            'customer_name' => $this->proforma->customer_name,
            'message' => "Proforma #{$this->proforma->file_number} has been completed and sent to you",
            'type' => 'proforma_sent_to_owner',
            'created_at' => now()->toISOString(),
        ];
    }
}
