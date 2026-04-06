<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class ProformaFloatedNotification extends Notification
{
    use Queueable;

    protected $proforma;

    public function __construct(Proforma $proforma)
    {
        $this->proforma = $proforma;
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
            'brand_name' => $this->proforma->brand?->name ?? 'Unknown',
            'message' => "Proforma #{$this->proforma->file_number} has been floated for {$this->proforma->customer_name}",
            'type' => 'proforma_floated',
            'created_at' => now()->toISOString(),
        ];
    }
}
