<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        // Get all roles for filter dropdown
        $roles = Role::all();

        return view('admin.employees.index', compact('roles'));
    }

    /**
     * DataTables server-side processing.
     */
    public function data(Request $request)
    {
        $query = Employee::with(['role']);

        // DataTables search
        if ($request->has('search') && $request->search['value'] != '') {
            $searchValue = $request->search['value'];
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

        // Get total records before filtering
        $totalRecords = Employee::count();

        // Get total filtered records
        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];

            $columns = ['name', 'email', 'username', 'position', 'role', 'is_active'];
            if (isset($columns[$orderColumn])) {
                $column = $columns[$orderColumn];
                if ($column === 'role') {
                    $query->with('role')->orderByRole($orderDirection);
                } else {
                    $query->orderBy($column, $orderDirection);
                }
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $employees = $query->offset($start)->limit($length)->get();

        // Format data for DataTables
        $data = [];
        foreach ($employees as $employee) {
            $data[] = [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'username' => $employee->username,
                'position' => $employee->position ?? 'N/A',
                'role' => $employee->role ? $employee->role->name : 'No Role',
                'role_id' => $employee->role_id,
                'is_active' => $employee->is_active,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw', 0)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.employees.create', compact('roles'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employee',
            'username' => 'required|string|max:255|unique:employee',
            'password' => 'required|string|min:8|confirmed',
            'position' => 'nullable|string|max:255',
            'role_id' => 'nullable|exists:role,id',
            'is_active' => 'required|in:0,1',
        ]);

        $employee = Employee::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'position' => $request->position,
            'role_id' => $request->role_id,
            'is_active' => (bool) $request->is_active,
        ]);

        return redirect()
            ->route('admin.employees')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee = Employee::with(['role', 'orgUnits.type', 'orgUnits.parent'])->find($id);

        if (!$employee) {
            return redirect()
                ->route('admin.employees')
                ->with('error', 'Employee not found.');
        }

        $roles = Role::all();

        return view('admin.employees.edit', compact('employee', 'roles'));
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return redirect()
                ->route('admin.employees')
                ->with('error', 'Employee not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employee,email,' . $id,
            'username' => 'required|string|max:255|unique:employee,username,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'position' => 'nullable|string|max:255',
            'role_id' => 'nullable|exists:role,id',
            'is_active' => 'required|in:0,1',
        ]);

        $employee->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'position' => $request->position,
            'role_id' => $request->role_id,
            'is_active' => (bool) $request->is_active,
        ]);

        if ($request->has('password') && $request->password) {
            $employee->update(['password' => Hash::make($request->password)]);
        }

        return redirect()
            ->route('admin.employees')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Deactivate the specified employee.
     */
    public function deactivate(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }
            return redirect()
                ->route('admin.employees')
                ->with('error', 'Employee not found.');
        }

        $employee->update(['is_active' => false]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee deactivated successfully.'
            ]);
        }

        return redirect()
            ->route('admin.employees')
            ->with('success', 'Employee deactivated successfully.');
    }

    /**
     * Activate the specified employee.
     */
    public function activate(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }
            return redirect()
                ->route('admin.employees')
                ->with('error', 'Employee not found.');
        }

        $employee->update(['is_active' => true]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee activated successfully.'
            ]);
        }

        return redirect()
            ->route('admin.employees')
            ->with('success', 'Employee activated successfully.');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }
            return redirect()
                ->route('admin.employees')
                ->with('error', 'Employee not found.');
        }

        // Prevent users from deleting themselves
        if ($employee->id === auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }
            return redirect()
                ->route('admin.employees')
                ->with('error', 'You cannot delete your own account.');
        }

        $employee->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.'
            ]);
        }

        return redirect()
            ->route('admin.employees')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Get all employees for dropdown.
     */
    public function getAll()
    {
        $employees = Employee::active()->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
            ];
        });

        return response()->json($employees);
    }
}
