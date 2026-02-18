<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Okr;
use App\Models\CheckIn;
use App\Models\OrgUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends \Illuminate\Routing\Controller
{
    /**
     * Show employee dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get employee's OKRs where they are owner (employee_id = user's id)
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
        $pendingApprovals = CheckIn::whereHas('approvalLogs', function ($query) use ($user) {
            $query->where('approver', $user->id);
        })
            ->whereHas('approvalLogs', function ($query) {
                $query->where('status', 'pending');
            })
            ->with(['objective', 'objective.okr', 'objective.trackerEmployee', 'approvalLogs'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard-employee', compact(
            'user',
            'myOkrs',
            'activeOkrs',
            'objectivesToTrack',
            'pendingApprovals'
        ));
    }
}
