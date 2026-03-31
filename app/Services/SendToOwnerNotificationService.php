<?php

namespace App\Services;

use App\Models\Proforma;
use App\Models\User;
use App\Notifications\SendToOwnerNotification;
use Illuminate\Support\Facades\Log;

class SendToOwnerNotificationService
{
    /**
     * Send proforma to garage users and notify them
     */
    public function sendToGarageUsers(Proforma $proforma, array $userIds = [])
    {
        try {
            // If no specific users provided, get all garage users
            if (empty($userIds)) {
                $garageUsers = User::where('role', 'garage')->get();
            } else {
                $garageUsers = User::whereIn('id', $userIds)
                    ->where('role', 'garage')
                    ->get();
            }

            foreach ($garageUsers as $user) {
                $user->notify(new SendToOwnerNotification($proforma, 'garage'));
            }

            Log::info("Send to owner notifications sent to " . $garageUsers->count() . " garage users for proforma {$proforma->id}");

            return [
                'success' => true,
                'message' => 'Send to owner notifications sent successfully',
                'count' => $garageUsers->count()
            ];

        } catch (\Exception $e) {
            Log::error("Error sending send to owner notifications: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error sending send to owner notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send proforma to insurance users and notify them
     */
    public function sendToInsuranceUsers(Proforma $proforma, array $userIds = [])
    {
        try {
            // If no specific users provided, get all insurance users
            if (empty($userIds)) {
                $insuranceUsers = User::where('role', 'insurance')->get();
            } else {
                $insuranceUsers = User::whereIn('id', $userIds)
                    ->where('role', 'insurance')
                    ->get();
            }

            foreach ($insuranceUsers as $user) {
                $user->notify(new SendToOwnerNotification($proforma, 'insurance'));
            }

            Log::info("Send to owner notifications sent to " . $insuranceUsers->count() . " insurance users for proforma {$proforma->id}");

            return [
                'success' => true,
                'message' => 'Send to owner notifications sent successfully',
                'count' => $insuranceUsers->count()
            ];

        } catch (\Exception $e) {
            Log::error("Error sending send to owner notifications: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error sending send to owner notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send proforma to business owner users and notify them
     */
    public function sendToBusinessOwnerUsers(Proforma $proforma, array $userIds = [])
    {
        try {
            // If no specific users provided, get all business owner users
            if (empty($userIds)) {
                $businessOwnerUsers = User::where('role', 'business_owner')->get();
            } else {
                $businessOwnerUsers = User::whereIn('id', $userIds)
                    ->where('role', 'business_owner')
                    ->get();
            }

            foreach ($businessOwnerUsers as $user) {
                $user->notify(new SendToOwnerNotification($proforma, 'business_owner'));
            }

            Log::info("Send to owner notifications sent to " . $businessOwnerUsers->count() . " business owner users for proforma {$proforma->id}");

            return [
                'success' => true,
                'message' => 'Send to owner notifications sent successfully',
                'count' => $businessOwnerUsers->count()
            ];

        } catch (\Exception $e) {
            Log::error("Error sending send to owner notifications: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error sending send to owner notifications: ' . $e->getMessage()
            ];
        }
    }
}
