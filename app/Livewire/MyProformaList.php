<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class MyProformaList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter = '';

    public $sortBy = 'desc';

    public $selectedProformas = [];

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $query = auth()->user()->proformaSelections()->with('proforma');

        if (! empty($this->search)) {
            $query->whereHas('proforma', function($q) {
                $q->where('file_number', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_name', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_phone_number', 'like', '%'.$this->search.'%')
                  ->orWhere('license_plate_number', 'like', '%'.$this->search.'%');
            });
        }

        if (! empty($this->filter)) {
            $query->whereHas('proforma', function($q) {
                $q->where('status', $this->filter);
            });
        }

        $query->orderBy('created_at', $this->sortBy);

        return view('livewire.my-proforma-list', [
            'proformas' => $query->with(['proforma.poster', 'proforma.brand'])->paginate(10),
        ]);
    }
}
