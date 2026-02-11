<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgUnit;
use App\Models\OrgUnitType;
use App\Models\OrgUnitEmployee;
use App\Models\OrgUnitRole;
use Illuminate\Http\Request;

class OrgUnitController extends Controller
{
    /**
     * Display a listing of org units.
     */
    public function index()
    {
        // Get all org units with their relationships and employees
        $orgUnits = OrgUnit::with(['parent', 'children', 'orgUnitEmployees.employee', 'orgUnitEmployees.orgUnitRole'])->get();

        // Build hierarchical tree structure
        function buildTree($units, $parentId = null, $level = 0)
        {
            $tree = [];
            foreach ($units as $unit) {
                if ($unit->parent_id == $parentId) {
                    $unit->level = $level;
                    $unit->children_count = $units->filter(function ($u) use ($unit) {
                        return $u->parent_id == $unit->id;
                    })->count();
                    $unit->members_count = $unit->orgUnitEmployees->count();
                    $tree[] = $unit;
                    $tree = array_merge($tree, buildTree($units, $unit->id, $level + 1));
                }
            }
            return $tree;
        }

        $orgUnitsTree = collect(buildTree($orgUnits));

        // Get data for member management
        $employees = \App\Models\Employee::active()->get();
        $orgUnitRoles = OrgUnitRole::all();

        return view('admin.org-units.index', compact('orgUnitsTree', 'employees', 'orgUnitRoles'));
    }

    /**
     * Display organization structure with members (with member management).
     */
    public function structure()
    {
        // Get all org units with their relationships and employees (including inactive)
        $orgUnits = OrgUnit::with(['parent', 'children', 'orgUnitEmployees.employee', 'orgUnitEmployees.orgUnitRole'])
            ->get();

        // Build hierarchical tree structure
        function buildTreeWithEmployees($units, $parentId = null, $level = 0)
        {
            $tree = [];
            foreach ($units as $unit) {
                if ($unit->parent_id == $parentId) {
                    $unit->level = $level;
                    $unit->children_count = $units->filter(function ($u) use ($unit) {
                        return $u->parent_id == $unit->id;
                    })->count();
                    $unit->members_count = $unit->orgUnitEmployees->count();
                    $tree[] = $unit;
                    $tree = array_merge($tree, buildTreeWithEmployees($units, $unit->id, $level + 1));
                }
            }
            return $tree;
        }

        $orgUnitsTree = collect(buildTreeWithEmployees($orgUnits));

        // Get data for member management
        $employees = \App\Models\Employee::active()->get();
        $orgUnitRoles = OrgUnitRole::all();

        return view('admin.org-units.structure', compact('orgUnitsTree', 'employees', 'orgUnitRoles'));
    }

