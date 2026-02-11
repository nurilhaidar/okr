<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgUnitType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrgUnitTypeController extends Controller
{
    /**
     * Display a listing of the org unit types with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = OrgUnitType::with('orgUnits');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $searchValue = $request->search;
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        // Order by
        $orderBy = $request->input('order_by', 'name');
        $orderDirection = $request->input('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $totalRecords = $query->count();
        $orgUnitTypes = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        // Get count of org units for each type
        $orgUnitTypes->loadCount('orgUnits');

        // Add org_units_count to each type
        $orgUnitTypes->each(function ($type) {
            $type->org_units_count = $type->org_units_count ?? 0;
        });

        return response()->json([
            'success' => true,
            'data' => $orgUnitTypes,
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
     * Store a newly created org unit type.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:orgunit_type,name',
        ]);

        $orgUnitType = OrgUnitType::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OrgUnitType created successfully',
            'data' => $orgUnitType
        ], 201);
    }

    /**
     * Display the specified org unit type.
     */
    public function show($id): JsonResponse
    {
        $orgUnitType = OrgUnitType::with(['orgUnits.parent', 'orgUnits.type'])->find($id);

        if (!$orgUnitType) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnitType not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $orgUnitType
        ]);
    }

    /**
     * Update the specified org unit type.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $orgUnitType = OrgUnitType::find($id);

        if (!$orgUnitType) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnitType not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:orgunit_type,name,' . $id,
        ]);

        $orgUnitType->update($request->only(['name']));

        return response()->json([
            'success' => true,
            'message' => 'OrgUnitType updated successfully',
            'data' => $orgUnitType
        ]);
    }

    /**
     * Remove the specified org unit type.
     */
    public function destroy($id): JsonResponse
    {
        $orgUnitType = OrgUnitType::find($id);

        if (!$orgUnitType) {
            return response()->json([
                'success' => false,
                'message' => 'OrgUnitType not found'
            ], 404);
        }

        // Check if there are org units using this type
        if ($orgUnitType->orgUnits()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete OrgUnitType because it has associated OrgUnits'
            ], 422);
        }

        $orgUnitType->delete();

        return response()->json([
            'success' => true,
            'message' => 'OrgUnitType deleted successfully'
        ]);
    }
}
