<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount('employees')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Role::withCount('employees');

        // Search
        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where('name', 'like', "%{$searchValue}%");
        }

        // Ordering
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];
            $columnNames = ['name', 'employees_count'];
            $columnName = $columnNames[$orderColumn] ?? 'name';
            $query->orderBy($columnName, $orderDirection);
        }

        // Pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $totalRecords = $query->count();
        $roles = $query->skip($start)->take($length)->get();

        // Format data
        $data = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'employees_count' => $role->employees_count,
            ];
        });

        return response()->json([
            'draw' => intval($request->draw ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:role',
        ]);

        Role::create([
            'name' => $request->name,
        ]);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
            ]);
        }

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.',
                ], 404);
            }

            return redirect()
                ->route('admin.roles')
                ->with('error', 'Role not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:role,name,' . $id,
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
            ]);
        }

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Request $request, $id)
    {
        $role = Role::with('employees')->find($id);

        if (!$role) {
            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.',
                ], 404);
            }

            return redirect()
                ->route('admin.roles')
                ->with('error', 'Role not found.');
        }

        // Check if role has employees
        if ($role->employees()->count() > 0) {
            $message = 'Cannot delete role. It has ' . $role->employees()->count() . ' employee(s) assigned.';

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 400);
            }

            return redirect()
                ->route('admin.roles')
                ->with('error', $message);
        }

        $role->delete();

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Activate the specified role.
     */
    public function activate(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.',
                ], 404);
            }
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Role not found.');
        }

        $role->update(['is_active' => true]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role activated successfully.',
            ]);
        }

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role activated successfully.');
    }

    /**
     * Deactivate the specified role.
     */
    public function deactivate(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.',
                ], 404);
            }
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Role not found.');
        }

        $role->update(['is_active' => false]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deactivated successfully.',
            ]);
        }

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role deactivated successfully.');
    }
}
