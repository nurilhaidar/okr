<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Okr;
use App\Models\CheckIn;
use App\Models\OrgUnit;

class DashboardController extends Controller
{
    /**
     * Show employee dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's OKRs where they are owner (employee_id = user's id)
        $myOkrs = Okr::where('employee_id', $user->id)
            ->with('okrType')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeOkrs = $myOkrs->where('is_active', true);

        // Get objectives where user is tracker
        $objectivesToTrack = $user->trackedObjectives()
            ->with('okr')
            ->get();

        // Get pending approvals (check-ins where user is approver and has pending approval)
        $pendingApprovals = CheckIn::whereHas('objective', function ($query) use ($user) {
            $query->where('approver', $user->id);
        })
            ->whereHas('approvalLogs', function ($query) {
                $query->where('status', 'pending');
            })
            ->with(['objective', 'objective.okr', 'objective.trackerEmployee', 'approvalLogs'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact('user', 'myOkrs', 'activeOkrs', 'objectivesToTrack', 'pendingApprovals'));
    }

    /**
     * Show admin dashboard.
     */
    public function admin()
    {
        $user = Auth::user();

        // Check if user is admin
        if (!$user->role || $user->role->name !== 'Admin') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Get active OKRs owned by orgunits (teams) with their progress
        $teamOkrs = Okr::active()
            ->whereNotNull('orgunit_id')
            ->with(['okrType', 'orgUnit', 'objectives'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact(
            'user',
            'teamOkrs'
        ));
    }

    /**
     * Get user profile.
     */
    public function profile()
    {
        $user = Auth::user()->load(['role', 'orgUnits.type', 'orgUnits.parent']);

        return view('profile', compact('user'));
    }
}
