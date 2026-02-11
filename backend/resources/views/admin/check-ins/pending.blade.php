@extends('layouts.app')

@section('title', 'Pending Approvals - OKR Management System')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Pending Check-in Approvals</h4>
                            <p class="text-muted mb-0">Review and approve or reject pending check-ins.</p>
                        </div>
                        <a href="{{ route('admin.check-ins.index') }}" class="btn btn-outline-primary">
                            <i class="ti ti-list me-2"></i>All Check-ins
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Check-ins Table -->
    <div class="row">
        <div class="col-12">
            @if($checkIns->count() > 0)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Objective</th>
                                        <th>OKR</th>
                                        <th>Submitted By</th>
                                        <th>Date</th>
                                        <th>Current / Target</th>
                                        <th>Progress</th>
                                        <th>Comments</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($checkIns as $checkIn)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $checkIn->objective->description }}</div>
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                                    {{ $checkIn->objective->okr->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($checkIn->objective->trackerEmployee)
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-user me-1"></i>
                                                        <span>{{ $checkIn->objective->trackerEmployee->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($checkIn->date)->format('M d, Y') }}
                                            </td>
                                            <td>
                                                {{ $checkIn->current_value }} / {{ $checkIn->objective->target_value }}
                                            </td>
                                            <td>
                                                @php
                                                    $progress = ($checkIn->objective->target_value > 0)
                                                        ? ($checkIn->current_value / $checkIn->objective->target_value) * 100
                                                        : 0;
                                                    $progress = min(100, max(0, $progress));
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                        <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 50 ? 'primary' : 'warning') }}"
                                                            role="progressbar" style="width: {{ $progress }}%"></div>
                                                    </div>
                                                    <span class="small">{{ number_format($progress, 1) }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($checkIn->comments)
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                                        {{ $checkIn->comments }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('admin.check-ins.show', $checkIn->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('admin.check-ins.approve', $checkIn->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                                            title="Approve"
                                                            onclick="return confirm('Approve this check-in?');">
                                                            <i class="ti ti-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST"
                                                        action="{{ route('admin.check-ins.reject', $checkIn->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="Reject"
                                                            onclick="return confirm('Reject this check-in?');">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-checkbox display-4 text-muted mb-3"></i>
                        <h5 class="mb-2">No Pending Approvals</h5>
                        <p class="text-muted">All check-ins have been reviewed.</p>
                        <a href="{{ route('admin.check-ins.index') }}" class="btn btn-primary mt-3">
                            <i class="ti ti-list me-2"></i>View All Check-ins
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page scripts here
        });
    </script>
@endsection
