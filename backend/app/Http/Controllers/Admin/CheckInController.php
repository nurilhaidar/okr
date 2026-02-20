<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Objective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $query = CheckIn::with(['objective', 'objective.okr.employee', 'objective.okr.orgUnit', 'objective.trackerEmployee', 'approvalLogs'])
            ->orderBy('date', 'desc');

        $checkIns = $query->get();

        // Add current_status to each check-in
        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        // Get filter parameters
        $status = $request->query('status', '');
        $search = $request->query('search', '');

        // Filter by status (only if status is not empty and not null)
        if ($status !== '' && $status !== null) {
            $checkIns = $checkIns->filter(function ($checkIn) use ($status) {
                return $checkIn->current_status === $status;
            })->values();
        }

        // Search by objective description or OKR name (only if search is not empty)
        if ($search !== '' && $search !== null) {
            $searchLower = strtolower($search);
            $checkIns = $checkIns->filter(function ($checkIn) use ($searchLower) {
                $objectiveDesc = strtolower($checkIn->objective->description ?? '');
                $okrName = strtolower($checkIn->objective->okr->name ?? '');
                return str_contains($objectiveDesc, $searchLower) || str_contains($okrName, $searchLower);
            })->values();
        }

        return view('admin.check-ins.index', compact('checkIns'));
    }

    public function create()
    {
        $objectives = Objective::with(['okr', 'trackerEmployee', 'approverEmployee'])
            ->whereHas('okr', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        return view('admin.check-ins.create', compact('objectives'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'objective_id' => 'required|integer|exists:objective,id',
            'date' => 'nullable|date',
            'current_value' => 'required|numeric',
            'comments' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Set date to today if not provided
        if (!$request->has('date') || empty($request->date)) {
            $request->merge(['date' => now()->format('Y-m-d')]);
        }

        $objective = Objective::with('okr')->find($request->objective_id);
        if (!$objective) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Objective not found.',
                ], 404);
            }
            return back()
                ->withInput()
                ->with('error', 'Objective not found.');
        }

        // Check if the OKR is active
        if (!$objective->okr || !$objective->okr->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create check-in for an inactive OKR.',
                ], 400);
            }
            return back()
                ->withInput()
                ->with('error', 'Cannot create check-in for an inactive OKR.');
        }

        // Check if there's a pending check-in for this objective
        $pendingCheckIn = CheckIn::where('objective_id', $request->objective_id)
            ->whereHas('approvalLogs', function ($query) {
                $query->where('status', 'pending');
            })
            ->whereDoesntHave('approvalLogs', function ($query) {
                $query->whereIn('status', ['approved', 'rejected']);
            })
            ->latest('date')
            ->first();

        if ($pendingCheckIn) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create a new check-in. The previous check-in is still pending approval.',
                ], 400);
            }
            return back()
                ->withInput()
                ->with('error', 'Cannot create a new check-in. The previous check-in is still pending approval.');
        }

        // Validate that the OKR's objectives total weight equals 100%
        $okr = $objective->okr()->with('objectives')->first();
        if ($okr && !$okr->hasValidObjectiveWeights()) {
            $totalWeight = $okr->getTotalObjectiveWeight() * 100;
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot create check-in. The total objective weight for this OKR is {$totalWeight}%. It must be exactly 100%.",
                ], 400);
            }
            return back()
                ->withInput()
                ->with('error', "Cannot create check-in. The total objective weight for this OKR is {$totalWeight}%. It must be exactly 100%.");
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

        // Automatically submit for approval
        $checkIn->submitForApproval();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in created and submitted for approval',
            ]);
        }

        return redirect()
            ->route('admin.check-ins.index')
            ->with('success', 'Check-in created and submitted for approval.');
    }

    public function show($id)
    {
        $checkIn = CheckIn::with(['objective', 'objective.okr', 'objective.trackerEmployee', 'objective.approverEmployee', 'approvalLogs'])
            ->find($id);

        if (!$checkIn) {
            return redirect()
                ->route('admin.check-ins.index')
                ->with('error', 'Check-in not found.');
        }

        $checkIn->current_status = $checkIn->getCurrentStatusAttribute();

        return view('admin.check-ins.show', compact('checkIn'));
    }

    public function edit($id)
    {
        $checkIn = CheckIn::find($id);

        if (!$checkIn) {
            return redirect()
                ->route('admin.check-ins.index')
                ->with('error', 'Check-in not found.');
        }

        // Only allow editing if not approved or rejected
        if (!$checkIn->canBeEdited()) {
            return redirect()
                ->route('admin.check-ins.show', $id)
                ->with('error', 'Cannot edit a check-in that has been approved or rejected.');
        }

        $objectives = Objective::with(['okr', 'trackerEmployee', 'approverEmployee'])
            ->whereHas('okr', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        return view('admin.check-ins.edit', compact('checkIn', 'objectives'));
    }

    public function update(Request $request, $id)
    {
        $checkIn = CheckIn::find($id);

        if (!$checkIn) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in not found',
                ], 404);
            }
            return back()
                ->withInput()
                ->with('error', 'Check-in not found.');
        }

        // Only allow editing if not approved or rejected
        if (!$checkIn->canBeEdited()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit a check-in that has been approved or rejected',
                ], 400);
            }
            return back()
                ->withInput()
                ->with('error', 'Cannot edit a check-in that has been approved or rejected.');
        }

        $validator = Validator::make($request->all(), [
            'objective_id' => 'integer|exists:objective,id',
            'date' => 'date',
            'current_value' => 'numeric',
            'comments' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle file upload
        $evidencePath = $checkIn->evidence_path;
        if ($request->hasFile('evidence_file')) {
            // Delete old file if exists
            if ($evidencePath) {
                $oldPath = str_replace('/storage/', '', $evidencePath);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('evidence_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('evidence', $filename, 'public');
            $evidencePath = '/storage/' . $path;
        }

        $checkIn->update([
            'objective_id' => $request->objective_id ?? $checkIn->objective_id,
            'date' => $request->date ?? $checkIn->date,
            'current_value' => $request->current_value ?? $checkIn->current_value,
            'comments' => $request->comments ?? $checkIn->comments,
            'evidence_path' => $evidencePath,
        ]);

        // Re-submit for approval after update
        $checkIn->submitForApproval();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in updated and submitted for approval',
            ]);
        }

        return redirect()
            ->route('admin.check-ins.index')
            ->with('success', 'Check-in updated and submitted for approval.');
    }

    public function destroy($id)
    {
        $checkIn = CheckIn::find($id);

        if (!$checkIn) {
            return redirect()
                ->route('admin.check-ins.index')
                ->with('error', 'Check-in not found.');
        }

        // Delete evidence file if exists
        if ($checkIn->evidence_path) {
            $path = str_replace('/storage/', '', $checkIn->evidence_path);
            Storage::disk('public')->delete($path);
        }

        $checkIn->delete();

        return redirect()
            ->route('admin.check-ins.index')
            ->with('success', 'Check-in deleted successfully.');
    }

    public function approve(Request $request, $id)
    {
        $checkIn = CheckIn::with(['objective', 'objective.okr'])->find($id);

        if (!$checkIn) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in not found',
                ], 404);
            }
            return back()->with('error', 'Check-in not found.');
        }

        // Check if the OKR is active
        if (!$checkIn->objective->okr || !$checkIn->objective->okr->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve check-in for an inactive OKR.',
                ], 400);
            }
            return back()->with('error', 'Cannot approve check-in for an inactive OKR.');
        }

        if ($checkIn->isApproved()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in has already been approved',
                ], 400);
            }
            return back()->with('error', 'Check-in has already been approved.');
        }

        if ($checkIn->isRejected()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve a rejected check-in',
                ], 400);
            }
            return back()->with('error', 'Cannot approve a rejected check-in.');
        }

        $checkIn->approve();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in approved successfully',
            ]);
        }

        return back()->with('success', 'Check-in approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $checkIn = CheckIn::with(['objective', 'objective.okr'])->find($id);

        if (!$checkIn) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in not found',
                ], 404);
            }
            return back()->with('error', 'Check-in not found.');
        }

        // Check if the OKR is active
        if (!$checkIn->objective->okr || !$checkIn->objective->okr->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reject check-in for an inactive OKR.',
                ], 400);
            }
            return back()->with('error', 'Cannot reject check-in for an inactive OKR.');
        }

        if ($checkIn->isApproved()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reject an approved check-in',
                ], 400);
            }
            return back()->with('error', 'Cannot reject an approved check-in.');
        }

        if ($checkIn->isRejected()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in has already been rejected',
                ], 400);
            }
            return back()->with('error', 'Check-in has already been rejected.');
        }

        $checkIn->reject();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in rejected successfully',
            ]);
        }

        return back()->with('success', 'Check-in rejected successfully.');
    }

    public function pendingApprovals()
    {
        // Get all check-ins with pending approval
        $checkIns = CheckIn::whereHas('approvalLogs', function ($query) {
            $query->pending();
        })
            ->whereDoesntHave('approvalLogs', function ($query) {
                $query->approved();
            })
            ->whereDoesntHave('approvalLogs', function ($query) {
                $query->rejected();
            })
            ->with(['objective', 'objective.okr.employee', 'objective.okr.orgUnit', 'objective.trackerEmployee', 'objective.approverEmployee', 'approvalLogs'])
            ->orderBy('date', 'desc')
            ->get();

        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        return view('admin.check-ins.pending', compact('checkIns'));
    }

    public function getByObjective($objectiveId)
    {
        $objective = Objective::with(['okr'])->find($objectiveId);

        if (!$objective) {
            return redirect()
                ->route('admin.check-ins.index')
                ->with('error', 'Objective not found.');
        }

        $checkIns = CheckIn::where('objective_id', $objectiveId)
            ->with(['approvalLogs'])
            ->orderBy('date', 'desc')
            ->get();

        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        return view('admin.check-ins.by-objective', compact('objective', 'checkIns'));
    }

    /**
     * Get updated progress data for objective and its OKR (for AJAX)
     */
    public function getProgressData($objectiveId)
    {
        $objective = Objective::with('okr')->find($objectiveId);

        if (!$objective) {
            return response()->json([
                'success' => false,
                'message' => 'Objective not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'objective_id' => $objective->id,
                'objective_current_value' => $objective->current_value,
                'objective_target_value' => $objective->target_value,
                'objective_progress' => $objective->progress,
                'okr_id' => $objective->okr->id,
                'okr_progress' => $objective->okr->progress,
            ],
        ]);
    }

    /**
     * Get check-ins by objective as JSON (for AJAX)
     */
    public function getByObjectiveJson($objectiveId)
    {
        $checkIns = CheckIn::where('objective_id', $objectiveId)
            ->with(['objective.trackerEmployee', 'objective.approverEmployee', 'approvalLogs'])
            ->orderBy('date', 'desc')
            ->get();

        $checkIns->each(function ($checkIn) {
            $checkIn->current_status = $checkIn->getCurrentStatusAttribute();
        });

        // Check if there's a pending check-in (blocks new check-ins)
        $hasPendingCheckIn = $checkIns->contains(function ($checkIn) {
            return $checkIn->current_status === 'pending';
        });

        return response()->json([
            'success' => true,
            'data' => $checkIns,
            'has_pending_check_in' => $hasPendingCheckIn,
        ]);
    }
}
