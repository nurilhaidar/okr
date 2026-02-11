@extends('layouts.app')

@section('title', 'Employees - OKR Management System')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Employees</h4>
                            <p class="text-muted mb-0">Manage your organization's employees and their roles.</p>
                        </div>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
                            <i class="ti ti-user-plus me-2"></i>Add Employee
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select id="filterRole" class="form-select">
                                <option value="">All Roles</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-select">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button id="resetFilters" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>Reset Filters
                            </button>
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
                        d.is_active = $('#filterStatus').val();
                        d.role_id = $('#filterRole').val();
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
                    ? `<form method="POST" action="/admin/employees/${data}/deactivate" class="d-inline">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Are you sure you want to deactivate this employee?')">
                                          <i class="ti ti-player-pause"></i>
                                        </button>
                                      </form>`
                    : `<form method="POST" action="/admin/employees/${data}/activate" class="d-inline">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                          <i class="ti ti-player-play"></i>
                                        </button>
                                      </form>`
                  }
                  <form method="POST" action="/admin/employees/${data}" class="d-inline">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this employee? This action cannot be undone.')">
                      <i class="ti ti-trash"></i>
                    </button>
                  </form>
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
                    searchPlaceholder: 'Search employees...',
                    lengthMenu: 'Show _MENU_ entries per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ employees',
                    infoEmpty: 'No employees found',
                    infoFiltered: '(filtered from _MAX_ total employees)',
                    paginate: {
                        first: '<i class="ti ti-chevrons-left"></i>',
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                        last: '<i class="ti ti-chevrons-right"></i>'
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            // Custom length menu with Bootstrap styling
            $('div.dataTables_length select').addClass('form-select form-select-sm');
            $('div.dataTables_filter input').addClass('form-control form-control-sm');

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

            // Filter handlers
            $('#filterRole, #filterStatus').on('change', function() {
                dataTable.ajax.reload();
            });

            $('#resetFilters').on('click', function() {
                $('#filterRole').val('');
                $('#filterStatus').val('');
                dataTable.ajax.reload();
            });

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
