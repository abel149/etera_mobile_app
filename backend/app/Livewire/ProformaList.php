<?php

namespace App\Livewire;

use App\Models\Proforma;
use App\Models\ProformaSelection;
use Livewire\Component;
use Livewire\WithPagination;

class ProformaList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter = '';

    public $mode = 'admin'; // 'admin' or 'operator'

    public $sortBy = 'desc';

    public $selectedProformas = [];

    public $showLogsModal = false;
    public $activityLogs = [];
    public $selectedProformaId = null;

    protected $paginationTheme = 'bootstrap';

    public function viewLogs($proformaId)
    {
        $this->selectedProformaId = $proformaId;
        $this->activityLogs = \App\Models\ProformaActivityLog::where('proforma_id', $proformaId)
            ->with('user')
            ->latest()
            ->get();
        $this->showLogsModal = true;
    }

    public function closeLogsModal()
    {
        $this->showLogsModal = false;
        $this->activityLogs = [];
        $this->selectedProformaId = null;
    }

    public function takeFiles()
    {
        // TODO: change the status of the proforma to Assigned
        foreach ($this->selectedProformas as $selected) {
            ProformaSelection::create([
                'proforma_id' => $selected,
                'employee_id' => auth()->user()->id,
            ]);
        }
    }

    public function render()
    {
        $query = Proforma::fromInsurances()->whereHas('poster');

        // Admin mode: show pending to all, non-pending only to the admin who processed them.
        // Also show proformas with no processor (null processed_by) so auto-closed proformas
        // that were never floated (processed_by never set) remain visible to all admins.
        if ($this->mode === 'admin') {
            $query->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhere('processed_by', auth()->id())
                  ->orWhereNull('processed_by');
            });
        }

        // Filter for operator mode
        if ($this->mode === 'operator') {
            $user = auth()->user();
            $query->whereHas('selections', function($q) use ($user) {
                $q->where('employee_id', $user->id);
            });
        }

        if (! empty($this->search)) {
            // Map friendly terms to DB proforma_type values
            $typeKeywords = [
                'dual'         => 'insurance_shop_garage',
                'shop only'    => 'insurance_shop_only',
                'garage only'  => 'insurance_garage_only',
                'shop garage'  => 'insurance_shop_garage',
                'etera'        => null,
            ];
            $searchLower = strtolower(trim($this->search));

            $query->where(function($q) use ($searchLower, $typeKeywords) {
                $q->where('file_number', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_name', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_phone_number', 'like', '%'.$this->search.'%')
                  ->orWhere('license_plate_number', 'like', '%'.$this->search.'%')
                  ->orWhere('proforma_type', 'like', '%'.$this->search.'%')
                  ->orWhereHas('poster', function($pq) {
                      $pq->where('name', 'like', '%'.$this->search.'%');
                  });
                if (array_key_exists($searchLower, $typeKeywords)) {
                    $mapped = $typeKeywords[$searchLower];
                    if ($mapped === null) {
                        $q->orWhereNull('proforma_type'); // etera chereta has no type
                    } else {
                        $q->orWhere('proforma_type', $mapped);
                    }
                }
            });
        }

        // Optional: Apply additional filters
        if (! empty($this->filter)) {
            $query->where('status', $this->filter);
        }

        $query->orderBy('created_at', $this->sortBy);

        return view('livewire.proforma-list', [
            'proformas' => $query
                ->with(['poster.parentInsurance', 'brand'])
                ->withCount([
                    'inboxes as shop_inboxes_count' => function ($q) {
                        $q->whereHas('user', function ($u) {
                            $u->where('role', 'shop');
                        });
                    },
                    'applications as shop_applications_count' => function ($q) {
                        $q->where('from', 'shop');
                    },
                ])
                ->paginate(10),
        ]);
    }
}
