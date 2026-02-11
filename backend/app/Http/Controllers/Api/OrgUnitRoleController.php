<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgUnitRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrgUnitRoleController extends Controller
{
    /**
     * Display a listing of the org unit roles with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = OrgUnitRole::with('orgUnitEmployees.employee', 'orgUnitEmployees.orgUnit');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $searchValue = $request->search;
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        // Filter by is_exclusive
        if ($request->has('is_exclusive') && $request->is_exclusive != '') {
            $query->where('is_exclusive', $request->is_exclusive);
        }

        // Order by
        $orderBy = $request->input('order_by', 'name');
        $orderDirection = $request->input('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $totalRecords = $query->count();
        $orgUnitRoles = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        // Get count of employees for each role
        $orgUnitRoles->loadCount('orgUnitEmployees');

        // Add employees_count to each role
        $orgUnitRoles->each(function ($role) {
            $role->employees_count = $role->org_unit_employees_count ?? 0;
        });

        return response()->json([
            'success' => true,
            'data' => $orgUnitRoles,
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

    /**
     * Store a newly created org unit role.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:orgunit_role,name',
            'is_exclusive' => 'boolean',
        ]);

        $orgUnitRole = OrgUnitRole::create([
            'name' => $request->name,
            'is_exclusive' => $request->is_exclusive ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OrgUnitRole created successfully',
            'data' => $orgUnitRole
        ], 201);
    }

    /**
     * Display the specified org unit role.
     */
    public function show($id): JsonResponse
    {
        $orgUnitRole = OrgUnitRole::with(['orgUnitEmployees.employee', 'orgUnitEmployees.orgUnit'])->find($id);

        if (!$orgUnitRole) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnitRole not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $orgUnitRole
        ]);
    }

    /**
     * Update the specified org unit role.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $orgUnitRole = OrgUnitRole::find($id);

        if (!$orgUnitRole) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnitRole not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:orgunit_role,name,' . $id,
            'is_exclusive' => 'boolean',
        ]);

        $orgUnitRole->update($request->only(['name', 'is_exclusive']));

        return response()->json([
            'success' => true,
            'message' => 'OrgUnitRole updated successfully',
            'data' => $orgUnitRole
        ]);
    }

    /**
     * Remove the specified org unit role.
     */
    public function destroy($id): JsonResponse
    {
        $orgUnitRole = OrgUnitRole::find($id);

        if (!$orgUnitRole) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnitRole not found'
            ], 404);
        }

        // Check if there are employees using this role
        if ($orgUnitRole->orgUnitEmployees()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete OrgUnitRole because it has associated employees'
            ], 422);
        }

        $orgUnitRole->delete();

        return response()->json([
            'success' => true,
            'message' => 'OrgUnitRole deleted successfully'
        ]);
    }
}
