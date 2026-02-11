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
     * Show the employee dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's OKRs where they are the owner
        $myOkrs = Okr::where('owner_type', Employee::class)
            ->where('owner_id', $user->id)
            ->with('okrType')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeOkrs = $myOkrs->where('is_active', true);

        // Get objectives where user is the tracker
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
     * Show the admin dashboard.
     */
    public function admin()
    {
        $user = Auth::user();

        // Check if user is admin
        if (!$user->role || $user->role->name !== 'Admin') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Get overall statistics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('is_active', true)->count();

        $totalOkrs = Okr::count();
        $activeOkrs = Okr::where('is_active', true)->count();

        $totalOrgUnits = OrgUnit::where('is_active', true)->count();

        // Get recent OKRs
        $recentOkrs = Okr::with(['okrType', 'owner'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get pending approvals (check-ins with pending approval status)
        $pendingApprovals = CheckIn::whereHas('approvalLogs', function ($query) {
            $query->where('status', 'pending');
        })
            ->with(['objective', 'objective.okr', 'objective.trackerEmployee', 'approvalLogs'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'user',
            'totalEmployees',
            'activeEmployees',
            'totalOkrs',
            'activeOkrs',
            'totalOrgUnits',
            'recentOkrs',
            'pendingApprovals'
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
