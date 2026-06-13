<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewProformaFile extends Notification
{
    use Queueable;

    protected $brand;
    protected $proforma;

    public function __construct($brand, $proforma)
    {
        $this->brand = $brand;
        $this->proforma = $proforma;
    }

    public function via($notifiable)
    {
        return ['database']; // Add 'mail' if you want to send emails as well
    }

    public function toArray($notifiable)
    {
        return [
            'type'       => 'new_proforma',
            'title'      => 'New Proforma Created',
            'brand_id'   => $this->brand->id,
            'brand_name' => $this->brand->name,
            'proforma_id'=> $this->proforma->id,
            'message'    => 'New proforma for brand: ' . $this->brand->name,
        ];
    }

    // Optional: If you want to send an email as well
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Proforma Created for ' . $this->brand->name)
            ->line('A new proforma has been created for the brand you serve: ' . $this->brand->name)
            ->action('View Proforma', url('/spare-part-shops/proformas'));
    }
} 