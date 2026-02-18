<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Okr;
use App\Models\OkrType;
use App\Models\Objective;
use App\Models\Employee;
use App\Models\OrgUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeOkrController extends Controller
{
    /**
     * Display a listing of employee's OKRs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $search = $request->query('search', '');
        $owner = $request->query('owner', '');
        $status = $request->query('status', '');

        // Build query - Employee sees only OKRs where they are owner, tracker, or approver
        $query = Okr::with(['okrType', 'employee', 'orgUnit', 'objectives.checkIns', 'objectives.checkIns.approvalLogs', 'objectives.trackerEmployee', 'objectives.approverEmployee'])
            ->where(function ($query) use ($user) {
                $query->where('employee_id', $user->id)
                    ->orWhereHas('objectives', function ($q) use ($user) {
                        $q->where('tracker', $user->id);
                    })
                    ->orWhereHas('objectives', function ($q) use ($user) {
                        $q->where('approver', $user->id);
                    });
            })
            ->orderBy('created_at', 'desc');

        // Get all OKRs first
        $okrs = $query->get();

        // Filter by search (OKR name)
        if ($search !== '' && $search !== null) {
            $searchLower = strtolower($search);
            $okrs = $okrs->filter(function ($okr) use ($searchLower) {
                $okrName = strtolower($okr->name ?? '');
                return str_contains($okrName, $searchLower);
            })->values();
        }

        // Filter by owner (specific employee or orgunit)
        if ($owner !== '' && $owner !== null) {
            $okrs = $okrs->filter(function ($okr) use ($owner) {
                if (str_starts_with($owner, 'employee_')) {
                    $employeeId = (int) str_replace('employee_', '', $owner);
                    return $okr->employee_id === $employeeId;
                } elseif (str_starts_with($owner, 'orgunit_')) {
                    $orgunitId = (int) str_replace('orgunit_', '', $owner);
                    return $okr->orgunit_id === $orgunitId;
                }
                return true;
            })->values();
        }

        // Filter by status (active/inactive)
        if ($status !== '' && $status !== null) {
            $okrs = $okrs->filter(function ($okr) use ($status) {
                if ($status === 'active') {
                    return $okr->is_active === true;
                } elseif ($status === 'inactive') {
                    return $okr->is_active === false;
                }
                return true;
            })->values();
        }

        // Determine role for each OKR
        foreach ($okrs as &$okr) {
            $okr->role = 'owner';
            foreach ($okr->objectives as $obj) {
                if ($obj->tracker == $user->id) {
                    $okr->role = 'tracker';
                    break;
                }
                if ($obj->approver == $user->id) {
                    $okr->role = 'approver';
                    break;
                }
            }
        }

        return view('employee.okrs.index', compact('okrs'));
    }

    /**
     * Show form for creating a new OKR.
     */
    public function create()
    {
        $okrTypes = OkrType::all();
        $employees = Employee::active()->get();
        $orgUnits = OrgUnit::active()->get();

        return view('employee.okrs.create', compact('okrTypes', 'employees', 'orgUnits'));
    }

    /**
     * Show the form for editing the specified OKR.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $okr = Okr::with(['okrType', 'employee', 'orgUnit', 'objectives.trackerEmployee', 'objectives.approverEmployee'])->find($id);

        if (!$okr) {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'OKR not found.');
        }

        // Check if user has access (owner, tracker, or approver)
        $hasAccess = $okr->employee_id == $user->id;
        foreach ($okr->objectives as $obj) {
            if ($obj->tracker == $user->id || $obj->approver == $user->id) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'You do not have permission to edit this OKR.');
        }

        $okrTypes = OkrType::all();
        $employees = Employee::active()->get();
        $orgUnits = OrgUnit::active()->get();

        return view('employee.okrs.edit', compact('okr', 'okrTypes', 'employees', 'orgUnits'));
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
            'employee_id' => 'nullable|integer|exists:employee,id',
            'orgunit_id' => 'nullable|integer|exists:orgunit,id',
            'is_active' => 'required|in:0,1',
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

        // Custom validation for store method
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

            if ($okrType->is_employee && !empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID should not be set for org unit OKR type.');
            }

            if (!$okrType->is_employee && !empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID should not be set for employee OKR type.');
            }

            // Validate objective deadlines are within OKR period
            if ($request->has('objectives')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;

                foreach ($request->objectives as $index => $objective) {
                    if (!empty($objective['deadline'])) {
                        $objectiveDeadline = $objective['deadline'];

                        if ($objectiveDeadline < $startDate) {
                            $validator->errors()->add("objectives.{$index}.deadline", 'Objective deadline must be on or after OKR start date.');
                        }

                        if ($objectiveDeadline > $endDate) {
                            $validator->errors()->add("objectives.{$index}.deadline", 'Objective deadline must be on or before OKR end date.');
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $okr = Okr::create([
            'name' => $request->name,
            'weight' => $request->weight / 100, // Convert percentage to decimal
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'employee_id' => $request->employee_id,
            'orgunit_id' => $request->orgunit_id,
            'is_active' => $request->is_active == '1',
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
                    'start_date' => now()->toDateString(),
                ]);
            }
        }

        return redirect()
            ->route('okrs.index')
            ->with('success', 'OKR created successfully.');
    }

    /**
     * Update the specified OKR.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $okr = Okr::with('objectives')->find($id);

        if (!$okr) {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'OKR not found.');
        }

        // Check if user has access (owner, tracker, or approver)
        $hasAccess = $okr->employee_id == $user->id;
        foreach ($okr->objectives as $obj) {
            if ($obj->tracker == $user->id || $obj->approver == $user->id) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'You do not have permission to edit this OKR.');
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'okr_type_id' => 'required|exists:okr_type,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'employee_id' => 'nullable|integer|exists:employee,id',
            'orgunit_id' => 'nullable|integer|exists:orgunit,id',
            'is_active' => 'required|in:0,1',
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

        // Custom validation for update method
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

            if ($okrType->is_employee && !empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID should not be set for org unit OKR type.');
            }

            if (!$okrType->is_employee && !empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID should not be set for employee OKR type.');
            }

            // Validate objective deadlines are within OKR period
            if ($request->has('objectives')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;

                foreach ($request->objectives as $index => $objective) {
                    if (!empty($objective['deadline'])) {
                        $objectiveDeadline = $objective['deadline'];

                        if ($objectiveDeadline < $startDate) {
                            $validator->errors()->add("objectives.{$index}.deadline", 'Objective deadline must be on or after OKR start date.');
                        }

                        if ($objectiveDeadline > $endDate) {
                            $validator->errors()->add("objectives.{$index}.deadline", 'Objective deadline must be on or before OKR end date.');
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $okr->update([
            'name' => $request->name,
            'weight' => $request->weight / 100,
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'employee_id' => $request->employee_id,
            'orgunit_id' => $request->orgunit_id,
            'is_active' => $request->is_active == '1',
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
                            'weight' => $objectiveData['weight'] / 100,
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
                        'weight' => $objectiveData['weight'] / 100,
                        'target_type' => $objectiveData['target_type'],
                        'target_value' => $objectiveData['target_value'],
                        'deadline' => $objectiveData['deadline'],
                        'tracking_type' => $objectiveData['tracking_type'],
                        'tracker' => $objectiveData['tracker'] ?? null,
                        'approver' => $objectiveData['approver'] ?? null,
                        'start_date' => now()->toDateString(),
                    ]);
                    $newIds[] = $objective->id;
                }
            }

            // Delete objectives that are not in the new list
            $toDelete = array_diff($existingIds, $newIds);
            Objective::whereIn('id', $toDelete)->delete();
        }

        return redirect()
            ->route('okrs.index')
            ->with('success', 'OKR updated successfully.');
    }

    /**
     * Remove the specified OKR.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $okr = Okr::find($id);

        if (!$okr) {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'OKR not found.');
        }

        // Check if user is the owner
        if ($okr->employee_id != $user->id) {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'You do not have permission to delete this OKR.');
        }

        $okr->delete();

        return redirect()
            ->route('okrs.index')
            ->with('success', 'OKR deleted successfully.');
    }
}
