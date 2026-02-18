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
        $query = Okr::with(['employee', 'orgUnit', 'okrType', 'objectives']);

        // Filter by OKR type if provided
        if ($request->has('okr_type_id')) {
            $query->where('okr_type_id', $request->okr_type_id);
        }

        // Filter by employee_id if provided
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by orgunit_id if provided
        if ($request->has('orgunit_id')) {
            $query->where('orgunit_id', $request->orgunit_id);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
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
            'employee_id' => 'required_without:orgunit_id|integer|exists:employee,id',
            'orgunit_id' => 'required_without:employee_id|integer|exists:orgunit,id',
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

        // Custom validation: employee_id or orgunit_id must be set based on okr_type
        $validator->after(function ($validator) use ($request) {
            $okrType = OkrType::find($request->okr_type_id);
            if (!$okrType) {
                return;
            }

            if ($okrType->is_employee && empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID is required for this OKR type.');
            }

            if (!$okrType->is_employee && empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID is required for this OKR type.');
            }

            if ($okrType->is_employee && !empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID should not be set for employee OKR type.');
            }

            if (!$okrType->is_employee && !empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID should not be set for org unit OKR type.');
            }
        });

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

        $okr = Okr::create([
            'name' => $request->name,
            'weight' => $request->weight,
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'employee_id' => $request->employee_id,
            'orgunit_id' => $request->orgunit_id,
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

        $okr->load(['employee', 'orgUnit', 'okrType', 'objectives']);

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
        $okr = Okr::with(['employee', 'orgUnit', 'okrType', 'objectives.trackerEmployee', 'objectives.approverEmployee'])->find($id);

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
            'employee_id' => 'sometimes|required_without:orgunit_id|integer|exists:employee,id',
            'orgunit_id' => 'sometimes|required_without:employee_id|integer|exists:orgunit,id',
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

        // Custom validation: employee_id or orgunit_id must be set based on okr_type
        $validator->after(function ($validator) use ($request, $okr) {
            $okrType = OkrType::find($request->okr_type_id ?? $okr->okr_type_id);
            if (!$okrType) {
                return;
            }

            if ($okrType->is_employee && empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID is required for this OKR type.');
            }

            if (!$okrType->is_employee && empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID is required for this OKR type.');
            }

            if ($okrType->is_employee && !empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID should not be set for employee OKR type.');
            }

            if (!$okrType->is_employee && !empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID should not be set for org unit OKR type.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = [
            'name' => $request->name,
            'weight' => $request->weight,
            'okr_type_id' => $request->okr_type_id ?? $okr->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->get('is_active', $okr->is_active),
        ];

        // Only update owner fields if provided
        if ($request->has('employee_id')) {
            $updateData['employee_id'] = $request->employee_id;
            $updateData['orgunit_id'] = null;
        } elseif ($request->has('orgunit_id')) {
            $updateData['orgunit_id'] = $request->orgunit_id;
            $updateData['employee_id'] = null;
        }

        $okr->update($updateData);

        // Update objectives if provided
        if ($request->has('objectives')) {
            foreach ($request->objectives as $objectiveData) {
                if (isset($objectiveData['id']) && $objectiveData['id']) {
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

        $okr->load(['employee', 'orgUnit', 'okrType', 'objectives']);

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
        // 1. The employee is owner (employee_id = employeeId)
        // 2. OR org unit (where employee is a member) is owner
        $query = Okr::with(['okrType', 'employee', 'orgUnit', 'objectives' => function ($query) {
            $query->with(['trackerEmployee', 'approverEmployee']);
        }]);

        $query->where(function ($q) use ($employeeId, $orgUnitIds) {
            // Employee is owner
            $q->where('employee_id', $employeeId);
        });

        if (!empty($orgUnitIds)) {
            // Or org unit (where employee is a member) is the owner
            $query->orWhere(function ($subQ) use ($orgUnitIds) {
                $subQ->whereIn('orgunit_id', $orgUnitIds);
            });
        }

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
