@extends('layouts.app')

@section('title', 'Edit Check-in - OKR Management System')

@push('styles')
    <style>
        .objective-card {
            transition: all 0.2s ease;
        }
        .objective-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .objective-card.selected {
            border-color: #7367F0 !important;
            background-color: #f8f7ff;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Check-in</h5>
                </div>
                <div class="card-body">
                    @if($checkIn->isApproved() || $checkIn->isRejected())
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            This check-in has been {{ $checkIn->current_status }} and cannot be edited.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.check-ins.update', $checkIn->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Objective Selection -->
                        <div class="mb-4">
                            <label class="form-label">Objective <span class="text-danger">*</span></label>
                            <select name="objective_id" id="objective_id" class="form-select" required {{ $checkIn->isApproved() || $checkIn->isRejected() ? 'disabled' : '' }}>
                                <option value="">Choose an objective...</option>
                                @foreach($objectives as $objective)
                                    <option value="{{ $objective->id }}"
                                        data-target="{{ $objective->target_value }}"
                                        data-description="{{ $objective->description }}"
                                        data-okr="{{ $objective->okr->name ?? 'N/A' }}"
                                        {{ old('objective_id', $checkIn->objective_id) == $objective->id ? 'selected' : '' }}>
                                        {{ $objective->description }}
                                        ({{ $objective->okr->name ?? 'No OKR' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selected Objective Info -->
                        <div id="objectiveInfo" class="alert alert-info">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">Current Objective</h6>
                                    <p class="mb-1">{{ $checkIn->objective->description }}</p>
                                    <small class="text-muted">
                                        OKR: {{ $checkIn->objective->okr->name ?? 'N/A' }}<br>
                                        Target: {{ $checkIn->objective->target_value }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Check-in Details -->
                        <div class="mb-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" required
                                value="{{ old('date', $checkIn->date->format('Y-m-d')) }}"
                                {{ $checkIn->isApproved() || $checkIn->isRejected() ? 'disabled' : '' }}>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Current Value <span class="text-danger">*</span></label>
                            <input type="number" name="current_value" class="form-control" step="any" required
                                placeholder="Enter current progress value"
                            <small class="text-muted">Target value: {{ $checkIn->objective->target_value }}</small>
                        </div>

                        <!-- Progress Preview -->
                        <div class="mb-4">
                            <label class="form-label">Progress Preview</label>
                            @php
                                $progress = ($checkIn->objective->target_value > 0)
                                    ? ($checkIn->current_value / $checkIn->objective->target_value) * 100
                                    : 0;
                                $progress = min(100, max(0, $progress));
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div id="progressBar" class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 50 ? 'primary' : 'warning') }}"
                                    role="progressbar" style="width: {{ $progress }}%">{{ number_format($progress, 1) }}%</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Comments</label>
                            <textarea name="comments" class="form-control" rows="3"
                                placeholder="Add any comments about this check-in...">{{ old('comments', $checkIn->comments) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Evidence File (optional)</label>
                            <input type="file" name="evidence_file" class="form-control"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                            @if($checkIn->evidence_path)
                                <small class="text-muted">
                                    Current: <a href="{{ $checkIn->evidence_path }}" target="_blank">View file</a>
                                </small>
                            @endif
                            <small class="text-muted d-block">Supported: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (max 10MB)</small>
                        </div>

                        <!-- Warning about re-approval -->
                        @if($checkIn->isRejected())
                            <div class="alert alert-warning mb-4">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Warning:</strong> After editing, this check-in will be re-submitted for approval.
                            </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.check-ins.show', $checkIn->id) }}" class="btn btn-label-secondary">Cancel</a>
                            @if($checkIn->canBeEdited())
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-2"></i>Update Check-in
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentValueInput = document.querySelector('input[name="current_value"]');
            const progressBar = document.getElementById('progressBar');
            const targetValue = {{ $checkIn->objective->target_value }};

            // Update progress bar when current value changes
            if (currentValueInput) {
                currentValueInput.value = {{ $checkIn->current_value }};
                currentValueInput.addEventListener('input', updateProgress);
            }

            function updateProgress() {
                const current = parseFloat(currentValueInput.value) || 0;
                const progress = targetValue > 0 ? Math.min(100, Math.max(0, (current / targetValue) * 100)) : 0;

                progressBar.style.width = progress + '%';
                progressBar.textContent = progress.toFixed(1) + '%';

                // Update color based on progress
                progressBar.classList.remove('bg-secondary', 'bg-success', 'bg-primary', 'bg-warning', 'bg-danger');
                if (progress >= 100) {
                    progressBar.classList.add('bg-success');
                } else if (progress >= 75) {
                    progressBar.classList.add('bg-primary');
                } else if (progress >= 50) {
                    progressBar.classList.add('bg-info');
                } else if (progress >= 25) {
                    progressBar.classList.add('bg-warning');
                } else {
                    progressBar.classList.add('bg-danger');
                }
            }
        });
    </script>
@endsection
