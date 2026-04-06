<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class ProformaRejectedNotification extends Notification
{
    use Queueable;

    protected $proforma;
    protected $reason;

    public function __construct(Proforma $proforma, string $reason = '')
    {
        $this->proforma = $proforma;
        $this->reason = $reason;
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
            'message' => "Application for proforma #{$this->proforma->file_number} has been rejected",
            'reason' => $this->reason,
            'type' => 'proforma_rejected',
            'created_at' => now()->toISOString(),
        ];
    }
}
