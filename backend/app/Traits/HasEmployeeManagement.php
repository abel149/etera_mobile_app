<?php

namespace App\Traits;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Shared employee management methods for owner-type controllers
 * (Shop, Garage, Insurance, BusinessOwner).
 *
 * Requires the consuming class to implement: getOwnerId(): int
 */
trait HasEmployeeManagement
{
    /**
     * GET /employees
     * List all employees registered under this owner.
     */
    public function listEmployees()
    {
        $ownerId   = $this->getOwnerId();
        $employees = User::where('registered_by', $ownerId)
            ->where('role', 'employee')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($employees),
        ]);
    }

    /**
     * POST /employees
     * Create one or multiple employees (max 10 total per owner).
     *
     * Single:  { "name":…, "phone_number":…, "password":…, "password_confirmation":… }
     * Bulk:    { "employees": [ {…}, {…} ] }
     */
    public function createEmployee(Request $request)
    {
        $ownerId      = $this->getOwnerId();
        $currentCount = User::where('registered_by', $ownerId)->where('role', 'employee')->count();
        $isBulk       = $request->has('employees');

        if ($isBulk) {
            $validated = $request->validate([
                'employees'                       => ['required', 'array', 'min:1', 'max:10'],
                'employees.*.name'                => ['required', 'string', 'max:255'],
                'employees.*.phone_number'        => ['required', 'string', 'regex:/^\d{10}$/', 'distinct', 'unique:users,phone_number'],
                'employees.*.email'               => ['nullable', 'email', 'distinct', 'unique:users,email'],
                'employees.*.password'            => ['required', 'string', 'min:6', 'confirmed'],
            ]);
            $newCount = count($validated['employees']);
        } else {
            $validated = $request->validate([
                'name'         => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'regex:/^\d{10}$/', 'unique:users,phone_number'],
                'email'        => ['nullable', 'email', 'unique:users,email'],
                'password'     => ['required', 'string', 'min:6', 'confirmed'],
            ]);
            $newCount = 1;
        }

        if ($currentCount + $newCount > 10) {
            return response()->json([
                'success' => false,
                'message' => "Cannot add {$newCount} employee(s). You have {$currentCount}/10 already. Limit is 10.",
            ], 422);
        }

        $created = [];
        DB::beginTransaction();
        try {
            foreach ($isBulk ? $validated['employees'] : [$validated] as $data) {
                $created[] = User::create([
                    'name'          => $data['name'],
                    'phone_number'  => $data['phone_number'],
                    'email'         => $data['email'] ?? null,
                    'password'      => Hash::make($data['password']),
                    'role'          => 'employee',
                    'approved'      => true,
                    'registered_by' => $ownerId,
                ]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' employee(s) created successfully.',
                'data'    => UserResource::collection(collect($created)),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Employee creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Employee creation failed. Please try again.'], 500);
        }
    }

    /**
     * DELETE /employees/{id}
     * Remove an employee registered under this owner.
     */
    public function deleteEmployee($id)
    {
        $ownerId  = $this->getOwnerId();
        $employee = User::where('id', $id)
            ->where('registered_by', $ownerId)
            ->where('role', 'employee')
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee removed successfully.']);
    }
}
