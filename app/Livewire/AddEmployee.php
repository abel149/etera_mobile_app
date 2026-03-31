<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Level;
use App\Models\EmployeeManager;
use Illuminate\Support\Facades\Hash;

class AddEmployee extends Component
{
    public $name;
    public $phone_number;
    public $email;
    public $password;
    public $password_confirmation;
    public $role_id;
    public $manager_id;

    public $roles;
    public $managers = [];

    protected $rules = [
        'name'         => 'required|string|max:255',
        'phone_number' => 'required|numeric|unique:users,phone_number',
        'email'        => 'required|email|unique:users,email',
        'password'     => 'nullable|min:6|confirmed',
        'role_id'      => 'required|exists:levels,id',
        'manager_id'   => 'nullable|exists:users,id',
    ];

    public function mount()
    {
        $this->roles = Level::orderBy('id')->get();
    }

    /**
     * Livewire v3 way to react to role change
     */
    public function updated($property, $value)
    {
        if ($property === 'role_id') {
            $this->manager_id = null;
            $this->loadManagers();
        }
    }

    /**
     * Load ONLY real managers
     */
    private function loadManagers(): void
    {
        $this->managers = User::where('role', User::ROLE_MANAGER)
            ->where('approved', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Submit form
     */
    public function submit()
    {
        $this->validate();

        $level = Level::findOrFail($this->role_id);

        /**
         * Determine USER ROLE from LEVEL NAME
         * (safe + explicit)
         */
        $role = match (strtolower($level->name)) {
            'manager'  => User::ROLE_MANAGER,
            'operator' => User::ROLE_OPERATOR,
            default    => User::ROLE_EMPLOYEE,
        };

        // Operator must have a manager
        if ($role === User::ROLE_OPERATOR && !$this->manager_id) {
            $this->addError('manager_id', 'Manager is required for operators.');
            return;
        }

        $password = $this->password ?: '123456';

        $user = User::create([
            'name'         => $this->name,
            'phone_number' => $this->phone_number,
            'email'        => $this->email,
            'password'     => Hash::make($password),
            'role'         => $role,
            'level_id'     => $level->id,
            'approved'     => true,
        ]);

        // Attach operator → manager
        if ($role === User::ROLE_OPERATOR) {
            EmployeeManager::create([
                'employee_id' => $user->id,
                'manager_id'  => $this->manager_id,
            ]);
        }

        session()->flash('message', 'Employee added successfully!');
        return redirect()->route('admin.employees.index');
    }

    public function render()
    {
        return view('livewire.add-employee');
    }
}
