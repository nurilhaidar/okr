@extends('layouts.app')

@section('title', 'Roles - OKR Management System')

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Roles</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filters Card with Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Roles</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                            <i class="ti ti-plus me-1"></i>Add Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rolesTable" class="table table-hover" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Employees Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.roles.store') }}" id="addRoleForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Enter role name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-2"></i>Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.roles.update', 0) }}" id="editRoleForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editRoleId">
                        <div class="mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editRoleName" class="form-control"
                                placeholder="Enter role name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-2"></i>Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/js/tables-datatables-advanced.js') }}"></script>
    <script>
        let dataTable;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            dataTable = $('#rolesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.roles.data') }}',
                    type: 'GET',
                },
                columns: [
                    {
                        data: 'name',
                        render: function(data) {
                            return `<span class="badge bg-label-primary">${data}</span>`;
                        }
                    },
                    {
                        data: 'employees_count'
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editRole(${data}, '${row.name}')">
                                        <i class="ti ti-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRole(${data})">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_ roles',
                    infoEmpty: 'No roles found',
                    infoFiltered: '(filtered from _MAX_ total roles)',
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            // Custom length menu with Bootstrap styling
            $('div.dataTables_length select').addClass('form-select form-select-sm');
            $('div.dataTables_filter input').addClass('form-control form-control-sm');

            // Handle add role form submit with AJAX
            document.getElementById('addRoleForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);

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
                        if (data.success) {
                            showToast('Success', data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('addRoleModal')).hide();
                            form.reset();
                            dataTable.ajax.reload();
                        } else {
                            showToast('Error', data.message || 'Failed to create role', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Error', 'Failed to create role', 'error');
                    });
            });

            // Handle edit role form submit with AJAX
            document.getElementById('editRoleForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);

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
                        if (data.success) {
                            showToast('Success', data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('editRoleModal')).hide();
                            dataTable.ajax.reload();
                        } else {
                            showToast('Error', data.message || 'Failed to update role', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Error', 'Failed to update role', 'error');
                    });
            });

            // Edit role function
            window.editRole = function(id, name) {
                document.getElementById('editRoleId').value = id;
                document.getElementById('editRoleName').value = name;
                document.getElementById('editRoleForm').action = '/admin/roles/' + id;

                const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
                modal.show();
            };

            // Delete role function
            window.deleteRole = function(id) {
                if (confirm('Are you sure you want to delete this role?')) {
                    fetch(`/admin/roles/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Success', data.message, 'success');
                                dataTable.ajax.reload();
                            } else {
                                showToast('Error', data.message || 'Failed to delete role', 'error');
                            }
                        })
                        .catch(error => {
                            showToast('Error', 'Failed to delete role', 'error');
                        });
                }
            };

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
