@extends('backend.app')

@section('title', 'Expert Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Expert Management</h1>
                        <p class="text-muted mb-0">Manage and monitor all experts on the platform</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <span>&nbsp; &gt&gt &nbsp;</span>
                            <li class="breadcrumb-item active" aria-current="page">Experts</li>
                        </ol>
                    </div>
                </div>

                <!-- STATS CARDS -->
                <div class="row mb-4">
                    <div class="col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #6366f1;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Experts</h6>
                                        <h3 class="mb-0">{{ $totalExperts }}</h3>
                                    </div>
                                    <div class="icon-service p-3 rounded-3"
                                        style="background:rgba(99,102,241,.12); color:#6366f1;">
                                        <i class="fe fe-users fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #28a745; cursor:pointer;"
                            onclick="filterByStatus('active')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Active</h6>
                                        <h3 class="mb-0">{{ $activeExperts }}</h3>
                                    </div>
                                    <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                        <i class="fe fe-check-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #6c757d; cursor:pointer;"
                            onclick="filterByStatus('inactive')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Inactive</h6>
                                        <h3 class="mb-0">{{ $inactiveExperts }}</h3>
                                    </div>
                                    <div class="icon-service bg-secondary-transparent text-secondary p-3 rounded-3">
                                        <i class="fe fe-pause-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #dc3545; cursor:pointer;"
                            onclick="filterByStatus('suspended')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Suspended</h6>
                                        <h3 class="mb-0">{{ $suspendedExperts }}</h3>
                                    </div>
                                    <div class="icon-service bg-danger-transparent text-danger p-3 rounded-3">
                                        <i class="fe fe-slash fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card" style="border-left:4px solid #17a2b8; cursor:pointer;"
                            onclick="toggleDeletedExperts()">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Deleted</h6>
                                        <h3 class="mb-0">{{ $deletedExperts }}</h3>
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
                            <!-- Filter Header -->
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

                            <!-- Filter Body -->
                            <div id="filterBody" style="display:none; margin-top:16px;">
                                <hr class="mt-0 mb-3">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Search</label>
                                        <input type="text" id="searchFilter" class="form-control"
                                            placeholder="Search by name, email, username...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                        <select class="form-select select3" id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Date From</label>
                                        <input type="text" class="form-control datepicker2" id="dateFrom"
                                            placeholder="Joined from...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Date To</label>
                                        <input type="text" class="form-control datepicker2" id="dateTo"
                                            placeholder="Joined to...">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <button type="button" class="btn btn-secondary w-100" id="resetFilter"
                                            title="Reset Filters">
                                            <i class="fe fe-refresh-cw"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <label class="deleted-toggle-label mb-0" for="showDeletedFilter"
                                            style="min-width:unset; padding:4px 12px;">
                                            <input type="checkbox" id="showDeletedFilter" class="deleted-toggle-input">
                                            <span class="deleted-toggle-track">
                                                <span class="deleted-toggle-thumb"></span>
                                            </span>
                                            <span class="deleted-toggle-text" style="font-size:12px;">Deleted</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EXPERT TABLE -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Expert List</h3>
                                <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
                                    onclick="exportExperts()">
                                    <i class="fe fe-download me-1"></i> Export
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th style="width:50px;">#</th>
                                                <th style="width:260px;">Expert</th>
                                                <th style="width:220px;">Contact</th>
                                                <th style="width:220px;" class="text-center">Stats</th>
                                                <th style="width:110px;" class="text-center">Status</th>
                                                <th style="width:130px;" class="text-center">Joined</th>
                                                <th style="width:110px;" class="text-center">Action</th>
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
                    <h5 class="modal-title">Change Expert Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <input type="hidden" id="expertId">
                    <input type="hidden" id="newStatus">
                    <div class="modal-body">
                        <div id="suspendReasonSection" style="display:none;">
                            <label class="form-label">Suspension Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="suspendReason" rows="4"
                                placeholder="Provide a reason for suspending this expert..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div id="confirmMessage" class="alert alert-info mb-0">
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

    <!-- Level Change Modal -->
    <div class="modal fade" id="levelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Expert Level</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Expert:</strong>
                        <span id="modalExpertName" class="fw-bold"></span>
                        <small class="text-muted ms-2">@<span id="modalExpertUsername"></span></small>
                    </div>

                    <form id="levelForm">
                        <input type="hidden" id="levelExpertId" name="expert_id">

                        <div class="mb-3">
                            <label class="form-label">Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="levelSelect" name="level" required>
                                <option value="">Select Level</option>
                                <option value="level 1">Level 1</option>
                                <option value="level 2">Level 2</option>
                                <option value="level 3">Level 3</option>
                                {{-- <option value="expert">Expert</option> --}}
                                {{-- <option value="top_rated">Top Rated</option> --}}
                                {{-- <option value="pro">Pro</option> --}}
                                <!-- Add more levels according to your business logic -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Level Display Name (optional)</label>
                            <input type="text" class="form-control" id="levelNameInput" name="level_name"
                                placeholder="e.g. Platinum Seller, Verified Pro, etc.">
                            <small class="text-muted">This will be shown publicly (badge/title)</small>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="submitLevelBtn">
                        <span class="btn-text">Save Level</span>
                        <span class="spinner-border spinner-border-sm d-none"></span>
                    </button>
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
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
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
                responsive: false,
                serverSide: true,
                language: {
                    processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width:50px;">
                    </div>`
                },
                pagingType: 'full_numbers',
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                ajax: {
                    url: '{{ route('admin.experts.data') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: function(d) {
                        d.search = $('#searchFilter').val();
                        d.status = $('#statusFilter').val();
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
                        data: 'expert_info',
                        name: 'email',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'contact',
                        name: 'contact',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'stats',
                        name: 'stats',
                        orderable: false,
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
                        data: 'joined_at',
                        name: 'created_at',
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

            // Filter bindings
            $('#resetFilter').click(function() {
                $('#statusFilter, #dateFrom, #dateTo').val('').trigger('change');
                $('#searchFilter').val('');
                $('#showDeletedFilter').prop('checked', false);
                document.querySelector('.deleted-toggle-label')?.classList.remove('active');
                checkActiveFilters();
                dataTable.ajax.reload();
            });

            $('#statusFilter, #dateFrom, #dateTo, #showDeletedFilter').change(function() {
                checkActiveFilters();
                dataTable.ajax.reload();
            });

            $('#searchFilter').on('keyup', function() {
                checkActiveFilters();
                dataTable.ajax.reload();
            });
        }

        function filterByStatus(status) {
            $('#statusFilter').val(status).trigger('change');
            // Open filter panel if collapsed
            const body = document.getElementById('filterBody');
            if (body.style.display === 'none') toggleFilterBody();
        }

        function toggleDeletedExperts() {
            const cb = $('#showDeletedFilter');
            cb.prop('checked', !cb.is(':checked')).trigger('change');
            const label = document.querySelector('.deleted-toggle-label');
            if (label) label.classList.toggle('active', cb.is(':checked'));
            const body = document.getElementById('filterBody');
            if (body.style.display === 'none') toggleFilterBody();
        }

        function changeExpertStatus(expertId, status) {
            event.preventDefault();
            $('#expertId').val(expertId);
            $('#newStatus').val(status);

            if (status === 'suspended') {
                $('#suspendReasonSection').show();
                $('#confirmMessage').hide();
            } else {
                $('#suspendReasonSection').hide();
                $('#confirmMessage').show().text(`Are you sure you want to set this expert as "${status}"?`);
            }
            $('#statusModal').modal('show');
        }

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            const expertId = $('#expertId').val();
            const status = $('#newStatus').val();
            const reason = $('#suspendReason').val();

            if (status === 'suspended' && !reason) {
                $('#suspendReason').addClass('is-invalid');
                $('#suspendReason').siblings('.invalid-feedback').text('Reason is required');
                return;
            }
            $('#suspendReason').removeClass('is-invalid');

            $('#submitStatusBtn').prop('disabled', true);
            $('#submitStatusBtn .btn-text').addClass('d-none');
            $('#submitStatusBtn .spinner-border').removeClass('d-none');
            NProgress.start();

            $.ajax({
                url: `/admin/experts/${expertId}/status`,
                type: 'PATCH',
                data: {
                    status,
                    reason
                },
                success: function(res) {
                    NProgress.done();
                    resetStatusBtn();
                    if (res.success) {
                        toastr.success(res.message);
                        $('#statusModal').modal('hide');
                        $('#statusForm')[0].reset();
                        dataTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    resetStatusBtn();
                    toastr.error(xhr.responseJSON?.message || 'Failed to update status');
                }
            });
        });

        function resetStatusBtn() {
            $('#submitStatusBtn').prop('disabled', false);
            $('#submitStatusBtn .btn-text').removeClass('d-none');
            $('#submitStatusBtn .spinner-border').addClass('d-none');
        }

        function exportExperts() {
            const params = new URLSearchParams({
                status: $('#statusFilter').val(),
                show_deleted: $('#showDeletedFilter').is(':checked') ? 'true' : 'false'
            });
            window.location.href = `{{ route('admin.experts.export') }}?${params.toString()}`;
        }

        /* -------- Filter Collapse -------- */
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

        /* -------- Deleted Toggle -------- */
        document.getElementById('showDeletedFilter').addEventListener('change', function() {
            this.closest('.deleted-toggle-label').classList.toggle('active', this.checked);
        });

        /* -------- Active Filter Badge -------- */
        function checkActiveFilters() {
            const hasValue = ['searchFilter', 'statusFilter', 'dateFrom', 'dateTo'].some(id => $('#' + id).val());
            const deleted = $('#showDeletedFilter').is(':checked');
            document.getElementById('activeFilterBadge').classList.toggle('d-none', !hasValue && !deleted);
        }

        function initSelect2() {
            if ($('.select3').length && typeof $.fn.select2 !== 'undefined') {
                $('.select3').select2({
                    placeholder: 'Select an option',
                    allowClear: true,
                    width: '100%'
                });
            }
        }

        // Expert level Up
        function openLevelModal(expertId) {
            $.ajax({
                url: `/admin/experts/${expertId}/level-form`,
                type: 'GET',
                success: function(res) {
                    if (res.success) {
                        $('#levelExpertId').val(res.expert_id);
                        $('#modalExpertName').text(res.full_name);
                        $('#modalExpertUsername').text(res.username);

                        // Pre-select current level if exists
                        $('#levelSelect').val(res.current_level || '');
                        $('#levelNameInput').val(res.current_level_name || '');

                        $('#levelModal').modal('show');
                    } else {
                        toastr.error('Could not load level form');
                    }
                },
                error: function() {
                    toastr.error('Error loading level data');
                }
            });
        }

        $('#submitLevelBtn').on('click', function() {
            const expertId = $('#levelExpertId').val();
            const level = $('#levelSelect').val();
            const levelName = $('#levelNameInput').val().trim();

            if (!level) {
                toastr.warning('Please select a level');
                $('#levelSelect').focus();
                return;
            }

            $('#submitLevelBtn').prop('disabled', true);
            $('#submitLevelBtn .btn-text').addClass('d-none');
            $('#submitLevelBtn .spinner-border').removeClass('d-none');

            $.ajax({
                url: `/admin/experts/${expertId}/level`,
                type: 'PATCH',
                data: {
                    level: level,
                    level_name: levelName,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#levelModal').modal('hide');
                        dataTable.ajax.reload(); // optional: refresh table
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to update level');
                },
                complete: function() {
                    $('#submitLevelBtn').prop('disabled', false);
                    $('#submitLevelBtn .btn-text').removeClass('d-none');
                    $('#submitLevelBtn .spinner-border').addClass('d-none');
                }
            });
        });
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
            cursor: default;
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

        /* Deleted Toggle */
        .deleted-toggle-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 9px 14px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            background: #f8f9fa;
            transition: border-color .25s, background .25s;
            user-select: none;
        }

        .deleted-toggle-label:hover {
            border-color: #adb5bd;
            background: #f1f3f5;
        }

        .deleted-toggle-label.active {
            border-color: #17a2b8;
            background: #e8f7f9;
        }

        .deleted-toggle-input {
            display: none;
        }

        .deleted-toggle-track {
            position: relative;
            width: 44px;
            height: 24px;
            background: #ced4da;
            border-radius: 50px;
            flex-shrink: 0;
            transition: background .25s;
        }

        .deleted-toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
            transition: left .25s;
        }

        .deleted-toggle-input:checked~.deleted-toggle-track {
            background: #17a2b8;
        }

        .deleted-toggle-input:checked~.deleted-toggle-track .deleted-toggle-thumb {
            left: 23px;
        }

        .deleted-toggle-text {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
        }

        .deleted-toggle-label.active .deleted-toggle-text {
            color: #17a2b8;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush
