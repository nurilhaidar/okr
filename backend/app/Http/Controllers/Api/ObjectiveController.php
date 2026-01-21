<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Objective;
use App\Models\Okr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ObjectiveController extends Controller
{
    public function index(Request $request)
    {
        $query = Objective::with(['okr', 'trackerEmployee', 'approverEmployee']);

        // Filter by OKR ID if provided
        if ($request->has('okr_id')) {
            $query->where('okr_id', $request->okr_id);
        }

        // Filter by tracker if provided
        if ($request->has('tracker_id')) {
            $query->where('tracker', $request->tracker_id);
        }

        // Filter by approver if provided
        if ($request->has('approver_id')) {
            $query->where('approver', $request->approver_id);
        }

        // Filter by target type if provided
        if ($request->has('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // Filter by tracking type if provided
        if ($request->has('tracking_type')) {
            $query->where('tracking_type', $request->tracking_type);
        }

        // Filter pending/completed
        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->where('deadline', '>', now());
            } elseif ($request->status === 'completed') {
                $query->where('deadline', '<=', now());
            }
        }

        $objectives = $query->orderBy('deadline', 'asc')->get();

        // Append progress and current_value to each objective
        $objectives->each(function ($objective) {
            $objective->progress = $objective->progress;
            $objective->current_value = $objective->current_value;
        });

        return response()->json([
            'success' => true,
            'data' => $objectives,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'okr_id' => 'required|integer|exists:okr,id',
            'description' => 'required|string',
            'weight' => 'required|numeric|min:0|max:1',
            'target_type' => 'required|in:numeric,binary',
            'target_value' => 'required|numeric',
            'deadline' => 'required|date',
            'tracking_type' => 'required|in:daily,weekly,monthly,quarterly',
            'tracker' => 'required|integer|exists:employee,id',
            'approver' => 'required|integer|exists:employee,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate deadline is before OKR end date
        $okr = Okr::find($request->okr_id);
        if ($request->deadline > $okr->end_date) {
            return response()->json([
                'success' => false,
                'message' => 'Objective deadline must be before OKR end date',
            ], 422);
        }

        $objective = Objective::create($request->all());

        $objective->load(['okr', 'trackerEmployee', 'approverEmployee']);

        return response()->json([
            'success' => true,
            'message' => 'Objective created successfully',
            'data' => $objective,
        ], 201);
    }

    public function show($id)
    {
        $objective = Objective::with(['okr', 'trackerEmployee', 'approverEmployee', 'checkIns', 'approvalLogs'])->find($id);

        if (!$objective) {
            return response()->json([
                'success' => false,
                'message' => 'Objective not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $objective,
        ]);
    }

    public function update(Request $request, $id)
    {
        $objective = Objective::find($id);

        if (!$objective) {
            return response()->json([
                'success' => false,
                'message' => 'Objective not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'okr_id' => 'integer|exists:okr,id',
            'description' => 'string',
            'weight' => 'numeric|min:0|max:1',
            'target_type' => 'in:numeric,binary',
            'target_value' => 'numeric',
            'deadline' => 'date',
            'tracking_type' => 'in:daily,weekly,monthly,quarterly',
            'tracker' => 'integer|exists:employee,id',
            'approver' => 'integer|exists:employee,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate deadline is before OKR end date if okr_id is being updated
        if ($request->has('okr_id') || $request->has('deadline')) {
            $okrId = $request->get('okr_id', $objective->okr_id);
            $deadline = $request->get('deadline', $objective->deadline);
            $okr = Okr::find($okrId);

            if ($deadline > $okr->end_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Objective deadline must be before OKR end date',
                ], 422);
            }
        }

        $objective->update($request->all());

        $objective->load(['okr', 'trackerEmployee', 'approverEmployee']);

        return response()->json([
            'success' => true,
            'message' => 'Objective updated successfully',
            'data' => $objective,
        ]);
    }

    public function destroy($id)
    {
        $objective = Objective::find($id);

        if (!$objective) {
            return response()->json([
                'success' => false,
                'message' => 'Objective not found',
            ], 404);
        }

        $objective->delete();

        return response()->json([
            'success' => true,
            'message' => 'Objective deleted successfully',
        ]);
    }

    public function getByOkr($okrId)
    {
        $okr = Okr::find($okrId);

        if (!$okr) {
            return response()->json([
                'success' => false,
                'message' => 'OKR not found',
            ], 404);
        }

        $objectives = Objective::with(['trackerEmployee', 'approverEmployee'])
            ->where('okr_id', $okrId)
            ->orderBy('deadline', 'asc')
            ->get();

        // Append progress and current_value to each objective
        $objectives->each(function ($objective) {
            $objective->progress = $objective->progress;
            $objective->current_value = $objective->current_value;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'okr' => $okr,
                'objectives' => $objectives,
            ],
        ]);
    }

    public function getByTracker($trackerId)
    {
        $objectives = Objective::with(['okr', 'approverEmployee'])
            ->where('tracker', $trackerId)
            ->orderBy('deadline', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $objectives,
        ]);
    }

    public function getByApprover($approverId)
    {
        $objectives = Objective::with(['okr', 'trackerEmployee'])
            ->where('approver', $approverId)
            ->orderBy('deadline', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $objectives,
        ]);
    }

    public function getProgress($id)
    {
        $objective = Objective::with(['okr', 'trackerEmployee', 'approverEmployee'])->find($id);

        if (!$objective) {
            return response()->json([
                'success' => false,
                'message' => 'Objective not found',
            ], 404);
        }

        $latestCheckIn = $objective->latestApprovedCheckIn();

        return response()->json([
            'success' => true,
            'data' => [
                'objective_id' => $objective->id,
                'description' => $objective->description,
                'target_value' => $objective->target_value,
                'target_type' => $objective->target_type,
                'current_value' => $objective->current_value,
                'progress' => $objective->progress,
                'latest_check_in' => $latestCheckIn,
            ],
        ]);
    }

    public function getAllProgress(Request $request)
    {
        $query = Objective::with(['okr', 'trackerEmployee', 'approverEmployee']);

        // Filter by OKR ID if provided
        if ($request->has('okr_id')) {
            $query->where('okr_id', $request->okr_id);
        }

        $objectives = $query->orderBy('deadline', 'asc')->get();

        // Add progress data to each objective
        $objectivesWithProgress = $objectives->map(function ($objective) {
            return [
                'id' => $objective->id,
                'description' => $objective->description,
                'weight' => $objective->weight,
                'target_value' => $objective->target_value,
                'target_type' => $objective->target_type,
                'deadline' => $objective->deadline,
                'current_value' => $objective->current_value,
                'progress' => $objective->progress,
                'tracker' => $objective->trackerEmployee,
                'approver' => $objective->approverEmployee,
                'okr' => $objective->okr,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $objectivesWithProgress,
        ]);
    }
}
