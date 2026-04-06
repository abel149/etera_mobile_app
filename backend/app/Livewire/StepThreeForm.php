<?php

namespace App\Livewire;

use Livewire\Component;

class StepThreeForm extends Component
{
    public $parts;
    public $spare_part_partners;
    public $garage_partners;

    public function mount()
    {
        $this->parts = \App\Models\CarPart::all();
        $this->spare_part_partners = auth()->user()->sparePartPartners();
        $this->garage_partners = auth()->user()->garagePartners();
    }

    public function render()
    {
        return view('livewire.step-three-form');
    }
}
