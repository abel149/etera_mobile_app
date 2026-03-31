<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Level;

class OperatorRoleForm extends Component
{
    public $name;
    public $rank;
    public $status_label;

    protected $rules = [
        'name' => 'required|string|max:255|unique:levels',
        'rank' => 'required|integer|min:1|unique:levels',
        'status_label' => 'required|string|max:255',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function submit()
    {
        $this->validate();

        Level::create([
            'name' => $this->name,
            'rank' => $this->rank,
            'status_label' => $this->status_label,
        ]);

        session()->flash('success', 'Role Added Successfully');
        return redirect()->to('admin/roles');
    }

    public function render()
    {
        return view('livewire.operator-role-form');
    }
}
