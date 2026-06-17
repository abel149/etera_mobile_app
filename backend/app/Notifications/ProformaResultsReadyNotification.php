<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class ProformaResultsReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Proforma $proforma) {}

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
            'Your Proforma Results Are Ready',
            "Proforma #{$this->proforma->file_number} has been closed. Tap to view your quotes.",
            [
                'type'        => 'proforma_results_ready',
                'proforma_id' => (string) $this->proforma->id,
            ],
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'proforma_results_ready',
            'title'       => 'Proforma Results Ready',
            'proforma_id' => $this->proforma->id,
            'file_number' => $this->proforma->file_number,
            'message'     => "Proforma #{$this->proforma->file_number} has been closed. Your price quotes are ready to view.",
            'created_at'  => now()->toISOString(),
        ];
    }
}
