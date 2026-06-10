<?php

namespace App\Services;

use App\Models\Proforma;
use App\Models\User;
use App\Models\Cost;
use App\Models\ProformaApplication;
use App\Jobs\AutoSelectProformaOffers;
use App\Notifications\ProformaClosed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;

class ProformaClosingService
{
    /**
     * Close a proforma manually (admin action)
     */
    public function closeProforma(Proforma $proforma, $userId = null)
    {
        try {
            DB::beginTransaction();

            // Update proforma status
            $proforma->update(['status' => 'closed']);

            // Clear inbox records
            $proforma->inboxes()->delete();

            // Send notification to admin
            $this->sendProformaClosedNotification($proforma);

            // Send billing information email (no invoice created)
            $this->sendBillingInfoEmail($proforma);

            // Send Telegram billing details to the poster
            try {
                if ($proforma->poster && !empty($proforma->poster->telegram_chat_id)) {
                    $billingData = $this->calculateBilling($proforma);
                    if ($billingData) {
                        (new TelegramService())->sendBillingDetailsNotification(
                            $proforma->poster->telegram_chat_id,
                            $proforma,
                            $billingData['charge'],
                            $billingData['vatAmount'],
                            $billingData['total']
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to send billing Telegram to poster for proforma {$proforma->id}", ['error' => $e->getMessage()]);
            }

            // Send Telegram notification to the processed_by user
            try {
                if ($proforma->processedBy && !empty($proforma->processedBy->telegram_chat_id)) {
                    (new TelegramService())->sendProcessedByClosedNotification(
                        $proforma->processedBy->telegram_chat_id,
                        $proforma
                    );
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to send processed_by Telegram for proforma {$proforma->id}", ['error' => $e->getMessage()]);
            }

            // Log the action
            Log::info("Proforma {$proforma->id} closed manually by user {$userId}");

            DB::commit();

            return [
                'success' => true,
                'message' => 'Proforma closed successfully',
                'proforma' => $proforma
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error closing proforma {$proforma->id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error closing proforma: ' . $e->getMessage()
            ];
        }
    }
    
    public function paymentCollected(Proforma $proforma, $userId = null)
    {
        try {
            DB::beginTransaction();

            // Update proforma status
            $proforma->update(['status' => 'payment collected']);

            // Clear inbox records
            $proforma->inboxes()->delete();


            // Log the action
            Log::info("Proforma {$proforma->id} Paid manually by user {$userId}");

            DB::commit();

            return [
                'success' => true,
                'message' => 'Proforma paid successfully',
                'proforma' => $proforma
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error closing proforma {$proforma->id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error closing proforma: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Complete a proforma (when all required applications are received)
     */
    public function completeProforma(Proforma $proforma)
    {
        try {
            DB::beginTransaction();

            // Update proforma status
            $proforma->update(['status' => 'completed']);

            // Log the action
            Log::info("Proforma {$proforma->id} completed automatically");

            DB::commit();

            return [
                'success' => true,
                'message' => 'Proforma completed successfully',
                'proforma' => $proforma
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error completing proforma {$proforma->id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error completing proforma: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle proforma expiration (called when timer expires)
     */
    public function handleExpiredProforma(Proforma $proforma)
    {
        try {
            Log::info("Handling expired proforma {$proforma->id}");

            if ($proforma->isEteraCheretaMode()) {
                // For Etera-Chereta mode, trigger auto-selection
                return $this->handleEteraCheretaExpiration($proforma);
            } else {
                // For regular proformas, just close them
                return $this->closeProforma($proforma);
            }

        } catch (\Exception $e) {
            Log::error("Error handling expired proforma {$proforma->id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error handling expired proforma: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle Etera-Chereta mode expiration
     */
    private function handleEteraCheretaExpiration(Proforma $proforma)
    {
        try {
            // Check if there are any applications
            if ($proforma->applications->isEmpty()) {
                // No applications, just close the proforma
                Log::info("Proforma {$proforma->id} expired with no applications, closing it");
                return $this->closeProforma($proforma);
            }

            // Dispatch auto-selection job
            Log::info("Dispatching auto-selection for expired Etera-Chereta proforma {$proforma->id}");
            AutoSelectProformaOffers::dispatch($proforma->id);

            return [
                'success' => true,
                'message' => 'Auto-selection job dispatched for expired Etera-Chereta proforma',
                'proforma' => $proforma
            ];

        } catch (\Exception $e) {
            Log::error("Error handling Etera-Chereta expiration for proforma {$proforma->id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error handling Etera-Chereta expiration: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if a proforma should be automatically closed
     */
    public function shouldAutoClose(Proforma $proforma)
    {
        // Check if timer has expired
        if ($proforma->timer_expires_at && $proforma->timer_expires_at->isPast()) {
            return true;
        }

        // Check if application limit has been reached
        $totalApplications = $proforma->applications()->count();
        $requiredApplications = $proforma->required_number_of_shops + $proforma->required_number_of_garages;

        if ($requiredApplications > 0 && $totalApplications >= $requiredApplications) {
            return true;
        }

        return false;
    }

    /**
     * Get proforma status summary
     */
    public function getStatusSummary(Proforma $proforma)
    {
        $totalApplications = $proforma->applications()->count();
        $requiredApplications = $proforma->required_number_of_shops + $proforma->required_number_of_garages;

        return [
            'status' => $proforma->status,
            'total_applications' => $totalApplications,
            'required_applications' => $requiredApplications,
            'is_etera_chereta' => $proforma->isEteraCheretaMode(),
            'timer_expired' => $proforma->isTimerExpired(),
            'remaining_time' => $proforma->getFormattedRemainingTime(),
            'can_apply' => $proforma->status === 'pending' || $proforma->status === 'opened',
            'is_closed' => in_array($proforma->status, ['closed', 'completed']),
        ];
    }

    /**
     * Calculate billing amounts for a proforma.
     * Returns ['charge' => float, 'vatAmount' => float, 'total' => float] or null.
     */
    private function calculateBilling(Proforma $proforma): ?array
    {
        try {
            $latestCost = Cost::latest()->first();
            if (!$latestCost) {
                return null;
            }

            $vatRate = 0.15;
            $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
            $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

            if ($proforma->proforma_type && str_starts_with($proforma->proforma_type, 'insurance_')) {
                $total = (float) ($proforma->insured
                    ? ($latestCost->insured_cost ?? 0)
                    : ($latestCost->insurance_proforma ?? 0));
            } elseif ($requiredShops > 0 && $requiredGarages == 0) {
                $count = ProformaApplication::where('proforma_id', $proforma->id)->count();
                $field = "{$count}_proforma_cost";
                $total = (float) ($latestCost->$field ?? 0);
            } elseif ($requiredShops == 3 && $requiredGarages == 3) {
                $total = (float) ($proforma->insured
                    ? ($latestCost->insured_cost ?? 0)
                    : ($latestCost->insurance_proforma ?? 0));
            } elseif ($requiredShops == 0 && $requiredGarages == 0) {
                $total = (float) ($latestCost->etera_chereta_cost ?? 0);
            } else {
                return null;
            }

            if ($total <= 0) {
                return null;
            }

            $charge = $total / (1 + $vatRate);
            $vatAmount = $total - $charge;

            return compact('charge', 'vatAmount', 'total');
        } catch (\Throwable $e) {
            Log::warning("Failed to calculate billing for proforma {$proforma->id}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send billing information email on close (no invoice created)
     */
    private function sendBillingInfoEmail(Proforma $proforma)
    {
        // Skip if billing emails are disabled
        if (!\App\Models\EmailSetting::isEnabled('proforma_closed_billing')) {
            return;
        }

        // Skip for test brands
        if ($proforma->brand && $proforma->brand->is_test) {
            return;
        }

        try {
            $latestCost = Cost::latest()->first();
            if (!$latestCost) {
                Log::warning("No cost data for billing email, proforma {$proforma->id}");
                return;
            }

            $vatRate = 0.15;
            $requiredShops   = (int) ($proforma->required_number_of_shops ?? 0);
            $requiredGarages = (int) ($proforma->required_number_of_garages ?? 0);

            // Determine type and total
            if ($proforma->proforma_type && str_starts_with($proforma->proforma_type, 'insurance_')) {
                $total = (float) ($proforma->insured
                    ? ($latestCost->insured_cost ?? 0)
                    : ($latestCost->insurance_proforma ?? 0));
            } elseif ($requiredShops > 0 && $requiredGarages == 0) {
                $count = ProformaApplication::where('proforma_id', $proforma->id)->count();
                $field = "{$count}_proforma_cost";
                $total = (float) ($latestCost->$field ?? 0);
            } elseif ($requiredShops == 3 && $requiredGarages == 3) {
                $total = (float) ($proforma->insured
                    ? ($latestCost->insured_cost ?? 0)
                    : ($latestCost->insurance_proforma ?? 0));
            } elseif ($requiredShops == 0 && $requiredGarages == 0) {
                $total = (float) ($latestCost->etera_chereta_cost ?? 0);
            } else {
                Log::warning("Unknown proforma type for billing email, proforma {$proforma->id}");
                return;
            }

            if ($total <= 0) {
                return;
            }

            $charge = $total / (1 + $vatRate);
            $vatAmount = $total - $charge;

            $recipientEmail = $proforma->customer_email ?? $proforma->poster?->email;
            if (!$recipientEmail) {
                return;
            }

            Mail::send('emails.proforma', 
    compact('proforma', 'charge', 'vatAmount', 'total'), 
    function ($message) use ($proforma, $recipientEmail) {
        $message->to($recipientEmail)
                ->subject("etera – Billing Info for Proforma #{$proforma->file_number}");
    }
);

            \App\Models\SentEmail::log(
                'proforma_closed_billing',
                $recipientEmail,
                $proforma->customer_name,
                $proforma->poster?->id,
                $proforma->id,
                "ETERA – Billing Info for Proforma #{$proforma->file_number}",
                'sent'
            );

            Log::info("Billing info email sent for proforma {$proforma->id}");

        } catch (\Throwable $e) {
            Log::warning("Failed to send billing info email for proforma {$proforma->id}", [
                'error' => $e->getMessage(),
            ]);
            try {
                \App\Models\SentEmail::log(
                    'proforma_closed_billing',
                    $proforma->customer_email ?? $proforma->poster?->email ?? 'unknown',
                    $proforma->customer_name,
                    $proforma->poster?->id,
                    $proforma->id,
                    "ETERA – Billing Info for Proforma #{$proforma->file_number}",
                    'failed',
                    $e->getMessage()
                );
            } catch (\Throwable $logEx) {}
        }
    }

    /**
     * Send notification to admin when proforma is closed
     */
    private function sendProformaClosedNotification(Proforma $proforma)
    {
        try {
            // Get all admin users
            $adminUsers = User::where('role', 'admin')->get();
            
            foreach ($adminUsers as $admin) {
                $admin->notify(new ProformaClosed($proforma));
            }
            
            Log::info("Proforma closed notification sent to admins for proforma {$proforma->id}");
            
        } catch (\Exception $e) {
            Log::error("Error sending proforma closed notification: " . $e->getMessage());
        }
    }
} 