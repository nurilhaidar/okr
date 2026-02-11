@extends('layouts.app')

@section('title', 'Admin Dashboard - OKR Management System')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-1">Admin Dashboard</h4>
                    <p class="text-muted">Manage your organization's OKRs and track progress.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-primary rounded-3 me-3">
                            <i class="ti ti-users fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Total Employees</h6>
                            <h3 class="mb-0">{{ $totalEmployees }}</h3>
                            <small class="text-muted">{{ $activeEmployees }} active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-success rounded-3 me-3">
                            <i class="ti ti-target fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Total OKRs</h6>
                            <h3 class="mb-0">{{ $totalOkrs }}</h3>
                            <small class="text-muted">{{ $activeOkrs }} active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-info rounded-3 me-3">
                            <i class="ti ti-building fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Org Units</h6>
                            <h3 class="mb-0">{{ $totalOrgUnits }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-warning rounded-3 me-3">
                            <i class="ti ti-alert-circle fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Pending Approvals</h6>
                            <h3 class="mb-0">{{ $pendingApprovals->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="{{ route('admin.employees.create') }}"
                                class="btn btn-label-primary d-block h-100 py-4">
                                <i class="ti ti-user-plus fs-2 mb-2"></i>
                                <span class="d-block">Add Employee</span>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="#" class="btn btn-label-success d-block h-100 py-4">
                                <i class="ti ti-target-plus fs-2 mb-2"></i>
                                <span class="d-block">Create OKR</span>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="{{ route('admin.org-units.create') }}" class="btn btn-label-info d-block h-100 py-4">
                                <i class="ti ti-building-plus fs-2 mb-2"></i>
                                <span class="d-block">Add Org Unit</span>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="#" class="btn btn-label-warning d-block h-100 py-4">
                                <i class="ti ti-tag-plus fs-2 mb-2"></i>
                                <span class="d-block">Add OKR Type</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent OKRs -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent OKRs</h5>
                        <a href="#" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($recentOkrs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>OKR Name</th>
                                        <th>Owner</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOkrs as $okr)
                                        @php
                                            $progress = $okr->progress ?? 0;
                                            $progressClass =
                                                $progress >= 80
                                                    ? 'bg-success'
                                                    : ($progress >= 50
                                                        ? 'bg-warning'
                                                        : 'bg-danger');
                                            $statusBadge = $okr->is_active
                                                ? '<span class="badge bg-label-success">Active</span>'
                                                : '<span class="badge bg-label-secondary">Inactive</span>';
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $okr->name }}</strong>
                                            </td>
                                            <td>{{ $okr->owner->name ?? 'N/A' }}</td>
                                            <td><span
                                                    class="badge bg-label-primary">{{ $okr->okrType->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{!! $statusBadge !!}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar {{ $progressClass }}" role="progressbar"
                                                            style="width: {{ $progress }}%"></div>
                                                    </div>
                                                    <small class="text-muted"
                                                        style="min-width: 40px;">{{ number_format($progress, 1) }}%</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No OKRs found yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    @if ($pendingApprovals->count() > 0)
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pending Approvals</h5>
                            <a href="#" class="btn btn-sm btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Objective</th>
                                        <th>Tracker</th>
                                        <th>Check-in Date</th>
                                        <th>Current Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingApprovals->take(5) as $checkIn)
                                        <tr>
                                            <td>
                                                <strong>{{ $checkIn->objective->description }}</strong><br>
                                                <small
                                                    class="text-muted">{{ $checkIn->objective->okr->name ?? '' }}</small>
                                            </td>
                                            <td>{{ $checkIn->objective->trackerEmployee->name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($checkIn->date)->format('M d, Y') }}</td>
                                            <td>{{ $checkIn->current_value }} / {{ $checkIn->objective->target_value }}
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-success">
                                                        <i class="ti ti-check"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger">
                                                        <i class="ti ti-x"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Display toastr notifications for session messages
            @if (session('success'))
                showToast('Success', '{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showToast('Error', '{{ session('error') }}', 'error');
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    showToast('Validation Error', '{{ $error }}', 'error');
                @endforeach
            @endif

            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                });
            }, 5000);
        });
    </script>
@endsection
