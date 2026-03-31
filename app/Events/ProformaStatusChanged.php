<?php

namespace App\Events;

use App\Models\Proforma;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProformaStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $proformaData;
    public string $action;
    public ?int $targetUserId;

    /**
     * Create a new event instance.
     *
     * @param Proforma $proforma
     * @param string $action (floated|closed|rejected|sent_to_owner)
     * @param int|null $targetUserId User ID who should receive this (null = broadcast to all)
     */
    public function __construct(Proforma $proforma, string $action, ?int $targetUserId = null)
    {
        $proforma->load('poster', 'brand');

        $this->action = $action;
        $this->targetUserId = $targetUserId;

        $this->proformaData = [
            'id' => $proforma->id,
            'file_number' => $proforma->file_number ?? 'N/A',
            'customer_name' => $proforma->customer_name ?? 'N/A',
            'brand' => $proforma->brand?->name ?? 'N/A',
            'model' => $proforma->model ?? '',
            'year' => $proforma->year ?? '',
            'plate' => $proforma->license_plate_number ?? '',
            'status' => $proforma->status,
            'poster_role' => $proforma->poster ? ucfirst($proforma->poster->role) : 'Unknown',
            'poster_id' => $proforma->poster_id,
            'is_etera_chereta' => $proforma->isEteraCheretaMode(),
            'remaining_time' => $proforma->isEteraCheretaMode() ? $proforma->getFormattedRemainingTime() : null,
            'timer_expires_at' => $proforma->timer_expires_at ? $proforma->timer_expires_at->toISOString() : null,
            'garage_count' => $proforma->applicationsFromGarages ? $proforma->applicationsFromGarages->count() : 0,
            'shop_count' => $proforma->applicationsFromShops ? $proforma->applicationsFromShops->count() : 0,
            'created_at' => $proforma->created_at?->format('D M d, Y h:i A') ?? 'N/A',
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        switch ($this->action) {
            case 'floated':
                // Public channel — all shops/garages should see this
                $channels[] = new Channel('proformas');
                // Also notify admin channel
                $channels[] = new Channel('admin-proformas');
                break;

            case 'closed':
                // Notify admin and the poster
                $channels[] = new Channel('admin-proformas');
                $channels[] = new Channel('proformas');
                if ($this->targetUserId) {
                    $channels[] = new PrivateChannel('user.' . $this->targetUserId);
                }
                break;

            case 'rejected':
                // Notify the specific user who got rejected
                if ($this->targetUserId) {
                    $channels[] = new PrivateChannel('user.' . $this->targetUserId);
                }
                $channels[] = new Channel('proformas');
                break;

            case 'sent_to_owner':
                // Notify the poster/owner
                if ($this->targetUserId) {
                    $channels[] = new PrivateChannel('user.' . $this->targetUserId);
                }
                $channels[] = new Channel('admin-proformas');
                break;

            default:
                $channels[] = new Channel('proformas');
                break;
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'proforma.status.changed';
    }
}
