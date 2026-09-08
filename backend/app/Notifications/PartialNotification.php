<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Proforma;

class PartialNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Proforma $proforma;
    protected int $group;
    protected int $partsNeeded;

    public function __construct(Proforma $proforma, int $group, int $partsNeeded)
    {
        $this->proforma    = $proforma;
        $this->group       = $group;
        $this->partsNeeded = $partsNeeded;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'proforma_id'  => $this->proforma->id,
            'file_number'  => $this->proforma->file_number,
            'customer_name'=> $this->proforma->customer_name,
            'license_plate'=> $this->proforma->license_plate_number,
            'brand_name'   => $this->proforma->brand->name ?? 'Unknown Brand',
            'inbox_group'  => $this->group,
            'parts_needed' => $this->partsNeeded,
            'message'      => "Proforma #{$this->proforma->file_number} needs {$this->partsNeeded} more part(s) priced. Help complete Group {$this->group}.",
            'type'         => 'partial_notification',
            'created_at'   => now()->toISOString(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Partial Proforma #{$this->proforma->file_number} — {$this->partsNeeded} Parts Needed")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A proforma needs your help completing missing parts.")
            ->line("File Number: #{$this->proforma->file_number}")
            ->line("Customer: {$this->proforma->customer_name}")
            ->line("Brand: " . ($this->proforma->brand->name ?? 'Unknown Brand'))
            ->line("Parts still needed: {$this->partsNeeded}")
            ->action('View Partial Proformas', url('/spare-part-shops/proformas'))
            ->line('Submit your prices for the missing parts to earn pro-rata commission!');
    }
}
