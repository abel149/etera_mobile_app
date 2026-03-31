<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EditInsurance extends Component
{
    public $insuranceId;
    public $name;
    public $email;
    public $phone_number;
    public $password; // Optional field for a new password
    public $showModal = false; // Track if the modal is open

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone_number' => 'required|string|max:15',
        'password' => 'nullable|string|min:8', // Optional validation
    ];

    public function mount($insuranceId = null)
    {
        if ($insuranceId) {
            $this->insuranceId = $insuranceId;
            $insurance = User::findOrFail($insuranceId);
            $this->name = $insurance->name;
            $this->email = $insurance->email;
            $this->phone_number = $insurance->phone_number;
        }
    }

    public function openEditModal($id)
    {
        $this->insuranceId = $id; // Set the ID
        $insurance = User::findOrFail($id);
        $this->name = $insurance->name;
        $this->email = $insurance->email;
        $this->phone_number = $insurance->phone_number;

        // Show the modal
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        $insurance = User::findOrFail($this->insuranceId);
        $insurance->name = $this->name;
        $insurance->email = $this->email;
        $insurance->phone_number = $this->phone_number;

        // Update the password if provided
        if ($this->password) {
            $insurance->password = Hash::make($this->password);
        }

        $insurance->save();

        $this->showModal = false; // Hide the modal after save
        session()->flash('success', 'Insurance user updated successfully!');
        return redirect()->route('marketer.insurances.index'); // Redirect after save
    }

    public function render()
    {
        return view('livewire.edit-insurance');
    }
}