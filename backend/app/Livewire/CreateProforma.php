<?php

namespace App\Livewire;

use App\Models\Proforma;
use App\Models\CarPart;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateProforma extends Component
{
    use WithFileUploads;

    public $file_number;
    public $brand_id;
    public $model;
    public $year;
    public $customer_name;
    public $customer_phone_number;
    public $license_plate_number;
    public $chassis_number;
    public $parts = [
        'id' => [],
        'number' => [],
        'grade' => [],
        'photo' => []
    ];

    protected $rules = [
        'file_number' => 'required',
        'brand_id' => 'required|exists:brands,id',
        'model' => 'required',
        'year' => 'required|numeric',
        'customer_name' => 'required',
        'customer_phone_number' => 'required|starts_with:251|numeric',
        'license_plate_number' => 'required',
        'chassis_number' => 'nullable',
        'parts.id' => 'required|array',
        'parts.id.*' => 'required|exists:car_parts,id',
        'parts.number' => 'required|array',
        'parts.number.*' => 'required|numeric',
        'parts.grade' => 'required|array',
        'parts.grade.*' => 'required|string',
        'parts.photo' => 'nullable|array',
        'parts.photo.*' => 'nullable|image|max:2048',
    ];

    public function createProforma()
    {
        $this->validate();

        $insuranceId = $this->getInsuranceId();

        $proforma = Proforma::create([
            'insurance_id' => $insuranceId,
            'file_number' => $this->file_number,
            'car_brand_id' => $this->brand_id,
            'customer_name' => $this->customer_name,
            'customer_phone_number' => $this->customer_phone_number,
            'license_plate_number' => $this->license_plate_number,
            'chassis_number' => $this->chassis_number,
            'year' => $this->year,
            'model' => $this->model,
        ]);

        foreach ($this->parts['id'] as $index => $partId) {
            $photoPath = isset($this->parts['photo'][$index])
                ? $this->parts['photo'][$index]->store('parts/photos', 'public')
                : null;

            $proforma->parts()->attach($partId, [
                'number' => $this->parts['number'][$index],
                'grade' => $this->parts['grade'][$index],
                'photo' => $photoPath,
            ]);
        }

        session()->flash('success', 'File created successfully.');

        return redirect()->to('/insurance');
    }

    private function getInsuranceId()
    {
        return auth()->check() && auth()->user()->role === 'insurance'
            ? auth()->user()->id
            : User::where('role', 'insurance')->first()->id;
    }

    public function render()
    {
        $carParts = CarPart::all();
        return view('livewire.create-proforma', [
            'carParts' => $carParts,
        ]);
    }
}
