@extends('backend.app')

@section('title', 'Extension Requests')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom:50px">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Extension Requests</h1>
                        <p class="text-muted mb-0">Manage delivery time extension requests</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item active">Extension Requests</li>
                        </ol>
                    </div>
                </div>

                <!-- STATS CARDS -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6">
                        <div class="card stats-card" style="border-left:4px solid #6366f1;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Requests</h6>
                                        <h3 class="mb-0">{{ $stats['total_requests'] }}</h3>
                                    </div>
                                    <div class="icon-service p-3 rounded-3"
                                        style="background:rgba(99,102,241,.12); color:#6366f1;">
                                        <i class="fe fe-calendar fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6">
                        <div class="card stats-card" style="border-left:4px solid #f59e0b; cursor:pointer;"
                            onclick="filterByStatus('pending')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Pending</h6>
                                        <h3 class="mb-0">{{ $stats['pending_requests'] }}</h3>
                                    </div>
                                    <div class="icon-service bg-warning-transparent text-warning p-3 rounded-3">
                                        <i class="fe fe-clock fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6">
                        <div class="card stats-card" style="border-left:4px solid #22c55e; cursor:pointer;"
                            onclick="filterByStatus('approved')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Approved</h6>
                                        <h3 class="mb-0">{{ $stats['approved_requests'] }}</h3>
                                    </div>
                                    <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                        <i class="fe fe-check-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6">
                        <div class="card stats-card" style="border-left:4px solid #ef4444; cursor:pointer;"
                            onclick="filterByStatus('rejected')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Rejected</h6>
                                        <h3 class="mb-0">{{ $stats['rejected_requests'] }}</h3>
                                    </div>
                                    <div class="icon-service bg-danger-transparent text-danger p-3 rounded-3">
                                        <i class="fe fe-x-circle fs-20"></i>
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
                            <div class="d-flex justify-content-between align-items-center filter-toggle-header"
                                style="cursor:pointer;" onclick="toggleFilterBody()">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fe fe-filter text-primary fs-16"></i>
                                    <span class="fw-600 text-dark fs-14">Filters & Search</span>
                                    <span id="activeFilterBadge" class="badge bg-primary d-none">Active</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted fs-12" id="filterCollapseHint">Click to expand</span>
                                    <i class="fe fe-chevron-down text-muted fs-18" id="filterChevron"
                                        style="transition: transform 0.3s ease;"></i>
                                </div>
                            </div>
                            <div id="filterBody" style="display:none; margin-top:16px;">
                                <hr class="mt-0 mb-3">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Search</label>
                                        <input type="text" id="searchFilter" class="form-control"
                                            placeholder="Search by order #, reason...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                        <select class="form-select select3" id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Date From</label>
                                        <input type="text" class="form-control datepicker2" id="dateFrom"
                                            placeholder="Requested from...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Date To</label>
                                        <input type="text" class="form-control datepicker2" id="dateTo"
                                            placeholder="Requested to...">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <button type="button" class="btn btn-secondary w-100" id="resetFilter">
                                            <i class="fe fe-refresh-cw"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Extension Request List</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th style="width:50px;">#</th>
                                                <th style="width:140px;">Order</th>
                                                <th style="width:140px;">Requested By</th>
                                                <th style="width:100px;" class="text-center">Days</th>
                                                <th style="width:280px;">Reason</th>
                                                <th style="width:120px;" class="text-center">Status</th>
                                                <th style="width:130px;" class="text-center">Requested</th>
                                                <th style="width:100px;" class="text-center">Action</th>
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
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
    <script>
        let dataTable;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.datepicker2').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
            initializeDataTable();
            initSelect2();
        });

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#datatable')) $('#datatable').DataTable().destroy();

            dataTable = $('#datatable').DataTable({
                order: [
                    [0, 'desc']
                ],
                lengthMenu: [
                    [20, 50, 100],
                    [20, 50, 100]
                ],
                processing: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center"><img src="{{ asset('default/loader.gif') }}" style="width:50px;"></div>`
                },
                searching: false,
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'>>tipr",
                ajax: {
                    url: '{{ route('admin.extension-requests.data') }}',
                    data: d => {
                        d.search = $('#searchFilter').val();
                        d.status = $('#statusFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order_info',
                        orderable: false
                    },
                    {
                        data: 'requested_by',
                        orderable: false
                    },
                    {
                        data: 'extension_days',
                        className: 'text-center'
                    },
                    {
                        data: 'reason',
                        orderable: false
                    },
                    {
                        data: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'requested_at',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ]
            });

            // Filter bindings
            $('#resetFilter').click(function() {
                $('#statusFilter, #dateFrom, #dateTo').val('').trigger('change');
                $('#searchFilter').val('');
                checkActiveFilters();
                dataTable.ajax.reload();
            });

            $('#statusFilter, #dateFrom, #dateTo').change(function() {
                checkActiveFilters();
                dataTable.ajax.reload();
            });

            $('#searchFilter').on('keyup', function() {
                checkActiveFilters();
                dataTable.ajax.reload();
            });
        }

        function approveExtension(id) {
            if (!confirm('Approve this extension request?')) return;
            NProgress.start();
            $.ajax({
                url: `/admin/extension-requests/${id}/approve`,
                type: 'PATCH',
                success: res => {
                    NProgress.done();
                    toastr.success(res.message);
                    dataTable.ajax.reload();
                },
                error: xhr => {
                    NProgress.done();
                    toastr.error(xhr.responseJSON?.message || 'Failed');
                }
            });
        }

        function rejectExtension(id) {
            if (!confirm('Reject this extension request?')) return;
            NProgress.start();
            $.ajax({
                url: `/admin/extension-requests/${id}/reject`,
                type: 'PATCH',
                success: res => {
                    NProgress.done();
                    toastr.warning(res.message);
                    dataTable.ajax.reload();
                },
                error: xhr => {
                    NProgress.done();
                    toastr.error(xhr.responseJSON?.message || 'Failed');
                }
            });
        }

        function filterByStatus(status) {
            $('#statusFilter').val(status).trigger('change');
            if (document.getElementById('filterBody').style.display === 'none') toggleFilterBody();
        }

        function toggleFilterBody() {
            const body = document.getElementById('filterBody');
            const chevron = document.getElementById('filterChevron');
            const hint = document.getElementById('filterCollapseHint');
            const isOpen = body.style.display !== 'none';
            if (isOpen) {
                $(body).slideUp(250);
                chevron.style.transform = 'rotate(0deg)';
                hint.textContent = 'Click to expand';
            } else {
                $(body).slideDown(250);
                chevron.style.transform = 'rotate(180deg)';
                hint.textContent = 'Click to collapse';
            }
        }

        function checkActiveFilters() {
            const hasValue = ['searchFilter', 'statusFilter', 'dateFrom', 'dateTo'].some(id => $('#' + id).val());
            document.getElementById('activeFilterBadge').classList.toggle('d-none', !hasValue);
        }

        function initSelect2() {
            if ($('.select3').length && typeof $.fn.select2 !== 'undefined') {
                $('.select3').select2({
                    placeholder: 'Select...',
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
        .filter-card {
            background: #fff;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        }

        .filter-card .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            margin-bottom: 6px;
        }

        .stats-card {
            transition: transform .2s, box-shadow .2s;
            border: 1px solid #e9ecef;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }

        .icon-service {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .table td {
            vertical-align: middle;
        }

        .select2-container {
            width: 100% !important;
        }

        .fw-600 {
            font-weight: 600;
        }
    </style>
@endpush
