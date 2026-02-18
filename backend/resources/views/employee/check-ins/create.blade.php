@extends('layouts.employee')

@section('title', 'New Check-in - OKR Management System')

@push('styles')
    <style>
        .objective-card {
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .objective-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            border-color: #7367F0 !important;
        }

        .objective-card.selected {
            border-color: #7367F0 !important;
            background-color: #f8f9fa;
        }

        .objective-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .objective-card-header {
            background: #f8f9fa;
            padding: 16px 24px;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
        }

        .objective-card-body {
            padding: 24px;
        }

        .progress {
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.5s ease, background-color 0.5s ease;
        }
    </style>
@endpush

@php
    function getProgressColor($progress)
    {
        if ($progress >= 100) {
            return 'bg-success';
        } elseif ($progress >= 75) {
            return 'bg-primary';
        } elseif ($progress >= 50) {
            return 'bg-info';
        } elseif ($progress >= 25) {
            return 'bg-warning';
        }
        return 'bg-danger';
    }
@endphp

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-1">New Check-in</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Check-in Form -->
    <div class="row mb-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create New Check-in</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.check-ins.store') }}" enctype="multipart/form-data">
                        @csrf
                        <!-- Objective Selection -->
                        <div class="mb-4">
                            <label class="form-label">Select Objective <span class="text-danger">*</span></label>
                            <select name="objective_id" id="objective_id" class="form-select" required>
                                @foreach($objectives as $objective)
                                    <option value="{{ $objective->id }}" data-target="{{ $objective->target_value }}" data-description="{{ $objective->description }}">
                                        {{ $objective->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Input -->
                        <div class="mb-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" name="date" class="form-control" required value="{{ old('date', now()->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <!-- Target Value Input -->
                        <div class="mb-4">
                            <label class="form-label">Target Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="current_value" class="form-control" step="any" required id="current_value">
                            </div>
                        </div>

                        <!-- Tracking Type -->
                        <div class="mb-4">
                            <label class="form-label">Tracking Type <span class="text-danger">*</span></label>
                            <select name="tracking_type" id="tracking_type" class="form-select">
                                <option value="">Select tracking type...</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>

                        <!-- Comments -->
                        <div class="mb-4">
                            <label class="form-label">Comments (optional)</label>
                            <div class="input-group">
                                <textarea name="comments" class="form-control" rows="3" placeholder="Add any comments about this check-in..."></textarea>
                            </div>
                        </div>

                        <!-- Evidence File -->
                        <div class="mb-4">
                            <label class="form-label">Evidence File (optional)</label>
                            <div class="input-group">
                                <input type="file" name="evidence_file" class="form-control"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                                    onchange="document.getElementById('fileName').textContent = this.files[0].name">
                                >
                                <small class="text-muted">Supported: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (max 10MB)</small>
                            </div>
                        </div>

                        <!-- Progress Preview -->
                        <div class="mb-4">
                            <label class="form-label">Progress Preview</label>
                            <div class="progress" style="height: 20px; border-radius: 10px; background: #e9ecef; overflow: hidden;">
                                <div id="progressBar" class="progress-bar bg-secondary" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.check-ins.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="ti ti-check me-1"></i> Submit Check-in
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection