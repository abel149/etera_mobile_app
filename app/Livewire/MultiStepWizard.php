<?php

namespace App\Livewire;

use Livewire\Component;

class MultiStepWizard extends Component
{
    public $currentStep = 1; //default
    public $step = 1;
    public $totalSteps = 4;
    public $parts;

    public function mount()
    {
        $this->parts = \App\Models\CarPart::all();
    }

    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep = $this->currentStep + 1;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep = $this->currentStep - 1;
        }
    }

    public function setStep($step)
    {
        $this->currentStep = $step;
    }

    public function render()
    {
        return view('livewire.multi-step-wizard');
    }
}
