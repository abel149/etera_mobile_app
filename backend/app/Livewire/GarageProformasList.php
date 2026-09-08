<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use Illuminate\Support\Facades\Auth;

class GarageProformasList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    /**
     * Filters
     */
    public $filters = [
        'license'   => '',
        'component' => 'Both',
        'car_type'  => 'All',
        'grade'     => 'All',
        'damage_severity' => 'All',
    ];

    public $sortBy = 'desc';

    /**
     * Reset pagination when filters change
     */
    public function updating($name, $value)
    {
        if (str_starts_with($name, 'filters.')) {
            $this->resetPage();
        }
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->filters = [
            'license'   => '',
            'component' => 'Both',
            'car_type'  => 'All',
            'grade'     => 'All',
            'damage_severity' => 'All',
        ];

        $this->resetPage();
    }

    /**
     * Render component
     */
    public function render()
    {
        $userId = Auth::id();

        /**
         * Base Query
         * Only published proformas from insurance
         */
        $isTest = Auth::user()->is_test ?? false;

        $isDualService = Auth::user()->shop_garage == 1;

        $query = Proforma::query()
            ->where('status', 'published')
            ->whereHas('poster', function ($q) use ($isTest) {
                $q->whereIn('role', ['insurance', 'insurance_agent'])
                  ->where(function ($q2) use ($isTest) {
                      if ($isTest) {
                          $q2->where('is_test', true);
                      } else {
                          $q2->where(fn($q3) => $q3->where('is_test', false)->orWhereNull('is_test'));
                      }
                  });
            })
            ->where(function ($q) use ($isDualService) {
                // Always exclude shop-only proformas (garages never handle these).
                // Exclude dual-service proformas too, unless this garage is dual-service enabled.
                $excludedTypes = $isDualService ? ['insurance_shop_only'] : ['insurance_shop_only', 'insurance_shop_garage'];
                $q->whereNull('proforma_type')
                  ->orWhereNotIn('proforma_type', $excludedTypes);
            });

        /**
         * Exclude already applied proformas
         */
        $appliedProformaIds = ProformaApplication::where('application_by', $userId)
            ->pluck('proforma_id')
            ->toArray();

        if (!empty($appliedProformaIds)) {
            $query->whereNotIn('id', $appliedProformaIds);
        }

        /**
         * Filter: License Plate
         */
        if (!empty($this->filters['license'])) {
            $search = trim($this->filters['license']);
            $query->where('license_plate_number', 'like', "%{$search}%");
        }

        /**
         * Filter: Component Type
         */
        if ($this->filters['component'] !== 'Both') {
            $query->whereIn('id', function ($sub) {
                $sub->select('proforma_id')
                    ->from('proforma_part')
                    ->where('component', $this->filters['component']);
            });
        }

        /**
         * Filter: Car Type (ICE / EV / Hybrid)
         * Assumes car_type column exists in proformas table
         */
        if ($this->filters['car_type'] !== 'All') {
            $query->where('car_type', $this->filters['car_type']);
        }

        /**
         * Filter: Grade (VARCHAR stored in proforma_part)
         * Uses LIKE to match:
         * "2nd" → "2nd Grade(After market)"
         */
        if ($this->filters['grade'] !== 'All') {
            $query->whereIn('id', function ($sub) {
                $sub->select('proforma_id')
                    ->from('proforma_part')
                    ->where('grade', 'LIKE', '%' . $this->filters['grade'] . '%');
            });
        }

        /**
         * Filter: Damage Severity
         */
        if ($this->filters['damage_severity'] !== 'All') {
            $query->where('damage_severity', $this->filters['damage_severity']);
        }

        /**
         * Sort & Paginate
         */
        $proformas = $query
            ->orderBy('created_at', $this->sortBy)
            ->paginate(10);

        return view('livewire.garage-proformas-list', [
            'proformas' => $proformas,
            'components' => ['Both', 'Body Parts', 'Mechanical Parts'],
        ]);
    }
}
