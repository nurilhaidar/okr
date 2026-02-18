@php
    $layout = auth()->user()->role && auth()->user()->role->name === 'Admin' ? 'layouts.app' : 'layouts.employee';
@endphp
@extends($layout)

@section('title', 'New Check-in - OKR Management System')

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
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.check-ins.index') }}">Check-ins</a></li>
                    <li class="breadcrumb-item active">Create New Check-in</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create New Check-in</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.check-ins.store') }}" enctype="multipart/form-data" id="checkInForm">
                        @csrf

                        <!-- Objective Selection -->
                        <div class="mb-4">
                            <label class="form-label">Select Objective <span class="text-danger">*</span></label>
                            <select name="objective_id" id="objective_id" class="form-select" required>
                                <option value="">Choose an objective...</option>
                                @foreach($objectives as $objective)
                                    <option value="{{ $objective->id }}"
                                        data-target="{{ $objective->target_value }}"
                                        data-description="{{ $objective->description }}"
                                        data-okr="{{ $objective->okr->name ?? 'N/A' }}">
                                        {{ $objective->description }}
                                        ({{ $objective->okr->name ?? 'No OKR' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selected Objective Info -->
                        <div id="objectiveInfo" class="alert alert-info" style="display: none;">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">Selected Objective</h6>
                                    <p class="mb-1" id="objectiveDescription"></p>
                                    <small class="text-muted">
                                        OKR: <span id="objectiveOkr"></span><br>
                                        Target: <span id="objectiveTarget"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Check-in Details -->
                        <div class="mb-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" required
                                value="{{ old('date', now()->format('Y-m-d')) }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Current Value <span class="text-danger">*</span></label>
                            <input type="number" name="current_value" class="form-control" step="any" required
                                placeholder="Enter current progress value" id="current_value">
                            <small class="text-muted">Target value: <span id="targetValue">-</span></small>
                        </div>

                        <!-- Progress Preview -->
                        <div class="mb-4">
                            <label class="form-label">Progress Preview</label>
                            <div class="progress" style="height: 20px;">
                                <div id="progressBar" class="progress-bar bg-secondary" role="progressbar"
                                    style="width: 0%">0%</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Comments</label>
                            <textarea name="comments" class="form-control" rows="3"
                                placeholder="Add any comments about this check-in...">{{ old('comments') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Evidence File (optional)</label>
                            <input type="file" name="evidence_file" class="form-control"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                            <small class="text-muted">Supported: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (max 10MB)</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.check-ins.index') }}" class="btn btn-label-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="ti ti-check me-2"></i>Submit Check-in
                            </button>
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
            const objectiveSelect = document.getElementById('objective_id');
            const currentValueInput = document.getElementById('current_value');
            const progressBar = document.getElementById('progressBar');
            const objectiveInfo = document.getElementById('objectiveInfo');
            const targetValueSpan = document.getElementById('targetValue');

            // Update objective info when selected
            objectiveSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (this.value) {
                    const target = parseFloat(selectedOption.dataset.target);
                    document.getElementById('objectiveDescription').textContent = selectedOption.dataset.description;
                    document.getElementById('objectiveOkr').textContent = selectedOption.dataset.okr;
                    document.getElementById('objectiveTarget').textContent = target;
                    targetValueSpan.textContent = target;
                    objectiveInfo.style.display = 'block';
                    updateProgress();
                } else {
                    objectiveInfo.style.display = 'none';
                    targetValueSpan.textContent = '-';
                    progressBar.style.width = '0%';
                    progressBar.textContent = '0%';
                }
            });

            // Update progress bar when current value changes
            currentValueInput.addEventListener('input', updateProgress);

            function updateProgress() {
                const selectedOption = objectiveSelect.options[objectiveSelect.selectedIndex];
                if (objectiveSelect.value && selectedOption) {
                    const target = parseFloat(selectedOption.dataset.target);
                    const current = parseFloat(currentValueInput.value) || 0;
                    const progress = target > 0 ? Math.min(100, Math.max(0, (current / target) * 100)) : 0;

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
            }
        });
    </script>
@endsection
