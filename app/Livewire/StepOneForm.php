<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Brand;

class StepOneForm extends Component
{
    public $file_number = '';
    public $brand_id = '';
    public $model = '';
    public $year = '';

    public $brands;

    public function mount()
    {
        $this->brands = Brand::all();
    }

    public function rules()
    {
        return [
            'file_number' => 'required|unique:proformas,file_number',
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required',
            'year' => 'required|numeric|min:1900|max:' . date('Y'),
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function render()
    {
        return view('livewire.step-one-form');
    }

    public function previousStep()
    {
    }

    public function nextStep()
    {
        $this->validate();

    }
}
