@extends('layouts.app')

@section('title', 'Add Organization Unit - OKR Management System')

@section('content')
  <!-- Breadcrumb -->
  <div class="row justify-content-center mb-3">
    <div class="col-12 col-lg-8">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.org-units') }}">Organization Units</a></li>
          <li class="breadcrumb-item active">Add New Organization Unit</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="card">
        <div class="card-header">
          <h4 class="mb-0">Add New Organization Unit</h4>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('admin.org-units.store') }}">
            @csrf

            <h5 class="mb-3">Basic Information</h5>

            <div class="row g-3 mb-4">
              <div class="col-md-12">
                <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., Engineering Department" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Parent Unit</label>
                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                  <option value="">No Parent (Root Level)</option>
                  @foreach($parentUnits as $unit)
                    <option value="{{ $unit->id }}" {{ old('parent_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                  @endforeach
                </select>
                @error('parent_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Optional: Create hierarchy by selecting parent</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" id="isActive" class="form-select">
                  <option value="1" selected>Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>

            <!-- Members Section -->
            <h5 class="mb-3 mt-4">Add Members</h5>

            <div id="membersContainer">
              <!-- Members will be added here dynamically -->
            </div>

            <button type="button" class="btn btn-outline-primary mb-4" onclick="addMemberRow()">
              <i class="ti ti-plus me-2"></i>Add Member
            </button>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('admin.org-units') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="ti ti-check me-2"></i>Create Unit
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
    let memberUniqueId = 0;
    const employees = @json($employees);
    const orgUnitRoles = @json($orgUnitRoles);

    // Initialize Select2 on employee dropdown
    function initSelect2(selectElement) {
      if (selectElement && typeof $.fn.select2 === 'function') {
        $(selectElement).select2({
          dropdownParent: selectElement.closest('.card'),
          placeholder: 'Select Employee',
          allowClear: true,
          width: '100%'
        });

        // Trigger change event for validation
        $(selectElement).on('change', function() {
          const memberCard = $(this).closest('.member-card');
          const uniqueId = memberCard.data('member-id');
          validateMember(uniqueId);
        });
      }
    }

    function addMemberRow() {
      memberUniqueId++;
      const uniqueId = memberUniqueId;
      const container = document.getElementById('membersContainer');
      const currentMemberNumber = container.children.length + 1;
      const memberHtml = `
        <div class="card mb-3 member-card" data-member-id="${uniqueId}">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0 member-number">Member #${currentMemberNumber}</h6>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMember(${uniqueId})">
                <i class="ti ti-trash"></i>
              </button>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Employee <span class="text-danger">*</span></label>
                <select name="members[${uniqueId}][employee_id]" class="select2 form-select member-employee" required>
                  <option value="">Select Employee</option>
                  ${employees.map(emp => `<option value="${emp.id}">${emp.name}</option>`).join('')}
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="members[${uniqueId}][orgunit_role_id]" class="form-select">
                  <option value="">No Role</option>
                  ${orgUnitRoles.map(role => `<option value="${role.id}">${role.name}</option>`).join('')}
                </select>
              </div>
            </div>
          </div>
        </div>
      `;
      container.insertAdjacentHTML('beforeend', memberHtml);

      // Initialize Select2 on the new employee dropdown
      const newCard = container.querySelector(`[data-member-id="${uniqueId}"]`);
      const newSelect = newCard.querySelector('.member-employee');
      initSelect2(newSelect);
    }

    function removeMember(uniqueId) {
      const element = document.querySelector(`.member-card[data-member-id="${uniqueId}"]`);
      if (element) {
        // Destroy Select2 before removing
        const selectElement = element.querySelector('.member-employee');
        if (selectElement && $(selectElement).data('select2')) {
          $(selectElement).select2('destroy');
        }
        element.remove();
        renumberMembers();
      }
    }

    function renumberMembers() {
      const container = document.getElementById('membersContainer');
      const memberCards = container.querySelectorAll('.member-card');
      memberCards.forEach((card, index) => {
        const numberElement = card.querySelector('.member-number');
        if (numberElement) {
          numberElement.textContent = 'Member #' + (index + 1);
        }
      });
    }

    function validateMember(uniqueId) {
      const memberCard = document.querySelector(`.member-card[data-member-id="${uniqueId}"]`);
      const selectElement = memberCard.querySelector('.member-employee');
      const selectedEmployee = selectElement.value;

      // Check if this employee is already selected in another row
      document.querySelectorAll('.member-employee').forEach(select => {
        if (select !== selectElement && select.value === selectedEmployee && selectedEmployee !== '') {
          showToast('Error', 'This employee is already added to the unit.', 'error');
          $(selectElement).val(null).trigger('change');
        }
      });
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Initialize Select2 on any existing employee dropdowns
      document.querySelectorAll('.member-employee').forEach(select => {
        initSelect2(select);
      });

      // Handle form submission via AJAX
      document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const isActiveSelect = document.getElementById('isActive');
        const data = {
          name: formData.get('name'),
          parent_id: formData.get('parent_id') || null,
          is_active: isActiveSelect.value === '1',
          members: []
        };

        // Collect members
        const memberEntries = formData.getAll('members').reduce((acc, entry) => {
          // Members are sent as individual fields
          return acc;
        }, []);

        for (let [key, value] of formData.entries()) {
          if (key.startsWith('members[')) {
            const match = key.match(/members\[(\d+)\]\[(.+)\]/);
            if (match) {
              const index = match[1];
              const field = match[2];
              if (!data.members[index]) {
                data.members[index] = {};
              }
              data.members[index][field] = value;
            }
          }
        }

        // Filter empty members and convert to array
        data.members = Object.values(data.members).filter(m => m.employee_id);

        fetch('{{ route('admin.org-units.store') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            localStorage.setItem('toast_success', 'Org Unit created successfully!');
            window.location.href = '{{ route('admin.org-units') }}';
          } else if (data.errors) {
            Object.values(data.errors).flat().forEach(msg => {
              showToast('Validation Error', msg, 'error');
            });
          } else {
            showToast('Error', data.message || 'An error occurred', 'error');
          }
        })
        .catch(err => {
          console.error(err);
          showToast('Error', 'An error occurred while creating the org unit.', 'error');
        });
      });

      // Display toastr notifications for CRUD operations
      @if (session('success'))
        showToast('Success', '{{ session('success') }}', 'success');
      @endif

      @if (session('error'))
        showToast('Error', '{{ session('error') }}', 'error');
      @endif

      @if ($errors->any())
        @foreach ($errors->all() as $error)
          showToast('Validation Error', '{{ $error }}', 'error');
        @endforeach
      @endif

      // Auto-dismiss alerts after 5 seconds
      setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
          alert.classList.remove('show');
          setTimeout(() => alert.remove(), 150);
        });
      }, 5000);
    });
  </script>
@endsection
