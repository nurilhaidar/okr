<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Employee;
use App\Models\Objective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $query = CheckIn::with(['objective.trackerEmployee', 'objective.approverEmployee', 'approvalLogs']);

        // Filter by objective if provided
        if ($request->has('objective_id')) {
            $query->where('objective_id', $request->objective_id);
        }

        $checkIns = $query->orderByDate()->get();

        // Append current_status to each check-in
        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        return response()->json([
            'success' => true,
            'data' => $checkIns,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'objective_id' => 'required|integer|exists:objective,id',
            'date' => 'required|date',
            'current_value' => 'required|numeric',
            'comments' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $objective = Objective::find($request->objective_id);
        if (!$objective) {
            return response()->json([
                'success' => false,
                'message' => 'Objective not found',
            ], 404);
        }

        // Handle file upload
        $evidencePath = null;
        if ($request->hasFile('evidence_file')) {
            $file = $request->file('evidence_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('evidence', $filename, 'public');
            $evidencePath = '/storage/' . $path;
        }

        $checkIn = CheckIn::create([
            'objective_id' => $request->objective_id,
            'date' => $request->date,
            'current_value' => $request->current_value,
            'comments' => $request->comments,
            'evidence_path' => $evidencePath,
        ]);

        // Automatically submit for approval (create pending log)
        $checkIn->submitForApproval();

        $checkIn->load(['objective', 'approvalLogs']);
        $checkIn->current_status = $checkIn->getCurrentStatusAttribute();

        return response()->json([
            'success' => true,
            'message' => 'Check-in created and submitted for approval',
            'data' => $checkIn,
        ], 201);
    }

    public function show($id)
    {
        $checkIn = CheckIn::with(['objective.trackerEmployee', 'objective.approverEmployee', 'approvalLogs'])->find($id);

        if (!$checkIn) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found',
            ], 404);
        }

        $checkIn->current_status = $checkIn->getCurrentStatusAttribute();

        return response()->json([
            'success' => true,
            'data' => $checkIn,
        ]);
    }

    public function update(Request $request, $id)
    {
        $checkIn = CheckIn::find($id);

        if (!$checkIn) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found',
            ], 404);
        }

        // Only allow editing if not approved or rejected
        if (!$checkIn->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit a check-in that has been approved or rejected',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'objective_id' => 'integer|exists:objective,id',
            'date' => 'date',
            'current_value' => 'numeric',
            'comments' => 'nullable|string',
            'evidence_path' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $checkIn->update($request->only([
            'objective_id',
            'date',
            'current_value',
            'comments',
            'evidence_path'
        ]));

        // Re-submit for approval after update
        $checkIn->submitForApproval();

        $checkIn->load(['objective', 'approvalLogs']);
        $checkIn->current_status = $checkIn->getCurrentStatusAttribute();

        return response()->json([
            'success' => true,
            'message' => 'Check-in updated and submitted for approval',
            'data' => $checkIn,
        ]);
    }

    public function destroy($id)
    {
        $checkIn = CheckIn::find($id);

        if (!$checkIn) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found',
            ], 404);
        }

        $checkIn->delete();

        return response()->json([
            'success' => true,
            'message' => 'Check-in deleted successfully',
        ]);
    }

    public function approve($id)
    {
        $checkIn = CheckIn::with(['objective'])->find($id);

        if (!$checkIn) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found',
            ], 404);
        }

        // Check if already approved or rejected
        if ($checkIn->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in has already been approved',
            ], 400);
        }

        if ($checkIn->isRejected()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve a rejected check-in. Please create a new check-in.',
            ], 400);
        }

        // Get authenticated user (employee is the authenticatable model)
        $employee = auth()->user();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated employee not found',
            ], 404);
        }

        // Load the employee's role to check if they're admin
        $employee->load('role');

        // Verify the employee is the approver for this objective OR is an admin
        $isAdmin = $employee->role && $employee->role->name === 'admin';
        if (!$isAdmin && $checkIn->objective->approver !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to approve this check-in',
            ], 403);
        }

        $checkIn->approve();
        $checkIn->load(['objective', 'approvalLogs']);
        $checkIn->current_status = $checkIn->getCurrentStatusAttribute();

        return response()->json([
            'success' => true,
            'message' => 'Check-in approved successfully',
            'data' => $checkIn,
        ]);
    }

    public function reject($id)
    {
        $checkIn = CheckIn::with(['objective'])->find($id);

        if (!$checkIn) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found',
            ], 404);
        }

        // Check if already approved or rejected
        if ($checkIn->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an approved check-in',
            ], 400);
        }

        if ($checkIn->isRejected()) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in has already been rejected',
            ], 400);
        }

        // Get authenticated user (employee is the authenticatable model)
        $employee = auth()->user();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated employee not found',
            ], 404);
        }

        // Load the employee's role to check if they're admin
        $employee->load('role');

        // Verify the employee is the approver for this objective OR is an admin
        $isAdmin = $employee->role && $employee->role->name === 'Admin';
        if (!$isAdmin && $checkIn->objective->approver !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reject this check-in',
            ], 403);
        }

        $checkIn->reject();
        $checkIn->load(['objective', 'approvalLogs']);
        $checkIn->current_status = $checkIn->getCurrentStatusAttribute();

        return response()->json([
            'success' => true,
            'message' => 'Check-in rejected successfully',
            'data' => $checkIn,
        ]);
    }

    public function getByObjective($objectiveId)
    {
        $objective = Objective::find($objectiveId);

        if (!$objective) {
            return response()->json([
                'success' => false,
                'message' => 'Objective not found',
            ], 404);
        }

        $checkIns = CheckIn::where('objective_id', $objectiveId)
            ->with(['objective.trackerEmployee', 'objective.approverEmployee', 'approvalLogs'])
            ->orderBy('date', 'desc')
            ->get();

        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        return response()->json([
            'success' => true,
            'data' => $checkIns,
        ]);
    }

    public function getByTracker($trackerId)
    {
        // Get all objectives where this employee is the tracker
        $objectives = Objective::where('tracker', $trackerId)->pluck('id');

        $checkIns = CheckIn::whereIn('objective_id', $objectives)
            ->with(['objective', 'approvalLogs'])
            ->orderBy('date', 'desc')
            ->get();

        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        return response()->json([
            'success' => true,
            'data' => $checkIns,
        ]);
    }

    public function getPendingApprovals()
    {
        // Get authenticated user (employee is the authenticatable model)
        $employee = auth()->user();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated employee not found',
            ], 404);
        }

        // Get all objectives where this employee is the approver
        $objectiveIds = Objective::where('approver', $employee->id)->pluck('id');

        // Get all check-ins for those objectives that have pending approval
        $checkIns = CheckIn::whereIn('objective_id', $objectiveIds)
            ->whereHas('approvalLogs', function ($query) {
                $query->pending();
            })
            ->whereDoesntHave('approvalLogs', function ($query) {
                $query->approved();
            })
            ->whereDoesntHave('approvalLogs', function ($query) {
                $query->rejected();
            })
            ->with(['objective.trackerEmployee', 'objective.approverEmployee', 'approvalLogs'])
            ->orderBy('date', 'desc')
            ->get();

        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        return response()->json([
            'success' => true,
            'data' => $checkIns,
        ]);
    }

    public function getApprovalLogs($id)
    {
        $checkIn = CheckIn::find($id);

        if (!$checkIn) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found',
            ], 404);
        }

        $logs = $checkIn->approvalLogs()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
