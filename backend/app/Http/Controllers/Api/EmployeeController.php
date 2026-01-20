<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['role'])->get();
        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employee',
            'username' => 'required|string|max:255|unique:employee',
            'password' => 'required|string|min:8',
            'position' => 'nullable|string|max:255',
            'role_id' => 'nullable|exists:role,id',
            'is_active' => 'boolean',
        ]);

        $employee = Employee::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'position' => $request->position,
            'role_id' => $request->role_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => $employee->load(['role'])
        ], 201);
    }

    public function show($id)
    {
        $employee = Employee::with(['role', 'orgUnits.type', 'orgUnits.parent'])->find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:employee,email,' . $id,
            'username' => 'sometimes|required|string|max:255|unique:employee,username,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'position' => 'nullable|string|max:255',
            'role_id' => 'nullable|exists:role,id',
            'is_active' => 'boolean',
        ]);

        $employee->update($request->only(['name', 'email', 'username', 'position', 'role_id', 'is_active']));

        if ($request->has('password')) {
            $employee->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee->load(['role'])
        ]);
    }

    public function deactivate($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $employee->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Employee deactivated successfully',
            'data' => $employee
        ]);
    }

    public function activate($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $employee->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Employee activated successfully',
            'data' => $employee
        ]);
    }

    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }
}
