@extends('layouts.app')

@section('title', 'Organization Structure - OKR Management System')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/libs/select2/select2.css') }}" />
<style>
    .org-unit-card {
        transition: all 0.3s ease;
        border-left: 4px solid #7367F0;
    }
    .org-unit-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .org-unit-header {
        cursor: pointer;
        user-select: none;
    }
    .org-unit-header .toggle-icon {
        transition: transform 0.3s ease;
    }
    .org-unit-header.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
    .org-unit-members {
        max-height: 500px;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    .org-unit-members.collapsed {
        max-height: 0;
    }
    .member-item {
        transition: background-color 0.2s ease;
    }
    .member-item:hover {
        background-color: #f8f7ff;
    }
    .member-actions {
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .member-item:hover .member-actions {
        opacity: 1;
    }
    .hierarchy-indent-0 { padding-left: 0rem; }
    .hierarchy-indent-1 { padding-left: 1.5rem; }
    .hierarchy-indent-2 { padding-left: 3rem; }
    .hierarchy-indent-3 { padding-left: 4.5rem; }
    .hierarchy-indent-4 { padding-left: 6rem; }
    .hierarchy-indent-5 { padding-left: 7.5rem; }
    .hierarchy-indent-6 { padding-left: 9rem; }
    .hierarchy-indent-7 { padding-left: 10.5rem; }
    .hierarchy-indent-8 { padding-left: 12rem; }
    .hierarchy-indent-9 { padding-left: 13.5rem; }
    .hierarchy-indent-10 { padding-left: 15rem; }
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
                <li class="breadcrumb-item"><a href="{{ route('admin.org-units') }}">Organization Units</a></li>
                <li class="breadcrumb-item active">Organization Structure</li>
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
                        <h4 class="mb-1">Organization Structure</h4>
                        <p class="text-muted mb-0">View your organization's hierarchy and manage team members.</p>
                    </div>
                    <a href="{{ route('admin.org-units') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-settings me-2"></i>Manage Units
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Organization Structure -->
<div class="row">
    <div class="col-12">
        @forelse($orgUnitsTree as $orgUnit)
            <div class="card org-unit-card mb-3 hierarchy-indent-{{ min($orgUnit->level, 10) }}">
                <div class="card-header org-unit-header d-flex justify-content-between align-items-center py-3" onclick="toggleMembers({{ $orgUnit->id }})">
                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="ti ti-chevron-down toggle-icon me-3"></i>
                        <div class="avatar bg-label-primary me-3" style="width: 48px; height: 48px;">
                            <span class="avatar-initials rounded-circle">{{ substr($orgUnit->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $orgUnit->name }}</h5>
                            <small class="text-muted">
                                @if($orgUnit->members_count > 0)
                                    <i class="ti ti-users me-1"></i>{{ $orgUnit->members_count }} member{{ $orgUnit->members_count > 1 ? 's' : '' }}
                                @else
                                    <span class="text-muted">No members</span>
                                @endif
                                @if($orgUnit->children_count > 0)
                                    <span class="mx-2">•</span>
                                    <i class="ti ti-building me-1"></i>{{ $orgUnit->children_count }} sub-unit{{ $orgUnit->children_count > 1 ? 's' : '' }}
                                @endif
                            </small>
                        </div>
                    </div>
                    <div onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-sm btn-primary" onclick="openAddMemberModal({{ $orgUnit->id }}, '{{ $orgUnit->name }}')">
                            <i class="ti ti-user-plus me-1"></i>Add Member
                        </button>
                    </div>
                </div>

                <!-- Members Section -->
                <div class="org-unit-members" id="members-{{ $orgUnit->id }}">
                    <div class="card-body pt-0">
                        @if($orgUnit->orgUnitEmployees->count() > 0)
                            <div class="row" id="members-container-{{ $orgUnit->id }}">
                                @foreach($orgUnit->orgUnitEmployees as $member)
                                    <div class="col-md-6 col-lg-4 mb-3 member-card-item" data-member-id="{{ $member->id }}">
                                        <div class="card member-item">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-label-primary me-3" style="width: 40px; height: 40px;">
                                                            <span class="avatar-initials rounded-circle fs-6">{{ substr($member->employee->name ?? 'N', 0, 1) }}</span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold">{{ $member->employee->name ?? 'N/A' }}</h6>
                                                            <small class="text-muted">{{ $member->employee->position ?? 'No Position' }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="member-actions">
                                                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary" onclick="openEditMemberModal({{ $member->id }}, {{ $orgUnit->id }}, '{{ str_replace("'", "\'", $member->employee->name ?? 'N/A') }}', {{ $member->org_unit_role_id ?? 'null' }})">
                                                            <i class="ti ti-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmRemoveMember({{ $member->id }}, {{ $orgUnit->id }}, '{{ str_replace("'", "\'", $member->employee->name ?? 'Member') }}')">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                @if($member->orgUnitRole)
                                                    <div class="mt-2">
                                                        <span class="badge bg-label-primary">{{ $member->orgUnitRole->name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ti ti-users display-4 text-muted mb-3"></i>
                                <p class="text-muted mb-0">No members assigned to this unit</p>
                                <button type="button" class="btn btn-outline-primary mt-3" onclick="openAddMemberModal({{ $orgUnit->id }}, '{{ $orgUnit->name }}')">
                                    <i class="ti ti-user-plus me-2"></i>Add First Member
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ti ti-building display-4 text-muted mb-3"></i>
                    <h5 class="mb-2">No Organization Units Found</h5>
                    <p class="text-muted">Start by creating your first organization unit.</p>
                    <a href="{{ route('admin.org-units.create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-building-plus me-2"></i>Create Organization Unit
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Member to <span id="addMemberOrgUnitName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMemberForm">
                    <input type="hidden" id="addMemberOrgUnitId" name="orgunit_id">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" id="addMemberEmployee" class="select2 form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="addMemberEmployeeError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="orgunit_role_id" id="addMemberRole" class="form-select">
                            <option value="">No Role</option>
                            @foreach($orgUnitRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="addMemberRoleError"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAddMember()">
                    <i class="ti ti-check me-2"></i>Add Member
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Member Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMemberForm">
                    <input type="hidden" id="editMemberId">
                    <input type="hidden" id="editMemberOrgUnitId">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" id="editMemberEmployeeName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="orgunit_role_id" id="editMemberRole" class="form-select">
                            <option value="">No Role</option>
                            @foreach($orgUnitRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="editMemberRoleError"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitEditMember()">
                    <i class="ti ti-check me-2"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page_scripts')
<script src="{{ asset('plugin/vuexy/assets/vendor/libs/select2/select2.js') }}"></script>
<script>
    let addMemberModal, editMemberModal;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize modals
        addMemberModal = new bootstrap.Modal(document.getElementById('addMemberModal'));
        editMemberModal = new bootstrap.Modal(document.getElementById('editMemberModal'));

        // Initialize Select2
        $('.select2').select2({
            dropdownParent: $('#addMemberModal'),
            placeholder: 'Select Employee',
            allowClear: true,
            width: '100%'
        });

        // Auto-expand first level org units on load
        const firstLevelUnits = document.querySelectorAll('.hierarchy-indent-0 .org-unit-card');
        firstLevelUnits.forEach(card => {
            const header = card.querySelector('.org-unit-header');
            const members = card.querySelector('.org-unit-members');
            if (members && members.querySelector('.card-body').textContent.trim() !== '') {
                // Keep expanded by default
            }
        });

        // Display toastr notifications
        @if (session('success'))
            showToast('Success', '{{ session('success') }}', 'success');
        @endif

        @if (session('error'))
            showToast('Error', '{{ session('error') }}', 'error');
        @endif

        const toastSuccess = localStorage.getItem('toast_success');
        if (toastSuccess) {
            showToast('Success', toastSuccess, 'success');
            localStorage.removeItem('toast_success');
        }
    });

    function toggleMembers(orgUnitId) {
        const header = document.querySelector(`#members-${orgUnitId}`).previousElementSibling;
        const membersSection = document.querySelector(`#members-${orgUnitId}`);

        header.classList.toggle('collapsed');
        membersSection.classList.toggle('collapsed');
    }

    function openAddMemberModal(orgUnitId, orgUnitName) {
        document.getElementById('addMemberOrgUnitId').value = orgUnitId;
        document.getElementById('addMemberOrgUnitName').textContent = orgUnitName;
        document.getElementById('addMemberEmployee').value = '';
        document.getElementById('addMemberRole').value = '';
        $('#addMemberEmployee').val(null).trigger('change');
        $('#addMemberRole').val(null).trigger('change');
        document.getElementById('addMemberEmployeeError').textContent = '';
        document.getElementById('addMemberRoleError').textContent = '';
        addMemberModal.show();
    }

    async function submitAddMember() {
        const orgUnitId = document.getElementById('addMemberOrgUnitId').value;
        const employeeId = document.getElementById('addMemberEmployee').value;
        const orgUnitRoleId = document.getElementById('addMemberRole').value;

        // Validation
        document.getElementById('addMemberEmployeeError').textContent = '';
        document.getElementById('addMemberRoleError').textContent = '';

        if (!employeeId) {
            document.getElementById('addMemberEmployeeError').textContent = 'Please select an employee.';
            return;
        }

        const data = {
            orgunit_id: orgUnitId,
            employee_id: employeeId,
            orgunit_role_id: orgUnitRoleId || null
        };

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

            if (result.success) {
                localStorage.setItem('toast_success', 'Member added successfully!');
                location.reload();
            } else {
                showToast('Error', result.message || 'Failed to add member', 'error');
            }
        } catch (error) {
            console.error(error);
            showToast('Error', 'An error occurred while adding the member.', 'error');
        }
    }

    function openEditMemberModal(memberId, orgUnitId, employeeName, roleId) {
        document.getElementById('editMemberId').value = memberId;
        document.getElementById('editMemberOrgUnitId').value = orgUnitId;
        document.getElementById('editMemberEmployeeName').value = employeeName;
        document.getElementById('editMemberRole').value = roleId || '';
        document.getElementById('editMemberRoleError').textContent = '';
        editMemberModal.show();
    }

    async function submitEditMember() {
        const memberId = document.getElementById('editMemberId').value;
        const orgUnitRoleId = document.getElementById('editMemberRole').value;

        const data = {
            orgunit_role_id: orgUnitRoleId || null
        };

        try {
            const response = await fetch(`{{ route('admin.org-units.employees.update', '') }}/${memberId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                localStorage.setItem('toast_success', 'Member role updated successfully!');
                location.reload();
            } else {
                showToast('Error', result.message || 'Failed to update member', 'error');
            }
        } catch (error) {
            console.error(error);
            showToast('Error', 'An error occurred while updating the member.', 'error');
        }
    }

    function confirmRemoveMember(memberId, orgUnitId, memberName) {
        if (confirm(`Are you sure you want to remove ${memberName} from this unit?`)) {
            removeMember(memberId);
        }
    }

    async function removeMember(memberId) {
        try {
            const response = await fetch(`{{ route('admin.org-units.employees.destroy', '') }}/${memberId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();

            if (result.success) {
                localStorage.setItem('toast_success', 'Member removed successfully!');
                location.reload();
            } else {
                showToast('Error', result.message || 'Failed to remove member', 'error');
            }
        } catch (error) {
            console.error(error);
            showToast('Error', 'An error occurred while removing the member.', 'error');
        }
    }
</script>
@endsection
