<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level; 
use App\Models\User;

class EmployeeController extends Controller
{
    public function index()
    {
        // Support filtering by role (manager, operator, or all employees)
        $role = request()->get('role', 'all');
        
        $query = \App\Models\User::whereIn('role', ['employee', 'manager', 'operator'])
            ->orderBy('created_at', 'desc');
        
        if ($role === 'manager') {
            $query->where('role', 'manager');
        } elseif ($role === 'operator') {
            $query->where('role', 'operator');
        }
        
        $employees = $query->with(['myManager.manager'])->get();
        
        return view("admin.employees.view", compact('employees', 'role'));
    }

    public function create()
    {
        $levels = \App\Models\Level::all();
        return view("admin.employees.add", compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
          'name' => 'required|unique:employees',
          'email' => 'required|unique:employees',
        ]);
    }












    public function edit($id)
    {
        $employee = User::findOrFail($id);
        $levels = Level::all();
        return view('admin.employees.edit', compact('employee', 'levels'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:employees,name,' . $id,
            'email' => 'required|unique:employees,email,' . $id,
        ]);

        // Update employee details
        $employee = User::findOrFail($id);
        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->phone_number = $request->phone_number;
        $employee->level_id = $request->level_id;
        $employee->save();

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function assignFiles(Request $request, $id)
    {
        $request->validate([
            'file_count' => 'required|integer|min:1|max:100',
        ]);

        $employee = User::findOrFail($id);
        
        // Update the employee's file quota instead of creating empty proforma selections
        $employee->file_quota = $request->file_count;
        $employee->save();

        return redirect()->route('admin.employees.index')->with('success', "{$request->file_count} files assigned to {$employee->name} successfully. Total quota: {$employee->file_quota}");
    }

    public function assignManager(Request $request, $id)
    {
        $request->validate([
            'manager_id' => 'required|exists:users,id',
        ]);

        $operator = User::findOrFail($id);
        $manager = User::findOrFail($request->manager_id);

        if (!$operator->isOperator()) {
            return redirect()->back()->with('error', 'Selected user is not an operator.');
        }

        if (!$manager->isManager()) {
            return redirect()->back()->with('error', 'Selected user is not a manager.');
        }

        // Assign manager (update existing or create new)
        \App\Models\EmployeeManager::updateOrCreate(
            ['employee_id' => $operator->id],
            ['manager_id' => $manager->id]
        );

        return redirect()->back()->with('success', "Manager assigned successfully to {$operator->name}.");
    }















}
