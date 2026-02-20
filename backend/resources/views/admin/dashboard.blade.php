@extends('layouts.app')

@section('title', 'Admin Dashboard - OKR Management System')

@section('content')
    @php
        $totalOkrs = $teamOkrs->count();
        $overallProgress = $totalOkrs > 0 ? $teamOkrs->avg('progress') : 0;
        $completedOkrs = $teamOkrs->where('progress', '>=', 100)->count();
        $onTrackOkrs = $teamOkrs->where('progress', '>=', 50)->where('progress', '<', 100)->count();
        $behindOkrs = $teamOkrs->where('progress', '<', 50)->count();
    @endphp

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold mb-1">Company Dashboard</h3>
            <p class="text-muted">Track your organization's OKR progress at a glance</p>
        </div>
    </div>

    @if($totalOkrs > 0)
        <!-- Main Progress Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Overall Company Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light"
                                    style="width: 180px; height: 180px;">
                                    <div class="text-center">
                                        <h2 class="mb-0 fw-bold {{ $overallProgress >= 80 ? 'text-success' : ($overallProgress >= 50 ? 'text-warning' : 'text-danger') }}">
                                            {{ number_format($overallProgress, 1) }}%
                                        </h2>
                                        <small class="text-muted">Complete</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="progress mb-3" style="height: 20px;">
                                    <div class="progress-bar {{ $overallProgress >= 80 ? 'bg-success' : ($overallProgress >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                        role="progressbar" style="width: {{ $overallProgress }}%">
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <h4 class="mb-1 text-success">{{ $completedOkrs }}</h4>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="mb-1 text-warning">{{ $onTrackOkrs }}</h4>
                                        <small class="text-muted">On Track</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="mb-1 text-danger">{{ $behindOkrs }}</h4>
                                        <small class="text-muted">Behind</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Breakdown -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Team Breakdown</h5>
                        <span class="badge bg-label-primary">{{ $totalOkrs }} Active OKRs</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Team</th>
                                        <th>OKR</th>
                                        <th class="text-end">Progress</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teamOkrs as $okr)
                                        @php
                                            $progress = $okr->progress ?? 0;
                                            $progressBarClass = $progress >= 80 ? 'bg-success' : ($progress >= 50 ? 'bg-warning' : 'bg-danger');
                                            $statusBadge = $progress >= 100
                                                ? '<span class="badge bg-label-success">Completed</span>'
                                                : ($progress >= 50
                                                    ? '<span class="badge bg-label-warning">On Track</span>'
                                                    : '<span class="badge bg-label-danger">Behind</span>');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar avatar-sm bg-label-info rounded-circle">
                                                        <span class="avatar-initials rounded-circle">
                                                            {{ strtoupper(substr($okr->orgUnit->name ?? 'N/A', 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <span class="fw-medium">{{ $okr->orgUnit->name ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $okr->name }}</span>
                                            </td>
                                            <td class="text-end" style="min-width: 150px;">
                                                <div class="d-flex align-items-center gap-2 justify-content-end">
                                                    <span class="text-muted small" style="min-width: 40px;">{{ number_format($progress, 0) }}%</span>
                                                    <div class="progress" style="height: 6px; width: 80px;">
                                                        <div class="progress-bar {{ $progressBarClass }}" style="width: {{ $progress }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{!! $statusBadge !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-5 text-center">
                        <div class="mb-4">
                            <i class="ti ti-chart-bar fs-1 text-muted"></i>
                        </div>
                        <h4 class="mb-2">No Active Team OKRs</h4>
                        <p class="text-muted mb-4">Start by creating OKRs for your teams to track progress.</p>
                        <a href="{{ route('admin.okrs.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-2"></i>Create Team OKR
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

