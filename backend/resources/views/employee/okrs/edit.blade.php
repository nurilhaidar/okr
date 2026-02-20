@extends('layouts.employee')

@section('title', 'Edit OKR - OKR Management System')

@push('styles')
    <style>
        .objective-item {
            background: #f8f8f8;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .objective-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .objective-title {
            font-weight: 600;
            font-size: 1rem;
            color: #333;
        }
    </style>
@endpush

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('okrs.index') }}">OKRs</a></li>
                    <li class="breadcrumb-item active">Edit OKR</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-1">Edit OKR</h4>
                            <p class="text-muted mb-0">Edit your Additional OKR</p>
                        </div>
                        <a href="{{ route('okrs.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-2"></i>Back to OKRs
                        </a>
                    </div>

                    <form method="POST" action="{{ route('okrs.update', $okr->id) }}" id="okrForm">
                        @csrf
                        @method('PUT')

                        <!-- OKR Details -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">OKR Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">OKR Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="e.g., Q1 Personal Goals"
                                            value="{{ old('name', $okr->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Weight <span class="text-danger">*</span></label>
                                        <input type="number" name="weight" class="form-control" step="0.01" min="0"
                                            max="100" value="{{ old('weight', $okr->weight * 100) }}" required>
                                        <small class="text-muted">Enter percentage (0-100%)</small>
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-4">
                                        <label class="form-label">OKR Type</label>
                                        <input type="text" class="form-control" value="Additional" readonly
                                            style="background-color: #e9ecef;">
                                        <input type="hidden" name="okr_type_id" value="{{ $okr->okr_type_id }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ old('start_date', $okr->start_date->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" class="form-control"
                                            value="{{ old('end_date', $okr->end_date->format('Y-m-d')) }}" required>
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label">Owner</label>
                                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly
                                            style="background-color: #e9ecef;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="is_active" class="form-select" required>
                                            <option value="1" {{ $okr->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$okr->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Objectives Section -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Objectives</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addObjective()">
                                    <i class="ti ti-plus me-1"></i>Add Objective
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info d-flex justify-content-between align-items-center mb-3" id="totalWeightAlert">
                                    <span><i class="ti ti-info-circle me-2"></i>Total Weight: <strong id="totalWeightDisplay">0</strong>% (must be exactly 100%)</span>
                                    <span class="badge bg-primary" id="weightStatus">Not Valid</span>
                                </div>
                                <div id="objectivesContainer">
                                    @forelse($okr->objectives as $index => $objective)
                                        <div class="objective-item" data-index="{{ $index }}">
                                            <div class="objective-header">
                                                <span class="objective-title">Objective #{{ $index + 1 }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeObjective(this)">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                                    <textarea name="objectives[{{ $index }}][description]" class="form-control" rows="2" required>{{ old('objectives.' . $index . '.description', $objective->description) }}</textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                                                    <input type="number" name="objectives[{{ $index }}][weight]" class="form-control" step="0.01" min="0" max="100" value="{{ old('objectives.' . $index . '.weight', $objective->weight * 100) }}" required>
                                                    <small class="text-muted">Enter percentage (0-100%)</small>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Target Type <span class="text-danger">*</span></label>
                                                    <select name="objectives[{{ $index }}][target_type]" class="form-select" required onchange="updateTargetValueField(this, {{ $index }})">
                                                        <option value="">Select Type</option>
                                                        <option value="numeric" {{ old('objectives.' . $index . '.target_type', $objective->target_type) == 'numeric' ? 'selected' : '' }}>Numeric</option>
                                                        <option value="binary" {{ old('objectives.' . $index . '.target_type', $objective->target_type) == 'binary' ? 'selected' : '' }}>Binary</option>
                                                        <option value="accounting" {{ old('objectives.' . $index . '.target_type', $objective->target_type) == 'accounting' ? 'selected' : '' }}>Accounting</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Target Value <span class="text-danger">*</span></label>
                                                    @php
                                                        $currentTargetType = old('objectives.' . $index . '.target_type', $objective->target_type);
                                                    @endphp
                                                    @if ($currentTargetType === 'binary')
                                                        <select name="objectives[{{ $index }}][target_value]" class="form-select" required>
                                                            <option value="">Select Status</option>
                                                            <option value="1" {{ old('objectives.' . $index . '.target_value', $objective->target_value) == 1 ? 'selected' : '' }}>Achieved</option>
                                                            <option value="0" {{ old('objectives.' . $index . '.target_value', $objective->target_value) == 0 ? 'selected' : '' }}>Not Achieved</option>
                                                        </select>
                                                    @elseif ($currentTargetType === 'accounting')
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input type="number" name="objectives[{{ $index }}][target_value]" class="form-control"
                                                                step="0.01" value="{{ old('objectives.' . $index . '.target_value', $objective->target_value) }}" required>
                                                            <span class="input-group-text">.00</span>
                                                        </div>
                                                    @else
                                                        <input type="number" name="objectives[{{ $index }}][target_value]" class="form-control" step="0.01" value="{{ old('objectives.' . $index . '.target_value', $objective->target_value) }}" required>
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                                    <input type="date" name="objectives[{{ $index }}][deadline]" class="form-control" value="{{ old('objectives.' . $index . '.deadline', $objective->deadline ? $objective->deadline->format('Y-m-d') : '') }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Tracking Type <span class="text-danger">*</span></label>
                                                    <select name="objectives[{{ $index }}][tracking_type]" class="form-select" required>
                                                        <option value="">Select Type</option>
                                                        <option value="daily" {{ old('objectives.' . $index . '.tracking_type', $objective->tracking_type) == 'daily' ? 'selected' : '' }}>Daily</option>
                                                        <option value="weekly" {{ old('objectives.' . $index . '.tracking_type', $objective->tracking_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                        <option value="monthly" {{ old('objectives.' . $index . '.tracking_type', $objective->tracking_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                        <option value="quarterly" {{ old('objectives.' . $index . '.tracking_type', $objective->tracking_type) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Tracker</label>
                                                    <select name="objectives[{{ $index }}][tracker]" class="form-select select2">
                                                        <option value="">Select Tracker (Optional)</option>
                                                        @foreach ($employees as $emp)
                                                            <option value="{{ $emp->id }}" {{ old('objectives.' . $index . '.tracker', $objective->tracker) == $emp->id ? 'selected' : '' }}>
                                                                {{ $emp->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Approver</label>
                                                    <select name="objectives[{{ $index }}][approver]" class="form-select select2">
                                                        <option value="">Select Approver (Optional)</option>
                                                        @foreach ($employees as $emp)
                                                            <option value="{{ $emp->id }}" {{ old('objectives.' . $index . '.approver', $objective->approver) == $emp->id ? 'selected' : '' }}>
                                                                {{ $emp->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <input type="hidden" name="objectives[{{ $index }}][id]" value="{{ $objective->id }}">
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                </div>
                                @if ($okr->objectives->count() === 0)
                                    <div class="text-center py-4 text-muted" id="noObjectivesMessage">
                                        <i class="ti ti-target-off" style="font-size: 40px;"></i>
                                        <p class="mb-0 mt-2">No objectives added yet. Click "Add Objective" to create one.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('okrs.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-2"></i>Back to OKRs
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-2"></i>Update OKR
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
        let objectiveIndex = {{ $okr->objectives->count() }};

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('.select2').select2({
                dropdownParent: $('#okrForm'),
                width: '100%'
            });

            // Initialize total weight display
            updateTotalWeightDisplay();

            // Add weight change listeners
            document.addEventListener('change', function(e) {
                if (e.target.name && e.target.name.includes('[weight]')) {
                    updateTotalWeightDisplay();
                }
            });

            // Show success message if exists
            const successMessage = localStorage.getItem('toast_success');
            if (successMessage) {
                showToast('Success', successMessage, 'success');
                localStorage.removeItem('toast_success');
            }
        });

        function addObjective() {
            const container = document.getElementById('objectivesContainer');
            const noObjectivesMsg = document.getElementById('noObjectivesMessage');
            if (noObjectivesMsg) {
                noObjectivesMsg.remove();
            }

            const objectiveHtml = `
                <div class="objective-item" data-index="${objectiveIndex}">
                    <div class="objective-header">
                        <span class="objective-title">Objective #${objectiveIndex + 1}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeObjective(this)">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="objectives[${objectiveIndex}][description]" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                            <input type="number" name="objectives[${objectiveIndex}][weight]" class="form-control" step="0.01" min="0" max="100" value="100" required>
                            <small class="text-muted">Enter percentage (0-100%)</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Target Type <span class="text-danger">*</span></label>
                            <select name="objectives[${objectiveIndex}][target_type]" class="form-select" required onchange="updateTargetValueField(this, ${objectiveIndex})">
                                <option value="">Select Type</option>
                                <option value="numeric">Numeric</option>
                                <option value="binary">Binary</option>
                                <option value="accounting">Accounting</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Target Value <span class="text-danger">*</span></label>
                            <input type="number" name="objectives[${objectiveIndex}][target_value]" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Deadline <span class="text-danger">*</span></label>
                            <input type="date" name="objectives[${objectiveIndex}][deadline]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tracking Type <span class="text-danger">*</span></label>
                            <select name="objectives[${objectiveIndex}][tracking_type]" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tracker</label>
                            <select name="objectives[${objectiveIndex}][tracker]" class="form-select select2">
                                <option value="">Select Tracker (Optional)</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Approver</label>
                            <select name="objectives[${objectiveIndex}][approver]" class="form-select select2">
                                <option value="">Select Approver (Optional)</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', objectiveHtml);

            // Initialize Select2 for new elements
            const newSelects = container.querySelectorAll('.objective-item:last-child .select2');
            newSelects.forEach(select => {
                $(select).select2({
                    dropdownParent: $('#okrForm'),
                    width: '100%'
                });
            });

            // Update total weight display
            updateTotalWeightDisplay();

            objectiveIndex++;
        }

        function removeObjective(button) {
            const objectiveItem = button.closest('.objective-item');
            objectiveItem.remove();

            // Update total weight display
            updateTotalWeightDisplay();

            // Check if no objectives left
            const container = document.getElementById('objectivesContainer');
            if (container.querySelectorAll('.objective-item').length === 0) {
                container.insertAdjacentHTML('afterbegin', `
                    <div class="text-center py-4 text-muted" id="noObjectivesMessage">
                        <i class="ti ti-target-off" style="font-size: 40px;"></i>
                        <p class="mb-0 mt-2">No objectives added yet. Click "Add Objective" to create one.</p>
                    </div>
                `);
            }
        }

        // Form submission handler
        document.getElementById('okrForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate total objective weight equals 100%
            const weightInputs = document.querySelectorAll('input[name*="[weight]"]');
            let totalWeight = 0;
            weightInputs.forEach(input => {
                totalWeight += parseFloat(input.value) || 0;
            });

            // Allow small floating point difference
            if (Math.abs(totalWeight - 100) > 0.01) {
                showToast('Validation Error', `Total objective weight must be exactly 100%. Current total: ${totalWeight.toFixed(2)}%`, 'error');
                return;
            }

            const form = this;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-2"></i>Saving...';

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                showToast('Success', data.message || 'OKR updated successfully', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("okrs.index") }}';
                }, 1000);
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (error.errors) {
                    const errorMessages = Object.values(error.errors).flat();
                    errorMessages.forEach(message => {
                        showToast('Error', message, 'error');
                    });
                } else if (error.message) {
                    showToast('Error', error.message, 'error');
                } else {
                    showToast('Error', 'An error occurred while updating the OKR', 'error');
                }
            });
        });

        function updateTotalWeightDisplay() {
            const weightInputs = document.querySelectorAll('input[name*="[weight]"]');
            let totalWeight = 0;
            weightInputs.forEach(input => {
                totalWeight += parseFloat(input.value) || 0;
            });

            const displayEl = document.getElementById('totalWeightDisplay');
            const statusEl = document.getElementById('weightStatus');
            const alertEl = document.getElementById('totalWeightAlert');

            if (displayEl) {
                displayEl.textContent = totalWeight.toFixed(2);
            }

            // Allow small floating point difference
            const isValid = Math.abs(totalWeight - 100) <= 0.01;

            if (statusEl && alertEl) {
                if (isValid) {
                    statusEl.textContent = 'Valid';
                    statusEl.className = 'badge bg-success';
                    alertEl.className = 'alert alert-success d-flex justify-content-between align-items-center mb-3';
                } else {
                    statusEl.textContent = 'Not Valid';
                    statusEl.className = 'badge bg-danger';
                    alertEl.className = 'alert alert-warning d-flex justify-content-between align-items-center mb-3';
                }
            }
        }

        function updateTargetValueField(select, index) {
            const container = select.closest('.row');
            const targetValueContainer = select.parentElement.nextElementSibling;
            const currentValueInput = container.querySelector(`[name*="[target_value]"]`);
            const currentValue = currentValueInput ? currentValueInput.value : '';

            let newField = '';
            const label = '<label class="form-label">Target Value <span class="text-danger">*</span></label>';

            if (select.value === 'binary') {
                newField = `
                    ${label}
                    <select name="objectives[${index}][target_value]" class="form-select" required>
                        <option value="">Select Status</option>
                        <option value="1" ${currentValue == 1 ? 'selected' : ''}>Achieved</option>
                        <option value="0" ${currentValue == 0 ? 'selected' : ''}>Not Achieved</option>
                    </select>
                `;
            } else if (select.value === 'accounting') {
                newField = `
                    ${label}
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="objectives[${index}][target_value]" class="form-control" step="0.01" value="${currentValue}" required>
                        <span class="input-group-text">.00</span>
                    </div>
                `;
            } else {
                newField = `
                    ${label}
                    <input type="number" name="objectives[${index}][target_value]" class="form-control" step="0.01" value="${currentValue}" required>
                `;
            }

            targetValueContainer.innerHTML = newField;
        }

        function showToast(title, message, type = 'info') {
            if (typeof toastr !== 'undefined') {
                toastr[type](message, title);
            } else {
                alert(`${title}: ${message}`);
            }
        }
    </script>
@endsection
