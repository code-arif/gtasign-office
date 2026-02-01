@extends('backend.app', ['title' => 'User Management'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">User Management</h1>
                        <p class="text-muted mb-0">Manage all system users and their roles</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Users</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Index</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- STATISTICS ROW -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left-color: #dc3545;" onclick="filterByRole('Admin')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Admins</h6>
                                        <h3 class="mb-0">{{ $roleCounts['admin'] ?? 0 }}</h3>
                                    </div>
                                    <div class="icon-service bg-danger-transparent text-danger p-3 rounded-3">
                                        <i class="fe fe-shield fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left-color: #007bff;" onclick="filterByRole('Director')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Directors</h6>
                                        <h3 class="mb-0">{{ $roleCounts['director'] ?? 0 }}</h3>
                                    </div>
                                    <div class="icon-service bg-primary-transparent text-primary p-3 rounded-3">
                                        <i class="fe fe-briefcase fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left-color: #28a745;" onclick="filterByRole('Referee')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Referees</h6>
                                        <h3 class="mb-0">{{ $roleCounts['referee'] ?? 0 }}</h3>
                                    </div>
                                    <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                        <i class="fe fe-flag fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left-color: #17a2b8;"
                            onclick="filterByRole('Evaluator')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Evaluators</h6>
                                        <h3 class="mb-0">{{ $roleCounts['evaluator'] ?? 0 }}</h3>
                                    </div>
                                    <div class="icon-service bg-info-transparent text-info p-3 rounded-3">
                                        <i class="fe fe-check-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILTERS -->
                <div class="row">
                    <div class="col-12">
                        <div class="filter-card">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Role Filter</label>
                                    <select class="form-select" id="roleFilter">
                                        <option value="">All Roles</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Director">Director</option>
                                        <option value="Referee">Referee</option>
                                        <option value="Evaluator">Evaluator</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date From</label>
                                    <input type="date" class="form-control" id="dateFrom">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date To</label>
                                    <input type="date" class="form-control" id="dateTo">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">
                                        <i class="fe fe-filter me-1"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fe fe-refresh-cw me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- USER LIST TABLE -->
                <div class="row">
                    <div class="col-12 col-sm-12">
                        <div class="card product-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Users List</h3>
                                <div class="card-options ms-auto">
                                    <button class="btn btn-sm btn-outline-primary me-2" onclick="exportUsers()">
                                        <i class="fe fe-download me-1"></i> Export
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th class="bg-transparent border-bottom-0" style="width: 50px;">ID</th>
                                                <th class="bg-transparent border-bottom-0" style="width: 250px;">User</th>
                                                <th class="bg-transparent border-bottom-0" style="width: 250px;">Contact
                                                </th>
                                                <th class="bg-transparent border-bottom-0">Roles</th>
                                                <th class="bg-transparent border-bottom-0">Last Active</th>
                                                <th class="bg-transparent border-bottom-0 text-center">Status</th>
                                                <th class="bg-transparent border-bottom-0 text-center"
                                                    style="width: 100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let dataTable;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            initializeDataTable();
        });

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }

            dataTable = $('#datatable').DataTable({
                order: [
                    [0, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                responsive: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center">
                    <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                </div>`
                },
                pagingType: "full_numbers",
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                ajax: {
                    url: "{{ route('admin.users.manage.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.role = $('#roleFilter').val();
                        d.status = $('#statusFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'full_name',
                        name: 'first_name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'contact',
                        name: 'email',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'roles',
                        name: 'roles',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'last_active',
                        name: 'last_activity_at',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'dt-center text-center'
                    }
                ]
            });
        }

        function applyFilters() {
            dataTable.ajax.reload();
        }

        function resetFilters() {
            $('#roleFilter').val('');
            $('#statusFilter').val('');
            $('#dateFrom').val('');
            $('#dateTo').val('');
            dataTable.ajax.reload();
        }

        function filterByRole(role) {
            $('#roleFilter').val(role);
            applyFilters();
        }

        function showStatusChangeAlert(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to update the user status?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    statusChange(id);
                }
            });
        }

        function statusChange(id) {
            NProgress.start();
            let url = "{{ route('admin.users.manage.status', ':id') }}";
            $.ajax({
                type: "GET",
                url: url.replace(':id', id),
                success: function(resp) {
                    NProgress.done();
                    if (resp.success) {
                        toastr.success(resp.message);
                        dataTable.ajax.reload(null, false);
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.responseJSON?.message || 'Something went wrong!');
                }
            });
        }

        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This user will be deleted permanently!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteUser(id);
                }
            });
        }

        function deleteUser(id) {
            NProgress.start();
            let url = "{{ route('admin.users.manage.destroy', ':id') }}";
            let csrfToken = '{{ csrf_token() }}';
            $.ajax({
                type: "DELETE",
                url: url.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    NProgress.done();
                    if (resp.success) {
                        toastr.success(resp.message);
                        dataTable.ajax.reload();
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.responseJSON?.message || 'Failed to delete user!');
                }
            });
        }

        function goToView(id) {
            let url = "{{ route('admin.users.manage.show', ':id') }}";
            window.location.href = url.replace(':id', id);
        }

        // function exportUsers() {
        //     toastr.info('Export functionality coming soon!');
        // }


        function exportUsers() {
            Swal.fire({
                title: 'Export Users',
                html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select class="form-select" id="exportFormat">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel (XLSX)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Fields to Export</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="exportAll" checked>
                            <label class="form-check-label" for="exportAll">
                                <strong>Select All</strong>
                            </label>
                        </div>
                        <hr>
                        <div class="form-check">
                            <input class="form-check-input export-field" type="checkbox" value="name" checked>
                            <label class="form-check-label">Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input export-field" type="checkbox" value="username" checked>
                            <label class="form-check-label">Username</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input export-field" type="checkbox" value="email" checked>
                            <label class="form-check-label">Email</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input export-field" type="checkbox" value="phone" checked>
                            <label class="form-check-label">Phone</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input export-field" type="checkbox" value="roles" checked>
                            <label class="form-check-label">Roles</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input export-field" type="checkbox" value="status" checked>
                            <label class="form-check-label">Status</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input export-field" type="checkbox" value="created_at" checked>
                            <label class="form-check-label">Created Date</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="applyCurrentFilters">
                            <label class="form-check-label" for="applyCurrentFilters">
                                Apply current filters to export
                            </label>
                        </div>
                    </div>
                </div>
            `,
                showCancelButton: true,
                confirmButtonText: '<i class="fe fe-download me-1"></i> Export',
                cancelButtonText: 'Cancel',
                width: '500px',
                didOpen: () => {
                    // Select All functionality
                    document.getElementById('exportAll').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('.export-field');
                        checkboxes.forEach(cb => cb.checked = this.checked);
                    });

                    // Individual checkbox handling
                    document.querySelectorAll('.export-field').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const allChecked = Array.from(document.querySelectorAll(
                                    '.export-field'))
                                .every(cb => cb.checked);
                            document.getElementById('exportAll').checked = allChecked;
                        });
                    });
                },
                preConfirm: () => {
                    const format = document.getElementById('exportFormat').value;
                    const fields = Array.from(document.querySelectorAll('.export-field:checked'))
                        .map(cb => cb.value);
                    const applyFilters = document.getElementById('applyCurrentFilters').checked;

                    if (fields.length === 0) {
                        Swal.showValidationMessage('Please select at least one field to export');
                        return false;
                    }

                    return {
                        format,
                        fields,
                        applyFilters
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    processExport(result.value);
                }
            });
        }

        function processExport(options) {
            // Show loading
            Swal.fire({
                title: 'Exporting...',
                html: `
                <div class="text-center">
                    <img src="{{ asset('default/loader.gif') }}" alt="Loading" style="width: 80px;">
                    <p class="mt-3">Please wait while we prepare your file...</p>
                </div>
            `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    NProgress.start();
                }
            });

            // Prepare export data
            const exportData = {
                format: options.format,
                fields: options.fields,
                role: options.applyFilters ? $('#roleFilter').val() : '',
                status: options.applyFilters ? $('#statusFilter').val() : '',
                date_from: options.applyFilters ? $('#dateFrom').val() : '',
                date_to: options.applyFilters ? $('#dateTo').val() : '',
            };

            // Make AJAX request
            $.ajax({
                url: "{{ route('admin.users.manage.export') }}",
                type: "POST",
                data: exportData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                xhrFields: {
                    responseType: 'blob' // Important for file download
                },
                success: function(blob, status, xhr) {
                    NProgress.done();
                    Swal.close();

                    // Get filename from header or create default
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'users_export.' + options.format;
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/['"]/g, '');
                        }
                    }

                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // Show success message
                    toastr.success('File downloaded successfully!');
                },
                error: function(xhr) {
                    NProgress.done();
                    Swal.close();

                    if (xhr.status === 404) {
                        toastr.error('No users found to export!');
                    } else if (xhr.status === 422) {
                        toastr.error('Invalid export parameters!');
                    } else {
                        toastr.error('Failed to export users. Please try again!');
                    }
                }
            });
        }
    </script>
@endpush

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        .filter-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .filter-card .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            margin-bottom: 5px;
        }

        .stats-card {
            border-left: 4px solid;
            transition: transform 0.2s;
            cursor: pointer;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .text-break {
            word-break: break-word;
        }
    </style>
@endpush
