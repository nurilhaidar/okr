<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgUnitRole;
use Illuminate\Http\Request;

class OrgUnitRoleController extends Controller
{
    /**
     * Display a listing of org unit roles.
     */
    public function index()
    {
        $orgUnitRoles = OrgUnitRole::withCount('orgUnitEmployees')->orderBy('name')->get();
        return view('admin.org-unit-roles.index', compact('orgUnitRoles'));
    }

    /**
     * Store a newly created org unit role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:orgunit_role',
            'is_exclusive' => 'boolean',
        ]);

        OrgUnitRole::create([
            'name' => $request->name,
            'is_exclusive' => $request->has('is_exclusive'),
        ]);

        return redirect()
            ->route('admin.org-unit-roles')
            ->with('success', 'Org Unit Role created successfully.');
    }

    /**
     * Update the specified org unit role.
     */
    public function update(Request $request, $id)
    {
        $orgUnitRole = OrgUnitRole::find($id);

        if (!$orgUnitRole) {
            return redirect()
                ->route('admin.org-unit-roles')
                ->with('error', 'Org Unit Role not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:orgunit_role,name,' . $id,
            'is_exclusive' => 'boolean',
        ]);

        $orgUnitRole->update([
            'name' => $request->name,
            'is_exclusive' => $request->has('is_exclusive'),
        ]);

        return redirect()
            ->route('admin.org-unit-roles')
            ->with('success', 'Org Unit Role updated successfully.');
    }

    /**
     * Remove the specified org unit role.
     */
    public function destroy($id)
    {
        $orgUnitRole = OrgUnitRole::find($id);

        if (!$orgUnitRole) {
            return redirect()
                ->route('admin.org-unit-roles')
                ->with('error', 'Org Unit Role not found.');
        }

        if ($orgUnitRole->orgUnitEmployees()->count() > 0) {
            return redirect()
                ->route('admin.org-unit-roles')
                ->with('error', 'Cannot delete role. It has ' . $orgUnitRole->orgUnitEmployees()->count() . ' member(s) assigned.');
        }

        $orgUnitRole->delete();

        return redirect()
            ->route('admin.org-unit-roles')
            ->with('success', 'Org Unit Role deleted successfully.');
    }

    /**
     * Get all org unit roles for dropdown.
     */
    public function getAll()
    {
        $roles = OrgUnitRole::orderBy('name')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        });

        return response()->json($roles);
    }
}
