@extends('layouts.app')

@section('title', 'OKRs - OKR Management System')

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
            margin-bottom: 12px;
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

        /* Check-in modal styles */
        .check-in-modal .modal-dialog {
            max-width: 900px;
        }

        .check-in-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 16px;
            overflow: hidden;
            background: #fff;
        }

        .check-in-card:last-child {
            margin-bottom: 0;
        }

        .check-in-card-header {
            background: #f8f9fa;
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
        }

        .check-in-card-body {
            padding: 16px;
        }

        .check-in-icon {
            width: 40px;
            height: 40px;
            background: #d1e7dd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .check-in-empty-state {
            text-align: center;
            padding: 32px;
            color: #6c757d;
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
        if ($progress >= 25) {
            return 'bg-warning';
        }
        return 'bg-danger';
    }
@endphp

@section('content')
    <!-- Admin View - All OKRs Table -->
    <div class="row">
        <div class="col-12 col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Objectives & Key Results</h4>
                    <!-- Search and Actions Form -->
                    <form method="GET" action="{{ route('admin.okrs') }}" id="searchForm">
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
                                        <a href="{{ route('admin.okrs.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i>Add OKR
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Preserve filter parameters -->
                        @if (request('owner'))
                            <input type="hidden" name="owner" value="{{ request('owner') }}">
                        @endif
                        @if (request('owner_type'))
                            <input type="hidden" name="owner_type" value="{{ request('owner_type') }}">
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
                    <form method="GET" action="{{ route('admin.okrs') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Owner Type</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <input type="hidden" name="owner_type" id="ownerTypeInput"
                                        value="{{ request('owner_type', '') }}">
                                    <button type="button"
                                        class="btn owner-type-btn {{ request('owner_type', '') === '' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        data-value="">
                                        All
                                    </button>
                                    <button type="button"
                                        class="btn owner-type-btn {{ request('owner_type') === 'employee' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        data-value="employee">
                                        <i class="ti ti-user me-1"></i>Employee
                                    </button>
                                    <button type="button"
                                        class="btn owner-type-btn {{ request('owner_type') === 'orgunit' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        data-value="orgunit">
                                        <i class="ti ti-building me-1"></i>Org Unit
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <input type="hidden" name="status" id="statusInput"
                                        value="{{ request('status', '') }}">
                                    <button type="button"
                                        class="btn status-btn {{ request('status', '') === '' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        data-value="">
                                        All
                                    </button>
                                    <button type="button"
                                        class="btn status-btn {{ request('status') === 'active' ? 'btn-success' : 'btn-outline-success' }}"
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
                        <!-- Preserve search and owner parameter -->
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
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Name</th>
                                    <th style="width: 12%;">Type</th>
                                    <th style="width: 12%;">Owner</th>
                                    <th style="width: 12%;">Period</th>
                                    <th style="width: 12%;">Progress</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($okrs as $okr)
                                    <tr class="okr-row" data-okr-id="{{ $okr->id }}" style="cursor: pointer;">
                                        <td>
                                            <div class="fw-bold">{{ $okr->name }}</div>
                                            <small class="text-muted">{{ $okr->objectives->count() }} objectives</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $okr->okrType->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if ($okr->owner_type === 'App\Models\Employee')
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-user me-1"></i>
                                                    <span>{{ $okr->owner->name ?? 'N/A' }}</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-building me-1"></i>
                                                    <span>{{ $okr->owner->name ?? 'N/A' }}</span>
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
                                                <div class="progress okr-progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar {{ getProgressColor($okr->progress) }}"
                                                        role="progressbar" style="width: {{ $okr->progress }}%"
                                                        aria-valuenow="{{ $okr->progress }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                                <span class="small fw-bold">{{ number_format($okr->progress, 1) }}%</span>
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
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.okrs.edit', $okr->id) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="event.stopPropagation();">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                                @if ($okr->is_active)
                                                    <form method="POST"
                                                        action="{{ route('admin.okrs.deactivate', $okr->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning"
                                                            onclick="event.stopPropagation(); return confirm('Are you sure you want to deactivate this OKR?');">
                                                            <i class="ti ti-player-pause"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST"
                                                        action="{{ route('admin.okrs.activate', $okr->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                                            onclick="event.stopPropagation();">
                                                            <i class="ti ti-player-play"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.okrs.destroy', $okr->id) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this OKR?');">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Objectives Row (hidden by default) -->
                                    <tr class="objectives-row" id="objectives-{{ $okr->id }}"
                                        style="display: none;">
                                        <td colspan="7" class="p-3 bg-light">
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
                                                        @if ($okr->is_active)
                                                            <button type="button" class="btn btn-sm btn-success"
                                                                onclick="openCheckInModal({{ $objective->id }}, '{{ $objective->description }}', {{ $objective->target_value }}, '{{ $objective->target_type }}')"
                                                                title="Check In">
                                                                <i class="ti ti-check me-1"></i>Check In
                                                            </button>
                                                        @else
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
                                        <td colspan="7" class="text-center text-muted">No OKRs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
        let currentObjectiveId = null;
        let currentTargetValue = 0;
        let currentTargetType = 'numeric';
        let checkInModal = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modal
            checkInModal = new bootstrap.Modal(document.getElementById('checkInModal'));

            // Check localStorage for success messages from form submission
            const successMessage = localStorage.getItem('toast_success');
            if (successMessage) {
                showToast('Success', successMessage, 'success');
                localStorage.removeItem('toast_success');
            }

            // Toggle objectives on OKR row click
            document.querySelectorAll('.okr-row').forEach(row => {
                row.addEventListener('click', function() {
                    const okrId = this.getAttribute('data-okr-id');
                    const objectivesRow = document.getElementById('objectives-' + okrId);
                    if (objectivesRow) {
                        objectivesRow.style.display = objectivesRow.style.display === 'none' ?
                            'table-row' : 'none';
                    }
                });
            });

            // Auto-dismiss alerts
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                });
            }, 5000);

            // Handle check-in form submission
            document.getElementById('checkInForm').addEventListener('submit', handleCheckInSubmit);

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

            // Handle owner type button clicks (update selection without submitting)
            document.querySelectorAll('.owner-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const ownerType = this.getAttribute('data-value');
                    document.getElementById('ownerTypeInput').value = ownerType;

                    // Update button styles
                    document.querySelectorAll('.owner-type-btn').forEach(b => {
                        const value = b.getAttribute('data-value');
                        if (value === ownerType) {
                            b.classList.remove('btn-outline-primary');
                            b.classList.add('btn-primary');
                        } else {
                            b.classList.remove('btn-primary');
                            b.classList.add('btn-outline-primary');
                        }
                    });
                });
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
                            b.classList.remove('btn-outline-primary', 'btn-outline-success',
                                'btn-outline-secondary');
                            if (value === '') {
                                b.classList.add('btn-primary');
                            } else if (value === 'active') {
                                b.classList.add('btn-success');
                            } else if (value === 'inactive') {
                                b.classList.add('btn-secondary');
                            }
                        } else {
                            // Remove solid classes and add outline classes
                            b.classList.remove('btn-primary', 'btn-success',
                                'btn-secondary');
                            if (value === '') {
                                b.classList.add('btn-outline-primary');
                            } else if (value === 'active') {
                                b.classList.add('btn-outline-success');
                            } else if (value === 'inactive') {
                                b.classList.add('btn-outline-secondary');
                            }
                        }
                    });
                });
            });
        });

        // Function to update filter badge count (from URL - on page load)
        function updateFilterBadge() {
            const search = new URLSearchParams(window.location.search).get('search') || '';
            const owner = new URLSearchParams(window.location.search).get('owner') || '';
            const ownerType = new URLSearchParams(window.location.search).get('owner_type') || '';
            const status = new URLSearchParams(window.location.search).get('status') || '';

            let count = 0;
            if (search) count++;
            if (owner) count++;
            if (ownerType) count++;
            if (status) count++;

            const badge = document.getElementById('filterBadge');
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        // Function to clear all filters and reload page
        function clearSearch() {
            const searchParams = new URLSearchParams(window.location.search);
            searchParams.delete('search');
            searchParams.delete('owner');
            searchParams.delete('owner_type');
            searchParams.delete('status');
            window.location.href = window.location.pathname;
        }

        function openCheckInModal(objectiveId, description, targetValue, targetType) {
            currentObjectiveId = objectiveId;
            currentTargetValue = targetValue;
            currentTargetType = targetType;

            // Set objective description
            document.getElementById('checkInObjectiveDescription').textContent = description;
            document.getElementById('checkInObjectiveId').value = objectiveId;

            // Set today's date as default
            document.getElementById('checkInDate').value = new Date().toISOString().split('T')[0];

            // Build current value input based on target type
            const container = document.getElementById('currentValueContainer');
            if (targetType === 'binary') {
                container.innerHTML = `
                    <select class="form-select" name="current_value" id="checkInCurrentValue" required>
                        <option value="">Select status</option>
                        <option value="0">0 - Not Achieved</option>
                        <option value="1">1 - Achieved</option>
                    </select>
                `;
            } else {
                container.innerHTML = `
                    <input type="number" class="form-control" name="current_value" id="checkInCurrentValue"
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
                const response = await fetch(`{{ route('admin.check-ins.by-objective-json', ':objectiveId') }}`
                    .replace(':objectiveId', objectiveId));
                if (!response.ok) throw new Error('Failed to load check-ins');

                const data = await response.json();

                if (data.data && data.data.length > 0) {
                    historyContainer.innerHTML = '';
                    data.data.forEach(checkIn => {
                        const card = createCheckInCard(checkIn);
                        historyContainer.appendChild(card);
                    });
                } else {
                    historyContainer.innerHTML = `
                        <div class="check-in-empty-state">
                            <i class="ti ti-clipboard-list" style="font-size: 40px;"></i>
                            <p class="mb-0 mt-2">No check-ins yet. Add your first check-in above.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading check-ins:', error);
                historyContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-circle me-2"></i>
                        Failed to load check-in history.
                    </div>
                `;
            }
        }

        function createCheckInCard(checkIn) {
            const status = checkIn.current_status || 'draft';
            const statusClass = getStatusBadgeClass(status);
            const progress = currentTargetValue > 0 ? Math.min(100, Math.max(0, (checkIn.current_value /
                currentTargetValue) * 100)) : 0;
            const date = new Date(checkIn.date).toLocaleDateString();

            const card = document.createElement('div');
            card.className = 'check-in-card';
            card.innerHTML = `
                <div class="check-in-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="check-in-icon">
                                <i class="ti ti-check text-success"></i>
                            </div>
                            <div>
                                <div class="fw-bold">${checkIn.current_value} / ${currentTargetValue}</div>
                                <small class="text-muted">${date}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge ${statusClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCheckIn(${checkIn.id})" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="check-in-card-body">
                    ${checkIn.comments ? `<p class="mb-2">${checkIn.comments}</p>` : ''}
                    ${checkIn.evidence_path ? `
                                            <a href="${checkIn.evidence_path}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                                <i class="ti ti-file me-1"></i>View Evidence
                                            </a>
                                        ` : ''}
                    ${status === 'pending' ? `
                                            <button type="button" class="btn btn-sm btn-success me-2" onclick="approveCheckIn(${checkIn.id})">
                                                <i class="ti ti-check me-1"></i>Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectCheckIn(${checkIn.id})">
                                                <i class="ti ti-x me-1"></i>Reject
                                            </button>
                                        ` : ''}
                </div>
            `;
            return card;
        }

        async function approveCheckIn(checkInId) {
            if (!confirm('Are you sure you want to approve this check-in?')) return;

            try {
                const response = await fetch(`{{ route('admin.check-ins.approve', ':id') }}`.replace(':id',
                    checkInId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showToast('Success', data.message || 'Check-in approved successfully', 'success');
                    await loadCheckIns(currentObjectiveId);
                } else {
                    showToast('Error', data.message || 'Failed to approve check-in', 'error');
                }
            } catch (error) {
                console.error('Error approving check-in:', error);
                showToast('Error', 'An error occurred while approving check-in', 'error');
            }
        }

        async function rejectCheckIn(checkInId) {
            if (!confirm('Are you sure you want to reject this check-in?')) return;

            try {
                const response = await fetch(`{{ route('admin.check-ins.reject', ':id') }}`.replace(':id',
                    checkInId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showToast('Success', data.message || 'Check-in rejected successfully', 'success');
                    await loadCheckIns(currentObjectiveId);
                } else {
                    showToast('Error', data.message || 'Failed to reject check-in', 'error');
                }
            } catch (error) {
                console.error('Error rejecting check-in:', error);
                showToast('Error', 'An error occurred while rejecting check-in', 'error');
            }
        }

        async function deleteCheckIn(checkInId) {
            if (!confirm('Are you sure you want to delete this check-in?')) return;

            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch(`/admin/check-ins/${checkInId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (response.ok || response.status === 204) {
                    showToast('Success', 'Check-in deleted successfully', 'success');
                    await loadCheckIns(currentObjectiveId);
                } else {
                    const data = await response.json();
                    showToast('Error', data.message || 'Failed to delete check-in', 'error');
                }
            } catch (error) {
                console.error('Error deleting check-in:', error);
                showToast('Error', 'An error occurred while deleting check-in', 'error');
            }
        }

        async function handleCheckInSubmit(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            formData.append('objective_id', currentObjectiveId);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Submitting...';

            try {
                const response = await fetch('{{ route('admin.check-ins.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    showToast('Success', data.message || 'Check-in created successfully', 'success');

                    // Reload check-ins and reset form
                    await loadCheckIns(currentObjectiveId);
                    form.reset();
                    document.getElementById('checkInDate').value = new Date().toISOString().split('T')[0];

                    // Re-populate current value input based on target type
                    const container = document.getElementById('currentValueContainer');
                    if (currentTargetType === 'binary') {
                        container.innerHTML = `
                            <select class="form-select" name="current_value" id="checkInCurrentValue" required>
                                <option value="">Select status</option>
                                <option value="0">0 - Not Achieved</option>
                                <option value="1">1 - Achieved</option>
                            </select>
                        `;
                    } else {
                        container.innerHTML = `
                            <input type="number" class="form-control" name="current_value" id="checkInCurrentValue"
                                step="0.01" min="0" placeholder="Enter current value" required>
                        `;
                    }
                } else {
                    showToast('Error', data.message || 'Failed to create check-in', 'error');
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        showToast('Validation Error', errorMessages, 'error');
                    }
                }
            } catch (error) {
                console.error('Error submitting check-in:', error);
                showToast('Error', 'An error occurred while creating check-in', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'approved':
                    return 'bg-success';
                case 'pending':
                    return 'bg-warning';
                case 'rejected':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }
    </script>
@endsection
