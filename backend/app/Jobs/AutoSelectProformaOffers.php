<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AutoSelectProformaOffers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $proformaId;

    public function __construct($proformaId)
    {
        $this->proformaId = $proformaId;
    }

    public function handle()
    {
        try {
            $proforma = Proforma::with(['applications.prices', 'applications.applicationBy'])
                ->find($this->proformaId);

            if (!$proforma || !$proforma->isEteraCheretaMode()) {
                Log::info("Auto-selection skipped for proforma {$this->proformaId}: Not in Etera-Chereta mode or proforma not found");
                return;
            }

            // Check if timer has expired
            if (!$proforma->isTimerExpired()) {
                Log::info("Auto-selection skipped for proforma {$this->proformaId}: Timer not expired yet");
                return;
            }

            // Get admin settings
            $settings = $this->getAdminSettings();
            
            if (!$settings['auto_selection_enabled']) {
                Log::info("Auto-selection disabled by admin for proforma {$this->proformaId}");
                return;
            }

            // Perform auto-selection
            $this->performAutoSelection($proforma, $settings);

            // Update proforma status to closed since timer expired
            $proforma->update(['status' => 'closed']);

            Log::info("Auto-selection completed for proforma {$this->proformaId}");

        } catch (\Exception $e) {
            Log::error("Error in auto-selection for proforma {$this->proformaId}: " . $e->getMessage());
        }
    }

    private function getAdminSettings()
    {
        return Cache::remember('system_settings', 3600, function () {
            return [
                'auto_selection_enabled' => config('etera.auto_selection_enabled', false),
                'auto_selection_count' => config('etera.auto_selection_count', 3),
                'auto_selection_criteria' => config('etera.auto_selection_criteria', 'lowest_price'),
            ];
        });
    }

    private function performAutoSelection($proforma, $settings)
    {
        $applications = $proforma->applications;
        
        if ($applications->isEmpty()) {
            Log::info("No applications found for proforma {$proforma->id}");
            return;
        }

        // For Etera-Chereta mode, always use lowest_price and top 5
        $criteria = 'lowest_price';
        $selectCount = 5;

        // Calculate scores for each application based on criteria
        $scoredApplications = $this->scoreApplications($applications, $criteria);

        // Sort by score (ascending for lowest price)
        $scoredApplications = $scoredApplications->sortBy('score', SORT_REGULAR, false);

        // Select top N applications
        $selectedCount = min($selectCount, $scoredApplications->count());
        $selectedApplications = $scoredApplications->take($selectedCount);

        // Mark selected applications
        foreach ($selectedApplications as $entry) {
            $application = $entry['application'];
            $application->update([
                'status' => 'selected',
                'selected_at' => now(),
                'selection_method' => 'auto_timer'
            ]);

            // Send notification to selected user
            $this->notifySelectedUser($application);
        }

        // Mark other applications as not selected
        $selectedIds = $selectedApplications->pluck('id');
        $notSelected = $applications->whereNotIn('id', $selectedIds);
        foreach ($notSelected as $application) {
            $application->update([
                'status' => 'not_selected',
                'selection_method' => 'auto_timer'
            ]);

            // Send notification to non-selected user
            $this->notifyNonSelectedUser($application);
        }

        Log::info("Auto-selected {$selectedCount} applications for proforma {$proforma->id}");
    }

    private function scoreApplications($applications, $criteria)
    {
        return $applications->map(function ($application) use ($criteria) {
            $score = 0;

            switch ($criteria) {
                case 'lowest_price':
                    $score = $application->amount ?? 0;
                    break;

                case 'highest_rating':
                    // You can implement rating logic here
                    $score = $application->applicationBy->rating ?? 0;
                    break;

                case 'earliest_submission':
                    $score = $application->created_at->timestamp;
                    break;

                default:
                    $score = $application->amount ?? 0;
            }

            return [
                'application' => $application,
                'score' => $score,
                'id' => $application->id
            ];
        });
    }

    private function notifySelectedUser($application)
    {
        try {
            // Send notification to selected user
            $user = $application->applicationBy;
            
            // You can implement your notification logic here
            // For example, sending email, SMS, or in-app notification
            
            Log::info("User {$user->id} ({$user->name}) selected for proforma {$application->proforma_id}");
            
        } catch (\Exception $e) {
            Log::error("Error notifying selected user: " . $e->getMessage());
        }
    }

    private function notifyNonSelectedUser($application)
    {
        try {
            // Send notification to non-selected user
            $user = $application->applicationBy;
            
            // You can implement your notification logic here
            
            Log::info("User {$user->id} ({$user->name}) not selected for proforma {$application->proforma_id}");
            
        } catch (\Exception $e) {
            Log::error("Error notifying non-selected user: " . $e->getMessage());
        }
    }
}
