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

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $role = Role::with('employees')->find($id);

        if (!$role) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Role not found.');
        }

        // Check if role has employees
        if ($role->employees()->count() > 0) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Cannot delete role. It has ' . $role->employees()->count() . ' employee(s) assigned.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role deleted successfully.');
    }
}
