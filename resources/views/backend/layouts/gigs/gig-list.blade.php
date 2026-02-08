@extends('backend.app')

@section('title', 'Gig Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Gig Management</h1>
                        <p class="text-muted mb-0">Manage and moderate all gigs on the platform</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            {{-- <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li> --}}
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gigs</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- STATISTICS ROW -->
                <div class="row mb-4">
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left: 4px solid #007bff;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Gigs</h6>
                                        <h3 class="mb-0">{{ $totalGigs }}</h3>
                                    </div>
                                    <div class="icon-service bg-primary-transparent text-primary p-3 rounded-3">
                                        <i class="fe fe-briefcase fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left: 4px solid #28a745;"
                            onclick="filterByStatus('active')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Active</h6>
                                        <h3 class="mb-0">{{ $activeGigs }}</h3>
                                    </div>
                                    <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                        <i class="fe fe-check-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left: 4px solid #ffc107;"
                            onclick="filterByStatus('pending_approval')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Pending</h6>
                                        <h3 class="mb-0">{{ $pendingGigs }}</h3>
                                    </div>
                                    <div class="icon-service bg-warning-transparent text-warning p-3 rounded-3">
                                        <i class="fe fe-clock fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left: 4px solid #dc3545;"
                            onclick="filterByStatus('rejected')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Rejected</h6>
                                        <h3 class="mb-0">{{ $rejectedGigs }}</h3>
                                    </div>
                                    <div class="icon-service bg-danger-transparent text-danger p-3 rounded-3">
                                        <i class="fe fe-x-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left: 4px solid #6c757d;"
                            onclick="filterByStatus('draft')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Draft</h6>
                                        <h3 class="mb-0">{{ $draftGigs }}</h3>
                                    </div>
                                    <div class="icon-service bg-secondary-transparent text-secondary p-3 rounded-3">
                                        <i class="fe fe-file fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left: 4px solid #17a2b8;" onclick="toggleDeletedGigs()">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Deleted</h6>
                                        <h3 class="mb-0">{{ $deletedGigs }}</h3>
                                    </div>
                                    <div class="icon-service bg-info-transparent text-info p-3 rounded-3">
                                        <i class="fe fe-trash-2 fs-20"></i>
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
                            <div class="row align-items-end g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" id="searchFilter" class="form-control"
                                        placeholder="Search by title, seller...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Category</label>
                                    <select class="form-select select3" id="categoryFilter">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sub-Category</label>
                                    <select class="form-select select3" id="subCategoryFilter">
                                        <option value="">All Sub-Categories</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-select select3" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="pending_approval">Pending Approval</option>
                                        <option value="active">Active</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Price Range</label>
                                    <div class="input-group">
                                        <input type="number" id="minPrice" class="form-control" placeholder="Min">
                                        <input type="number" id="maxPrice" class="form-control" placeholder="Max">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-secondary w-100" id="resetFilter">
                                        <i class="fe fe-refresh-cw"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-3 align-items-end g-3">
                                <div class="col-md-2">
                                    <label class="form-label">Max Delivery Days</label>
                                    <input type="number" id="maxDeliveryDays" class="form-control"
                                        placeholder="e.g., 7">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date From</label>
                                    <input type="text" class="form-control datepicker2" id="dateFrom"
                                        placeholder="Created from...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date To</label>
                                    <input type="text" class="form-control datepicker2" id="dateTo"
                                        placeholder="Created to...">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showDeletedFilter">
                                        <label class="form-check-label" for="showDeletedFilter">
                                            Show Deleted Gigs
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GIG LIST TABLE -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Gig List</h3>
                                <div class="card-options">
                                    <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
                                        onclick="exportGigs()">
                                        <i class="fe fe-download me-1"></i> Export
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">ID</th>
                                                <th style="width: 300px;">Gig Info</th>
                                                <th style="width: 200px;">Seller</th>
                                                <th style="width: 150px;">Category</th>
                                                <th style="width: 120px;" class="text-center">Pricing</th>
                                                <th style="width: 120px;" class="text-center">Status</th>
                                                <th style="width: 140px;" class="text-center">Published</th>
                                                <th style="width: 120px;" class="text-center">Action</th>
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

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Gig Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <input type="hidden" id="gigId">
                    <input type="hidden" id="newStatus">
                    <div class="modal-body">
                        <div id="rejectionReasonSection" style="display: none;">
                            <label for="rejectionReason" class="form-label">
                                Rejection Reason <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="rejectionReason" rows="4"
                                placeholder="Please provide a reason for rejecting this gig..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div id="confirmMessage" class="alert alert-info">
                            Are you sure you want to change the status?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitStatusBtn">
                            <span class="btn-text">Confirm</span>
                            <span class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
    <script>
        let dataTable;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            $('.datepicker2').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });

            initializeDataTable();
            initializeSelect2();
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
                    [20, 50, 100, 200],
                    [20, 50, 100, 200]
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
                    url: "{{ route('admin.gigs.get.data') }}",
                    type: "GET",
                    dataType: 'json',
                    data: function(d) {
                        d.search = $('#searchFilter').val();
                        d.category_id = $('#categoryFilter').val();
                        d.sub_category_id = $('#subCategoryFilter').val();
                        d.status = $('#statusFilter').val();
                        d.min_price = $('#minPrice').val();
                        d.max_price = $('#maxPrice').val();
                        d.max_delivery_days = $('#maxDeliveryDays').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                        d.show_deleted = $('#showDeletedFilter').is(':checked') ? 'true' : 'false';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'gig_info',
                        name: 'title',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'seller',
                        name: 'seller',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'category',
                        name: 'category',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'pricing',
                        name: 'price',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'published_at',
                        name: 'published_at',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // Filter events
            $('#resetFilter').click(function() {
                $('#categoryFilter, #subCategoryFilter, #statusFilter, #minPrice, #maxPrice, #maxDeliveryDays, #dateFrom, #dateTo')
                    .val('').trigger('change');
                $('#searchFilter').val('');
                $('#showDeletedFilter').prop('checked', false);
                dataTable.ajax.reload();
            });

            $('#categoryFilter, #subCategoryFilter, #statusFilter, #minPrice, #maxPrice, #maxDeliveryDays, #dateFrom, #dateTo, #showDeletedFilter')
                .change(function() {
                    dataTable.ajax.reload();
                });

            $('#searchFilter').on('keyup', function() {
                dataTable.ajax.reload();
            });

            // Category change - load sub-categories
            $('#categoryFilter').change(function() {
                const categoryId = $(this).val();
                $('#subCategoryFilter').empty().append('<option value="">All Sub-Categories</option>');

                if (categoryId) {
                    $.ajax({
                        url: '{{ url('admin/gigs/categories') }}/' + categoryId + '/sub-categories',
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                response.data.forEach(function(subCat) {
                                    $('#subCategoryFilter').append(
                                        `<option value="${subCat.id}">${subCat.name}</option>`
                                    );
                                });
                            }
                        }
                    });
                }
            });
        }

        function filterByStatus(status) {
            $('#statusFilter').val(status).trigger('change');
        }

        function toggleDeletedGigs() {
            $('#showDeletedFilter').prop('checked', !$('#showDeletedFilter').is(':checked')).trigger('change');
        }

        function changeGigStatus(gigId, status) {
            event.preventDefault();

            $('#gigId').val(gigId);
            $('#newStatus').val(status);

            // Show/hide rejection reason section
            if (status === 'rejected') {
                $('#rejectionReasonSection').show();
                $('#confirmMessage').hide();
            } else {
                $('#rejectionReasonSection').hide();
                $('#confirmMessage').show().text(
                `Are you sure you want to change status to "${status.replace('_', ' ')}"?`);
            }

            $('#statusModal').modal('show');
        }

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();

            const gigId = $('#gigId').val();
            const status = $('#newStatus').val();
            const rejectionReason = $('#rejectionReason').val();

            // Validate rejection reason
            if (status === 'rejected' && !rejectionReason) {
                $('#rejectionReason').addClass('is-invalid');
                $('#rejectionReason').siblings('.invalid-feedback').text('Rejection reason is required');
                return;
            }

            $('#rejectionReason').removeClass('is-invalid');

            $('#submitStatusBtn').prop('disabled', true);
            $('#submitStatusBtn .btn-text').addClass('d-none');
            $('#submitStatusBtn .spinner-border').removeClass('d-none');

            NProgress.start();

            $.ajax({
                url: `{{ url('admin/gigs') }}/${gigId}/change-status`,
                type: 'POST',
                data: {
                    status: status,
                    rejection_reason: rejectionReason
                },
                success: function(response) {
                    NProgress.done();
                    $('#submitStatusBtn').prop('disabled', false);
                    $('#submitStatusBtn .btn-text').removeClass('d-none');
                    $('#submitStatusBtn .spinner-border').addClass('d-none');

                    if (response.success) {
                        toastr.success(response.message);
                        $('#statusModal').modal('hide');
                        $('#statusForm')[0].reset();
                        dataTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    $('#submitStatusBtn').prop('disabled', false);
                    $('#submitStatusBtn .btn-text').removeClass('d-none');
                    $('#submitStatusBtn .spinner-border').addClass('d-none');

                    toastr.error(xhr.responseJSON?.message || 'Failed to update status');
                }
            });
        });

        function exportGigs() {
            const params = new URLSearchParams({
                status: $('#statusFilter').val(),
                show_deleted: $('#showDeletedFilter').is(':checked') ? 'true' : 'false'
            });

            window.location.href = `{{ route('admin.gigs.export') }}?${params.toString()}`;
        }

        function initializeSelect2() {
            if ($('.select3').length && typeof $.fn.select2 !== 'undefined') {
                $('.select3').select2({
                    placeholder: 'Select an option',
                    allowClear: true,
                    width: '100%'
                });
            }
        }
    </script>
@endpush

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        .select2-container {
            width: 100% !important;
        }

        .filter-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filter-card .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            margin-bottom: 8px;
        }

        .stats-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: 1px solid #e9ecef;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .icon-service {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endpush
