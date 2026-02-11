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
                    <select name="okr_type_id" class="form-select" required>
                        <option value="">Select Type</option>
                        @foreach ($okrTypes as $type)
                            <option value="{{ $type->id }}"
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
                <div class="col-md-4">
                    <label class="form-label">Owner Type <span class="text-danger">*</span></label>
                    <select name="owner_type" id="owner_type" class="form-select" required
                        onchange="updateOwnerDropdown()">
                        <option value="">Select Owner Type</option>
                        <option value="employee"
                            {{ old('owner_type', $isEdit && $okr->owner_type === 'App\Models\Employee' ? 'employee' : '') == 'employee' ? 'selected' : '' }}>
                            Employee</option>
                        <option value="orgunit"
                            {{ old('owner_type', $isEdit && $okr->owner_type === 'App\Models\OrgUnit' ? 'orgunit' : '') == 'orgunit' ? 'selected' : '' }}>
                            Organization Unit</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Owner <span class="text-danger">*</span></label>
                    <select name="owner_id" id="owner_id" class="form-select" required>
                        <option value="">Select Owner</option>
                        @if ($isEdit)
                            @if ($okr->owner_type === 'App\Models\Employee')
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" data-type="employee"
                                        {{ old('owner_id', $okr->owner_id) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            @else
                                @foreach ($orgUnits as $unit)
                                    <option value="{{ $unit->id }}" data-type="orgunit"
                                        {{ old('owner_id', $okr->owner_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            @endif
                        @endif
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                            {{ old('is_active', $isEdit ? $okr->is_active : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
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
            <div class="alert alert-info mb-3">
                <i class="ti ti-info-circle me-2"></i>
                Total weight of all objectives should equal 100%. Current total: <strong id="totalWeight">0%</strong>
            </div>

            <div id="objectivesContainer">
                @if ($isEdit && $okr->objectives->count() > 0)
                    @foreach ($okr->objectives as $objective)
                        <div class="objective-item" id="objective-{{ $loop->index }}">
                            <div class="objective-header">
                                <span class="objective-title">Objective {{ $loop->iteration }}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="removeObjective({{ $loop->index }})">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                            <input type="hidden" name="objectives[{{ $loop->index }}][id]"
                                value="{{ $objective->id }}">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea name="objectives[{{ $loop->index }}][description]" class="form-control" rows="2" required>{{ $objective->description }}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Weight <span class="text-danger">*</span></label>
                                    <input type="number" name="objectives[{{ $loop->index }}][weight]"
                                        class="form-control objective-weight" step="0.01" min="0"
                                        max="100" value="{{ $objective->weight * 100 }}" required
                                        onchange="updateTotalWeight()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Target Type <span class="text-danger">*</span></label>
                                    <select name="objectives[{{ $loop->index }}][target_type]" class="form-select"
                                        required onchange="toggleTargetValue({{ $loop->index }})">
                                        <option value="numeric"
                                            {{ $objective->target_type === 'numeric' ? 'selected' : '' }}>Numeric
                                        </option>
                                        <option value="binary"
                                            {{ $objective->target_type === 'binary' ? 'selected' : '' }}>Binary
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Target Value <span class="text-danger">*</span></label>
                                    <input type="number" name="objectives[{{ $loop->index }}][target_value]"
                                        class="form-control" step="any" value="{{ $objective->target_value }}"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                    <input type="date" name="objectives[{{ $loop->index }}][deadline]"
                                        class="form-control"
                                        value="{{ $objective->deadline ? $objective->deadline->format('Y-m-d') : '' }}"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tracking Type <span class="text-danger">*</span></label>
                                    <select name="objectives[{{ $loop->index }}][tracking_type]" class="form-select"
                                        required>
                                        <option value="daily"
                                            {{ $objective->tracking_type === 'daily' ? 'selected' : '' }}>Daily
                                        </option>
                                        <option value="weekly"
                                            {{ $objective->tracking_type === 'weekly' ? 'selected' : '' }}>Weekly
                                        </option>
                                        <option value="monthly"
                                            {{ $objective->tracking_type === 'monthly' ? 'selected' : '' }}>Monthly
                                        </option>
                                        <option value="quarterly"
                                            {{ $objective->tracking_type === 'quarterly' ? 'selected' : '' }}>Quarterly
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tracker</label>
                                    <select name="objectives[{{ $loop->index }}][tracker]" class="form-select">
                                        <option value="">Select Tracker</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}"
                                                {{ $objective->tracker == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Approver</label>
                                    <select name="objectives[{{ $loop->index }}][approver]" class="form-select">
                                        <option value="">Select Approver</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}"
                                                {{ $objective->approver == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="{{ route('admin.okrs') }}" class="btn btn-label-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="ti ti-check me-2"></i>{{ $isEdit ? 'Update OKR' : 'Create OKR' }}
        </button>
    </div>
</form>

@push('scripts')
    <script>
        let objectiveCount = {{ $isEdit ? $okr->objectives->count() : 0 }};

        const employees = @json($employees);
        const orgUnits = @json($orgUnits);

        document.addEventListener('DOMContentLoaded', function() {
            updateOwnerDropdown();
            updateTotalWeight();

            // Handle form submission via AJAX
            document.getElementById('okrForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm();
            });

        });

        function submitForm() {
            const form = document.getElementById('okrForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-2"></i>Saving...';

            // Collect objectives
            const objectives = [];
            document.querySelectorAll('.objective-item').forEach(item => {
                const id = item.id.replace('objective-', '');
                const description = formData.get(`objectives[${id}][description]`);
                if (description) {
                    objectives.push({
                        id: formData.get(`objectives[${id}][id]`) || null,
                        description: description,
                        weight: formData.get(`objectives[${id}][weight]`),
                        target_type: formData.get(`objectives[${id}][target_type]`),
                        target_value: formData.get(`objectives[${id}][target_value]`),
                        deadline: formData.get(`objectives[${id}][deadline]`),
                        tracking_type: formData.get(`objectives[${id}][tracking_type]`),
                        tracker: formData.get(`objectives[${id}][tracker]`),
                        approver: formData.get(`objectives[${id}][approver]`),
                    });
                }
            });

            // Build request data
            const data = {
                name: formData.get('name'),
                weight: formData.get('weight'),
                okr_type_id: formData.get('okr_type_id'),
                start_date: formData.get('start_date'),
                end_date: formData.get('end_date'),
                owner_type: formData.get('owner_type'),
                owner_id: formData.get('owner_id'),
                is_active: formData.get('is_active') === 'on',
                objectives: objectives
            };

            const url = form.action;
            const method = form.querySelector('input[name="_method"]')?.value || 'POST';

            fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    // Always parse JSON response
                    const contentType = res.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server returned non-JSON response');
                    }

                    const data = await res.json();
                    // Return both data and status for handling
                    return { data, status: res.status };
                })
                .then(({ data: response, status }) => {
                    if (response.success) {
                        // Store success message in localStorage to show on index page
                        localStorage.setItem('toast_success', response.message || 'OKR saved successfully!');
                        window.location.href = '{{ route('admin.okrs') }}';
                    } else if (response.errors) {
                        // Show validation errors
                        const errorMessages = Object.values(response.errors).flat();
                        errorMessages.forEach(msg => {
                            showToast('Validation Error', msg, 'error');
                        });
                    } else {
                        showToast('Error', response.message || 'An error occurred while saving the OKR.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    const errorMsg = err.message || 'An error occurred while saving the OKR.';
                    showToast('Error', errorMsg, 'error');
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML =
                        '<i class="ti ti-check me-2"></i>{{ $isEdit ? 'Update OKR' : 'Create OKR' }}';
                });
        }

        function updateOwnerDropdown() {
            const ownerType = document.getElementById('owner_type').value;
            const ownerSelect = document.getElementById('owner_id');

            // Clear existing options
            ownerSelect.innerHTML = '<option value="">Select Owner</option>';

            if (ownerType === 'employee') {
                employees.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.id;
                    option.textContent = emp.name;
                    option.dataset.type = 'employee';
                    ownerSelect.appendChild(option);
                });
            } else if (ownerType === 'orgunit') {
                orgUnits.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name;
                    option.dataset.type = 'orgunit';
                    ownerSelect.appendChild(option);
                });
            }

            // Preserve selected value if it matches the current type
            @if ($isEdit)
                const currentOwnerId = '{{ $okr->owner_id }}';
                const currentOwnerType = '{{ $okr->owner_type === 'App\Models\Employee' ? 'employee' : 'orgunit' }}';
                if (ownerType === currentOwnerType) {
                    ownerSelect.value = currentOwnerId;
                }
            @endif
        }

        function addObjective() {
            const container = document.getElementById('objectivesContainer');
            const objectiveNumber = container.children.length + 1;

            const objectiveHtml = `
        <div class="objective-item" id="objective-${objectiveCount}">
          <div class="objective-header">
            <span class="objective-title">Objective ${objectiveNumber}</span>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeObjective(${objectiveCount})">
              <i class="ti ti-trash"></i>
            </button>
          </div>
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <textarea name="objectives[${objectiveCount}][description]" class="form-control" rows="2" required></textarea>
            </div>
            <div class="col-md-3">
              <label class="form-label">Weight <span class="text-danger">*</span></label>
              <input type="number" name="objectives[${objectiveCount}][weight]" class="form-control objective-weight" step="0.01" min="0" max="100" value="10" required onchange="updateTotalWeight()">
            </div>
            <div class="col-md-3">
              <label class="form-label">Target Type <span class="text-danger">*</span></label>
              <select name="objectives[${objectiveCount}][target_type]" class="form-select" required onchange="toggleTargetValue(${objectiveCount})">
                <option value="numeric">Numeric</option>
                <option value="binary">Binary</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Target Value <span class="text-danger">*</span></label>
              <input type="number" name="objectives[${objectiveCount}][target_value]" class="form-control" step="any" value="100" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Deadline <span class="text-danger">*</span></label>
              <input type="date" name="objectives[${objectiveCount}][deadline]" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tracking Type <span class="text-danger">*</span></label>
              <select name="objectives[${objectiveCount}][tracking_type]" class="form-select" required>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tracker</label>
              <select name="objectives[${objectiveCount}][tracker]" class="form-select">
                <option value="">Select Tracker</option>
                ${employees.map(emp => `<option value="${emp.id}">${emp.name}</option>`).join('')}
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Approver</label>
              <select name="objectives[${objectiveCount}][approver]" class="form-select">
                <option value="">Select Approver</option>
                ${employees.map(emp => `<option value="${emp.id}">${emp.name}</option>`).join('')}
              </select>
            </div>
          </div>
        </div>
      `;

            container.insertAdjacentHTML('beforeend', objectiveHtml);
            objectiveCount++;
            updateTotalWeight();
        }

        function removeObjective(id) {
            const element = document.getElementById('objective-' + id);
            if (element) {
                element.remove();
                renumberObjectives();
                updateTotalWeight();
            }
        }

        function renumberObjectives() {
            const container = document.getElementById('objectivesContainer');
            const objectives = container.querySelectorAll('.objective-item');
            objectives.forEach((obj, index) => {
                const title = obj.querySelector('.objective-title');
                if (title) {
                    title.textContent = 'Objective ' + (index + 1);
                }
            });
        }

        function updateTotalWeight() {
            const weights = document.querySelectorAll('.objective-weight');
            let total = 0;
            weights.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            const totalEl = document.getElementById('totalWeight');
            if (totalEl) {
                totalEl.textContent = total.toFixed(2) + '%';

                // Update color based on total
                if (Math.abs(total - 100) < 0.01) {
                    totalEl.className = 'text-success';
                } else if (total > 100) {
                    totalEl.className = 'text-danger';
                } else {
                    totalEl.className = 'text-warning';
                }
            }
        }

        function toggleTargetValue(id) {
            // Could add logic here to handle binary vs numeric target values
        }
    </script>
@endpush
