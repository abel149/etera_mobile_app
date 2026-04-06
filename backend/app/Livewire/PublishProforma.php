<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class PublishProforma extends Component
{
    public $selectedInsuranceShop = null;
    public $selectedClientShop1 = null;
    public $selectedClientShop2 = null;

    public $selectedInsuranceGarage = null;
    public $selectedClientGarage1 = null;
    public $selectedClientGarage2 = null;
    public $proforma;

    public function mount($proforma)
    {
        $this->proforma = $proforma;
    }

    public function updated($propertyName)
    {
    }

    public function render()
    {

        $shops = User::where('role', 'shop')->orderBy('name', 'asc')->get();
        $garages = User::where('role', 'garage')->orderBy('name', 'asc')->get();

        return view('livewire.publish-proforma', compact('shops', 'garages'));
    }
}
