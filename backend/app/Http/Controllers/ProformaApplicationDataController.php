<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Services\ProformaApplicationRegistrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProformaApplicationDataController extends Controller
{
    protected $registrationService;

    public function __construct(ProformaApplicationRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * Register a new application from spare part shop or garage
     */
    public function registerApplication(Request $request, $proformaId)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'parts' => 'nullable|array',
                'parts.*.unit_price' => 'required_with:parts|numeric|min:0',
                'parts.*.quantity' => 'required_with:parts|integer|min:1',
                'parts.*.condition' => 'nullable|string|in:new,used,refurbished',
                'parts.*.country' => 'nullable|string|max:100',
                'parts.*.grade' => 'nullable|string|max:50',
                'shop_rating' => 'nullable|numeric|min:1|max:5',
                'delivery_time' => 'nullable|string|max:100',
                'repair_time' => 'nullable|string|max:100',
                'warranty' => 'nullable|string|max:200',
            ]);

            $data = $request->all();
            $data['parts'] = $request->input('parts', []);

            // Handle file uploads if present
            if ($request->hasFile('images')) {
                $data['media']['images'] = $request->file('images');
            }

            if ($request->hasFile('documents')) {
                $data['media']['documents'] = $request->file('documents');
            }

            if ($request->has('voice_note')) {
                $data['media']['voice_note'] = $request->input('voice_note');
            }

            $result = $this->registrationService->registerApplication(
                $proformaId,
                Auth::id(),
                $data
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'application_id' => $result['application']->id,
                    'proforma_status' => $result['proforma_status']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error("Error registering application: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while registering the application'
            ], 500);
        }
    }

    /**
     * Get comprehensive application data for insurance/business owners
     */
    public function getApplicationData(Request $request, $proformaId)
    {
        try {
            $proforma = Proforma::findOrFail($proformaId);
            
            // Check if user has permission to view this proforma
            if (!Auth::user()->isAdmin() && $proforma->poster_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to proforma data'
                ], 403);
            }

            $summary = $this->registrationService->getApplicationSummary($proformaId);

            if (!$summary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to retrieve application data'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error("Error retrieving application data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving application data'
            ], 500);
        }
    }

    /**
     * Export application data in various formats
     */
    public function exportApplicationData(Request $request, $proformaId)
    {
        try {
            $proforma = Proforma::findOrFail($proformaId);
            
            // Check if user has permission to export this proforma
            if (!Auth::user()->isAdmin() && $proforma->poster_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to proforma data'
                ], 403);
            }

            $format = $request->get('format', 'json');
            $data = $this->registrationService->exportApplicationData($proformaId, $format);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to export application data'
                ], 404);
            }

            $filename = "proforma_{$proforma->file_number}_applications_{$format}_" . now()->format('Y-m-d_H-i-s');

            switch ($format) {
                case 'csv':
                    return response($data)
                        ->header('Content-Type', 'text/csv')
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
                
                case 'pdf':
                    return response($data)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.pdf\"");
                
                default:
                    return response()->json([
                        'success' => true,
                        'data' => $data
                    ]);
            }

        } catch (\Exception $e) {
            Log::error("Error exporting application data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while exporting application data'
            ], 500);
        }
    }

    /**
     * Get application statistics for dashboard
     */
    public function getApplicationStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Proforma::with(['applications.applicationBy']);

            // Filter by user role
            if ($user->role === 'insurance') {
                $query->where('poster_id', $user->id);
            } elseif ($user->role === 'business_owner') {
                $query->where('poster_id', $user->id);
            } elseif ($user->role === 'admin') {
                // Admin can see all proformas
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $statistics = [
                'total_proformas' => $query->count(),
                'pending_proformas' => $query->where('status', 'pending')->count(),
                'completed_proformas' => $query->where('status', 'completed')->count(),
                'closed_proformas' => $query->where('status', 'closed')->count(),
                'etera_chereta_proformas' => $query->where('required_number_of_shops', 0)->count(),
                'total_applications' => 0,
                'total_value' => 0,
                'average_response_time' => 0,
            ];

            // Get applications data
            $applications = $query->with('applications')->get()->pluck('applications')->flatten();
            $statistics['total_applications'] = $applications->count();
            $statistics['total_value'] = $applications->sum('amount');

            // Calculate average response time
            $responseTimes = $applications->map(function ($app) {
                return $app->created_at->diffInHours($app->proforma->created_at);
            })->filter();
            
            if ($responseTimes->count() > 0) {
                $statistics['average_response_time'] = round($responseTimes->avg(), 2);
            }

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error("Error retrieving application statistics: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving statistics'
            ], 500);
        }
    }

    /**
     * Get real-time application updates
     */
    public function getRealTimeUpdates(Request $request)
    {
        try {
            $user = Auth::user();
            $lastUpdate = $request->get('last_update', now()->subMinutes(5)->toISOString());

            $query = Proforma::with(['applications.applicationBy'])
                ->where('updated_at', '>=', $lastUpdate);

            // Filter by user role
            if ($user->role === 'insurance') {
                $query->where('poster_id', $user->id);
            } elseif ($user->role === 'business_owner') {
                $query->where('poster_id', $user->id);
            } elseif ($user->role === 'admin') {
                // Admin can see all updates
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $updates = $query->get()->map(function ($proforma) {
                return [
                    'proforma_id' => $proforma->id,
                    'file_number' => $proforma->file_number,
                    'status' => $proforma->status,
                    'updated_at' => $proforma->updated_at,
                    'new_applications' => $proforma->applications()
                        ->where('created_at', '>=', $proforma->updated_at)
                        ->count(),
                    'total_applications' => $proforma->applications()->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $updates,
                'last_update' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error("Error retrieving real-time updates: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving updates'
            ], 500);
        }
    }
} 