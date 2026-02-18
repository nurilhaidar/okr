@php
    $isEdit = isset($okr);
    $formAction = $isEdit ? route('admin.okrs.update', $okr->id) : route('admin.okrs.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

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

<form method="POST" action="{{ $formAction }}" id="okrForm">
    @csrf
    @method($formMethod)
    <input type="hidden" name="okr_id" value="{{ $isEdit ? $okr->id : '' }}">

    <!-- OKR Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">OKR Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">OKR Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Q1 Sales Goals"
                        value="{{ old('name', $isEdit ? $okr->name : '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Weight <span class="text-danger">*</span></label>
                    <input type="number" name="weight" class="form-control" step="0.01" min="0"
                        max="100" value="{{ old('weight', $isEdit ? $okr->weight * 100 : 100) }}" required>
                    <small class="text-muted">Enter percentage (0-100%)</small>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label class="form-label">OKR Type <span class="text-danger">*</span></label>
                    <select name="okr_type_id" id="okr_type_id" class="form-select" required
                        onchange="updateOwnerFields()">
                        <option value="">Select Type</option>
                        @foreach ($okrTypes as $type)
                            <option value="{{ $type->id }}" data-is-employee="{{ $type->is_employee ? '1' : '0' }}"
                                {{ old('okr_type_id', $isEdit ? $okr->okr_type_id : '') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ old('start_date', $isEdit ? $okr->start_date->format('Y-m-d') : '') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control"
                        value="{{ old('end_date', $isEdit ? $okr->end_date->format('Y-m-d') : '') }}" required>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4" id="employeeOwnerField" style="display: none;">
                    <label class="form-label">Employee Owner <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-select">
                        <option value="">Select Employee</option>
                        @if ($isEdit && $okr->employee_id)
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ old('employee_id', $okr->employee_id) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        @else
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-4" id="orgUnitOwnerField" style="display: none;">
                    <label class="form-label">Org Unit Owner <span class="text-danger">*</span></label>
                    <select name="orgunit_id" id="orgunit_id" class="form-select">
                        <option value="">Select Organization Unit</option>
                        @if ($isEdit && $okr->orgunit_id)
                            @foreach ($orgUnits as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('orgunit_id', $okr->orgunit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        @else
                            @foreach ($orgUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="is_active" id="is_active" class="form-select" required>
                        <option value="1" {{ $isEdit ? ($okr->is_active ? 'selected' : '') : 'selected' }}>Active</option>
                        <option value="0" {{ $isEdit && !$okr->is_active ? 'selected' : '' }}>Inactive</option>
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
            <div id="objectivesContainer">
                @if ($isEdit && $okr->objectives->count() > 0)
                    @foreach ($okr->objectives as $index => $objective)
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
                                    <textarea name="objectives[{{ $index }}][description]" class="form-control" rows="2"
                                        required>{{ old('objectives.' . $index . '.description', $objective->description) }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="objectives[{{ $index }}][weight]" class="form-control"
                                        step="0.01" min="0" max="100" value="{{ old('objectives.' . $index . '.weight', $objective->weight * 100) }}" required>
                                    <small class="text-muted">Enter percentage (0-100%)</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Target Type <span class="text-danger">*</span></label>
                                    <select name="objectives[{{ $index }}][target_type]" class="form-select" required
                                        onchange="updateTargetValueField(this, {{ $index }})">
                                        <option value="">Select Type</option>
                                        <option value="numeric" {{ old('objectives.' . $index . '.target_type', $objective->target_type) == 'numeric' ? 'selected' : '' }}>Numeric</option>
                                        <option value="binary" {{ old('objectives.' . $index . '.target_type', $objective->target_type) == 'binary' ? 'selected' : '' }}>Binary</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Target Value <span class="text-danger">*</span></label>
                                    <input type="number" name="objectives[{{ $index }}][target_value]" class="form-control"
                                        step="0.01" value="{{ old('objectives.' . $index . '.target_value', $objective->target_value) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                    <input type="date" name="objectives[{{ $index }}][deadline]" class="form-control"
                                        value="{{ old('objectives.' . $index . '.deadline', $objective->deadline ? $objective->deadline->format('Y-m-d') : '') }}" required>
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
                                @if ($isEdit)
                                    <input type="hidden" name="objectives[{{ $index }}][id]" value="{{ $objective->id }}">
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            @if (!$isEdit || $okr->objectives->count() === 0)
                <div class="text-center py-4 text-muted" id="noObjectivesMessage">
                    <i class="ti ti-target-off" style="font-size: 40px;"></i>
                    <p class="mb-0 mt-2">No objectives added yet. Click "Add Objective" to create one.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="{{ route('admin.okrs') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-2"></i>Back to OKRs
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-2"></i>{{ $isEdit ? 'Update OKR' : 'Create OKR' }}
        </button>
    </div>
</form>

@push('scripts')
    <script>
        let objectiveIndex = {{ $isEdit ? $okr->objectives->count() : 0 }};

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('.select2').select2({
                dropdownParent: $('#okrForm'),
                width: '100%'
            });

            // Initialize owner fields based on OKR type
            updateOwnerFields();

            // Show success message if exists
            const successMessage = localStorage.getItem('toast_success');
            if (successMessage) {
                showToast('Success', successMessage, 'success');
                localStorage.removeItem('toast_success');
            }

            // Show error message if exists
            const errorMessage = localStorage.getItem('toast_error');
            if (errorMessage) {
                showToast('Error', errorMessage, 'error');
                localStorage.removeItem('toast_error');
            }
        });

        function updateOwnerFields() {
            const okrTypeId = document.getElementById('okr_type_id').value;
            const employeeField = document.getElementById('employeeOwnerField');
            const orgUnitField = document.getElementById('orgUnitOwnerField');
            const employeeSelect = document.getElementById('employee_id');
            const orgUnitSelect = document.getElementById('orgunit_id');

            // Reset both fields
            employeeField.style.display = 'none';
            orgUnitField.style.display = 'none';
            employeeSelect.removeAttribute('required');
            orgUnitSelect.removeAttribute('required');

            if (okrTypeId) {
                const selectedOption = document.querySelector(`#okr_type_id option[value="${okrTypeId}"]`);
                const isEmployee = selectedOption.getAttribute('data-is-employee') === '1';

                if (isEmployee) {
                    employeeField.style.display = 'block';
                    employeeSelect.setAttribute('required', 'required');
                } else {
                    orgUnitField.style.display = 'block';
                    orgUnitSelect.setAttribute('required', 'required');
                }
            }
        }

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

            // Reinitialize Select2 for new elements
            const newSelects = container.querySelectorAll('.objective-item:last-child .select2');
            newSelects.forEach(select => {
                $(select).select2({
                    dropdownParent: $('#okrForm'),
                    width: '100%'
                });
            });

            objectiveIndex++;
        }

        function removeObjective(button) {
            const objectiveItem = button.closest('.objective-item');
            objectiveItem.remove();

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

        function updateTargetValueField(select, index) {
            const targetValueInput = document.querySelector(`.objective-item[data-index="${index}"] input[name="objectives[${index}][target_value]"]`);
            if (select.value === 'binary') {
                targetValueInput.setAttribute('step', '1');
                targetValueInput.setAttribute('min', '0');
                targetValueInput.setAttribute('max', '1');
                targetValueInput.placeholder = '0 or 1';
            } else {
                targetValueInput.setAttribute('step', '0.01');
                targetValueInput.removeAttribute('min');
                targetValueInput.removeAttribute('max');
                targetValueInput.placeholder = 'Enter target value';
            }
        }

        // Form submission handler
        document.getElementById('okrForm').addEventListener('submit', function(e) {
            e.preventDefault();

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
            .then(response => response.json())
            .then(data => {
                if (data.success || data.message) {
                    localStorage.setItem('toast_success', data.message || 'OKR saved successfully');
                    window.location.href = '{{ route("admin.okrs") }}';
                } else if (data.errors) {
                    localStorage.setItem('toast_error', Object.values(data.errors).flat().join('\n'));
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                localStorage.setItem('toast_error', 'An error occurred while saving the OKR');
                window.location.reload();
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script>
@endpush
