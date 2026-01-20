<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgUnitType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrgUnitTypeController extends Controller
{
    /**
     * Display a listing of the org unit types.
     */
    public function index(): JsonResponse
    {
        $orgUnitTypes = OrgUnitType::with('orgUnits')->get();
        return response()->json([
            'success' => true,
            'data' => $orgUnitTypes
        ]);
    }

    /**
     * Get org unit types for datatables.
     */
    public function datatables(Request $request): JsonResponse
    {
        $query = OrgUnitType::query();

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
        $orgUnitTypes = $query->offset($start)->limit($length)->get();

        // Get count of org units for each type
        $orgUnitTypes->loadCount('orgUnits');

        // Format data for datatables
        $data = $orgUnitTypes->map(function ($orgUnitType) {
            return [
                'id' => $orgUnitType->id,
                'name' => $orgUnitType->name,
                'org_units_count' => $orgUnitType->org_units_count ?? 0,
                'created_at' => $orgUnitType->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $orgUnitType->updated_at?->format('Y-m-d H:i:s'),
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
