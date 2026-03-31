<?php

use Illuminate\Support\Facades\Route;
use App\Models\Proforma;
use App\Models\ProformaSelection;

// =====================
// Manager Middleware - Redirect non-managers
// =====================
Route::middleware(['auth'])->group(function () {
    
    // =====================
    // Manager Routes
    // =====================
    Route::prefix('manager')
        ->middleware(['auth'])
        ->name('manager.')
        ->group(function () {
            
            // Dashboard
            Route::get('/dashboard', function () {
                $manager = auth()->user();
                
                if (!$manager->isManager()) {
                    return redirect()->route('login')->with('error', 'Unauthorized access');
                }
                
                $operators = $manager->getOperators();
                $operatorIds = $operators->pluck('id')->toArray();
                
                // Get closed proformas processed by operators managed by this manager
                // Pending: closed proformas that haven't been reviewed yet
                $pendingFilesCount = Proforma::where('status', 'payment collected')
                    ->whereHas('selections', function($q) use ($operatorIds) {
                        $q->whereIn('employee_id', $operatorIds)
                          ->where(function($subQ) {
                              $subQ->whereNull('review_status')
                                   ->orWhere('review_status', 'pending');
                          });
                    })
                    ->count();
                
                // Approved: closed proformas that have been approved
                $approvedFilesCount = Proforma::where('status', 'closed')
                    ->whereHas('selections', function($q) use ($operatorIds) {
                        $q->whereIn('employee_id', $operatorIds)
                          ->where('review_status', 'approved');
                    })
                    ->count();
                    
                // Rejected: closed proformas that have been rejected
                $rejectedFilesCount = Proforma::where('status', 'closed')
                    ->whereHas('selections', function($q) use ($operatorIds) {
                        $q->whereIn('employee_id', $operatorIds)
                          ->where('review_status', 'rejected');
                    })
                    ->count();
                
                return view('manager.dashboard', compact(
                    'operators',
                    'pendingFilesCount',
                    'approvedFilesCount',
                    'rejectedFilesCount'
                ));
            })->name('dashboard');
            
            // View operators
            Route::get('/operators', [\App\Http\Controllers\ManagerController::class, 'viewAssignedOperators'])
                ->name('operators');
            Route::get('/operators/{operator}/files', [\App\Http\Controllers\ManagerController::class, 'viewOperatorFiles'])
                ->name('operators.files');
            
            // Operators list for manager
            Route::get('/proformas', function () {
                $manager = auth()->user();
                
                if (!$manager->isManager()) {
                    return redirect()->route('login')->with('error', 'Unauthorized access');
                }
                
                $operators = $manager->getOperators();
                
                // Add statistics for each operator
                $operators = $operators->map(function ($operator) {
                    $operator->total_quota = $operator->file_quota ?? 0;
                    $operator->used_quota = $operator->proformaSelections()->where('active', true)->count();
                    $operator->available_quota = $operator->getAvailableFileQuota();
                    
                    // Count processed files (closed proformas)
                    $operator->processed_files_count = Proforma::where('status', 'closed')
                        ->whereHas('selections', function($q) use ($operator) {
                            $q->where('employee_id', $operator->id);
                        })
                        ->count();
                    
                    // Count total files assigned (all proformas with selections)
                    $operator->total_files_assigned = $operator->proformaSelections()->count();
                    
                    $operator->total_commissions = $operator->getEarnedCommissions();
                    $operator->pending_commissions = $operator->getPendingCommissions();
                    $operator->approved_commissions = $operator->getApprovedCommissions();
                    
                    return $operator;
                });
                
                return view('manager.proformas.index', compact('operators'));
            })->name('proformas.index');
            
            // Manager ONLY: Send back to owner
            Route::post('/proformas/{proforma}/send-back', function (Proforma $proforma) {
                $manager = auth()->user();
                
                if (!$manager->isManager()) {
                    return redirect()->back()->with('error', 'Unauthorized action');
                }
                
                $proforma->update(['status' => 'returned']);

                // Send database notification (bell icon) to poster
                try {
                    if ($proforma->poster) {
                        $proforma->poster->notify(
                            new \App\Notifications\ProformaSentToOwnerNotification($proforma)
                        );
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Send-back notification failed', ['error' => $e->getMessage()]);
                }

                // Send Telegram notification to poster
                try {
                    if ($proforma->poster && !empty($proforma->poster->telegram_chat_id)) {
                        (new \App\Services\TelegramService())->sendSentToOwnerNotification(
                            $proforma->poster->telegram_chat_id,
                            $proforma
                        );
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Telegram send-back notification failed', [
                        'proforma_id' => $proforma->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                return redirect()->back()->with('success', 'Proforma sent back to owner successfully');
            })->name('proformas.send-back');
            
            // Review routes
            Route::get('/review/pending', [\App\Http\Controllers\ManagerController::class, 'reviewPendingFiles'])
                ->name('review.pending');
            Route::post('/review/{selection}/approve', [\App\Http\Controllers\ManagerController::class, 'approveFile'])
                ->name('review.approve');
            Route::post('/review/{selection}/reject', [\App\Http\Controllers\ManagerController::class, 'rejectFile'])
                ->name('review.reject');
            
            // Legacy route
            Route::post('/proforma/{proforma}/send-back', [\App\Http\Controllers\ManagerController::class, 'sendBackToUser'])
                ->name('proforma.send-back');

            // Assign files (quota) to operator
            Route::post('/operators/{id}/assign-files', function (\Illuminate\Http\Request $request, $id) {
                $manager = auth()->user();
                
                if (!$manager->isManager()) {
                    return redirect()->back()->with('error', 'Unauthorized action');
                }

                $request->validate([
                    'file_count' => 'required|integer|min:1|max:100',
                ]);

                $operator = \App\Models\User::findOrFail($id);

                // Ensure this operator belongs to this manager
                $operatorIds = $manager->getOperators()->pluck('id')->toArray();
                if (!in_array($operator->id, $operatorIds)) {
                    return redirect()->back()->with('error', 'This operator is not assigned to you');
                }

                $operator->file_quota = $request->file_count;
                $operator->save();

                return redirect()->back()->with('success', "{$request->file_count} files assigned to {$operator->name} successfully. Total quota: {$operator->file_quota}");
            })->name('operators.assign-files');
        });

    // =====================
    // Operator Routes
    // =====================
    Route::prefix('operator')
        ->middleware(['auth'])
        ->name('operator.')
        ->group(function () {
            
            // Dashboard
            Route::get('/dashboard', function () {
                $operator = auth()->user();
                
                if (!$operator->isOperator()) {
                    return redirect()->route('login')->with('error', 'Unauthorized access');
                }
                
                $stats = [
                    'total_quota' => $operator->file_quota ?? 0,
                    'used_quota' => $operator->proformaSelections()->where('active', true)->count(),
                    'available_quota' => $operator->getAvailableFileQuota(),
                    'total_commissions' => $operator->getEarnedCommissions(),
                    'pending_commissions' => $operator->getPendingCommissions(),
                    'approved_commissions' => $operator->getApprovedCommissions(),
                ];
                
                $recentFiles = $operator->proformaSelections()
                    ->with('proforma')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
                
                return view('operator.dashboard', compact('stats', 'recentFiles'));
            })->name('dashboard');
            
            // Proformas list for operator
            Route::get('/proformas', function () {
                $operator = auth()->user();
                
                if (!$operator->isOperator()) {
                    return redirect()->route('login')->with('error', 'Unauthorized access');
                }
                
                // Get proformas assigned to this operator
                $proformas = Proforma::whereHas('selections', function($q) use ($operator) {
                    $q->where('employee_id', $operator->id);
                })->with(['poster', 'brand'])
                  ->orderBy('created_at', 'desc')
                  ->paginate(20);
                
                // Available statuses for operator (EXCLUDING 'returned')
                $availableStatuses = [
                    'pending' => 'Pending',
                    'published' => 'Published',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'closed' => 'Closed',
                    'rejected' => 'Rejected',
                ];
                
                return view('operator.proformas.index', compact('proformas', 'availableStatuses'));
            })->name('proformas.index');

            // Take Files (Fill Quota)
            Route::post('/proformas/take', function () {
                $operator = auth()->user();
                
                if (!$operator->isOperator()) {
                    return redirect()->back()->with('error', 'Unauthorized action');
                }
                
                $quota = $operator->file_quota ?? 0;
                // Count active files (not returned)
                $activeFiles = $operator->proformaSelections()
                    ->whereHas('proforma', function($q) {
                        $q->where('status', '=', '!completed');
                    })
                    ->where('active', true)
                    ->count();
                
                $remaining = $quota - $activeFiles;
                
                if ($remaining <= 0) {
                    return redirect()->back()->with('error', 'Quota already full');
                }
                
                // Find available proformas (pending/published and not assigned)
                $availableProformas = Proforma::whereIn('status', ['pending'])
                    ->doesntHave('selections')
                    ->orderBy('created_at', 'asc')
                    ->limit($remaining)
                    ->get();
                
                if ($availableProformas->isEmpty()) {
                    return redirect()->back()->with('warning', 'No available files to take');
                }
                
                $count = 0;
                foreach ($availableProformas as $proforma) {
                    ProformaSelection::create([
                        'proforma_id' => $proforma->id,
                        'employee_id' => $operator->id,
                        'active' => true,
                    ]);
                    $count++;
                }
                
                return redirect()->back()->with('success', "Successfully took {$count} files.");
            })->name('proformas.take');

            // Operator: View Proforma Details
            Route::get('/proformas/{proforma}', function (Proforma $proforma) {
                $operator = auth()->user();
                
                if (!$operator->isOperator()) {
                    return redirect()->route('login')->with('error', 'Unauthorized access');
                }
                
                // Ensure the proforma is assigned to this operator or is available
                // For now, we allow viewing if they have the link, or we could restrict it.
                // Let's restrict to assigned or available (pending/published)
                
                return view('operator.proformas.details', compact('proforma'));
            })->name('proforma.show');
            
            // Operator: Change status (CANNOT use 'returned')
            Route::post('/proformas/{proforma}/status', function (Proforma $proforma, \Illuminate\Http\Request $request) {
                $operator = auth()->user();
                
                if (!$operator->isOperator()) {
                    return redirect()->back()->with('error', 'Unauthorized action');
                }
                
                $request->validate([
                    'status' => 'required|string|in:pending,published,in_progress,completed,closed,rejected',
                ]);
                
                // Prevent operators from using 'returned' status
                if ($request->status === 'returned') {
                    return redirect()->back()->with('error', 'Only managers can send proforma back to owner');
                }
                
                $proforma->update(['status' => $request->status]);
                
                return redirect()->back()->with('success', 'Status updated to ' . ucfirst($request->status));
            })->name('proformas.status');
            
            // Commissions
            Route::get('/commissions', function () {
                $operator = auth()->user();
                
                if (!$operator->isOperator()) {
                    return redirect()->route('login')->with('error', 'Unauthorized access');
                }
                
                $commissions = $operator->processedFiles()
                    ->with(['proforma', 'reviewedBy'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                return view('operator.commissions', compact('commissions'));
            })->name('commissions');

            // Balance / Transactions (Ledger)
            Route::get('/balance', [\App\Http\Controllers\UserBalanceController::class, 'index'])
                ->name('balance');
        });
});
