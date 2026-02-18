@extends('layouts.app')

@section('title', 'Organization Units - OKR Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/libs/select2/select2.css') }}" />
    <style>
        .hierarchy-indent-0 {
            padding-left: 0rem;
        }

        .hierarchy-indent-1 {
            padding-left: 1.5rem;
        }

        .hierarchy-indent-2 {
            padding-left: 3rem;
        }

        .hierarchy-indent-3 {
            padding-left: 4.5rem;
        }

        .hierarchy-indent-4 {
            padding-left: 6rem;
        }

        .hierarchy-indent-5 {
            padding-left: 7.5rem;
        }

        .hierarchy-indent-6 {
            padding-left: 9rem;
        }

        .hierarchy-indent-7 {
            padding-left: 10.5rem;
        }

        .hierarchy-indent-8 {
            padding-left: 12rem;
        }

        .hierarchy-indent-9 {
            padding-left: 13.5rem;
        }

        .hierarchy-indent-10 {
            padding-left: 15rem;
        }

        .member-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .member-item:hover .member-actions {
            opacity: 1;
        }

        .avatar-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            font-weight: 600;
            color: #7367F0;
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
                    <li class="breadcrumb-item active">Organization Units</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Organization Units</h4>
                        </div>
                        <a href="{{ route('admin.org-units.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Add Organization Unit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Org Units Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Members</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orgUnitsTree as $orgUnit)
                                    <tr>
                                        <td>
                                            <div class="hierarchy-indent-{{ min($orgUnit->level, 10) }}">
                                                <strong>{{ $orgUnit->name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">{{ $orgUnit->members_count ?? 0 }}
                                                member{{ ($orgUnit->members_count ?? 0) != 1 ? 's' : '' }}</span>
                                        </td>
                                        <td>
                                            @if ($orgUnit->is_active)
                                                <span class="badge bg-label-success">Active</span>
                                            @else
                                                <span class="badge bg-label-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="openViewStructureModal({{ $orgUnit->id }}, '{{ $orgUnit->name }}')"
                                                    title="View Structure">
                                                    <i class="ti ti-users"></i>
                                                </button>
                                                <a href="{{ route('admin.org-units.edit', $orgUnit->id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                                @if ($orgUnit->is_active)
                                                    <form method="POST"
                                                        action="{{ route('admin.org-units.deactivate', $orgUnit->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning"
                                                            title="Deactivate"
                                                            onclick="return confirm('Are you sure you want to deactivate this org unit?')">
                                                            <i class="ti ti-player-pause"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST"
                                                        action="{{ route('admin.org-units.activate', $orgUnit->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                                            title="Activate">
                                                            <i class="ti ti-player-play"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST"
                                                    action="{{ route('admin.org-units.destroy', $orgUnit->id) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this org unit?')">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No organization units found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Structure Modal -->
    <div class="modal fade" id="viewStructureModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-building me-2"></i>
                        <span id="structureOrgUnitName"></span> - Members
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="structureOrgUnitId">

                    <!-- Add Member Form -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Add New Member</h6>
                            <form id="addMemberFormInline" class="row g-2">
                                <div class="col-md-5">
                                    <select name="employee_id" id="addEmployeeSelect" class="form-select select2" required>
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }} -
                                                {{ $employee->position ?? 'No Position' }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="addEmployeeError"></div>
                                </div>
                                <div class="col-md-4">
                                    <select name="orgunit_role_id" id="addRoleSelect" class="form-select select2">
                                        <option value="">No Role</option>
                                        @foreach ($orgUnitRoles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="addRoleError"></div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-user-plus me-1"></i>Add
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Members List -->
                    <div id="membersListContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        let viewStructureModal;
        let currentOrgUnitId = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modal
            viewStructureModal = new bootstrap.Modal(document.getElementById('viewStructureModal'));

            // Initialize Select2
            $('.select2').select2({
                dropdownParent: $('body'),
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%'
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

            // Check for localStorage toast (set after redirect from AJAX form)
            const toastSuccess = localStorage.getItem('toast_success');
            if (toastSuccess) {
                showToast('Success', toastSuccess, 'success');
                localStorage.removeItem('toast_success');
            }

            // Add Member Form Submit
            document.getElementById('addMemberFormInline').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const employeeId = document.getElementById('addEmployeeSelect').value;
                const orgUnitRoleId = document.getElementById('addRoleSelect').value;

                // Validation
                document.getElementById('addEmployeeError').textContent = '';

                if (!employeeId) {
                    document.getElementById('addEmployeeError').textContent = 'Please select an employee.';
                    return;
                }

                const data = {
                    orgunit_id: currentOrgUnitId,
                    employee_id: employeeId,
                    orgunit_role_id: orgUnitRoleId || null
                };

                submitAddMember(data);
            });

            // Handle modal shown event for Select2
            document.getElementById('viewStructureModal').addEventListener('shown.bs.modal', function() {
                $('#addEmployeeSelect').select2({
                    dropdownParent: $('#viewStructureModal'),
                    placeholder: 'Select Employee',
                    allowClear: true,
                    width: '100%'
                });
                $('#addRoleSelect').select2({
                    dropdownParent: $('#viewStructureModal'),
                    placeholder: 'No Role',
                    allowClear: true,
                    width: '100%'
                });
            });
        });

        async function openViewStructureModal(orgUnitId, orgUnitName) {
            currentOrgUnitId = orgUnitId;
            document.getElementById('structureOrgUnitId').value = orgUnitId;
            document.getElementById('structureOrgUnitName').textContent = orgUnitName;
            document.getElementById('addEmployeeSelect').value = '';
            document.getElementById('addRoleSelect').value = '';
            $('#addEmployeeSelect').val(null).trigger('change');
            $('#addRoleSelect').val(null).trigger('change');

            // Load members
            await loadMembers(orgUnitId);

            viewStructureModal.show();
        }

        async function loadMembers(orgUnitId) {
            const container = document.getElementById('membersListContainer');

            try {
                const response = await fetch(`/admin/org-units/${orgUnitId}/employees`);
                const members = await response.json();

                if (!Array.isArray(members)) {
                    container.innerHTML = '<div class="text-center py-4 text-muted">Failed to load members</div>';
                    return;
                }

                if (members.length === 0) {
                    container.innerHTML = `
          <div class="text-center py-4">
            <i class="ti ti-users display-4 text-muted mb-3"></i>
            <p class="text-muted mb-0">No members assigned to this unit</p>
          </div>
        `;
                    return;
                }

                // Get roles for dropdown
                const rolesOptions = @json($orgUnitRoles);

                let html = `
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Position</th>
                <th>Role</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
      `;
                members.forEach(member => {
                    let roleOptions = '<option value="">No Role</option>';
                    rolesOptions.forEach(role => {
                        const selected = member.role_id == role.id ? 'selected' : '';
                        roleOptions += `<option value="${role.id}" ${selected}>${role.name}</option>`;
                    });

                    html += `
          <tr id="member-row-${member.id}">
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar bg-label-primary me-2" style="width: 32px; height: 32px;">
                  <span class="avatar-initials rounded-circle fs-6">${member.employee_name ? member.employee_name.charAt(0) : 'N'}</span>
                </div>
                <span class="fw-bold">${member.employee_name || 'N/A'}</span>
              </div>
            </td>
            <td>${member.employee_position || 'No Position'}</td>
            <td>
              <select class="form-select form-select-sm" style="width: 150px;" onchange="updateMemberRole(${member.id}, this.value, '${(member.employee_name || 'Member').replace(/'/g, "\\'")}')">
                ${roleOptions}
              </select>
            </td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmRemoveMember(${member.id}, '${(member.employee_name || 'Member').replace(/'/g, "\\'")}')">
                <i class="ti ti-trash"></i>
              </button>
            </td>
          </tr>
        `;
                });
                html += `
            </tbody>
          </table>
        </div>
      `;
                container.innerHTML = html;

            } catch (error) {
                console.error(error);
                container.innerHTML = '<div class="text-center py-4 text-danger">Failed to load members</div>';
            }
        }

        async function submitAddMember(data) {
            try {
                const response = await fetch('{{ route('admin.org-units.employees.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success || response.ok) {
                    showToast('Success', 'Member added successfully!', 'success');
                    // Reset form
                    document.getElementById('addEmployeeSelect').value = '';
                    document.getElementById('addRoleSelect').value = '';
                    $('#addEmployeeSelect').val(null).trigger('change');
                    $('#addRoleSelect').val(null).trigger('change');
                    // Reload members
                    await loadMembers(currentOrgUnitId);
                } else {
                    showToast('Error', result.message || result.error || 'Failed to add member', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Error', 'An error occurred while adding the member.', 'error');
            }
        }

        async function updateMemberRole(memberId, newRoleId, memberName) {
            const data = {
                orgunit_role_id: newRoleId || null
            };

            try {
                const response = await fetch(`/admin/org-units/employees/${memberId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success || response.ok) {
                    showToast('Success', `Role updated for ${memberName}`, 'success');
                } else {
                    showToast('Error', result.message || result.error || 'Failed to update role', 'error');
                    // Reload to revert the select change on error
                    await loadMembers(currentOrgUnitId);
                }
            } catch (error) {
                console.error(error);
                showToast('Error', 'An error occurred while updating the role.', 'error');
                // Reload to revert the select change on error
                await loadMembers(currentOrgUnitId);
            }
        }

        function confirmRemoveMember(memberId, memberName) {
            if (confirm(`Are you sure you want to remove ${memberName} from this unit?`)) {
                removeMember(memberId);
            }
        }

        async function removeMember(memberId) {
            try {
                const response = await fetch(`/admin/org-units/employees/${memberId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();

                if (result.success || response.ok) {
                    showToast('Success', 'Member removed successfully!', 'success');
                    await loadMembers(currentOrgUnitId);
                } else {
                    showToast('Error', result.message || result.error || 'Failed to remove member', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Error', 'An error occurred while removing the member.', 'error');
            }
        }
    </script>
@endsection
