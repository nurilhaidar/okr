<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgUnit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}
