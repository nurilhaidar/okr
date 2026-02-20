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
        $status = $request->query('status', 'active');
        $tab = $request->query('tab', 'my-okrs');

        // Get OrgUnits where the employee is a member
        $memberOrgUnits = $user->orgUnits()->active()->pluck('orgunit.id')->toArray();

        // Build base query with all relationships
        $query = Okr::with(['okrType', 'employee', 'orgUnit', 'objectives.checkIns', 'objectives.checkIns.approvalLogs', 'objectives.trackerEmployee', 'objectives.approverEmployee'])
            ->orderBy('created_at', 'desc');

        // Apply tab filter - only get OKRs relevant to that tab
        switch ($tab) {
            case 'my-okrs':
                // Only OKRs where user is the owner
                $query->where('employee_id', $user->id);
                break;
            case 'tracking':
                // Only OKRs where user is tracker on any objective
                $query->whereHas('objectives', function ($q) use ($user) {
                    $q->where('tracker', $user->id);
                });
                break;
            case 'approving':
                // Only OKRs where user is approver on any objective
                $query->whereHas('objectives', function ($q) use ($user) {
                    $q->where('approver', $user->id);
                });
                break;
            case 'team-okrs':
                // Only OKRs owned by user's org units
                $query->whereIn('orgunit_id', $memberOrgUnits);
                break;
            default:
                // All OKRs where user has any role
                $query->where(function ($q) use ($user, $memberOrgUnits) {
                    $q->where('employee_id', $user->id)
                        ->orWhereHas('objectives', function ($q) use ($user) {
                            $q->where('tracker', $user->id);
                        })
                        ->orWhereHas('objectives', function ($q) use ($user) {
                            $q->where('approver', $user->id);
                        })
                        ->orWhereIn('orgunit_id', $memberOrgUnits);
                });
                break;
        }

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
            $roles = [];

            // Check if user is a member of the owning OrgUnit (role: member)
            if (in_array($okr->orgunit_id, $memberOrgUnits)) {
                $roles[] = 'member';
            }

            // Check if user is owner (employee_id = user.id)
            if ($okr->employee_id == $user->id) {
                $roles[] = 'owner';
            }

            // Check if user is tracker or approver
            foreach ($okr->objectives as $obj) {
                if ($obj->tracker == $user->id) {
                    $roles[] = 'tracker';
                }
                if ($obj->approver == $user->id) {
                    $roles[] = 'approver';
                }
            }

            // Remove duplicates and set as array
            $okr->roles = array_unique($roles);
            // Keep single role for backward compatibility
            $okr->role = !empty($roles) ? $roles[0] : null;
        }

        return view('employee.okrs.index', compact('okrs', 'tab'));
    }

    /**
     * Show form for creating a new OKR.
     * Employees can only create "Additional" type OKRs for themselves.
     */
    public function create()
    {
        $user = Auth::user();

        // Only get "Additional" OKR type for employees
        $okrTypes = OkrType::where('name', 'Additional')->where('is_employee', true)->get();

        // For employees, only pass themselves as the owner option
        $employees = collect([$user]);
        $orgUnits = collect();

        return view('employee.okrs.create', compact('okrTypes', 'employees', 'orgUnits'));
    }

    /**
     * Show the form for editing the specified OKR.
     * Employees can only edit their own Additional OKRs.
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

        // Employees can only edit their own Additional OKRs
        if ($okr->employee_id != $user->id || $okr->okrType->name !== 'Additional') {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'You can only edit your own Additional OKRs.');
        }

        // Only get "Additional" OKR type for employees
        $okrTypes = OkrType::where('name', 'Additional')->where('is_employee', true)->get();

        // For employees, only pass themselves as the owner option
        $employees = collect([$user]);
        $orgUnits = collect();

        return view('employee.okrs.edit', compact('okr', 'okrTypes', 'employees', 'orgUnits'));
    }

    /**
     * Store a newly created OKR.
     * Employees can only create "Additional" type OKRs for themselves.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'okr_type_id' => 'required|exists:okr_type,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
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

        // Custom validation for employee store method
        $validator->after(function ($validator) use ($request, $user) {
            $okrType = OkrType::find($request->okr_type_id);
            if (!$okrType) {
                return;
            }

            // Employees can only create "Additional" type OKRs
            if ($okrType->name !== 'Additional' || !$okrType->is_employee) {
                $validator->errors()->add('okr_type_id', 'Employees can only create Additional type OKRs.');
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
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $okr = Okr::create([
            'name' => $request->name,
            'weight' => $request->weight / 100, // Convert percentage to decimal
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'employee_id' => $user->id, // Force owner to be the current employee
            'orgunit_id' => null,
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

        return response()->json([
            'success' => true,
            'message' => 'OKR created successfully.'
        ]);
    }

    /**
     * Update the specified OKR.
     * Employees can only update their own Additional OKRs.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $okr = Okr::with('objectives', 'okrType')->find($id);

        if (!$okr) {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'OKR not found.');
        }

        // Employees can only update their own Additional OKRs
        if ($okr->employee_id != $user->id || $okr->okrType->name !== 'Additional') {
            return redirect()
                ->route('okrs.index')
                ->with('error', 'You can only edit your own Additional OKRs.');
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'okr_type_id' => 'required|exists:okr_type,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
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

        // Custom validation for employee update method
        $validator->after(function ($validator) use ($request, $user) {
            $okrType = OkrType::find($request->okr_type_id);
            if (!$okrType) {
                return;
            }

            // Employees can only update "Additional" type OKRs
            if ($okrType->name !== 'Additional' || !$okrType->is_employee) {
                $validator->errors()->add('okr_type_id', 'Employees can only create Additional type OKRs.');
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
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $okr->update([
            'name' => $request->name,
            'weight' => $request->weight / 100,
            'okr_type_id' => $request->okr_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'employee_id' => $user->id, // Force owner to remain the current employee
            'orgunit_id' => null,
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

        return response()->json([
            'success' => true,
            'message' => 'OKR updated successfully.'
        ]);
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
