<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgUnitRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrgUnitRoleController extends Controller
{
    /**
     * Display a listing of the org unit roles.
     */
    public function index(): JsonResponse
    {
        $orgUnitRoles = OrgUnitRole::with('orgUnitEmployees.employee', 'orgUnitEmployees.orgUnit')->get();
        return response()->json([
            'success' => true,
            'data' => $orgUnitRoles
        ]);
    }

    /**
     * Get org unit roles for datatables.
     */
    public function datatables(Request $request): JsonResponse
    {
        $query = OrgUnitRole::query();

        // Search functionality
        if ($request->has('search') && $request->search['value'] != '') {
            $searchValue = $request->search['value'];
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        // Order column mapping
        $orderColumn = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $columnMapping = [
            0 => 'id',
            1 => 'name',
            2 => 'created_at',
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
        $orgUnitRoles = $query->offset($start)->limit($length)->get();

        // Get count of employees for each role
        $orgUnitRoles->loadCount('orgUnitEmployees');

        // Format data for datatables
        $data = $orgUnitRoles->map(function ($orgUnitRole) {
            return [
                'id' => $orgUnitRole->id,
                'name' => $orgUnitRole->name,
                'employees_count' => $orgUnitRole->org_unit_employees_count ?? 0,
                'created_at' => $orgUnitRole->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $orgUnitRole->updated_at?->format('Y-m-d H:i:s'),
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
     * Store a newly created org unit role.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:orgunit_role,name',
        ]);

        $orgUnitRole = OrgUnitRole::create([
            'name' => $request->name,
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
        ]);

        $orgUnitRole->update($request->only(['name']));

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
