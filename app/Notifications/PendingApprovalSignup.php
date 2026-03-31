<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PendingApprovalSignup extends Notification
{
    use Queueable;

    public function __construct(
        public int $userId,
        public string $userName,
        public ?string $userRole,
        public ?string $email,
        public ?string $phoneNumber
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $parts = [];
        if (!empty($this->email)) {
            $parts[] = $this->email;
        }
        if (!empty($this->phoneNumber)) {
            $parts[] = $this->phoneNumber;
        }

        $contact = count($parts) ? (' (' . implode(' / ', $parts) . ')') : '';
        $roleText = $this->userRole ? ('Role: ' . $this->userRole) : 'Role: N/A';

        return [
            'type' => 'approval_pending_signup',
            'file_number' => 'Approval',
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_role' => $this->userRole,
            'message' => "New signup pending approval: {$this->userName}{$contact}. {$roleText}.",
            'created_at' => now()->toISOString(),
        ];
    }
}
