@extends('layouts.app')

@section('title', 'Employees - OKR Management System')

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Employees</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filters Card with Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-1">Employees</h4>
                    <!-- Filter Dropdowns -->
                    <div class="row align-items-center mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="filterStatus" class="form-select">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Role</label>
                            <select id="filterRole" class="form-select">
                                <option value="">All Roles</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" id="resetFilters" class="btn btn-outline-secondary grow">
                                    <i class="ti ti-refresh me-1"></i>Reset
                                </button>
                                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary grow">
                                    <i class="ti ti-plus me-1"></i>Add Employee
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="employeesTable" class="table table-hover" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Position</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/js/tables-datatables-advanced.js') }}"></script>
    <script>
        let dataTable;
        let currentStatusFilter = '';
        let currentRoleFilter = '';

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            dataTable = $('#employeesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.employees.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.is_active = currentStatusFilter;
                        d.role_id = currentRoleFilter;
                    }
                },
                columns: [{
                        data: 'name',
                        render: function(data, type, row) {
                            return `<h6 class="mb-0">${data}</h6>`;
                        }
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'position'
                    },
                    {
                        data: 'role',
                        render: function(data) {
                            if (data === 'No Role') {
                                return '<span class="text-muted">No Role</span>';
                            }
                            return `<span class="badge bg-label-primary">${data}</span>`;
                        }
                    },
                    {
                        data: 'is_active',
                        render: function(data) {
                            if (data) {
                                return '<span class="badge bg-label-success">Active</span>';
                            }
                            return '<span class="badge bg-label-secondary">Inactive</span>';
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                <div class="d-flex gap-1">
                  <a href="/admin/employees/${data}/edit" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-pencil"></i>
                  </a>
                  ${row.is_active
                    ? `<button type="button" class="btn btn-sm btn-outline-warning" onclick="toggleEmployeeStatus(${data}, false)">
                                                                                                                                                                                                                    <i class="ti ti-player-pause"></i>
                                                                                                                                                                                                                  </button>`
                    : `<button type="button" class="btn btn-sm btn-outline-success" onclick="toggleEmployeeStatus(${data}, true)">
                                                                                                                                                                                                                    <i class="ti ti-player-play"></i>
                                                                                                                                                                                                                  </button>`
                  }
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(${data})">
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
                    info: 'Showing _START_ to _END_ of _TOTAL_ employees',
                    infoEmpty: 'No employees found',
                    infoFiltered: '(filtered from _MAX_ total employees)',
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            // Custom length menu with Bootstrap styling
            $('div.dataTables_length select').addClass('form-select form-select-sm');
            $('div.dataTables_filter input').addClass('form-control form-control-sm');

            // Status filter handler
            $('#filterStatus').on('change', function() {
                currentStatusFilter = $(this).val();
                dataTable.ajax.reload();
            });

            // Role filter handler
            $('#filterRole').on('change', function() {
                currentRoleFilter = $(this).val();
                dataTable.ajax.reload();
            });

            // Reset filters handler
            $('#resetFilters').on('click', function() {
                $('#filterStatus').val('');
                $('#filterRole').val('');
                currentStatusFilter = '';
                currentRoleFilter = '';
                dataTable.ajax.reload();
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

            // Delete employee function
            window.deleteEmployee = function(id) {
                if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                    fetch(`/admin/employees/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Success', data.message || 'Employee deleted successfully',
                                    'success');
                                dataTable.ajax.reload();
                            } else {
                                showToast('Warning', data.message || 'Failed to delete employee',
                                    'warning');
                            }
                        })
                        .catch(error => {
                            showToast('Error', 'Failed to delete employee', 'error');
                        });
                }
            };

            // Toggle employee status function
            window.toggleEmployeeStatus = function(id, activate) {
                const action = activate ? 'activate' : 'deactivate';
                const confirmMsg = activate ?
                    'Are you sure you want to activate this employee?' :
                    'Are you sure you want to deactivate this employee?';

                if (confirm(confirmMsg)) {
                    fetch(`/admin/employees/${id}/${action}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Success', data.message || `Employee ${action}d successfully`,
                                    'success');
                                dataTable.ajax.reload();
                            } else {
                                showToast('Warning', data.message || `Failed to ${action} employee`,
                                    'warning');
                            }
                        })
                        .catch(error => {
                            showToast('Error', `Failed to ${action} employee`, 'error');
                        });
                }
            };

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
