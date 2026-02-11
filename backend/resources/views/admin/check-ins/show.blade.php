@extends('layouts.app')

@section('title', 'Check-in Details - OKR Management System')

@section('content')
    <div class="row">
        <!-- Back Button -->
        <div class="col-12 mb-3">
            <a href="{{ route('admin.check-ins.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-2"></i>Back to Check-ins
            </a>
        </div>

        <!-- Check-in Details -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Check-in Details</h5>
                    @if($checkIn->canBeEdited())
                        <a href="{{ route('admin.check-ins.edit', $checkIn->id) }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-pencil me-1"></i>Edit
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Objective</label>
                        <h6>{{ $checkIn->objective->description }}</h6>
                        <small class="text-muted">
                            OKR: {{ $checkIn->objective->okr->name ?? 'N/A' }}
                        </small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Date</label>
                            <div>{{ \Carbon\Carbon::parse($checkIn->date)->format('F d, Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                @if($checkIn->current_status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($checkIn->current_status === 'pending')
                                    <span class="badge bg-warning">Pending Approval</span>
                                @elseif($checkIn->current_status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($checkIn->current_status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Progress</label>
                        @php
                            $progress = ($checkIn->objective->target_value > 0)
                                ? ($checkIn->current_value / $checkIn->objective->target_value) * 100
                                : 0;
                            $progress = min(100, max(0, $progress));
                        @endphp
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 50 ? 'primary' : 'warning') }}"
                                role="progressbar" style="width: {{ $progress }}%">
                                {{ number_format($progress, 1) }}%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Current: {{ $checkIn->current_value }}</span>
                            <span>Target: {{ $checkIn->objective->target_value }}</span>
                        </div>
                    </div>

                    @if($checkIn->comments)
                        <div class="mb-3">
                            <label class="form-label text-muted">Comments</label>
                            <p>{{ $checkIn->comments }}</p>
                        </div>
                    @endif

                    @if($checkIn->evidence_path)
                        <div class="mb-3">
                            <label class="form-label text-muted">Evidence File</label>
                            <div>
                                <a href="{{ $checkIn->evidence_path }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-file me-1"></i>View Evidence
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approval Actions & Info -->
        <div class="col-12 col-lg-4">
            <!-- Approval Actions -->
            @if($checkIn->isPending())
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Approval Actions</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.check-ins.approve', $checkIn->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success me-2"
                                onclick="return confirm('Approve this check-in?');">
                                <i class="ti ti-check me-1"></i>Approve
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.check-ins.reject', $checkIn->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Reject this check-in?');">
                                <i class="ti ti-x me-1"></i>Reject
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- People -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">People</h6>
                </div>
                <div class="card-body">
                    @if($checkIn->objective->trackerEmployee)
                        <div class="d-flex align-items-center mb-3">
                            <i class="ti ti-user me-2"></i>
                            <div>
                                <small class="text-muted">Tracker</small>
                                <div>{{ $checkIn->objective->trackerEmployee->name }}</div>
                            </div>
                        </div>
                    @endif
                    @if($checkIn->objective->approverEmployee)
                        <div class="d-flex align-items-center">
                            <i class="ti ti-shield-check me-2"></i>
                            <div>
                                <small class="text-muted">Approver</small>
                                <div>{{ $checkIn->objective->approverEmployee->name }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval History -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Approval History</h6>
                </div>
                <div class="card-body">
                    @if($checkIn->approvalLogs->count() > 0)
                        <div class="timeline">
                            @foreach($checkIn->approvalLogs->sortBy('created_at') as $log)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $log->status === 'approved' ? 'success' : ($log->status === 'rejected' ? 'danger' : 'warning') }}"></div>
                                    <div class="timeline-content">
                                        <small class="text-muted">{{ $log->created_at->format('M d, Y - H:i') }}</small>
                                        <div class="badge bg-{{ $log->status === 'approved' ? 'success' : ($log->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($log->status) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No approval history yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 20px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
            padding-left: 20px;
            border-left: 2px solid #e0e0e0;
        }
        .timeline-item:last-child {
            border-left: none;
        }
        .timeline-marker {
            position: absolute;
            left: -9px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #fff;
        }
    </style>
@endsection

@section('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page scripts here
        });
    </script>
@endsection
