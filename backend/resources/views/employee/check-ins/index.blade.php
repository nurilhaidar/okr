@php
    $layout = auth()->user()->role && auth()->user()->role->name === 'Admin' ? 'layouts.app' : 'layouts.employee';
@endphp
@extends($layout)

@section('title', 'Check-ins - OKR Management System')

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Check-ins</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Check-ins</h4>
                            <p class="text-muted mb-0">Manage and review objective check-ins.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.check-ins.index') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text" class="form-control" name="search"
                                        placeholder="Search by objective or OKR name..." value="{{ request('search') }}"
                                        id="searchInput">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Status</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <input type="hidden" name="status" id="statusInput" value="{{ request('status', '') }}">
                                    <button type="button" class="status-btn btn {{ request('status', '') === '' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        data-status="">
                                        All
                                    </button>
                                    <button type="button" class="status-btn btn {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}"
                                        data-status="pending">
                                        <i class="ti ti-clock me-1"></i>Pending
                                    </button>
                                    <button type="button" class="status-btn btn {{ request('status') === 'approved' ? 'btn-success' : 'btn-outline-success' }}"
                                        data-status="approved">
                                        <i class="ti ti-check me-1"></i>Approved
                                    </button>
                                    <button type="button" class="status-btn btn {{ request('status') === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}"
                                        data-status="rejected">
                                        <i class="ti ti-x me-1"></i>Rejected
                                    </button>
                                    <a href="{{ route('admin.check-ins.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-refresh me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Check-ins Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Objective</th>
                                    <th>OKR</th>
                                    <th>OKR Owner</th>
                                    <th>Tracker</th>
                                    <th>Period</th>
                                    <th>Date</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($checkIns as $checkIn)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $checkIn->objective->description }}</div>
                                            <small class="text-muted">Target:
                                                {{ $checkIn->objective->target_value }}</small>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 120px;">
                                                {{ $checkIn->objective->okr->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($checkIn->objective->okr->owner)
                                                @if ($checkIn->objective->okr->owner_type === 'App\Models\Employee')
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-user me-1"></i>
                                                        <span>{{ $checkIn->objective->okr->owner->name ?? 'N/A' }}</span>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-building me-1"></i>
                                                        <span>{{ $checkIn->objective->okr->owner->name ?? 'N/A' }}</span>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($checkIn->objective->trackerEmployee)
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-user me-1"></i>
                                                    <span>{{ $checkIn->objective->trackerEmployee->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @php
                                                    $checkInDate = \Carbon\Carbon::parse($checkIn->date);
                                                    $trackingType = $checkIn->objective->tracking_type;
                                                    $periodEnd = $checkInDate->copy();

                                                    switch($trackingType) {
                                                        case 'daily':
                                                            $periodLabel = 'Daily';
                                                            $periodEnd->addDay();
                                                            break;
                                                        case 'weekly':
                                                            $periodLabel = 'Weekly';
                                                            $periodEnd->addWeek();
                                                            break;
                                                        case 'monthly':
                                                            $periodLabel = 'Monthly';
                                                            $periodEnd->addMonth();
                                                            break;
                                                        case 'quarterly':
                                                            $periodLabel = 'Quarterly';
                                                            $periodEnd->addMonths(3);
                                                            break;
                                                        default:
                                                            $periodLabel = '';
                                                            break;
                                                    }
                                                @endphp
                                                @if($periodLabel)
                                                    <span class="badge bg-label-info">{{ $periodLabel }}</span>
                                                    <br>
                                                    <span class="small">{{ $checkInDate->format('M d') }} - {{ $periodEnd->format('M d') }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($checkIn->date)->format('M d, Y') }}
                                        </td>
                                        <td>
                                            @php
                                                $progress =
                                                    $checkIn->objective->target_value > 0
                                                        ? ($checkIn->current_value /
                                                                $checkIn->objective->target_value) *
                                                            100
                                                        : 0;
                                                $progress = min(100, max(0, $progress));
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 50 ? 'primary' : 'warning') }}"
                                                        role="progressbar" style="width: {{ $progress }}%"
                                                        aria-valuenow="{{ $progress }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                                <span class="small">{{ number_format($progress, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($checkIn->current_status === 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @elseif($checkIn->current_status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($checkIn->current_status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($checkIn->current_status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ route('admin.check-ins.show', $checkIn->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.check-ins.destroy', $checkIn->id) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this check-in?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No check-ins found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Status button click handlers
            const statusButtons = document.querySelectorAll('.status-btn');
            const statusInput = document.getElementById('statusInput');
            const searchInput = document.getElementById('searchInput');
            const filterForm = document.getElementById('filterForm');

            statusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    statusInput.value = status;

                    // Update button styles
                    statusButtons.forEach(btn => {
                        const btnStatus = btn.getAttribute('data-status');
                        btn.classList.remove('btn-primary', 'btn-outline-primary',
                            'btn-warning', 'btn-outline-warning',
                            'btn-success', 'btn-outline-success',
                            'btn-danger', 'btn-outline-danger');

                        if (btnStatus === '') {
                            btn.classList.add(btnStatus === status ? 'btn-primary' : 'btn-outline-primary');
                        } else if (btnStatus === 'pending') {
                            btn.classList.add(btnStatus === status ? 'btn-warning' : 'btn-outline-warning');
                        } else if (btnStatus === 'approved') {
                            btn.classList.add(btnStatus === status ? 'btn-success' : 'btn-outline-success');
                        } else if (btnStatus === 'rejected') {
                            btn.classList.add(btnStatus === status ? 'btn-danger' : 'btn-outline-danger');
                        }
                    });

                    // Build URL with only non-empty parameters
                    const urlParams = new URLSearchParams();
                    if (searchInput.value.trim() !== '') {
                        urlParams.append('search', searchInput.value.trim());
                    }
                    if (status !== '') {
                        urlParams.append('status', status);
                    }

                    // Submit with clean URL
                    const queryString = urlParams.toString();
                    window.location.href = filterForm.action + (queryString ? '?' + queryString : '');
                });
            });

            // Auto-submit search on keyup with delay
            let searchTimeout;
            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    const statusValue = statusInput.value || '';
                    const searchValue = searchInput.value.trim();

                    // Build URL with only non-empty parameters
                    const urlParams = new URLSearchParams();
                    if (searchValue !== '') {
                        urlParams.append('search', searchValue);
                    }
                    if (statusValue !== '') {
                        urlParams.append('status', statusValue);
                    }

                    const queryString = urlParams.toString();
                    window.location.href = filterForm.action + (queryString ? '?' + queryString : '');
                }, 500);
            });
        });
    </script>
@endsection
