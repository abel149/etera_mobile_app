<?php

namespace App\Livewire;

use App\Models\Proforma;
use App\Models\ProformaApplication;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AllProformasList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filters = [
        'license'   => '',
        'type'      => 'default',
        'component' => 'Both',
        'car_type'  => 'All',
        'grade'     => 'All',
    ];

    public $sortBy = 'desc';

    public function updating($name, $value)
    {
        if (str_starts_with($name, 'filters.')) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->filters = [
            'license'   => '',
            'type'      => 'default',
            'component' => 'Both',
            'car_type'  => 'All',
            'grade'     => 'All',
        ];

        $this->resetPage();
    }

    public function render()
    {
        $user   = Auth::user();
        $userId = $user->id;

        /**
         * Base Query
         */
        $query = Proforma::query()
            ->where('status', 'published');

        /**
         * ✅ Brand filter — ONLY brands accepted by logged-in user
         */
        /**
 * Brand filter — uses car_brand_id (correct column)
 */
$acceptedBrandIds = $user->brands()->pluck('brands.id')->toArray();

if (!empty($acceptedBrandIds)) {
    $query->whereIn('car_brand_id', $acceptedBrandIds);
} else {
    // No brands assigned → return empty result
    $query->whereRaw('1 = 0');
}


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
         * Filter: Poster Type
         */
        switch ($this->filters['type']) {
            case 'insurance':
                $query->whereHas('poster', fn ($q) =>
                    $q->where('role', 'insurance')
                );
                break;

            case 'others':
                $query->whereHas('poster', fn ($q) =>
                    $q->whereIn('role', ['business_owner', 'garage', 'individual'])
                );
                break;
        }

        /**
         * Filter: License Plate OR Phone
         */
        if (!empty($this->filters['license'])) {
            $search = trim($this->filters['license']);

            $query->where(function ($q) use ($search) {
                $q->where('license_plate_number', 'like', "%{$search}%")
                  ->orWhereHas('poster', fn ($q2) =>
                      $q2->where('customer_phone_number', 'like', "%{$search}%")
                  );
            });
        }

        /**
         * Filter: Component
         */
        if ($this->filters['component'] !== 'Both') {
            $query->whereIn('id', function ($sub) {
                $sub->select('proforma_id')
                    ->from('proforma_part')
                    ->where('component', $this->filters['component']);
            });
        }

        /**
         * Filter: Car Type
         */
        if ($this->filters['car_type'] !== 'All') {
            $query->where('car_type', $this->filters['car_type']);
        }

        /**
         * Filter: Grade (partial match from proforma_part)
         */
        if ($this->filters['grade'] !== 'All') {
            $query->whereIn('id', function ($sub) {
                $sub->select('proforma_id')
                    ->from('proforma_part')
                    ->where('grade', 'LIKE', '%' . $this->filters['grade'] . '%');
            });
        }

        /**
         * Sort & Paginate
         */
        $proformas = $query
            ->orderBy('created_at', $this->sortBy)
            ->paginate(10);

        return view('livewire.all-proformas-list', [
            'proformas'  => $proformas,
            'components' => ['Both', 'Body Parts', 'Mechanical Parts'],
        ]);
    }
}