    /**
     * DataTables server-side processing.
     */
    public function data(Request $request)
    {
        $query = OrgUnit::with(['parent']);

        // DataTables search
        if ($request->has('search') && $request->search['value'] != '') {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            });
        }

        // Filter by is_active
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        // Get all units for hierarchy calculation
        $allUnits = $query->get();

        // Calculate hierarchy level and parent path for each unit
        $unitsWithHierarchy = $allUnits->map(function ($unit) use ($allUnits) {
            $level = 0;
            $parentPath = [];
            $currentParent = $unit->parent;

            while ($currentParent) {
                $level++;
                array_unshift($parentPath, $currentParent->name);
                $currentParent = $currentParent->parent;
            }

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'parent' => $unit->parent ? $unit->parent->name : 'N/A',
                'parent_id' => $unit->parent_id,
                'is_active' => $unit->is_active,
                'level' => $level,
                'parent_path' => implode(' > ', $parentPath),
                'has_children' => $allUnits->where('parent_id', $unit->id)->count() > 0,
                'children_count' => $allUnits->where('parent_id', $unit->id)->count(),
            ];
        });

        // Filter by parent after hierarchy calculation
        if ($request->has('parent_id') && $request->parent_id != '') {
            $unitsWithHierarchy = $unitsWithHierarchy->where('parent_id', $request->parent_id);
        }

        // Apply pagination after filtering
        $totalRecords = $allUnits->count();
        $filteredRecords = $unitsWithHierarchy->count();

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        // Order by hierarchy (parent before children) then by name
        $unitsWithHierarchy = $unitsWithHierarchy->sortBy([
            ['parent_path', 'asc'],
            ['name', 'asc']
        ]);

        $paginatedUnits = $unitsWithHierarchy->slice($start, $length)->values();

        return response()->json([
            'draw' => intval($request->input('draw', 0)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $paginatedUnits,
        ]);
    }

    /**
     * Show the form for creating a new org unit.
     */
    public function create()
    {
        $orgUnitTypes = OrgUnitType::all();
        $parentUnits = OrgUnit::all();
        $employees = \App\Models\Employee::active()->get();
        $orgUnitRoles = OrgUnitRole::all();
        return view('admin.org-units.create', compact('orgUnitTypes', 'parentUnits', 'employees', 'orgUnitRoles'));
    }

    /**
     * Store a newly created org unit.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:orgunit,id',
            'is_active' => 'nullable|boolean',
            'members' => 'array',
            'members.*.employee_id' => 'required|exists:employee,id',
            'members.*.orgunit_role_id' => 'nullable|exists:orgunit_role,id',
        ]);

        $orgUnit = OrgUnit::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'is_active' => $request->input('is_active', true),
        ]);

        // Add members to the org unit
        if ($request->has('members')) {
            foreach ($request->members as $member) {
                // Check for exclusive roles
                if (!empty($member['orgunit_role_id'])) {
                    $role = OrgUnitRole::find($member['orgunit_role_id']);
                    if ($role && $role->is_exclusive) {
                        $existing = OrgUnitEmployee::where('orgunit_id', $orgUnit->id)
                            ->where('orgunit_role_id', $member['orgunit_role_id'])
                            ->first();
                        if ($existing) {
                            continue; // Skip this member if role is exclusive and already taken
                        }
                    }
                }

                // Check if employee is already assigned to this unit
                $existing = OrgUnitEmployee::where('orgunit_id', $orgUnit->id)
                    ->where('employee_id', $member['employee_id'])
                    ->first();

                if (!$existing) {
                    OrgUnitEmployee::create([
                        'orgunit_id' => $orgUnit->id,
                        'employee_id' => $member['employee_id'],
                        'orgunit_role_id' => $member['orgunit_role_id'] ?? null,
                    ]);
                }
            }
        }

        // Return JSON for AJAX requests
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Org Unit created successfully.',
                'data' => $orgUnit
            ]);
        }

        return redirect()
            ->route('admin.org-units')
            ->with('success', 'Org Unit created successfully.');
    }

    /**
     * Show the form for editing the specified org unit.
     */
    public function edit($id)
    {
        $orgUnit = OrgUnit::with(['parent', 'children'])->find($id);

        if (!$orgUnit) {
            return redirect()
                ->route('admin.org-units')
                ->with('error', 'Org Unit not found.');
        }

        $parentUnits = OrgUnit::where('id', '!=', $id)->get();

        return view('admin.org-units.edit', compact('orgUnit', 'parentUnits'));
    }

    /**
     * Update the specified org unit.
     */
    public function update(Request $request, $id)
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Org Unit not found.'], 404);
            }
            return redirect()
                ->route('admin.org-units')
                ->with('error', 'Org Unit not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:orgunit,id',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent setting parent to self
        if ($request->parent_id == $id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An org unit cannot be its own parent.'], 400);
            }
            return redirect()
                ->route('admin.org-units.edit', $id)
                ->with('error', 'An org unit cannot be its own parent.');
        }

        $orgUnit->update($request->only(['name', 'parent_id', 'is_active']));

        // Return JSON for AJAX requests
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Org Unit updated successfully.',
                'data' => $orgUnit
            ]);
        }

        return redirect()
            ->route('admin.org-units')
            ->with('success', 'Org Unit updated successfully.');
    }

    /**
     * Deactivate the specified org unit.
     */
    public function deactivate($id)
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return redirect()
                ->route('admin.org-units')
                ->with('error', 'Org Unit not found.');
        }

        $orgUnit->update(['is_active' => false]);

        return redirect()
            ->route('admin.org-units')
            ->with('success', 'Org Unit deactivated successfully.');
    }

    /**
     * Activate the specified org unit.
     */
    public function activate($id)
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return redirect()
                ->route('admin.org-units')
                ->with('error', 'Org Unit not found.');
        }

        $orgUnit->update(['is_active' => true]);

        return redirect()
            ->route('admin.org-units')
            ->with('success', 'Org Unit activated successfully.');
    }

    /**
     * Remove the specified org unit.
     */
    public function destroy($id)
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return redirect()
                ->route('admin.org-units')
                ->with('error', 'Org Unit not found.');
        }

        $orgUnit->delete();

        return redirect()
            ->route('admin.org-units')
            ->with('success', 'Org Unit deleted successfully.');
    }

    /**
     * Get all org units for dropdown.
     */
    public function getAll()
    {
        $orgUnits = OrgUnit::active()->get()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
            ];
        });

        return response()->json($orgUnits);
    }

    /**
     * Get employees for a specific org unit.
     */
    public function getEmployees($id)
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json(['error' => 'Org Unit not found.'], 404);
        }

        $employees = $orgUnit->orgUnitEmployees()
            ->with(['employee', 'orgUnitRole'])
            ->get()
            ->map(function ($orgUnitEmployee) {
                return [
                    'id' => $orgUnitEmployee->id,
                    'employee_id' => $orgUnitEmployee->employee_id,
                    'employee_name' => $orgUnitEmployee->employee->name ?? 'N/A',
                    'employee_email' => $orgUnitEmployee->employee->email ?? 'N/A',
                    'employee_position' => $orgUnitEmployee->employee->position ?? 'N/A',
                    'role_id' => $orgUnitEmployee->orgUnitRole->id ?? null,
                    'role_name' => $orgUnitEmployee->orgUnitRole->name ?? 'No Role',
                ];
            });

        return response()->json($employees);
    }

    /**
     * Add an employee to an org unit.
     */
    public function addEmployee(Request $request)
    {
        $request->validate([
            'orgunit_id' => 'required|exists:orgunit,id',
            'employee_id' => 'required|exists:employee,id',
            'orgunit_role_id' => 'nullable|exists:orgunit_role,id',
        ]);

        // Check if employee is already assigned to this org unit
        $existing = OrgUnitEmployee::where('orgunit_id', $request->orgunit_id)
            ->where('employee_id', $request->employee_id)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Employee is already assigned to this org unit.'], 400);
        }

        // Check if role is exclusive and already assigned
        if ($request->orgunit_role_id) {
            $role = OrgUnitRole::find($request->orgunit_role_id);
            if ($role && $role->is_exclusive) {
                $existingRole = OrgUnitEmployee::where('orgunit_id', $request->orgunit_id)
                    ->where('orgunit_role_id', $request->orgunit_role_id)
                    ->first();
                if ($existingRole) {
                    return response()->json(['error' => 'This role is exclusive and already assigned to another employee.'], 400);
                }
            }
        }

        $orgUnitEmployee = OrgUnitEmployee::create([
            'orgunit_id' => $request->orgunit_id,
            'employee_id' => $request->employee_id,
            'orgunit_role_id' => $request->orgunit_role_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee added successfully.',
            'data' => [
                'id' => $orgUnitEmployee->id,
                'employee_id' => $orgUnitEmployee->employee_id,
                'orgunit_role_id' => $orgUnitEmployee->orgunit_role_id,
            ]
        ]);
    }

    /**
     * Update an employee's role in an org unit.
     */
    public function updateEmployee(Request $request, $id)
    {
        $orgUnitEmployee = OrgUnitEmployee::find($id);

        if (!$orgUnitEmployee) {
            return response()->json(['error' => 'Assignment not found.'], 404);
        }

        $request->validate([
            'orgunit_role_id' => 'nullable|exists:orgunit_role,id',
        ]);

        // Check if role is exclusive and already assigned to another employee
        if ($request->orgunit_role_id) {
            $role = OrgUnitRole::find($request->orgunit_role_id);
            if ($role && $role->is_exclusive) {
                $existingRole = OrgUnitEmployee::where('orgunit_id', $orgUnitEmployee->orgunit_id)
                    ->where('orgunit_role_id', $request->orgunit_role_id)
                    ->where('id', '!=', $id)
                    ->first();
                if ($existingRole) {
                    return response()->json(['error' => 'This role is exclusive and already assigned to another employee.'], 400);
                }
            }
        }

        $orgUnitEmployee->update([
            'orgunit_role_id' => $request->orgunit_role_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee role updated successfully.',
        ]);
    }

    /**
     * Remove an employee from an org unit.
     */
    public function removeEmployee($id)
    {
        $orgUnitEmployee = OrgUnitEmployee::find($id);

        if (!$orgUnitEmployee) {
            return response()->json(['error' => 'Assignment not found.'], 404);
        }

        $orgUnitEmployee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee removed successfully.',
        ]);
    }
}
