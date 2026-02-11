<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Okr;
use App\Models\OkrType;
use App\Models\Employee;
use App\Models\OrgUnit;
use App\Models\OrgUnitMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OkrController extends Controller
{
    public function index(Request $request)
    {
        $query = Okr::with(['owner', 'okrType', 'objectives']);

        // Filter by owner type if provided
        if ($request->has('owner_type')) {
            $query->where('owner_type', $request->owner_type);
        }

        // Filter by owner_id if provided
        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Filter by OKR type if provided
        if ($request->has('okr_type_id')) {
            $query->where('okr_type_id', $request->okr_type_id);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter for specific employee
        if ($request->has('employee_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('owner_type', Employee::class)
                  ->where('owner_id', $request->employee_id)
                  ->orWhere('owner_type', OrgUnit::class);
            });
        }

        $okrs = $query->orderBy('created_at', 'desc')->get();

        // Append progress to each OKR
        $okrs->each(function ($okr) {
            // Load objectives with their progress to calculate OKR progress
            $okr->load(['objectives' => function ($query) {
                $query->with(['trackerEmployee', 'approverEmployee']);
            }]);
            $okr->progress = $okr->progress;
        });

        return response()->json([
            'success' => true,
            'data' => $okrs,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:1',
            'okr_type_id' => 'required|integer|exists:okr_type,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'owner_type' => 'required|in:App\\\Models\\\Employee,App\\\Models\\\OrgUnit',
            'owner_id' => 'required|integer',
            'is_active' => 'boolean',
            'objectives' => 'array',
            'objectives.*.description' => 'required|string',
            'objectives.*.weight' => 'required|numeric|min:0|max:1',
            'objectives.*.target_type' => 'required|in:numeric,binary',
            'objectives.*.target_value' => 'required|numeric',
            'objectives.*.deadline' => 'required|date|before:end_date',
            'objectives.*.tracking_type' => 'required|in:daily,weekly,monthly,quarterly',
            'objectives.*.tracker' => 'required|integer|exists:employee,id',
            'objectives.*.approver' => 'required|integer|exists:employee,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate OKR type exists
        $okrType = OkrType::find($request->okr_type_id);
        if (!$okrType) {
            return response()->json([
                'success' => false,
                'message' => 'OKR type not found',
            ], 404);
        }

        // Validate owner exists
        $ownerClass = $request->owner_type;
        if (!$ownerClass::where('id', $request->owner_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Owner not found',
            ], 404);
        }

        $okr = Okr::create([
            'name' => $request->name,
            'weight' => $request->weight,
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'owner_type' => $request->owner_type,
            'owner_id' => $request->owner_id,
            'is_active' => $request->get('is_active', true),
        ]);

        // Create objectives if provided
        if ($request->has('objectives')) {
            foreach ($request->objectives as $objective) {
                $okr->objectives()->create([
                    'description' => $objective['description'],
                    'weight' => $objective['weight'],
                    'target_type' => $objective['target_type'],
                    'target_value' => $objective['target_value'],
                    'deadline' => $objective['deadline'],
                    'tracking_type' => $objective['tracking_type'],
                    'tracker' => $objective['tracker'],
                    'approver' => $objective['approver'],
                ]);
            }
        }

        $okr->load(['owner', 'okrType', 'objectives']);

        // Append progress
        $okr->progress = $okr->progress;

        return response()->json([
            'success' => true,
            'message' => 'OKR created successfully',
            'data' => $okr,
        ], 201);
    }

    public function show($id)
    {
        $okr = Okr::with(['owner', 'okrType', 'objectives.trackerEmployee', 'objectives.approverEmployee'])->find($id);

        if (!$okr) {
            return response()->json([
                'success' => false,
                'message' => 'OKR not found',
            ], 404);
        }

        // Append progress
        $okr->progress = $okr->progress;

        return response()->json([
            'success' => true,
            'data' => $okr,
        ]);
    }

    public function update(Request $request, $id)
    {
        $okr = Okr::find($id);

        if (!$okr) {
            return response()->json([
                'success' => false,
                'message' => 'OKR not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'weight' => 'numeric|min:0|max:1',
            'okr_type_id' => 'integer|exists:okr_type,id',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'owner_type' => [
                'sometimes',
                Rule::in(['App\Models\Employee', 'App\Models\OrgUnit'])
            ],
            'owner_id' => 'integer',
            'is_active' => 'boolean',
            'objectives' => 'array',
            'objectives.*.id' => 'integer|exists:objective,id',
            'objectives.*.description' => 'string',
            'objectives.*.weight' => 'numeric|min:0|max:1',
            'objectives.*.target_type' => 'in:numeric,binary',
            'objectives.*.target_value' => 'numeric',
            'objectives.*.deadline' => 'date',
            'objectives.*.tracking_type' => 'in:daily,weekly,monthly,quarterly',
            'objectives.*.tracker' => 'integer|exists:employee,id',
            'objectives.*.approver' => 'integer|exists:employee,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $okr->update($request->only(['name', 'weight', 'okr_type_id', 'start_date', 'end_date', 'owner_type', 'owner_id', 'is_active']));

        // Update objectives if provided
        if ($request->has('objectives')) {
            foreach ($request->objectives as $objectiveData) {
                if (isset($objectiveData['id'])) {
                    // Update existing objective
                    $objective = \App\Models\Objective::find($objectiveData['id']);
                    if ($objective && $objective->okr_id == $okr->id) {
                        $objective->update([
                            'description' => $objectiveData['description'],
                            'weight' => $objectiveData['weight'],
                            'target_type' => $objectiveData['target_type'],
                            'target_value' => $objectiveData['target_value'],
                            'deadline' => $objectiveData['deadline'],
                            'tracking_type' => $objectiveData['tracking_type'],
                            'tracker' => $objectiveData['tracker'],
                            'approver' => $objectiveData['approver'],
                        ]);
                    }
                }
            }
        }

        $okr->load(['owner', 'okrType', 'objectives']);

        // Append progress
        $okr->progress = $okr->progress;

        return response()->json([
            'success' => true,
            'message' => 'OKR updated successfully',
            'data' => $okr,
        ]);
    }

    public function destroy($id)
    {
        $okr = Okr::find($id);

        if (!$okr) {
            return response()->json([
                'success' => false,
                'message' => 'OKR not found',
            ], 404);
        }

        $okr->delete();

        return response()->json([
            'success' => true,
            'message' => 'OKR deleted successfully',
        ]);
    }

    public function activate($id)
    {
        $okr = Okr::find($id);

        if (!$okr) {
            return response()->json([
                'success' => false,
                'message' => 'OKR not found',
            ], 404);
        }

        $okr->update(['is_active' => true]);
        $okr->load(['objectives']);

        // Append progress
        $okr->progress = $okr->progress;

        return response()->json([
            'success' => true,
            'message' => 'OKR activated successfully',
            'data' => $okr,
        ]);
    }

    public function deactivate($id)
    {
        $okr = Okr::find($id);

        if (!$okr) {
            return response()->json([
                'success' => false,
                'message' => 'OKR not found',
            ], 404);
        }

        $okr->update(['is_active' => false]);
        $okr->load(['objectives']);

        // Append progress
        $okr->progress = $okr->progress;

        return response()->json([
            'success' => true,
            'message' => 'OKR deactivated successfully',
            'data' => $okr,
        ]);
    }

    public function getAvailableOwners()
    {
        $employees = Employee::active()->get(['id', 'name as title', 'email']);
        $orgUnits = OrgUnit::active()->get(['id', 'name as title', 'custom_type']);

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employees,
                'org_units' => $orgUnits,
            ],
        ]);
    }

    public function getByEmployee($employeeId)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Get all org units where this employee is a member
        $orgUnitIds = OrgUnitMember::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->pluck('org_unit_id')
            ->toArray();

        // Get OKRs where:
        // 1. The employee is the owner (owner_type = Employee and owner_id = employeeId)
        // 2. OR the org unit (where employee is a member) is the owner
        $query = Okr::with(['owner', 'okrType', 'objectives' => function ($query) {
            $query->with(['trackerEmployee', 'approverEmployee']);
        }]);

        $query->where(function ($q) use ($employeeId, $orgUnitIds) {
            // Employee is the owner
            $q->where('owner_type', Employee::class)
              ->where('owner_id', $employeeId);

            // Or org unit (where employee is a member) is the owner
            if (!empty($orgUnitIds)) {
                $q->orWhere(function ($subQ) use ($orgUnitIds) {
                    $subQ->where('owner_type', OrgUnit::class)
                          ->whereIn('owner_id', $orgUnitIds);
                });
            }
        });

        $okrs = $query->orderBy('created_at', 'desc')->get();

        // Append progress to each OKR
        $okrs->each(function ($okr) {
            $okr->progress = $okr->progress;
        });

        return response()->json([
            'success' => true,
            'data' => $okrs,
        ]);
    }
}
