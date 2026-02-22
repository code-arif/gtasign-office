@extends('backend.app')

@section('title', 'QA Review Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">QA Review Management</h1>
                        <p class="text-muted mb-0">Review and approve expert deliveries before sending to clients</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item active" aria-current="page">QA Reviews</li>
                        </ol>
                    </div>
                </div>

                <!-- STATS CARDS -->
                <div class="row mb-1">
                    <div class="col-xl col-lg-3 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #6366f1;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Reviews</h6>
                                        <h3 class="mb-0">{{ $totalAll }}</h3>
                                    </div>
                                    <div class="icon-service p-3 rounded-3"
                                        style="background:rgba(99,102,241,.12); color:#6366f1;">
                                        <i class="fe fe-clipboard fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-lg-3 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #f59e0b; cursor:pointer;"
                            onclick="filterByStatus('pending')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Pending</h6>
                                        <h3 class="mb-0">{{ $totalPending }}</h3>
                                    </div>
                                    <div class="icon-service bg-warning-transparent text-warning p-3 rounded-3">
                                        <i class="fe fe-clock fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-lg-3 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #22c55e; cursor:pointer;"
                            onclick="filterByStatus('approved')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Approved</h6>
                                        <h3 class="mb-0">{{ $totalApproved }}</h3>
                                    </div>
                                    <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                        <i class="fe fe-check-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-lg-3 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #f43f5e; cursor:pointer;"
                            onclick="filterByStatus('rejected')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Rejected</h6>
                                        <h3 class="mb-0">{{ $totalRejected }}</h3>
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
                                id="filterToggleBtn" style="cursor:pointer;" onclick="toggleFilterBody()">
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

                            <div id="filterBody" style="display:none; margin-top:10px;">
                                <hr class="mt-0 mb-3">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Search</label>
                                        <input type="text" id="searchFilter" class="form-control"
                                            placeholder="Order number, expert name...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select select3" id="statusFilter">
                                            <option value="all">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Date From</label>
                                        <input type="text" class="form-control datepicker2" id="dateFrom"
                                            placeholder="Submitted from...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Date To</label>
                                        <input type="text" class="form-control datepicker2" id="dateTo"
                                            placeholder="Submitted to...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <button type="button" class="btn btn-secondary w-100" id="resetFilter"
                                            title="Reset Filters">
                                            <i class="fe fe-refresh-cw me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QA TABLE -->
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <h3 class="card-title mb-0">QA Review Queue</h3>
                                    @if ($totalPending > 0)
                                        <span class="badge bg-warning">{{ $totalPending }} Pending</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th style="width:50px;">#</th>
                                                <th style="width:180px;">Order</th>
                                                <th style="width:180px;">Expert</th>
                                                <th style="width:150px;">Delivery</th>
                                                <th style="width:130px;" class="text-center">Status</th>
                                                <th style="width:130px;" class="text-center">Submitted</th>
                                                <th style="width:130px;" class="text-center">Action</th>
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

    <!-- Quick Reject Modal (from datatable) -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fe fe-x-circle me-2"></i>Reject Delivery</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm">
                    <input type="hidden" id="rejectReviewId">
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex gap-2 align-items-start">
                            <i class="fe fe-alert-triangle flex-shrink-0 mt-1"></i>
                            <span>The expert will be notified and the delivery will be returned for revision.
                                Provide clear, actionable feedback.</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600">Feedback / Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectFeedback" rows="4"
                                placeholder="Describe what needs to be fixed or improved..."></textarea>
                            <div class="invalid-feedback">Feedback is required</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-600">Issue Tags <small
                                    class="text-muted">(optional)</small></label>
                            <div class="d-flex flex-wrap gap-2" id="issueTags">
                                @foreach (['Quality Issue', 'Incomplete Work', 'Wrong Format', 'Copyright Issue', 'Missing Files', 'Does Not Match Requirements'] as $tag)
                                    <label class="issue-tag-label">
                                        <input type="checkbox" class="issue-tag-input" value="{{ $tag }}">
                                        <span class="issue-tag-text">{{ $tag }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="rejectSubmitBtn">
                            <span class="btn-text"><i class="fe fe-x me-1"></i>Reject & Return to Expert</span>
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
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            $('.datepicker2').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });

            initDataTable();
            initSelect2();
        });

        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
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
                responsive: false,
                language: {
                    processing: `<div class="text-center"><img src="{{ asset('default/loader.gif') }}" style="width:50px;"></div>`
                },
                pagingType: 'full_numbers',
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                ajax: {
                    url: '{{ route('admin.qa.data') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: function(d) {
                        d.search = $('#searchFilter').val();
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
                        data: 'order_info',
                        name: 'order_info',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'seller_info',
                        name: 'seller_info',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'delivery_info',
                        name: 'delivery_info',
                        orderable: false,
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
                        data: 'submitted_at',
                        name: 'submitted_at',
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
                    },
                ]
            });

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

            $('#searchFilter').on('keyup', debounce(function() {
                checkActiveFilters();
                dataTable.ajax.reload();
            }, 400));
        }

        function filterByStatus(status) {
            $('#statusFilter').val(status).trigger('change');
            const body = document.getElementById('filterBody');
            if (body.style.display === 'none') toggleFilterBody();
        }

        /* ── Quick Approve ── */
        function quickApprove(reviewId) {

            Swal.fire({
                title: 'Approve Delivery?',
                text: 'This will approve the QA and send the delivery to the client.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {

                if (!result.isConfirmed) return;

                NProgress.start();

                $.ajax({
                    url: `/admin/qa/${reviewId}/approve`,
                    type: 'POST',
                    success: function(res) {
                        NProgress.done();

                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            dataTable.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: res.message
                            });
                        }
                    },
                    error: function(xhr) {
                        NProgress.done();

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to approve'
                        });
                    }
                });

            });
        }


        /* ── Open Reject Modal ── */
        function openRejectModal(reviewId) {
            $('#rejectReviewId').val(reviewId);
            $('#rejectFeedback').val('').removeClass('is-invalid');
            $('.issue-tag-input').prop('checked', false);
            $('#rejectModal').modal('show');
        }

        /* ── Reject Form Submit ── */
        $('#rejectForm').on('submit', function(e) {
            e.preventDefault();
            const reviewId = $('#rejectReviewId').val();
            const feedback = $('#rejectFeedback').val().trim();

            if (!feedback) {
                $('#rejectFeedback').addClass('is-invalid');
                return;
            }
            $('#rejectFeedback').removeClass('is-invalid');

            const issues = [];
            $('.issue-tag-input:checked').each(function() {
                issues.push($(this).val());
            });

            $('#rejectSubmitBtn').prop('disabled', true);
            $('#rejectSubmitBtn .btn-text').addClass('d-none');
            $('#rejectSubmitBtn .spinner-border').removeClass('d-none');
            NProgress.start();

            $.ajax({
                url: `/admin/qa/${reviewId}/reject`,
                type: 'POST',
                data: {
                    feedback,
                    issues
                },
                success: function(res) {
                    NProgress.done();
                    resetRejectBtn();
                    if (res.success) {
                        toastr.success(res.message);
                        $('#rejectModal').modal('hide');
                        dataTable.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    resetRejectBtn();
                    toastr.error(xhr.responseJSON?.message || 'Failed to reject');
                }
            });
        });

        function resetRejectBtn() {
            $('#rejectSubmitBtn').prop('disabled', false);
            $('#rejectSubmitBtn .btn-text').removeClass('d-none');
            $('#rejectSubmitBtn .spinner-border').addClass('d-none');
        }

        /* ── Filter Helpers ── */
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
                $(body).slideDown(250, function() {

                    // 🔥 FIX SELECT2 WIDTH AFTER SHOW
                    $('#statusFilter').select2('destroy');
                    initSelect2();
                });

                chevron.style.transform = 'rotate(180deg)';
                hint.textContent = 'Click to collapse';
            }
        }

        function checkActiveFilters() {
            const has = ['searchFilter', 'statusFilter', 'dateFrom', 'dateTo']
                .some(id => $('#' + id).val() && $('#' + id).val() !== 'all');
            document.getElementById('activeFilterBadge').classList.toggle('d-none', !has);
        }

        function initSelect2() {
            if ($('.select3').length && typeof $.fn.select2 !== 'undefined') {
                $('.select3').select2({
                    placeholder: 'All Status',
                    allowClear: true,
                    width: 'resolve', // important
                    dropdownAutoWidth: true
                });
            }
        }

        function debounce(fn, delay) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
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

        .filter-toggle-header {
            padding: 4px 0;
            user-select: none;
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

        .fw-600 {
            font-weight: 600;
        }

        /* Issue Tags */
        .issue-tag-label {
            cursor: pointer;
        }

        .issue-tag-input {
            display: none;
        }

        .issue-tag-text {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            border: 2px solid #dee2e6;
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            transition: all .2s;
        }

        .issue-tag-input:checked~.issue-tag-text {
            border-color: #f43f5e;
            background: #fff0f3;
            color: #f43f5e;
        }

        .issue-tag-label:hover .issue-tag-text {
            border-color: #adb5bd;
        }
    </style>
@endpush
