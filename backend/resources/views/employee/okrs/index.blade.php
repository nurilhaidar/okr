@extends('layouts.employee')

@section('title', 'My OKRs - OKR Management System')

@push('styles')
    <style>
        .okr-progress {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }

        .okr-progress .progress-bar {
            transition: width 0.5s ease, background-color 0.5s ease;
        }

        .objective-item {
            background: #f8f8f8;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 8px;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Fix Select2 overflow */
        #searchForm {
            overflow: visible;
        }

        #searchForm .select2-container {
            max-width: 100%;
        }

        .select2-owner-dropdown {
            max-width: 300px !important;
        }

        /* Smaller filter badge */
        .filter-badge-sm {
            font-size: 0.65rem !important;
            padding: 0 !important;
            min-width: 1.2rem;
            height: 1.2rem;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 0.25rem;
            line-height: 1 !important;
        }

        /* Check-in Modal Styles */
        .check-in-icon {
            width: 40px;
            height: 40px;
            background: #d1e7dd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .check-in-empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .timeline {
            position: relative;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-dot {
            position: absolute;
            left: -24px;
            top: 4px;
            width: 12px;
            height: 12px;
            background: #696cff;
            border-radius: 50%;
        }

        .timeline-content {
            background: #f8f8f8;
            border-radius: 6px;
            padding: 12px;
        }
    </style>
@endpush

@php
    function getProgressColor($progress)
    {
        $progress = (float) $progress;
        if ($progress >= 100) {
            return 'bg-success';
        }
        if ($progress >= 50) {
            return 'bg-primary';
        }
        return 'bg-danger';
    }
@endphp

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">
                        @switch($tab)
                            @case('my-okrs')
                                My OKRs
                                @break
                            @case('tracking')
                                Tracking
                                @break
                            @case('approving')
                                Approving
                                @break
                            @case('team-okrs')
                                Team OKRs
                                @break
                            @default
                                My OKRs
                        @endswitch
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Page Header with Tabs -->
    <div class="row mb-4">
        <div class="col-12 col-lg-12 mb-4">
            <!-- Navbar pills -->
            <ul class="nav nav-pills flex-column flex-sm-row mb-4">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'my-okrs' ? 'active' : '' }}"
                       href="{{ route('okrs.index', ['tab' => 'my-okrs', 'status' => request('status', 'active')]) }}">
                        <i class="ti-xs ti ti-user me-1"></i> My OKRs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'tracking' ? 'active' : '' }}"
                       href="{{ route('okrs.index', ['tab' => 'tracking', 'status' => request('status', 'active')]) }}">
                        <i class="ti-xs ti ti-chart-line me-1"></i> Tracking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'approving' ? 'active' : '' }}"
                       href="{{ route('okrs.index', ['tab' => 'approving', 'status' => request('status', 'active')]) }}">
                        <i class="ti-xs ti ti-shield-check me-1"></i> Approving
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'team-okrs' ? 'active' : '' }}"
                       href="{{ route('okrs.index', ['tab' => 'team-okrs', 'status' => request('status', 'active')]) }}">
                        <i class="ti-xs ti ti-users me-1"></i> Team OKRs
                    </a>
                </li>
            </ul>

            <!-- Search and Actions Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Page Title based on tab -->
                    <h4 class="mb-3">
                        @switch($tab)
                            @case('my-okrs')
                                My OKRs
                                @break
                            @case('tracking')
                                OKRs I'm Tracking
                                @break
                            @case('approving')
                                OKRs I'm Approving
                                @break
                            @case('team-okrs')
                                Team OKRs
                                @break
                            @default
                                All OKRs
                        @endswitch
                    </h4>

                    <!-- Search and Actions Form -->
                    <form method="GET" action="{{ route('okrs.index') }}" id="searchForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Search by Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text" class="form-control" name="search"
                                        placeholder="Search by OKR name..." value="{{ request('search') }}"
                                        id="searchInput">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search by Owner</label>
                                <select name="owner" class="form-select select2" id="ownerSelect">
                                    <option value="">All Owners</option>
                                    @php
                                        $owners = collect();
                                        foreach ($okrs as $okr) {
                                            if ($okr->employee_id && $okr->employee) {
                                                $owners->push(
                                                    (object) [
                                                        'id' => 'employee_' . $okr->employee_id,
                                                        'name' => $okr->employee->name,
                                                        'type' => 'employee',
                                                        'icon' => '👤',
                                                    ],
                                                );
                                            }
                                            if ($okr->orgunit_id && $okr->orgUnit) {
                                                $owners->push(
                                                    (object) [
                                                        'id' => 'orgunit_' . $okr->orgunit_id,
                                                        'name' => $okr->orgUnit->name,
                                                        'type' => 'orgunit',
                                                        'icon' => '🏢',
                                                    ],
                                                );
                                            }
                                        }
                                        $owners = $owners->unique('id')->sortBy('name');
                                    @endphp
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}" data-icon="{{ $owner->icon }}"
                                            {{ request('owner') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearSearch()" title="Reset">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <div class="ms-auto d-flex gap-2">
                                        <button type="button"
                                            class="btn btn-outline-primary position-relative d-flex align-items-center"
                                            data-bs-toggle="modal" data-bs-target="#filterModal" id="filterButton">
                                            <i class="ti ti-filter me-1"></i>Filter
                                            <span class="badge bg-primary text-white filter-badge-sm ms-1" id="filterBadge"
                                                style="display: none;">
                                                0
                                            </span>
                                        </button>
                                        @if($tab === 'my-okrs')
                                            <a href="{{ route('okrs.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus me-1"></i>Create OKR
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Preserve filter parameters -->
                        <input type="hidden" name="tab" value="{{ request('tab', 'my-okrs') }}">
                        @if (request('owner'))
                            <input type="hidden" name="owner" value="{{ request('owner') }}">
                        @endif
                        @if (request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter OKRs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="{{ route('okrs.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <input type="hidden" name="status" id="statusInput"
                                        value="{{ request('status', 'active') }}">
                                    <button type="button"
                                        class="btn status-btn {{ request('status', 'active') === 'active' ? 'btn-success' : 'btn-outline-success' }}"
                                        data-value="active">
                                        <i class="ti ti-check me-1"></i>Active
                                    </button>
                                    <button type="button"
                                        class="btn status-btn {{ request('status') === 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                        data-value="inactive">
                                        <i class="ti ti-x me-1"></i>Inactive
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Preserve search, owner, and tab parameter -->
                        <input type="hidden" name="tab" value="{{ request('tab', 'my-okrs') }}">
                        @if (request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if (request('owner'))
                            <input type="hidden" name="owner" value="{{ request('owner') }}">
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="filterForm" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- OKRs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if ($okrs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">OKR Name</th>
                                        <th style="width: 12%;">Type</th>
                                        <th style="width: 12%;">Owner</th>
                                        <th style="width: 12%;">Period</th>
                                        <th style="width: 12%;">Progress</th>
                                        <th style="width: 12%;">Status</th>
                                        <th style="width: 10%;">Role</th>
                                        <th style="width: 7%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($okrs as $okr)
                                        <tr class="okr-row" data-okr-id="{{ $okr->id }}" style="cursor: pointer;">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-chevron-right me-2" id="chevron-{{ $okr->id }}"></i>
                                                    <div>
                                                        <div class="fw-bold">{{ $okr->name }}</div>
                                                        <small class="text-muted">{{ $okr->objectives->count() }}
                                                            objectives</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-label-primary">{{ $okr->okrType->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if ($okr->employee)
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-user me-1"></i>
                                                        <span>{{ $okr->employee->name ?? 'N/A' }}</span>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-building me-1"></i>
                                                        <span>{{ $okr->orgUnit->name ?? 'N/A' }}</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($okr->start_date)->format('M d') }} -
                                                    {{ \Carbon\Carbon::parse($okr->end_date)->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar {{ getProgressColor($okr->progress) }}"
                                                            role="progressbar" style="width: {{ $okr->progress }}%"
                                                            aria-valuenow="{{ $okr->progress }}" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                    <span
                                                        class="small fw-bold">{{ number_format($okr->progress, 1) }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($okr->is_active)
                                                    <span class="badge bg-label-success">Active</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @php
                                                        $roles = $okr->roles ?? [$okr->role];
                                                        $roleConfig = [
                                                            'owner' => ['badge' => 'bg-label-primary', 'icon' => 'ti-user', 'label' => 'Owner'],
                                                            'tracker' => ['badge' => 'bg-label-info', 'icon' => 'ti-chart-line', 'label' => 'Tracker'],
                                                            'approver' => ['badge' => 'bg-label-warning', 'icon' => 'ti-shield-check', 'label' => 'Approver'],
                                                            'member' => ['badge' => 'bg-label-secondary', 'icon' => 'ti-users', 'label' => 'Member'],
                                                        ];
                                                    @endphp
                                                    @foreach ($roles as $r)
                                                        @if (isset($roleConfig[$r]))
                                                            <span class="badge {{ $roleConfig[$r]['badge'] }}">
                                                                <i class="ti {{ $roleConfig[$r]['icon'] }} me-1"></i>{{ $roleConfig[$r]['label'] }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('okrs.edit', $okr->id) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                        onclick="event.stopPropagation();">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Objectives Row (hidden by default) -->
                                        <tr class="objectives-row" id="objectives-{{ $okr->id }}"
                                            style="display: none;">
                                            <td colspan="8" class="p-3 bg-light">
                                                <h6 class="mb-3">Objectives</h6>
                                                @forelse($okr->objectives as $objective)
                                                    <div class="objective-item">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <div class="fw-bold">{{ $objective->description }}</div>
                                                                    @if ($objective->checkIns && $objective->checkIns->count() > 0)
                                                                        <span class="badge bg-label-primary rounded-pill">
                                                                            {{ $objective->checkIns->count() }}
                                                                            {{ str('check-in')->plural($objective->checkIns->count()) }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <small class="text-muted">
                                                                    Target: {{ $objective->target_value }}
                                                                    ({{ $objective->target_type }})
                                                                    |
                                                                    Deadline:
                                                                    {{ \Carbon\Carbon::parse($objective->deadline)->format('M d, Y') }}
                                                                    |
                                                                    Tracking: {{ ucfirst($objective->tracking_type) }}
                                                                    @if ($objective->trackerEmployee)
                                                                        | Tracker: {{ $objective->trackerEmployee->name }}
                                                                    @endif
                                                                    @if ($objective->approverEmployee)
                                                                        | Approver: {{ $objective->approverEmployee->name }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                            <div class="text-end ms-3">
                                                                <div class="small text-muted mb-1">
                                                                    {{ number_format($objective->weight * 100) }}%</div>
                                                                <div class="progress okr-progress"
                                                                    style="width: 100px; height: 6px;">
                                                                    <div class="progress-bar {{ getProgressColor($objective->progress) }}"
                                                                        role="progressbar"
                                                                        style="width: {{ $objective->progress }}%"
                                                                        aria-valuenow="{{ $objective->progress }}"
                                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                                <div class="small fw-bold">
                                                                    {{ number_format($objective->progress, 1) }}%</div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="small text-muted">
                                                                    {{ $objective->current_value ?? 0 }} /
                                                                    {{ $objective->target_value }}
                                                                </span>
                                                            </div>
                                                            @if ($okr->is_active && (int)$objective->tracker === (int)auth()->user()->id)
                                                                <button type="button" class="btn btn-sm btn-success"
                                                                    onclick="event.stopPropagation(); openCheckInModal({{ $objective->id }}, '{{ $objective->description }}', {{ $objective->target_value }}, '{{ $objective->target_type }}')">
                                                                    <i class="ti ti-check me-1"></i>Check In
                                                                </button>
                                                            @elseif (!$okr->is_active)
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary" disabled
                                                                    title="OKR is inactive"
                                                                    style="opacity: 0.5; cursor: not-allowed;">
                                                                    <i class="ti ti-check me-1"></i>Check In
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-muted">No objectives defined.</p>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No OKRs found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-clipboard-list" style="font-size: 40px;"></i>
                            <p class="mb-0">No OKRs found. Create your first OKR to get started.</p>
                            <a href="{{ route('okrs.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-2"></i>Create OKR
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Check-in Modal -->
    <div class="modal fade check-in-modal" id="checkInModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="check-in-icon me-2">
                            <i class="ti ti-check text-success"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0">Check In</h5>
                            <small class="text-muted" id="checkInObjectiveDescription"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Check-in Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-1">Add New Check-In</h6>
                        </div>
                        <div class="card-body">
                            <form id="checkInForm">
                                <input type="hidden" id="checkInObjectiveId" name="objective_id">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="date" id="checkInDate"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Current Value <span class="text-danger">*</span></label>
                                        <div id="currentValueContainer">
                                            <!-- Will be populated based on target type -->
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Comments</label>
                                        <textarea class="form-control" name="comments" id="checkInComments" rows="3"
                                            placeholder="Add comments about progress..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Evidence File (optional)</label>
                                        <input type="file" class="form-control" name="evidence_file"
                                            id="checkInEvidence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                                        <small class="text-muted">Accepted: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX</small>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-success">
                                            <i class="ti ti-check me-1"></i>Add Check-In
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Check-in History -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Check-In History</h6>
                        </div>
                        <div class="card-body" id="checkInHistory">
                            <div class="check-in-empty-state">
                                <i class="ti ti-clipboard-list" style="font-size: 40px;"></i>
                                <p class="mb-0 mt-2">No check-ins yet. Add your first check-in above.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update filter badge on page load
            updateFilterBadge();

            // Initialize Select2 for owner dropdown
            $('#ownerSelect').select2({
                dropdownParent: $('#searchForm'),
                width: 'resolve',
                dropdownAutoWidth: false,
                minimumResultsForSearch: 0,
                containerCssClass: 'select2-owner-container',
                dropdownCssClass: 'select2-owner-dropdown',
                templateResult: function(owner) {
                    if (!owner.id) {
                        return owner.text;
                    }
                    const $owner = $(owner.element);
                    const icon = $owner.data('icon') || '';
                    return $(`<span>${icon} ${owner.text}</span>`);
                },
                templateSelection: function(owner) {
                    if (!owner.id) {
                        return owner.text;
                    }
                    const $owner = $(owner.element);
                    const icon = $owner.data('icon') || '';
                    return $(`<span>${icon} ${owner.text}</span>`);
                }
            });

            // Handle status button clicks (update selection without submitting)
            document.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const status = this.getAttribute('data-value');
                    document.getElementById('statusInput').value = status;

                    // Update button styles
                    document.querySelectorAll('.status-btn').forEach(b => {
                        const value = b.getAttribute('data-value');
                        if (value === status) {
                            // Remove outline classes and add solid classes
                            b.classList.remove('btn-outline-success', 'btn-outline-secondary');
                            if (value === 'active') {
                                b.classList.add('btn-success');
                            } else if (value === 'inactive') {
                                b.classList.add('btn-secondary');
                            }
                        } else {
                            // Remove solid classes and add outline classes
                            b.classList.remove('btn-success', 'btn-secondary');
                            if (value === 'active') {
                                b.classList.add('btn-outline-success');
                            } else if (value === 'inactive') {
                                b.classList.add('btn-outline-secondary');
                            }
                        }
                    });
                });
            });

            // Toggle objectives on OKR row click
            document.querySelectorAll('.okr-row').forEach(row => {
                row.addEventListener('click', function() {
                    const okrId = this.getAttribute('data-okr-id');
                    const objectivesRow = document.getElementById('objectives-' + okrId);
                    const chevron = document.getElementById('chevron-' + okrId);
                    if (objectivesRow) {
                        const isHidden = objectivesRow.style.display === 'none';
                        objectivesRow.style.display = isHidden ? 'table-row' : 'none';
                        if (chevron) {
                            chevron.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
                            chevron.style.transition = 'transform 0.2s';
                        }
                    }
                });
            });
        });

        // Function to update filter badge count (from URL - on page load)
        function updateFilterBadge() {
            const search = new URLSearchParams(window.location.search).get('search') || '';
            const owner = new URLSearchParams(window.location.search).get('owner') || '';
            const status = new URLSearchParams(window.location.search).get('status') || 'active';

            let count = 0;
            if (search) count++;
            if (owner) count++;
            if (status && status !== 'active') count++;

            const badge = document.getElementById('filterBadge');
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        // Function to clear all filters and reload page (resets to active)
        function clearSearch() {
            const searchParams = new URLSearchParams(window.location.search);
            searchParams.delete('search');
            searchParams.delete('owner');
            searchParams.set('status', 'active');
            // Keep the current tab
            if (!searchParams.has('tab')) {
                searchParams.set('tab', 'my-okrs');
            }
            window.location.href = window.location.pathname + '?' + searchParams.toString();
        }

        // CheckIn Modal Variables and Functions
        let currentObjectiveId = null;
        let currentTargetValue = 0;
        let currentTargetType = 'numeric';
        let checkInModal = null;

        // Initialize checkIn modal when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            checkInModal = new bootstrap.Modal(document.getElementById('checkInModal'));

            // CheckIn form submission handler
            document.getElementById('checkInForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="ti ti-loader me-2"></i>Saving...';
                submitBtn.disabled = true;

                try {
                    const response = await fetch('{{ route('check-ins.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success || (data.message && !data.errors)) {
                        showToast('Success', data.message || 'Check-in added successfully!', 'success');
                        checkInModal.hide();
                        // Reload check-in history
                        if (currentObjectiveId) {
                            loadCheckIns(currentObjectiveId);
                            // Refresh page to show updated progress
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat();
                        errorMessages.forEach(message => {
                            showToast('Error', message, 'error');
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred while saving the check-in', 'error');
                } finally {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        });

        function openCheckInModal(objectiveId, description, targetValue, targetType) {
            currentObjectiveId = objectiveId;
            currentTargetValue = targetValue;
            currentTargetType = targetType;

            document.getElementById('checkInObjectiveId').value = objectiveId;
            document.getElementById('checkInObjectiveDescription').textContent = description;

            // Set default date to today
            document.getElementById('checkInDate').value = new Date().toISOString().split('T')[0];

            // Configure current value input based on target type
            const container = document.getElementById('currentValueContainer');
            if (targetType === 'binary') {
                container.innerHTML = `
                    <select class="form-select" name="current_value" required>
                        <option value="">Select status</option>
                        <option value="0">Not Done</option>
                        <option value="1">Done</option>
                    </select>
                `;
            } else {
                container.innerHTML = `
                    <input type="number" class="form-control" name="current_value" id="currentValue"
                        step="0.01" min="0" placeholder="Enter current value" required>
                `;
            }

            // Reset form and load check-ins
            document.getElementById('checkInComments').value = '';
            document.getElementById('checkInEvidence').value = '';
            loadCheckIns(objectiveId);

            // Show modal
            checkInModal.show();
        }

        async function loadCheckIns(objectiveId) {
            const historyContainer = document.getElementById('checkInHistory');

            try {
                const response = await fetch(`{{ route('check-ins.by-objective', ':objectiveId') }}`
                    .replace(':objectiveId', objectiveId));
                if (!response.ok) throw new Error('Failed to load check-ins');

                const data = await response.json();
                const checkIns = data.data || [];

                if (checkIns.length === 0) {
                    historyContainer.innerHTML = `
                        <div class="check-in-empty-state text-center py-4">
                            <i class="ti ti-clipboard-list" style="font-size: 40px;"></i>
                            <p class="mb-0 mt-2 text-muted">No check-ins yet. Add your first check-in above.</p>
                        </div>
                    `;
                    return;
                }

                let html = '<div class="timeline timeline-simple">';
                checkIns.forEach(checkIn => {
                    const statusClass = checkIn.latest_status === 'approved' ? 'bg-label-success' :
                                       checkIn.latest_status === 'rejected' ? 'bg-label-danger' :
                                       checkIn.latest_status === 'pending' ? 'bg-label-warning' : 'bg-label-secondary';

                    const statusText = checkIn.latest_status ? checkIn.latest_status.charAt(0).toUpperCase() + checkIn.latest_status.slice(1) : 'Draft';

                    html += `
                        <div class="timeline-item mb-3">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            ${checkIn.comments ? checkIn.comments.substring(0, 50) + (checkIn.comments.length > 50 ? '...' : '') : 'No comments'}
                                        </h6>
                                        <small class="text-muted">
                                            <i class="ti ti-calendar me-1"></i>${checkIn.date}
                                            <span class="mx-2">•</span>
                                            <i class="ti ti-chart-bar me-1"></i>${checkIn.current_value} / ${targetValue}
                                        </small>
                                    </div>
                                    <span class="badge ${statusClass}">${statusText}</span>
                                </div>
                                </div>
                    `;
                });
                html += '</div>';
                historyContainer.innerHTML = html;
            } catch (error) {
                console.error('Error loading check-ins:', error);
                historyContainer.innerHTML = '<p class="text-danger">Failed to load check-in history.</p>';
            }
        }

        function showToast(title, message, type = 'info') {
            // Check if toastr is available
            if (typeof toastr !== 'undefined') {
                toastr[type](message, title);
            } else {
                // Fallback to alert
                alert(`${title}: ${message}`);
            }
        }
    </script>
@endsection
