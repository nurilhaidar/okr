<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgUnit;
use App\Models\OrgUnitEmployee;
use App\Models\OrgUnitRole;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrgUnitController extends Controller
{
    /**
     * Display a listing of the org units.
     */
    public function index(): JsonResponse
    {
        $orgUnits = OrgUnit::with(['type', 'parent', 'children'])->get();
        return response()->json([
            'success' => true,
            'data' => $orgUnits
        ]);
    }

    /**
     * Get org units for datatables.
     */
    public function datatables(Request $request): JsonResponse
    {
        $query = OrgUnit::with(['type', 'parent']);

        // Search functionality
        if ($request->has('search') && $request->search['value'] != '') {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('custom_type', 'like', '%' . $searchValue . '%');
            });
        }

        // Filter by orgunit_type_id
        if ($request->has('orgunit_type_id') && $request->orgunit_type_id != '') {
            $query->where('orgunit_type_id', $request->orgunit_type_id);
        }

        // Filter by is_active
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        // Filter by parent_id
        if ($request->has('parent_id') && $request->parent_id != '') {
            $query->where('parent_id', $request->parent_id);
        }

        // Order column mapping
        $orderColumn = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $columnMapping = [
            0 => 'id',
            1 => 'name',
            2 => 'custom_type',
            3 => 'orgunit_type_id',
            4 => 'parent_id',
            5 => 'is_active',
            6 => 'created_at',
        ];

        if (isset($columnMapping[$orderColumn])) {
            $query->orderBy($columnMapping[$orderColumn], $orderDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $totalRecords = $query->count();
        $orgUnits = $query->offset($start)->limit($length)->get();

        // Format data for datatables
        $data = $orgUnits->map(function ($orgUnit) {
            return [
                'id' => $orgUnit->id,
                'name' => $orgUnit->name,
                'custom_type' => $orgUnit->custom_type,
                'orgunit_type_id' => $orgUnit->orgunit_type_id,
                'orgunit_type_name' => $orgUnit->type?->name ?? '-',
                'parent_id' => $orgUnit->parent_id,
                'parent_name' => $orgUnit->parent?->name ?? '-',
                'is_active' => $orgUnit->is_active,
                'created_at' => $orgUnit->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $orgUnit->updated_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
        ]);
    }

    /**
     * Store a newly created org unit.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'custom_type' => 'nullable|string|max:100',
            'orgunit_type_id' => 'nullable|exists:orgunit_type,id',
            'parent_id' => 'nullable|exists:orgunit,id',
            'is_active' => 'boolean',
        ]);

        $orgUnit = OrgUnit::create([
            'name' => $request->name,
            'custom_type' => $request->custom_type,
            'orgunit_type_id' => $request->orgunit_type_id,
            'parent_id' => $request->parent_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OrgUnit created successfully',
            'data' => $orgUnit->load(['type', 'parent'])
        ], 201);
    }

    /**
     * Display the specified org unit.
     */
    public function show($id): JsonResponse
    {
        $orgUnit = OrgUnit::with(['type', 'parent', 'children', 'employees'])->find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $orgUnit
        ]);
    }

    /**
     * Update the specified org unit.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'custom_type' => 'nullable|string|max:100',
            'orgunit_type_id' => 'nullable|exists:orgunit_type,id',
            'parent_id' => 'nullable|exists:orgunit,id',
            'is_active' => 'boolean',
        ]);

        $orgUnit->update($request->only(['name', 'custom_type', 'orgunit_type_id', 'parent_id', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'OrgUnit updated successfully',
            'data' => $orgUnit->load(['type', 'parent'])
        ]);
    }

    /**
     * Deactivate the specified org unit.
     */
    public function deactivate($id): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $orgUnit->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'OrgUnit deactivated successfully',
            'data' => $orgUnit
        ]);
    }

    /**
     * Activate the specified org unit.
     */
    public function activate($id): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $orgUnit->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'OrgUnit activated successfully',
            'data' => $orgUnit
        ]);
    }

    /**
     * Remove the specified org unit.
     */
    public function destroy($id): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $orgUnit->delete();

        return response()->json([
            'success' => true,
            'message' => 'OrgUnit deleted successfully'
        ]);
    }

    /**
     * Get available roles for adding members.
     * Returns all roles with information about which ones are already taken.
     */
    public function getAvailableRoles($id): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        // Get all roles
        $allRoles = OrgUnitRole::all();

        // Get taken roles (roles where is_exclusive = true)
        $takenRoles = OrgUnitEmployee::where('orgunit_id', $id)
            ->whereIn('orgunit_role_id', function ($query) {
                $query->select('id')->from('orgunit_role')
                    ->where('is_exclusive', true);
            })
            ->pluck('orgunit_role_id')
            ->unique()
            ->toArray();

        // Get existing members
        $existingMembers = OrgUnitEmployee::where('orgunit_id', $id)
            ->with(['employee', 'orgUnitRole'])
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'employee_id' => $member->employee_id,
                    'employee_name' => $member->employee->name,
                    'orgunit_role_id' => $member->orgunit_role_id,
                    'role_name' => $member->orgUnitRole->name,
                ];
            });

        $data = $allRoles->map(function ($role) use ($takenRoles) {
            $isTaken = in_array($role->id, $takenRoles);

            return [
                'id' => $role->id,
                'name' => $role->name,
                'is_exclusive' => (bool) $role->is_exclusive,
                'is_taken' => $isTaken,
                'available' => !($role->is_exclusive && $isTaken),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $data,
                'existing_members' => $existingMembers,
            ]
        ]);
    }

    /**
     * Add a member to the org unit.
     */
    public function addMember(Request $request, $id): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $request->validate([
            'employee_id' => 'required|exists:employee,id',
            'orgunit_role_id' => 'required|exists:orgunit_role,id',
        ]);

        // Check if employee is already a member
        $existingMember = OrgUnitEmployee::where('orgunit_id', $id)
            ->where('employee_id', $request->employee_id)
            ->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'message' => 'Employee is already a member of this org unit'
            ], 422);
        }

        // Check if the role is exclusive
        $role = OrgUnitRole::find($request->orgunit_role_id);

        if ($role->is_exclusive) {
            // Check if this role is already taken
            $existingRole = OrgUnitEmployee::where('orgunit_id', $id)
                ->where('orgunit_role_id', $request->orgunit_role_id)
                ->first();

            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => "This role ({$role->name}) is already assigned to another member"
                ], 422);
            }
        }

        // Add the member
        $orgUnitEmployee = OrgUnitEmployee::create([
            'orgunit_id' => $id,
            'employee_id' => $request->employee_id,
            'orgunit_role_id' => $request->orgunit_role_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully',
            'data' => $orgUnitEmployee->load(['employee', 'orgUnitRole'])
        ], 201);
    }

    /**
     * Update member role in the org unit.
     */
    public function updateMemberRole(Request $request, $id, $memberId): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $orgUnitEmployee = OrgUnitEmployee::where('orgunit_id', $id)
            ->where('id', $memberId)
            ->first();

        if (!$orgUnitEmployee) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found in this org unit'
            ], 404);
        }

        $request->validate([
            'orgunit_role_id' => 'required|exists:orgunit_role,id',
        ]);

        // Check if the role is exclusive and already taken
        $role = OrgUnitRole::find($request->orgunit_role_id);

        if ($role->is_exclusive) {
            $existingRole = OrgUnitEmployee::where('orgunit_id', $id)
                ->where('orgunit_role_id', $request->orgunit_role_id)
                ->where('id', '!=', $memberId)
                ->first();

            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => "This role ({$role->name}) is already assigned to another member"
                ], 422);
            }
        }

        $orgUnitEmployee->update([
            'orgunit_role_id' => $request->orgunit_role_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member role updated successfully',
            'data' => $orgUnitEmployee->load(['employee', 'orgUnitRole'])
        ]);
    }

    /**
     * Remove a member from the org unit.
     */
    public function removeMember($id, $memberId): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $orgUnitEmployee = OrgUnitEmployee::where('orgunit_id', $id)
            ->where('id', $memberId)
            ->first();

        if (!$orgUnitEmployee) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found in this org unit'
            ], 404);
        }

        $orgUnitEmployee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully'
        ]);
    }

    /**
     * Get all members of an org unit with their roles.
     * Sorted by role priority (exclusive roles first).
     */
    public function getMembers($id): JsonResponse
    {
        $orgUnit = OrgUnit::find($id);

        if (!$orgUnit) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnit not found'
            ], 404);
        }

        $members = OrgUnitEmployee::where('orgunit_id', $id)
            ->with(['employee', 'orgUnitRole'])
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'employee_id' => $member->employee_id,
                    'employee_name' => $member->employee->name,
                    'employee_email' => $member->employee->email,
                    'employee_position' => $member->employee->position,
                    'orgunit_role_id' => $member->orgunit_role_id,
                    'role_name' => $member->orgUnitRole->name,
                    'is_exclusive' => (bool) $member->orgUnitRole->is_exclusive,
                ];
            })
            ->sortBy(function ($member) {
                // Sort by is_exclusive (descending) then by role_name (ascending)
                return [!(int) $member['is_exclusive'], $member['role_name']];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }
}
