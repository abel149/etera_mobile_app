<?php

namespace App\Livewire;

use App\Models\EmployeeManager;
use App\Models\Level; 
use App\Models\User as Employee;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class EditEmployee extends Component
{
    public $employee; // Employee to edit
    public $name;
    public $phone_number;
    public $email;
    public $password;
    public $password_confirmation;
    public $role_id;
    public $manager_id;
    public $roles;
    public $managers = [];
    public $selectedRole = null;

    private $rules = [
        'name' => 'required|string|max:255',
        'phone_number' => 'required|numeric|unique:users,phone_number',
        'email' => 'required|email|unique:users,email',
        'password' => 'nullable|min:8',
        'role_id' => 'required|exists:levels,id',
        'manager_id' => 'nullable|exists:users,id',
    ];

    public function mount($employeeId)
    {
        // Retrieve the employee to edit
        $this->employee = Employee::findOrFail($employeeId);

        // Initialize the form fields with the employee data
        $this->name = $this->employee->name;
        $this->phone_number = $this->employee->phone_number;
        $this->email = $this->employee->email;
        $this->role_id = $this->employee->level_id;
        $this->manager_id = $this->employee->manager_id;

        // Fetch roles
        $this->roles = Level::all();

        // Set selected role
        $this->selectedRole = Level::find($this->role_id);

        // Fetch managers based on the selected role
        $this->updatedRoleId($this->role_id);
    }

    public function updatedRoleId($roleId)
    {
        $this->selectedRole = Level::find($roleId);
    
        if ($this->selectedRole) {
            $this->managers = Employee::where('level_id', $this->selectedRole->rank - 1)
                ->where('role', Employee::ROLE_EMPLOYEE)
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $this->managers = [];
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules);
    }

    public function submit()
    {
        $this->validate($this->rules);

        // Ensure the selected role is updated
        $this->selectedRole = Level::find($this->role_id);

        if (!$this->selectedRole) {
            session()->flash('error', 'Invalid role selected.');
            return;
        }

        // Determine level_id based on role
        $level_id = ($this->selectedRole->name === 'Manager') ? 1 : 2;

        // If it's an operator (level 2), a manager is required
        if ($level_id == 2 && !$this->manager_id) {
            $this->rules['manager_id'] = 'required';
            $this->validate($this->rules);
            return;
        }

        // Update the employee record
        $this->employee->update([
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'password' => $this->password ? Hash::make($this->password) : $this->employee->password,
            'level_id' => $level_id,
        ]);

        // Update manager if applicable
        if ($level_id == 2 && $this->manager_id) {
            EmployeeManager::updateOrCreate(
                ['employee_id' => $this->employee->id],
                ['manager_id' => $this->manager_id]
            );
        }

        session()->flash('message', 'Employee updated successfully!');
        return redirect()->route('admin.employees.index');
    }

    public function render()
    {
        return view('livewire.edit-employee');
    }
}
