<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['role']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $searchValue = $request->search;
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('username', 'like', '%' . $searchValue . '%')
                    ->orWhere('position', 'like', '%' . $searchValue . '%');
            });
        }

        // Filter by is_active
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        // Filter by role_id
        if ($request->has('role_id') && $request->role_id != '') {
            $query->where('role_id', $request->role_id);
        }

        // Order by
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $totalRecords = $query->count();
        $employees = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $employees,
            'pagination' => [
                'total' => $totalRecords,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($totalRecords / $limit),
                'from' => ($page - 1) * $limit + 1,
                'to' => min($page * $limit, $totalRecords),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
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

    public function show($id): JsonResponse
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

    public function update(Request $request, $id): JsonResponse
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

    public function deactivate($id): JsonResponse
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

    public function activate($id): JsonResponse
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

    public function destroy($id): JsonResponse
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
