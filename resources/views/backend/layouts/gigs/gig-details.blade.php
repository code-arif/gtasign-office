@extends('backend.app')

@section('title', 'Gig Details')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom:60px">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Gig Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                {{-- <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li> --}}
                                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                                <span>&nbsp; &gt&gt &nbsp;</span>
                                <li class="breadcrumb-item"><a href="{{ route('admin.gigs.index') }}">Gigs</a></li>
                                <span>&nbsp; &gt&gt &nbsp;</span>
                                <li class="breadcrumb-item active" aria-current="page">Details</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('admin.gigs.index') }}"
                            class="btn btn-outline-secondary d-inline-flex align-items-center">
                            <i class="fe fe-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                @php
                    $profile = $gig->user->profile ?? null;
                    $fullName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : 'No Name';
                    $avatar =
                        $profile && $profile->avatar
                            ? asset($profile->avatar)
                            : 'https://ui-avatars.com/api/?name=' .
                                urlencode($fullName) .
                                '&background=6366f1&color=fff&size=200';
                @endphp

                <div class="row">
                    <!-- Left Sidebar - Gig Navigation -->
                    <div class="col-xl-3 col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-zap text-warning me-2"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            @if (!$gig->deleted_at)
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        <i class="fe fe-edit me-1"></i> Change Status
                                                    </button>
                                                    <ul class="dropdown-menu w-100">
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="changeStatus('active')">
                                                                <i
                                                                    class="fe fe-check-circle text-success me-2"></i>Approve</a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="changeStatus('rejected')">
                                                                <i class="fe fe-x-circle text-danger me-2"></i>Reject</a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="changeStatus('draft')">
                                                                <i class="fe fe-file text-secondary me-2"></i>Set Draft</a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="changeStatus('pending_approval')">
                                                                <i class="fe fe-clock text-warning me-2"></i>Set Pending</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <a href="mailto:{{ $gig?->user?->email }}" class="btn btn-outline-primary">
                                                <i class="fe fe-mail me-1"></i> Contact Seller
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gig List Navigation -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-list text-primary me-2"></i>All Gigs
                                </h5>
                            </div>
                            <div class="card-body p-0" style="max-height: 700px; overflow-y: auto;">
                                <div class="list-group list-group-flush">
                                    @foreach ($gigs as $g)
                                        <a href="{{ route('admin.gigs.show', $g->id) }}"
                                            class="list-group-item list-group-item-action {{ $g->id == $gig->id ? 'active' : '' }}">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $gProfile = $g->user->profile ?? null;
                                                    $gName = $gProfile
                                                        ? trim(
                                                            $gProfile->first_name . ' ' . ($gProfile->last_name ?? ''),
                                                        )
                                                        : 'N/A';
                                                    $gAvatar =
                                                        $gProfile && $gProfile->avatar
                                                            ? asset('storage/' . $gProfile->avatar)
                                                            : 'https://ui-avatars.com/api/?name=' .
                                                                urlencode($gName) .
                                                                '&background=random';
                                                @endphp
                                                <img src="{{ $gAvatar }}" class="rounded-circle me-2" width="32"
                                                    height="32" style="object-fit: cover;">
                                                <div class="flex-grow-1 text-truncate">
                                                    <div class="fw-semibold text-truncate small">{{ $g->title }}</div>
                                                    <small style="color: #05402e">ID: {{ $g->id }}</small>
                                                </div>
                                                @if ($g->deleted_at)
                                                    <span class="badge bg-danger ms-2">Del</span>
                                                @else
                                                    @php
                                                        $statusColors = [
                                                            'draft' => 'secondary',
                                                            'pending_approval' => 'warning',
                                                            'active' => 'success',
                                                            'rejected' => 'danger',
                                                        ];
                                                        $color = $statusColors[$g->status] ?? 'secondary';
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $color }} ms-2">{{ ucfirst($g->status) }}</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-xl-9 col-lg-8">
                        <!-- Deleted Notice -->
                        @if ($gig->deleted_at)
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="fe fe-alert-triangle fs-20 me-3"></i>
                                <div>
                                    <strong>This gig has been deleted by the user</strong>
                                    <p class="mb-0">Deleted on {{ $gig->deleted_at->format('M d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Rejection Notice -->
                        @if ($gig->status === 'rejected' && $gig->rejection_reason)
                            <div class="alert alert-warning d-flex align-items-start" role="alert">
                                <i class="fe fe-info fs-20 me-3 mt-1"></i>
                                <div>
                                    <strong>Rejection Reason:</strong>
                                    <p class="mb-0 mt-2">{{ $gig->rejection_reason }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Gig Header Card -->
                        <div class="card gig-header-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h2 class="mb-3">{{ $gig->title }}</h2>
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="{{ asset('storage/' . $avatar) }}" class="rounded-circle me-3" width="50"
                                                height="50" style="object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold">{{ $fullName }}</div>
                                                <small class="text-muted">{{ $gig?->user?->email }}</small>
                                            </div>
                                        </div>
                                        <div class="row g-2 align-items-center">

                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'pending_approval' => 'warning',
                                                    'active' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                                $color = $statusColors[$gig->status] ?? 'secondary';
                                            @endphp

                                            <!-- Gig Status -->
                                            <div class="col-auto d-flex align-items-center gap-1">
                                                <strong>Gig Status:</strong>
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst(str_replace('_', ' ', $gig->status)) }}
                                                </span>
                                            </div>

                                            <!-- Category -->
                                            <div class="col-auto d-flex align-items-center gap-1">
                                                <strong>Category:</strong>
                                                <span class="badge bg-info">
                                                    {{ $gig->category->name ?? 'N/A' }}
                                                </span>
                                            </div>

                                            <!-- Sub Category -->
                                            @if ($gig->subCategory)
                                                <div class="col-auto d-flex align-items-center gap-1">
                                                    <strong>Sub Category:</strong>
                                                    <span class="badge bg-secondary">
                                                        {{ $gig->subCategory->name }}
                                                    </span>
                                                </div>
                                            @endif

                                        </div>

                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="pricing-box">
                                            <h3 class="text-success mb-0">${{ number_format($gig->price, 2) }}</h3>
                                            <small class="text-muted">Base Price</small>
                                        </div>
                                        <div class="delivery-box mt-3">
                                            <h5 class="mb-0">{{ $gig->delivery_days }} Days</h5>
                                            <small class="text-muted">Delivery Time</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="fw-bold text-primary">{{ number_format($gig->impressions) }}</div>
                                        <small class="text-muted">Impressions</small>
                                    </div>
                                    <div class="col-4 border-start border-end">
                                        <div class="fw-bold text-info">{{ number_format($gig->clicks) }}</div>
                                        <small class="text-muted">Clicks</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-success">{{ number_format($gig->orders) }}</div>
                                        <small class="text-muted">Orders</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gig Description -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-file-text text-primary me-2"></i>Gig Description
                                </h5>
                            </div>
                            <div class="card-body">
                                @if ($gig->scope)
                                    <div class="gig-description">{{ $gig->scope }}</div>
                                @else
                                    <p class="text-muted mb-0">No description provided</p>
                                @endif
                            </div>
                        </div>

                        <!-- Gig Images -->
                        @if ($gig->images && $gig->images->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-image text-primary me-2"></i>Gig Images
                                        <span class="badge bg-primary ms-2">{{ $gig->images->count() }}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        @foreach ($gig->images as $image)
                                            <div class="col-md-3 col-sm-6">
                                                <div class="gig-image-wrapper">
                                                    <img src="{{ asset('storage/' . $image->path) }}"
                                                        class="img-fluid rounded-1 gig-thumbnail" alt="Gig Image"
                                                        data-bs-toggle="modal" data-bs-target="#imageModal"
                                                        onclick="showImageModal('{{ asset('storage/' . $image->path) }}')">
                                                    @if ($image->is_primary)
                                                        <span class="badge bg-success primary-badge">Primary</span>
                                                    @endif
                                                    <div class="image-overlay">
                                                        <i class="fe fe-maximize-2"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Gig Documents -->
                        @if ($gig->documents && $gig->documents->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-paperclip text-primary me-2"></i>Gig Documents
                                        <span class="badge bg-primary ms-2">{{ $gig->documents->count() }}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        @foreach ($gig->documents as $document)
                                            @php
                                                $fileName = basename($document->path);
                                                $fileExt = pathinfo($document->path, PATHINFO_EXTENSION);
                                                $fileSize = file_exists(storage_path('app/public/' . $document->path))
                                                    ? filesize(storage_path('app/public/' . $document->path))
                                                    : 0;
                                                $fileSizeFormatted =
                                                    $fileSize > 0 ? round($fileSize / 1024, 2) . ' KB' : 'N/A';
                                            @endphp
                                            <div class="list-group-item">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="file-icon me-3">
                                                            <i class="fe fe-file-text fs-30 text-info"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $fileName }}</div>
                                                            <small class="text-muted">
                                                                {{ strtoupper($fileExt) }} • {{ $fileSizeFormatted }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ asset('storage/' . $document->path) }}"
                                                            class="doc-btn doc-download" download>
                                                            <i class="fe fe-download"></i>
                                                        </a>

                                                        @if (in_array($fileExt, ['pdf']))
                                                            <a href="{{ asset('storage/' . $document->path) }}"
                                                                class="doc-btn doc-preview" target="_blank">
                                                                <i class="fe fe-eye"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Tags -->
                        @if ($gig->tags && $gig->tags->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-tag text-primary me-2"></i>Tags
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-2 flex-wrap">
                                        @foreach ($gig->tags as $tag)
                                            <span class="badge bg-light text-dark border px-3 py-2">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Requirements -->
                        @if ($gig->system_questions || $gig->custom_questions)
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-help-circle text-primary me-2"></i>Requirements & Questions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if ($gig->system_questions && is_array($gig->system_questions))
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-3">System Questions</h6>
                                            <ol class="ps-3">
                                                @foreach ($gig->system_questions as $question)
                                                    <li class="mb-2">
                                                        {{ is_array($question) ? $question['question'] ?? '' : $question }}
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @endif

                                    @if ($gig->custom_questions && is_array($gig->custom_questions))
                                        <div>
                                            <h6 class="text-muted mb-3">Custom Questions</h6>
                                            <ol class="ps-3">
                                                @foreach ($gig->custom_questions as $question)
                                                    <li class="mb-2">
                                                        {{ is_array($question) ? $question['question'] ?? '' : $question }}
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @endif

                                    @if (
                                        (!$gig->system_questions || count($gig->system_questions) == 0) &&
                                            (!$gig->custom_questions || count($gig->custom_questions) == 0))
                                        <p class="text-muted mb-0">No requirements specified</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Metadata -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-info text-primary me-2"></i>Metadata
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Gig ID</small>
                                            <strong>{{ $gig->id }}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Created At</small>
                                            <strong>{{ $gig->created_at->format('M d, Y \a\t h:i A') }}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Updated At</small>
                                            <strong>{{ $gig->updated_at->format('M d, Y \a\t h:i A') }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Published At</small>
                                            <strong>{{ $gig->published_at ? $gig->published_at->format('M d, Y \a\t h:i A') : 'Not Published' }}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Images Count</small>
                                            <strong>{{ $gig->images->count() }}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Documents Count</small>
                                            <strong>{{ $gig->documents->count() }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gig Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Gig Image">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <form id="statusForm">
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
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center"
                            id="submitStatusBtn">
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
    <script>
        const gigId = {{ $gig->id }};

        function showImageModal(imageSrc) {
            $('#modalImage').attr('src', imageSrc);
        }

        function changeStatus(status) {
            event.preventDefault();

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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
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

                        // Reload page to show updated status
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
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
    </script>
@endpush

@push('styles')
    <style>
        /* Gig Header Card */
        .gig-header-card {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .pricing-box,
        .delivery-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        /* Gig Images */
        .gig-image-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .gig-image-wrapper:hover {
            transform: scale(1.05);
        }

        .gig-image-wrapper:hover .image-overlay {
            opacity: 1;
        }

        .gig-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .primary-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 8px;
        }

        .image-overlay i {
            color: white;
            font-size: 32px;
        }

        /* File Icon */
        .file-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f7ff;
            border-radius: 8px;
        }

        /* Gig Description */
        .gig-description {
            white-space: pre-wrap;
            line-height: 1.8;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        .card-title {
            font-weight: 600;
            color: #2c3e50;
        }

        /* Base list item */
        .list-group-item {
            transition:
                background-color 0.25s ease,
                transform 0.25s ease,
                color 0.25s ease;
        }

        .doc-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid transparent;
            background-color: rgba(5, 64, 46, 0.08);
            color: #05402e;
            transition: all 0.25s ease;
            text-decoration: none;
        }

        /* Download */
        .doc-download {
            background-color: rgba(13, 110, 253, 0.12);
            color: #0d6efd;
        }

        /* Preview */
        .doc-preview {
            background-color: rgba(13, 202, 240, 0.12);
            color: #0dcaf0;
        }

        /* Hover effect */
        .doc-btn:hover {
            background-color: #05402e !important;
            color: #ffffff;
            border-color: #ffffff;
        }

        /* Icon size */
        .doc-btn i {
            font-size: 16px;
        }

        /* Active state */
        .list-group-item.active {
            background-color: #05402e;
            border-color: #05402e;
            color: #ffffff;
        }

        /* Force all child text white when active */
        .list-group-item.active *,
        .list-group-item.active small,
        .list-group-item.active .badge {
            color: #ffffff !important;
        }

        /* Hover state (slightly lighter than active) */
        .list-group-item:hover {
            background-color: #0a5a42;
            /* lighter shade of #05402e */
            color: #ffffff;
            transform: translateX(6px);
        }

        /* Hover child elements */
        .list-group-item:hover *,
        .list-group-item:hover small,
        .list-group-item:hover .badge {
            color: #ffffff !important;
        }

        /* Keep active stable on hover */
        .list-group-item.active:hover {
            background-color: #05402e;
            transform: translateX(6px);
        }


        /* Badge spacing */
        .badge {
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .gig-thumbnail {
                height: 150px;
            }
        }
    </style>
@endpush
