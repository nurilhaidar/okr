<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Okr;
use App\Models\OkrType;
use App\Models\Objective;
use App\Models\Employee;
use App\Models\OrgUnit;
use App\Models\OrgUnitRole;
use App\Services\CheckInService;
use Illuminate\Http\Request;

class OkrController extends Controller
{
    public function __construct(
        protected CheckInService $checkInService
    ) {}

    /**
     * Display a listing of OKRs.
     * Admin sees all OKRs with filtering.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->query('search', '');
        $owner = $request->query('owner', '');
        $owner_type = $request->query('owner_type', '');
        $status = $request->query('status', '');

        // Build query with all relationships
        $query = Okr::with(['okrType', 'employee', 'orgUnit', 'orgUnit.orgUnitEmployees.employee', 'objectives.checkIns', 'objectives.checkIns.approvalLogs', 'objectives.trackerEmployee', 'objectives.approverEmployee'])
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

        // Filter by owner type (employee or orgunit)
        if ($owner_type !== '' && $owner_type !== null) {
            $okrs = $okrs->filter(function ($okr) use ($owner_type) {
                if ($owner_type === 'employee') {
                    return $okr->employee_id !== null;
                } elseif ($owner_type === 'orgunit') {
                    return $okr->orgunit_id !== null;
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
        $okr = Okr::with(['okrType', 'employee', 'orgUnit', 'objectives.trackerEmployee', 'objectives.approverEmployee'])->find($id);

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

            if ($okrType->is_employee && !empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID should not be set for employee OKR type.');
            }

            if (!$okrType->is_employee && !empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID should not be set for org unit OKR type.');
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

            // Validate that if OKR is being set to active, current date must be within period
            if ($request->is_active == '1') {
                $currentDate = now()->startOfDay();
                $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

                if ($currentDate->lt($startDate)) {
                    $validator->errors()->add('is_active', 'Cannot activate OKR before the start date. Current date is ' . now()->toDateString() . ' but start date is ' . $request->start_date . '.');
                }

                if ($currentDate->gt($endDate)) {
                    $validator->errors()->add('is_active', 'Cannot activate OKR after the end date. Current date is ' . now()->toDateString() . ' but end date is ' . $request->end_date . '.');
                }
            }
        });

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
                    'start_date' => now()->toDateString() // Set start date to today
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

            if ($okrType->is_employee && !empty($request->orgunit_id)) {
                $validator->errors()->add('orgunit_id', 'Organization Unit ID should not be set for employee OKR type.');
            }

            if (!$okrType->is_employee && !empty($request->employee_id)) {
                $validator->errors()->add('employee_id', 'Employee ID should not be set for org unit OKR type.');
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

            // Validate that if OKR is being set to active, current date must be within period
            if ($request->is_active == '1') {
                $currentDate = now()->startOfDay();
                $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

                if ($currentDate->lt($startDate)) {
                    $validator->errors()->add('is_active', 'Cannot activate OKR before the start date. Current date is ' . now()->toDateString() . ' but start date is ' . $request->start_date . '.');
                }

                if ($currentDate->gt($endDate)) {
                    $validator->errors()->add('is_active', 'Cannot activate OKR after the end date. Current date is ' . now()->toDateString() . ' but end date is ' . $request->end_date . '.');
                }
            }
        });

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

        $okr->update([
            'name' => $request->name,
            'weight' => $request->weight / 100, // Convert percentage to decimal
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
                            'weight' => $objectiveData['weight'] / 100, // Convert percentage to decimal
                            'target_type' => $objectiveData['target_type'],
                            'target_value' => $objectiveData['target_value'],
                            'deadline' => $objectiveData['deadline'],
                            'tracking_type' => $objectiveData['tracking_type'],
                            'tracker' => $objectiveData['tracker'] ?? null,
                            'approver' => $objectiveData['approver'] ?? null,
                        ]);

                        // Ensure start_date is set
                        if (!$objective->start_date) {
                            $objective->update(['start_date' => now()->toDateString()]);
                        }

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
                        'start_date' => now()->toDateString() // Set start date to today
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
