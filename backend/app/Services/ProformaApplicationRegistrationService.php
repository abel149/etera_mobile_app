<?php

namespace App\Services;

use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\ProformaPartPrice;
use App\Models\Inbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProformaApplicationReceived;
use App\Notifications\ProformaApplicationCompleted;
use App\Notifications\InboxNotification;

class ProformaApplicationRegistrationService
{
    /**
     * Register a complete application from spare part shop or garage
     */
    public function registerApplication($proformaId, $userId, $data)
    {
        try {
            DB::beginTransaction();

            $proforma = Proforma::findOrFail($proformaId);
            $user = \App\Models\User::findOrFail($userId);

            // Create the main application
            $application = $this->createMainApplication($proforma, $user, $data);

            // Register part-specific pricing if provided
            if (isset($data['parts']) && is_array($data['parts'])) {
                $this->registerPartPricing($application, $data['parts']);
            }

            // Register additional media if provided
            if (isset($data['media'])) {
                $this->registerMedia($application, $data['media']);
            }

            // Update proforma status if needed
            $this->updateProformaStatus($proforma);

            // Send notifications
            $this->sendNotifications($proforma, $application, $user);

            // Log the registration
            $this->logApplicationRegistration($application, $user);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Application registered successfully',
                'application' => $application,
                'proforma_status' => $proforma->status
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registering application: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error registering application: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create the main application record
     */
    private function createMainApplication($proforma, $user, $data)
    {
        $applicationData = [
            'proforma_id' => $proforma->id,
            'application_by' => $user->id,
            'from' => $user->role,
            'amount' => $data['amount'] ?? null,
            'discount' => $data['discount'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'submitted_at' => now(),
        ];

        // Add role-specific fields
        if ($user->role === 'shop') {
            $applicationData['shop_rating'] = $data['shop_rating'] ?? null;
            $applicationData['delivery_time'] = $data['delivery_time'] ?? null;
        } elseif ($user->role === 'garage') {
            $applicationData['repair_time'] = $data['repair_time'] ?? null;
            $applicationData['warranty'] = $data['warranty'] ?? null;
        }

        return ProformaApplication::create($applicationData);
    }

    /**
     * Register part pricing for the application
     */
    private function registerPartPricing($application, $partsData)
    {
        foreach ($partsData as $partId => $partData) {
            if (isset($partData['unit_price']) && isset($partData['quantity'])) {
                ProformaPartPrice::create([
                    'application_id' => $application->id,
                    'car_part_id' => $partId,
                    'quantity' => $partData['quantity'],
                    'unit_price' => $partData['unit_price'],
                    'part_total' => $partData['unit_price'] * $partData['quantity'],
                    'condition' => $partData['condition'] ?? 'new',
                    'country' => $partData['country'] ?? null,
                    'grade' => $partData['grade'] ?? null,
                ]);
            }
        }
    }

    /**
     * Register additional media
     */
    private function registerMedia($application, $mediaData)
    {
        // Handle images
        if (isset($mediaData['images'])) {
            foreach ($mediaData['images'] as $image) {
                $application->addMedia($image)
                    ->toMediaCollection('application_images');
            }
        }

        // Handle documents
        if (isset($mediaData['documents'])) {
            foreach ($mediaData['documents'] as $document) {
                $application->addMedia($document)
                    ->toMediaCollection('application_documents');
            }
        }

        // Handle voice notes
        if (isset($mediaData['voice_note'])) {
            $application->addMedia($mediaData['voice_note'])
                ->toMediaCollection('voice_notes');
        }
    }

    /**
     * Update proforma status based on applications
     */
    private function updateProformaStatus($proforma)
    {
        // Get application counts by type
        $garageApplications = $proforma->applications()->where('from', 'garage')->count();
        $shopApplications = $proforma->applications()->where('from', 'shop')->count();
        
        // Get required counts - default to 3 for each if not set
        $requiredGarages = (int) ($proforma->required_number_of_garages ?? 3);
        $requiredShops = (int) ($proforma->required_number_of_shops ?? 3);

        Log::info("Proforma status check", [
            'proforma_id' => $proforma->id,
            'garage_applications' => $garageApplications,
            'shop_applications' => $shopApplications,
            'required_garages' => $requiredGarages,
            'required_shops' => $requiredShops,
            'is_etera_chereta' => $proforma->isEteraCheretaMode()
        ]);

        // For Etera-Chereta mode, check timer expiration first
        if ($proforma->isEteraCheretaMode() && $proforma->isTimerExpired()) {
            Log::info("Etera-Chereta timer expired for proforma {$proforma->id}");
            $proforma->update(['status' => 'closed']);
            return;
        }

        // Check if BOTH garage and shop requirements are met
        $garageRequirementMet = $garageApplications >= $requiredGarages;
        $shopRequirementMet = $shopApplications >= $requiredShops;
        
        Log::info("Requirements check", [
            'proforma_id' => $proforma->id,
            'garage_requirement_met' => $garageRequirementMet,
            'shop_requirement_met' => $shopRequirementMet
        ]);
        
        // Only close/complete if BOTH requirements are met
        if ($garageRequirementMet && $shopRequirementMet) {
            if ($proforma->selected()) {
                Log::info("Closing proforma {$proforma->id} - requirements met and selected");
                $proforma->update(['status' => 'closed']);
            } else {
                Log::info("Completing proforma {$proforma->id} - requirements met");
                $proforma->update(['status' => 'completed']);
            }
        }
    }

    /**
     * Send notifications to relevant parties
     */
    private function sendNotifications($proforma, $application, $user)
    {
        try {
            // Notify proforma creator
            $this->notifyProformaCreator($proforma, $application, $user);

            // Notify other applicants about new application
            $this->notifyOtherApplicants($proforma, $application, $user);

            // Notify admin if needed
            $this->notifyAdmin($proforma, $application, $user);

        } catch (\Exception $e) {
            Log::error("Error sending notifications: " . $e->getMessage());
        }
    }

    /**
     * Notify proforma creator about new application
     */
    private function notifyProformaCreator($proforma, $application, $user)
    {
        $creator = $proforma->poster;
        
        if ($creator && $creator->id !== $user->id) {
            try {
                $creator->notify(new ProformaApplicationReceived($proforma, $application, $user));
            } catch (\Exception $e) {
                Log::error("Error notifying proforma creator: " . $e->getMessage());
            }
        }
    }

    /**
     * Notify other applicants about new application
     */
    private function notifyOtherApplicants($proforma, $application, $user)
    {
        $otherApplicants = $proforma->applications()
            ->where('application_by', '!=', $user->id)
            ->with('applicationBy')
            ->get();

        foreach ($otherApplicants as $otherApp) {
            try {
                $otherApp->applicationBy->notify(new ProformaApplicationReceived($proforma, $application, $user));
            } catch (\Exception $e) {
                Log::error("Error notifying other applicant: " . $e->getMessage());
            }
        }
    }

    /**
     * Notify admin about new application
     */
    private function notifyAdmin($proforma, $application, $user)
    {
        $admins = \App\Models\User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            try {
                $admin->notify(new ProformaApplicationReceived($proforma, $application, $user));
            } catch (\Exception $e) {
                Log::error("Error notifying admin: " . $e->getMessage());
            }
        }
    }

    /**
     * Log application registration
     */
    private function logApplicationRegistration($application, $user)
    {
        Log::info("Application registered", [
            'application_id' => $application->id,
            'proforma_id' => $application->proforma_id,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'amount' => $application->amount,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get comprehensive application summary for insurance/business owners
     */
    public function getApplicationSummary($proformaId)
    {
        try {
            $proforma = Proforma::with([
                'applications.applicationBy',
                'applications.prices.part',
                'applications.media'
            ])->findOrFail($proformaId);

            $summary = [
                'proforma' => [
                    'id' => $proforma->id,
                    'file_number' => $proforma->file_number,
                    'status' => $proforma->status,
                    'customer_name' => $proforma->customer_name,
                    'created_at' => $proforma->created_at,
                    'is_etera_chereta' => $proforma->isEteraCheretaMode(),
                    'timer_expires_at' => $proforma->timer_expires_at,
                ],
                'applications' => [],
                'statistics' => [
                    'total_applications' => $proforma->applications->count(),
                    'shops_applied' => $proforma->applications()->where('from', 'shop')->count(),
                    'garages_applied' => $proforma->applications()->where('from', 'garage')->count(),
                    'total_value' => $proforma->applications->sum('amount'),
                    'average_price' => $proforma->applications->avg('amount'),
                ]
            ];

            foreach ($proforma->applications as $application) {
                $appSummary = [
                    'id' => $application->id,
                    'applicant' => [
                        'id' => $application->applicationBy->id,
                        'name' => $application->applicationBy->name,
                        'role' => $application->applicationBy->role,
                        'rating' => $application->applicationBy->rating ?? null,
                    ],
                    'initial_price' => $application->initial_price,
                    'discount' => $application->discount,
                    'final_price' => $application->final_price,
                    'amount' => $application->amount,
                    'parts' => [],
                ];

                // Add part details for both shops and garages
                foreach ($application->prices as $price) {
                    $appSummary['parts'][] = [
                        'part_name' => $price->part->name ?? 'Unknown Part',
                        'quantity' => $price->quantity,
                        'unit_price' => $price->unit_price,
                        'part_total' => $price->part_total,
                    ];
                }

                $summary['applications'][] = $appSummary;
            }

            return $summary;

        } catch (\Exception $e) {
            Log::error("Error getting application summary: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Export application data for insurance/business owners
     */
    public function exportApplicationData($proformaId, $format = 'json')
    {
        $summary = $this->getApplicationSummary($proformaId);
        
        if (!$summary) {
            return null;
        }

        switch ($format) {
            case 'json':
                return json_encode($summary, JSON_PRETTY_PRINT);
            
            case 'csv':
                return $this->convertToCsv($summary);
            
            case 'pdf':
                return $this->convertToPdf($summary);
            
            default:
                return $summary;
        }
    }

    /**
     * Convert summary to CSV format
     */
    private function convertToCsv($summary)
    {
        // Implementation for CSV conversion
        // This would create a CSV file with all application data
        return "CSV data would be generated here";
    }

    /**
     * Convert summary to PDF format
     */
    private function convertToPdf($summary)
    {
        // Implementation for PDF conversion
        // This would create a PDF report with all application data
        return "PDF data would be generated here";
    }
} 