@extends('layouts.app')

@section('title', 'Dashboard - OKR Management System')

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-primary rounded-3 me-3">
                            <i class="ti ti-user fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">My OKRs</h6>
                            <h3 class="mb-0">{{ $totalOkrs }}</h3>
                            <small class="text-muted">{{ $activeOkrs }} active</small>
                        </div>
                    </div>
                </div>
            </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-success rounded-3 me-3">
                            <i class="ti ti-check-square fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Completed</h6>
                            <h3 class="mb-0">{{ $completedOkrs ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-info rounded-3 me-3">
                            <i class="ti ti-clipboard-check fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Pending Approvals</h6>
                            <h3 class="mb-0">{{ $pendingApprovals->count() ?? 0 }}</h3>
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
                            <a href="{{ route('okrs.create') }}" class="btn btn-label-primary d-block h-100 py-4">
                                <i class="ti ti-target-plus fs-2 mb-2"></i>
                                <span class="d-block">Create OKR</span>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="{{ route('check-ins.create') }}" class="btn btn-label-success d-block h-100 py-4">
                                <i class="ti ti-plus fs-2 mb-2"></i>
                                <span class="d-block">New Check-In</span>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="{{ route('objectives.create') }}" class="btn btn-label-info d-block h-100 py-4">
                                <i class="ti ti-briefcase-plus fs-2 mb-2"></i>
                                <span class="d-block">Add Objective</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My OKRs -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My OKRs</h5>
                        <a href="{{ route('okrs') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($myOkrs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>OKR Name</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th>Deadline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($myOkrs->take(5) as $okr)
                                        <tr>
                                            <td>
                                                <strong>{{ $okr->name }}</strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($okr->progress ?? 0) }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($okr->progress ?? 0, 1) }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($okr->is_active)
                                                    <span class="badge bg-label-success">Active</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $okr->end_date ? $okr->end_date->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
                                        <th>OKR</th>
                                        <th>Current Value</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingApprovals->take(5) as $checkIn)
                                        <tr>
                                            <td>
                                                <strong>{{ $checkIn->objective->description }}</strong><br>
                                                <small class="text-muted">{{ $checkIn->objective->okr->name ?? '' }}</small>
                                            </td>
                                            <td>{{ $checkIn->okr->name ?? 'N/A' }}</td>
                                            <td>{{ $checkIn->current_value }} / {{ $checkIn->objective->target_value }}</td>
                                            <td>{{ \Carbon\Carbon::parse($checkIn->date)->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-success" onclick="approveCheckIn({{ $checkIn->id }}, {{ $checkIn->objective->id }})">
                                                        <i class="ti ti-check"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="rejectCheckIn({{ $checkIn->id }}, {{ $checkIn->objective->id }})">
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

        function approveCheckIn(checkInId, objectiveId) {
            if (!confirm('Approve this check-in?')) return;

            fetch(`/check-ins/${checkInId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    showToast('Success', 'Check-in approved successfully', 'success');
                    location.reload();
                } else {
                    return response.json().then(data => {
                        showToast('Error', data.message || 'Failed to approve check-in', 'error');
                    });
                }
            });
        }

        function rejectCheckIn(checkInId, objectiveId) {
            if (!confirm('Reject this check-in?')) return;

            fetch(`/check-ins/${checkInId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    showToast('Success', 'Check-in rejected successfully', 'success');
                    location.reload();
                } else {
                    return response.json().then(data => {
                        showToast('Error', data.message || 'Failed to reject check-in', 'error');
                    });
                }
            });
        }
    </script>
@endsection
