<?php

namespace App\Livewire;

use App\Models\Proforma;
use App\Models\ProformaSelection;
use Livewire\Component;
use Livewire\WithPagination;

class OthersProformaList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter = '';

    public $sortBy = 'desc';

    public $selectedProformas = [];

    protected $paginationTheme = 'bootstrap';

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
        $query = Proforma::fromOthers()->whereHas('poster');

        // Admin: show pending to all, non-pending only to the admin who processed them
        if (auth()->user()->role === 'admin') {
            $query->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhere('status', 'rejected')
                  ->orWhere('processed_by', auth()->id());
            });
        }

        if (! empty($this->search)) {
            $query->where(function($q) {
                $q->where('file_number', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_name', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_phone_number', 'like', '%'.$this->search.'%')
                  ->orWhere('license_plate_number', 'like', '%'.$this->search.'%');
            });
        }

        if (! empty($this->filter)) {
            $query->where('status', $this->filter);
        }

        $query->orderBy('created_at', $this->sortBy);

        return view('livewire/others-proforma-list', [
            'proformas' => $query->with(['poster', 'brand'])->paginate(10),
        ]);
    }
}
