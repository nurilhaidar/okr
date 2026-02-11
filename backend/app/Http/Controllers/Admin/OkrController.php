<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Okr;
use App\Models\OkrType;
use App\Models\Objective;
use App\Models\Employee;
use App\Models\OrgUnit;
use Illuminate\Http\Request;

class OkrController extends Controller
{
    /**
     * Display a listing of OKRs.
     */
    public function index()
    {
        $okrs = Okr::with(['okrType', 'owner', 'objectives.checkIns', 'objectives.checkIns.approvalLogs'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.okrs.index', compact('okrs'));
    }

    /**
     * Show the form for creating a new OKR.
     */
    public function create()
    {
        $okrTypes = OkrType::all();
        $employees = Employee::active()->get();
        $orgUnits = OrgUnit::active()->get();

        return view('admin.okrs.create', compact('okrTypes', 'employees', 'orgUnits'));
    }

    /**
     * Show the form for editing the specified OKR.
     */
    public function edit($id)
    {
        $okr = Okr::with(['okrType', 'owner', 'objectives.trackerEmployee', 'objectives.approverEmployee'])->find($id);

        if (!$okr) {
            return redirect()
                ->route('admin.okrs')
                ->with('error', 'OKR not found.');
        }

        $okrTypes = OkrType::all();
        $employees = Employee::active()->get();
        $orgUnits = OrgUnit::active()->get();

        return view('admin.okrs.edit', compact('okr', 'okrTypes', 'employees', 'orgUnits'));
    }

    /**
     * Store a newly created OKR.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'okr_type_id' => 'required|exists:okr_type,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'owner_type' => 'required|in:employee,orgunit',
            'owner_id' => 'required|integer',
            'is_active' => 'boolean',
            'objectives' => 'array',
            'objectives.*.description' => 'required|string',
            'objectives.*.weight' => 'required|numeric|min:0|max:100',
            'objectives.*.target_type' => 'required|in:numeric,binary',
            'objectives.*.target_value' => 'required|numeric',
            'objectives.*.deadline' => 'required|date',
            'objectives.*.tracking_type' => 'required|in:daily,weekly,monthly,quarterly',
            'objectives.*.tracker' => 'nullable|exists:employee,id',
            'objectives.*.approver' => 'nullable|exists:employee,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->messages()
                ], 422);
            }
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Map owner_type to actual model class
        $ownerType = $request->owner_type === 'employee'
            ? Employee::class
            : OrgUnit::class;

        // Verify owner exists
        if ($ownerType === Employee::class) {
            $owner = Employee::find($request->owner_id);
        } else {
            $owner = OrgUnit::find($request->owner_id);
        }

        if (!$owner) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected owner not found.'
                ], 422);
            }
            return back()
                ->withInput()
                ->with('error', 'Selected owner not found.');
        }

        $okr = Okr::create([
            'name' => $request->name,
            'weight' => $request->weight / 100, // Convert percentage to decimal
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'owner_type' => $ownerType,
            'owner_id' => $request->owner_id,
            'is_active' => $request->has('is_active'),
        ]);

        // Create objectives if provided
        if ($request->has('objectives')) {
            foreach ($request->objectives as $objectiveData) {
                Objective::create([
                    'okr_id' => $okr->id,
                    'description' => $objectiveData['description'],
                    'weight' => $objectiveData['weight'] / 100, // Convert percentage to decimal
                    'target_type' => $objectiveData['target_type'],
                    'target_value' => $objectiveData['target_value'],
                    'deadline' => $objectiveData['deadline'],
                    'tracking_type' => $objectiveData['tracking_type'],
                    'tracker' => $objectiveData['tracker'] ?? null,
                    'approver' => $objectiveData['approver'] ?? null,
                ]);
            }
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'OKR created successfully.'
            ]);
        }

        return redirect()
            ->route('admin.okrs')
            ->with('success', 'OKR created successfully.');
    }

    /**
     * Update the specified OKR.
     */
    public function update(Request $request, $id)
    {
        $okr = Okr::with('objectives')->find($id);

        if (!$okr) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OKR not found.'
                ], 404);
            }
            return redirect()
                ->route('admin.okrs')
                ->with('error', 'OKR not found.');
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'okr_type_id' => 'required|exists:okr_type,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'owner_type' => 'required|in:employee,orgunit',
            'owner_id' => 'required|integer',
            'is_active' => 'boolean',
            'objectives' => 'array',
            'objectives.*.id' => 'nullable|integer',
            'objectives.*.description' => 'required|string',
            'objectives.*.weight' => 'required|numeric|min:0|max:100',
            'objectives.*.target_type' => 'required|in:numeric,binary',
            'objectives.*.target_value' => 'required|numeric',
            'objectives.*.deadline' => 'required|date',
            'objectives.*.tracking_type' => 'required|in:daily,weekly,monthly,quarterly',
            'objectives.*.tracker' => 'nullable|exists:employee,id',
            'objectives.*.approver' => 'nullable|exists:employee,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->messages()
                ], 422);
            }
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Map owner_type to actual model class
        $ownerType = $request->owner_type === 'employee'
            ? Employee::class
            : OrgUnit::class;

        // Verify owner exists
        if ($ownerType === Employee::class) {
            $owner = Employee::find($request->owner_id);
        } else {
            $owner = OrgUnit::find($request->owner_id);
        }

        if (!$owner) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected owner not found.'
                ], 422);
            }
            return back()
                ->withInput()
                ->with('error', 'Selected owner not found.');
        }

        $okr->update([
            'name' => $request->name,
            'weight' => $request->weight / 100, // Convert percentage to decimal
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'owner_type' => $ownerType,
            'owner_id' => $request->owner_id,
            'is_active' => $request->has('is_active'),
        ]);

        // Sync objectives
        if ($request->has('objectives')) {
            // Get existing objective IDs
            $existingIds = $okr->objectives->pluck('id')->toArray();
            $newIds = [];

            foreach ($request->objectives as $objectiveData) {
                if (isset($objectiveData['id']) && $objectiveData['id']) {
                    // Update existing objective
                    $objective = Objective::find($objectiveData['id']);
                    if ($objective && $objective->okr_id == $okr->id) {
                        $objective->update([
                            'description' => $objectiveData['description'],
                            'weight' => $objectiveData['weight'] / 100, // Convert percentage to decimal
                            'target_type' => $objectiveData['target_type'],
                            'target_value' => $objectiveData['target_value'],
                            'deadline' => $objectiveData['deadline'],
                            'tracking_type' => $objectiveData['tracking_type'],
                            'tracker' => $objectiveData['tracker'] ?? null,
                            'approver' => $objectiveData['approver'] ?? null,
                        ]);
                        $newIds[] = $objective->id;
                    }
                } else {
                    // Create new objective
                    $objective = Objective::create([
                        'okr_id' => $okr->id,
                        'description' => $objectiveData['description'],
                        'weight' => $objectiveData['weight'] / 100, // Convert percentage to decimal
                        'target_type' => $objectiveData['target_type'],
                        'target_value' => $objectiveData['target_value'],
                        'deadline' => $objectiveData['deadline'],
                        'tracking_type' => $objectiveData['tracking_type'],
                        'tracker' => $objectiveData['tracker'] ?? null,
                        'approver' => $objectiveData['approver'] ?? null,
                    ]);
                    $newIds[] = $objective->id;
                }
            }

            // Delete objectives that are not in the new list
            $toDelete = array_diff($existingIds, $newIds);
            Objective::whereIn('id', $toDelete)->delete();
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'OKR updated successfully.'
            ]);
        }

        return redirect()
            ->route('admin.okrs')
            ->with('success', 'OKR updated successfully.');
    }

    /**
     * Remove the specified OKR.
     */
    public function destroy($id)
    {
        $okr = Okr::find($id);

        if (!$okr) {
            return redirect()
                ->route('admin.okrs')
                ->with('error', 'OKR not found.');
        }

        $okr->delete();

        return redirect()
            ->route('admin.okrs')
            ->with('success', 'OKR deleted successfully.');
    }

    /**
     * Activate the specified OKR.
     */
    public function activate($id)
    {
        $okr = Okr::find($id);

        if (!$okr) {
            return redirect()
                ->route('admin.okrs')
                ->with('error', 'OKR not found.');
        }

        $okr->update(['is_active' => true]);

        return redirect()
            ->route('admin.okrs')
            ->with('success', 'OKR activated successfully.');
    }

    /**
     * Deactivate the specified OKR.
     */
    public function deactivate($id)
    {
        $okr = Okr::find($id);

        if (!$okr) {
            return redirect()
                ->route('admin.okrs')
                ->with('error', 'OKR not found.');
        }

        $okr->update(['is_active' => false]);

        return redirect()
            ->route('admin.okrs')
            ->with('success', 'OKR deactivated successfully.');
    }

    /**
     * Get available owners (employees and org units).
     */
    public function getAvailableOwners()
    {
        $employees = Employee::active()->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'type' => 'employee',
            ];
        });

        $orgUnits = OrgUnit::active()->get()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'type' => 'orgunit',
            ];
        });

        return response()->json([
            'employees' => $employees,
            'orgUnits' => $orgUnits,
        ]);
    }

    /**
     * Get all employees for dropdown.
     */
    public function getAllEmployees()
    {
        $employees = Employee::active()->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
            ];
        });

        return response()->json($employees);
    }
}
