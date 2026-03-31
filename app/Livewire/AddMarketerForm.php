<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User as Marketer;
use Illuminate\Support\Facades\Hash;

class AddMarketerForm extends Component
{
    public $name;
    public $phone_number;
    public $email;
    public $password;
    public $password_confirmation;

    private $rules = [
        'name' => 'required|string|max:255',
        'phone_number' => 'required|numeric|unique:users,phone_number',
        'email' => 'required|email|unique:users,email',
        'password' => 'nullable|min:6|confirmed', // now optional and min length 6
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules);
    }

    public function submit()
    {
        $this->validate($this->rules);

        // Default password if null
        $password = $this->password ?: '123456';

        $marketer = Marketer::create([
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'password' => Hash::make($password),
            'role' => 'marketer',
        ]);

        session()->flash('message', 'Marketer added successfully!');
        return redirect()->to('/admin/marketers');
    }

    public function render()
    {
        return view('livewire.add-marketer-form');
    }
}
