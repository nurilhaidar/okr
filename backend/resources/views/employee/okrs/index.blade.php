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
            padding: 12px;
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
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">My OKRs</h4>
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
                                        <a href="{{ route('okrs.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i>Create OKR
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Preserve filter parameters -->
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
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $okr->name }}</div>
                                                <small class="text-muted">{{ $okr->objectives->count() }}
                                                    objectives</small>
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
                                                @php
                                                    $roleBadge = '';
                                                    $roleIcon = '';
                                                    $roleLabel = '';
                                                    switch ($okr->role) {
                                                        case 'owner':
                                                            $roleBadge = 'bg-label-primary';
                                                            $roleIcon = '<i class="ti ti-user me-1"></i>';
                                                            $roleLabel = 'Owner';
                                                            break;
                                                        case 'tracker':
                                                            $roleBadge = 'bg-label-info';
                                                            $roleIcon = '<i class="ti ti-chart-line me-1"></i>';
                                                            $roleLabel = 'Tracker';
                                                            break;
                                                        case 'approver':
                                                            $roleBadge = 'bg-label-warning';
                                                            $roleIcon = '<i class="ti ti-shield-check me-1"></i>';
                                                            $roleLabel = 'Approver';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="badge {{ $roleBadge }}">{!! $roleIcon !!}
                                                    {{ $roleLabel }}</span>
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
            const status = new URLSearchParams(window.location.search).get('status') || '';

            let count = 0;
            if (search) count++;
            if (owner) count++;
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
            searchParams.delete('status');
            window.location.href = window.location.pathname;
        }
    </script>
@endsection
