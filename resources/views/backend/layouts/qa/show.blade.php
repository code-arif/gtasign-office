@extends('backend.app')

@section('title', 'QA Review — Order #' . $order->order_number)

@section('content')
    @php
        $sellerProfile = $order->seller?->profile;
        $buyerProfile = $order->buyer?->profile;

        $sellerName = $sellerProfile
            ? trim($sellerProfile->first_name . ' ' . ($sellerProfile->last_name ?? ''))
            : $order->seller?->email ?? '—';
        $buyerName = $buyerProfile
            ? trim($buyerProfile->first_name . ' ' . ($buyerProfile->last_name ?? ''))
            : $order->buyer?->email ?? '—';

        $sellerAvatar = $sellerProfile?->avatar
            ? asset('storage/' . $sellerProfile->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($sellerName) . '&background=6366f1&color=fff&size=64';

        $buyerAvatar = $buyerProfile?->avatar
            ? asset('storage/' . $buyerProfile->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($buyerName) . '&background=0ea5e9&color=fff&size=64';

        $reviewStatusColors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
        $reviewStatusColor = $reviewStatusColors[$review->status] ?? 'secondary';

        $orderStatusColors = [
            'pending_payment' => 'warning',
            'active' => 'primary',
            'qa_pending' => 'info',
            'qa_rejected' => 'danger',
            'delivered' => 'info',
            'revision_requested' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
    @endphp

    <div class="app-content main-content mt-0">
        <div class="side-app pb-5" style="margin-bottom: 40px">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">QA Review Detail</h1>
                        <p class="text-muted mb-0">Order #{{ $order->order_number }}</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item"><a href="{{ route('admin.qa.index') }}">QA Reviews</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
                        </ol>
                    </div>
                </div>

                <!-- ── STATUS BANNER ── -->
                @if ($review->status === 'pending')
                    <div class="alert alert-info d-flex align-items-center gap-3 mb-4 shadow-sm">
                        <i class="fe fe-clock fs-24 flex-shrink-0"></i>
                        <div class="flex-grow-1">
                            <strong>Awaiting Your Review</strong> — This delivery has been submitted by the expert and is
                            waiting for QA approval before it can be sent to the client.
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <button class="btn btn-success btn-sm d-inline-flex align-items-center gap-1"
                                onclick="openApproveModal()">
                                <i class="fe fe-check-circle"></i> Approve
                            </button>
                            <button class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
                                onclick="openRejectModal()">
                                <i class="fe fe-x-circle"></i> Reject
                            </button>
                        </div>
                    </div>
                @elseif($review->status === 'approved')
                    <div class="alert alert-success d-flex align-items-center gap-3 mb-4">
                        <i class="fe fe-check-circle fs-24"></i>
                        <div>
                            <strong>Delivery Approved</strong> — This delivery was approved and sent to the client
                            on
                            {{ $review->reviewed_at ? \Carbon\Carbon::parse($review->reviewed_at)->format('M d, Y h:i A') : '—' }}.
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger d-flex align-items-center gap-3 mb-4">
                        <i class="fe fe-x-circle fs-24"></i>
                        <div>
                            <strong>Delivery Rejected</strong> — This delivery was rejected and returned to the expert
                            on
                            {{ $review->reviewed_at ? \Carbon\Carbon::parse($review->reviewed_at)->format('M d, Y h:i A') : '—' }}.
                            @if ($review->feedback)
                                <div class="mt-1 small">Reason: {{ $review->feedback }}</div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="row g-4">

                    <!-- ════ LEFT COLUMN ════ -->
                    <div class="col-xl-8">

                        <!-- DELIVERY FILES -->
                        <div class="card mb-4">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-package me-2 text-primary"></i>
                                    Delivery #{{ $delivery?->delivery_number ?? 1 }}
                                    <span
                                        class="badge bg-{{ $reviewStatusColor }} ms-2">{{ ucfirst($review->status) }}</span>
                                </h5>
                                <small class="text-muted">
                                    Submitted
                                    {{ $delivery?->submitted_at ? \Carbon\Carbon::parse($delivery->submitted_at)->diffForHumans() : '—' }}
                                </small>
                            </div>
                            <div class="card-body">

                                <!-- Expert message -->
                                @if ($delivery?->message)
                                    <div class="delivery-message p-3 rounded-1 mb-4">
                                        <label class="form-label fw-600 text-muted text-uppercase small mb-2">
                                            <i class="fe fe-message-circle me-1"></i>Expert's Message
                                        </label>
                                        <p class="mb-0">{{ $delivery->message }}</p>
                                    </div>
                                @endif

                                <!-- Files -->
                                @php $files = $delivery?->files ?? []; @endphp
                                @if (count($files) > 0)
                                    <label class="form-label fw-600 text-muted text-uppercase small mb-3">
                                        <i class="fe fe-paperclip me-1"></i>Attached Files ({{ count($files) }})
                                    </label>
                                    <div class="row g-3">
                                        @foreach ($files as $file)
                                            @php
                                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                                $filename = basename($file);
                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                                $isPdf = $ext === 'pdf';
                                                $isVideo = in_array($ext, ['mp4', 'mov', 'avi', 'webm']);
                                                $icon = match (true) {
                                                    $isPdf => 'fe-file-text',
                                                    $isVideo => 'fe-video',
                                                    in_array($ext, ['zip', 'rar', '7z']) => 'fe-archive',
                                                    in_array($ext, ['doc', 'docx']) => 'fe-file',
                                                    in_array($ext, ['xls', 'xlsx']) => 'fe-grid',
                                                    in_array($ext, ['ppt', 'pptx']) => 'fe-monitor',
                                                    default => 'fe-file',
                                                };
                                            @endphp

                                            <div class="col-md-6 col-lg-4">
                                                <div class="file-card">
                                                    @if ($isImage)
                                                        <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $file) }}"
                                                                class="file-preview-img" alt="{{ $filename }}">
                                                        </a>
                                                    @elseif($isVideo)
                                                        <video controls class="file-preview-img"
                                                            style="object-fit:contain;background:#000;">
                                                            <source src="{{ asset('storage/' . $file) }}"
                                                                type="video/{{ $ext }}">
                                                        </video>
                                                    @else
                                                        <div class="file-icon-wrap">
                                                            <i class="fe {{ $icon }} fs-32 text-primary"></i>
                                                        </div>
                                                    @endif
                                                    <div class="file-meta">
                                                        <div class="fw-semibold small text-truncate"
                                                            title="{{ $filename }}">
                                                            {{ $filename }}
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                                            <span
                                                                class="badge bg-secondary-transparent text-secondary small">
                                                                {{ strtoupper($ext) }}
                                                            </span>
                                                            <a href="{{ asset('storage/' . $file) }}"
                                                                download="{{ $filename }}"
                                                                class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1">
                                                                <i class="fe fe-download" style="font-size:11px;"></i>
                                                                Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fe fe-inbox fs-32 d-block mb-2"></i>
                                        No files attached to this delivery.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- DELIVERY HISTORY -->
                        @if ($allDeliveries->count() > 1)
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-clock me-2 text-muted"></i>Delivery History
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    @foreach ($allDeliveries as $d)
                                        @php
                                            $dStatusColors = [
                                                'pending_qa' => 'warning',
                                                'qa_approved' => 'info',
                                                'qa_rejected' => 'danger',
                                                'delivered_to_client' => 'primary',
                                                'accepted' => 'success',
                                                'revision_requested' => 'warning',
                                            ];
                                            $dColor = $dStatusColors[$d->status] ?? 'secondary';
                                            $isCurrent = $d->id === $delivery?->id;
                                        @endphp
                                        <div
                                            class="d-flex align-items-center gap-3 p-3 border-bottom
                                            {{ $isCurrent ? 'bg-primary-transparent' : '' }}">
                                            <div class="flex-shrink-0">
                                                <span class="badge bg-secondary rounded-circle"
                                                    style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                                                    {{ $d->delivery_number }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">
                                                    Delivery #{{ $d->delivery_number }}
                                                    @if ($isCurrent)
                                                        <span class="badge bg-primary ms-1 small">Current</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">
                                                    {{ count($d->files ?? []) }} file(s) ·
                                                    {{ \Carbon\Carbon::parse($d->submitted_at)->format('M d, Y') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-{{ $dColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $d->status)) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- ORDER ACTIVITY LOG -->
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-activity me-2 text-muted"></i>Order Activity Log
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="timeline-wrap">
                                    @forelse($order->activities->sortByDesc('created_at') as $activity)
                                        @php
                                            $actIcons = [
                                                'order_placed' => ['fe-shopping-bag', 'primary'],
                                                'payment_received' => ['fe-credit-card', 'success'],
                                                'delivery_submitted' => ['fe-upload-cloud', 'info'],
                                                'qa_approved' => ['fe-check-circle', 'success'],
                                                'qa_rejected' => ['fe-x-circle', 'danger'],
                                                'delivered_to_client' => ['fe-send', 'primary'],
                                                'revision_requested' => ['fe-rotate-ccw', 'warning'],
                                                'extension_requested' => ['fe-clock', 'warning'],
                                                'extension_approved' => ['fe-calendar', 'success'],
                                                'order_completed' => ['fe-award', 'success'],
                                                'order_cancelled' => ['fe-slash', 'danger'],
                                                'escrow_released' => ['fe-dollar-sign', 'success'],
                                                'auto_completed' => ['fe-zap', 'info'],
                                            ];
                                            [$actIcon, $actColor] = $actIcons[$activity->type] ?? [
                                                'fe-circle',
                                                'secondary',
                                            ];
                                        @endphp
                                        <div class="timeline-item d-flex gap-3 p-3 border-bottom">
                                            <div
                                                class="timeline-dot bg-{{ $actColor }}-transparent text-{{ $actColor }} flex-shrink-0">
                                                <i class="fe {{ $actIcon }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">
                                                    {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                                                </div>
                                                @if ($activity->description)
                                                    <small class="text-muted">{{ $activity->description }}</small>
                                                @endif
                                            </div>
                                            <small class="text-muted flex-shrink-0">
                                                {{ $activity->created_at->format('M d, h:i A') }}
                                            </small>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-4">No activity yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>{{-- /left col --}}

                    <!-- ════ RIGHT COLUMN ════ -->
                    <div class="col-xl-4">

                        <!-- ORDER INFO CARD -->
                        <div class="card mb-4">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-shopping-bag me-2 text-primary"></i>Order Info
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted fw-600 small">Order #</td>
                                            <td class="fw-semibold">{{ $order->order_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Gig</td>
                                            <td class="small">{{ \Str::limit($order->gig?->title ?? 'Custom', 30) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Price</td>
                                            <td class="fw-bold text-success">${{ number_format($order->price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Platform Fee</td>
                                            <td class="small">${{ number_format($order->platform_fee, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Expert Earns</td>
                                            <td class="fw-semibold text-primary">
                                                ${{ number_format($order->seller_earnings, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Delivery Days</td>
                                            <td class="small">{{ $order->delivery_days }} day(s)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Due Date</td>
                                            <td
                                                class="small {{ $order->expected_delivery_at && $order->expected_delivery_at->isPast() ? 'text-danger fw-semibold' : '' }}">
                                                {{ $order->expected_delivery_at?->format('M d, Y') ?? '—' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Status</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $orderStatusColors[$order->status] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Revisions</td>
                                            <td class="small">{{ $order->revision_count }}/{{ $order->max_revisions }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600 small">Started</td>
                                            <td class="small">{{ $order->started_at?->format('M d, Y') ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- EXPERT CARD -->
                        <div class="card mb-4">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0"><i class="fe fe-star me-2 text-warning"></i>Expert</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="{{ $sellerAvatar }}" class="rounded-circle" width="52" height="52"
                                        style="object-fit:cover; border:3px solid #e9ecef;">
                                    <div>
                                        <div class="fw-semibold">{{ $sellerName }}</div>
                                        <small class="text-muted">{{ $sellerProfile?-> username ?? '—' }}</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.experts.show', $order->seller_id) }}"
                                    class="btn btn-outline-primary btn-sm w-100 d-inline-flex align-items-center">
                                    <i class="fe fe-external-link me-1"></i>View Expert Profile
                                </a>
                            </div>
                        </div>

                        <!-- CLIENT CARD -->
                        <div class="card mb-4">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0"><i class="fe fe-user me-2 text-info"></i>Client</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $buyerAvatar }}" class="rounded-circle" width="52" height="52"
                                        style="object-fit:cover; border:3px solid #e9ecef;">
                                    <div>
                                        <div class="fw-semibold">{{ $buyerName }}</div>
                                        <small class="text-muted">{{ $buyerProfile?-> username ?? '—' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PREVIOUS REVIEWS -->
                        @if ($previousReviews->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-archive me-2 text-muted"></i>Previous QA Reviews
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    @foreach ($previousReviews as $pr)
                                        <div class="p-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span
                                                    class="badge bg-{{ $reviewStatusColors[$pr->status] ?? 'secondary' }}">
                                                    {{ ucfirst($pr->status) }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ $pr->reviewed_at ? \Carbon\Carbon::parse($pr->reviewed_at)->format('M d') : '—' }}
                                                </small>
                                            </div>
                                            @if ($pr->feedback)
                                                <p class="mb-0 mt-2 small text-muted">{{ \Str::limit($pr->feedback, 80) }}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- QA ACTIONS (sticky for pending) -->
                        @if ($review->status === 'pending')
                            <div class="card border-primary sticky-top" style="top:20px; z-index:100;">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0 text-white">
                                        <i class="fe fe-shield me-2"></i>QA Decision
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        Review the delivery files carefully. Once approved, the client will be notified.
                                    </p>
                                    <div class="d-grid gap-2">
                                        <button
                                            class="btn btn-success d-flex align-items-center justify-content-center gap-2"
                                            onclick="openApproveModal()">
                                            <i class="fe fe-check-circle"></i>
                                            <span>Approve & Send to Client</span>
                                        </button>
                                        <button
                                            class="btn btn-outline-danger d-flex align-items-center justify-content-center gap-2"
                                            onclick="openRejectModal()">
                                            <i class="fe fe-x-circle"></i>
                                            <span>Reject & Return to Expert</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>{{-- /right col --}}

                </div>{{-- /row --}}

            </div>{{-- /container --}}
        </div>
    </div>

    <!-- APPROVE MODAL -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fe fe-check-circle me-2"></i>Approve Delivery</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm">
                    <div class="modal-body">
                        <div class="alert alert-success d-flex gap-2 align-items-start">
                            <i class="fe fe-info flex-shrink-0 mt-1"></i>
                            <span>The delivery will be approved and immediately sent to the client.
                                The client will have {{ config('orders.auto_accept_days', 3) }} days to review
                                before auto-completion.</span>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-600">Feedback / Notes <small
                                    class="text-muted">(optional)</small></label>
                            <textarea class="form-control" id="approveFeedback" rows="3"
                                placeholder="Optional notes about this delivery..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="approveSubmitBtn">
                            <span class="btn-text"><i class="fe fe-check me-1"></i>Approve & Send to Client</span>
                            <span class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- REJECT MODAL -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fe fe-x-circle me-2"></i>Reject Delivery</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm">
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex gap-2 align-items-start">
                            <i class="fe fe-alert-triangle flex-shrink-0 mt-1"></i>
                            <span>The expert will be notified with your feedback and the delivery will be
                                returned for revision. Please be specific and actionable.</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600">Feedback <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectFeedback" rows="5"
                                placeholder="What needs to be fixed? Be specific and actionable..."></textarea>
                            <div class="invalid-feedback">Feedback is required</div>
                        </div>

                        <div>
                            <label class="form-label fw-600">Issue Tags <small
                                    class="text-muted">(optional)</small></label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach (['Quality Issue', 'Incomplete Work', 'Wrong Format', 'Copyright Issue', 'Missing Files', 'Does Not Match Requirements', 'Technical Error', 'Plagiarism'] as $tag)
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
    <script>
        const REVIEW_ID = {{ $review->id }};

        function openApproveModal() {
            $('#approveModal').modal('show');
        }

        function openRejectModal() {
            $('#rejectModal').modal('show');
        }

        /* ── Approve ── */
        $('#approveForm').on('submit', function(e) {
            e.preventDefault();
            const feedback = $('#approveFeedback').val().trim();

            setLoading('#approveSubmitBtn', true);
            NProgress.start();

            $.ajax({
                url: `{{ route('admin.qa.approve', $review->id) }}`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    feedback
                },
                success: function(res) {
                    NProgress.done();
                    setLoading('#approveSubmitBtn', false);
                    if (res.success) {
                        toastr.success(res.message);
                        $('#approveModal').modal('hide');
                        setTimeout(() => location.reload(), 900);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    setLoading('#approveSubmitBtn', false);
                    toastr.error(xhr.responseJSON?.message || 'Failed to approve');
                }
            });
        });

        /* ── Reject ── */
        $('#rejectForm').on('submit', function(e) {
            e.preventDefault();
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

            setLoading('#rejectSubmitBtn', true);
            NProgress.start();

            $.ajax({
                url: `{{ route('admin.qa.reject', $review->id) }}`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    feedback,
                    issues
                },
                success: function(res) {
                    NProgress.done();
                    setLoading('#rejectSubmitBtn', false);
                    if (res.success) {
                        toastr.success(res.message);
                        $('#rejectModal').modal('hide');
                        setTimeout(() => location.reload(), 900);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    setLoading('#rejectSubmitBtn', false);
                    toastr.error(xhr.responseJSON?.message || 'Failed to reject');
                }
            });
        });

        function setLoading(btnSel, loading) {
            const btn = $(btnSel);
            btn.prop('disabled', loading);
            btn.find('.btn-text').toggleClass('d-none', loading);
            btn.find('.spinner-border').toggleClass('d-none', !loading);
        }
    </script>
@endpush

@push('styles')
    <style>
        /* ── File Cards ── */
        .file-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            transition: box-shadow .2s;
        }

        .file-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }

        .file-preview-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }

        .file-icon-wrap {
            width: 100%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .file-meta {
            padding: 10px 12px;
        }

        /* ── Delivery Message ── */
        .delivery-message {
            background: #f0f4ff;
            border-left: 4px solid #6366f1;
        }

        /* ── Timeline ── */
        .timeline-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        /* ── Issue Tags ── */
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

        /* ── Misc ── */
        .fw-600 {
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-xs {
            padding: 2px 8px;
            font-size: 12px;
        }

        .fs-32 {
            font-size: 32px;
        }
    </style>
@endpush
