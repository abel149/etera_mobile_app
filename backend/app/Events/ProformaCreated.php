<?php

namespace App\Events;

use App\Models\Proforma;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProformaCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $proformaData;

    /**
     * Create a new event instance.
     */
    public function __construct(Proforma $proforma)
    {
        $proforma->load('poster', 'brand');

        $posterRole = 'Unknown';
        if ($proforma->poster) {
            $posterRole = $proforma->poster->role == 'business_owner' 
                ? 'Business Owner' 
                : ucfirst($proforma->poster->role);
        }

        $this->proformaData = [
            'id' => $proforma->id,
            'file_number' => $proforma->file_number ?? 'N/A',
            'from' => $posterRole,
            'customer_name' => $proforma->customer_name ?? 'N/A',
            'garage_count' => $proforma->applicationsFromGarages ? $proforma->applicationsFromGarages->count() : 0,
            'shop_count' => $proforma->applicationsFromShops ? $proforma->applicationsFromShops->count() : 0,
            'status' => $proforma->status ?? 'pending',
            'is_etera_chereta' => $proforma->isEteraCheretaMode(),
            'remaining_time' => $proforma->isEteraCheretaMode() ? $proforma->getFormattedRemainingTime() : 'N/A',
            'timer_expires_at' => $proforma->timer_expires_at ? $proforma->timer_expires_at->toISOString() : null,
            'created_at' => $proforma->created_at ? $proforma->created_at->format('D M d, Y h:i A') : 'N/A',
            'is_from_others' => $proforma->poster ? $proforma->isFromOthers() : false,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-proformas'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'proforma.created';
    }
}
