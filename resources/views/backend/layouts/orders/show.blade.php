@extends('backend.app')

@section('title', 'Order Details — #' . $order->order_number)

@section('content')
    @php
        $statusColors = [
            'pending_payment' => 'warning',
            'active' => 'primary',
            'qa_pending' => 'info',
            'qa_rejected' => 'danger',
            'delivered' => 'info',
            'revision_requested' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'disputed' => 'danger',
        ];
        $deliveryStatusColors = [
            'pending_qa' => 'warning',
            'qa_approved' => 'success',
            'qa_rejected' => 'danger',
            'delivered_to_client' => 'info',
            'accepted' => 'success',
            'revision_requested' => 'warning',
        ];
        $buyerName = $order->buyer?->profile
            ? trim($order->buyer->profile->first_name . ' ' . ($order->buyer->profile->last_name ?? ''))
            : $order->buyer?->email;
        $sellerName = $order->seller?->profile
            ? trim($order->seller->profile->first_name . ' ' . ($order->seller->profile->last_name ?? ''))
            : $order->seller?->email;
    @endphp

    <div class="app-content main-content mt-0">
        <div class="side-app pb-5" style="margin-bottom:40px;">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Order Details</h1>
                        <p class="text-muted mb-0">#{{ $order->order_number }}</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
                        </ol>
                    </div>
                </div>

                {{-- Order Header Card --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h4 class="mb-2">{{ $order->gig?->title ?? 'Custom Offer' }}</h4>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span class="badge uniform-badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>

                                    @if ($order->funds_in_escrow)
                                        <span class="badge uniform-badge bg-warning-transparent text-warning">
                                            <i class="fe fe-lock"></i>
                                            <span>Funds in Escrow</span>
                                        </span>
                                    @endif

                                    @if ($order->payment_method)
                                        <span class="badge uniform-badge bg-info-transparent text-info">
                                            <i class="fe fe-credit-card"></i>
                                            <span>{{ ucfirst($order->payment_method) }}</span>
                                        </span>
                                    @endif

                                    <span class="uniform-date">
                                        <i class="fe fe-calendar"></i>
                                        {{ $order->created_at->format('M d, Y · h:i A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <div class="fw-bold fs-20 text-success">${{ number_format($order->price, 2) }}</div>
                                <small class="text-muted">Platform Fee:
                                    ${{ number_format($order->platform_fee, 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    {{-- Buyer --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header pb-2 border-0">
                                <h6 class="card-title mb-0"><i class="fe fe-user text-primary me-2"></i>Buyer</h6>
                            </div>
                            <div class="card-body pt-2">
                                @php
                                    $bp = $order->buyer?->profile;

                                    $ba = $bp?->avatar
                                        ? asset('storage/' . $bp->avatar)
                                        : 'https://ui-avatars.com/api/?name=' .
                                            urlencode($buyerName) .
                                            '&size=64&background=0ea5e9&color=fff';
                                @endphp
                                <div class="text-center mb-3">
                                    <img src="{{ $ba }}" class="rounded-circle" width="64" height="64"
                                        style="object-fit:cover; border:3px solid #e9ecef;" alt="">
                                </div>
                                <div class="text-center">
                                    <div class="fw-semibold">{{ $buyerName }}</div>
                                    <small class="text-muted">{{ $order->buyer?->email }}</small>
                                    @if ($bp?->username)
                                        <div class="mt-1"><small class="text-muted">{{ $bp->username }}</small></div>
                                    @endif
                                    <a href="{{ route('admin.clients.show', $order->buyer_id) }}"
                                        class="btn btn-sm btn-outline-primary mt-2">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seller --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header pb-2 border-0">
                                <h6 class="card-title mb-0"><i class="fe fe-briefcase text-success me-2"></i>Seller</h6>
                            </div>
                            <div class="card-body pt-2">
                                @php
                                    $sp = $order->seller?->profile;
                                    $sa = $sp?->avatar
                                        ? asset('storage/' . $sp->avatar)
                                        : 'https://ui-avatars.com/api/?name=' .
                                            urlencode($sellerName) .
                                            '&size=64&background=6366f1&color=fff';
                                @endphp
                                <div class="text-center mb-3">
                                    <img src="{{ $sa }}" class="rounded-circle" width="64" height="64"
                                        style="object-fit:cover; border:3px solid #e9ecef;" alt="">
                                </div>
                                <div class="text-center">
                                    <div class="fw-semibold">{{ $sellerName }}</div>
                                    <small class="text-muted">{{ $order->seller?->email }}</small>
                                    @if ($sp?->username)
                                        <div class="mt-1"><small class="text-muted">{{ $sp->username }}</small></div>
                                    @endif
                                    <a href="{{ route('admin.experts.show', $order->seller_id) }}"
                                        class="btn btn-sm btn-outline-success mt-2">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pricing Breakdown --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header pb-2 border-0">
                                <h6 class="card-title mb-0"><i class="fe fe-dollar-sign text-warning me-2"></i>Pricing</h6>
                            </div>
                            <div class="card-body pt-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Order Price</td>
                                        <td class="text-end fw-bold">${{ number_format($order->price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Platform Fee</td>
                                        <td class="text-end fw-bold text-warning">
                                            ${{ number_format($order->platform_fee, 2) }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="text-muted fw-600">Seller Earnings</td>
                                        <td class="text-end fw-bold text-success">
                                            ${{ number_format($order->seller_earnings, 2) }}</td>
                                    </tr>
                                </table>
                                @if ($order->paid_at)
                                    <div class="alert alert-success-transparent mt-2 mb-0 small">
                                        <i class="fe fe-check-circle me-1"></i>Paid on
                                        {{ $order->paid_at->format('M d, Y') }}
                                    </div>
                                @else
                                    <div class="alert alert-warning-transparent mt-2 mb-0 small">
                                        <i class="fe fe-clock me-1"></i>Payment Pending
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Info --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header pb-2 border-0">
                                <h6 class="card-title mb-0"><i class="fe fe-clock text-info me-2"></i>Delivery</h6>
                            </div>
                            <div class="card-body pt-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Delivery Days</td>
                                        <td class="text-end fw-600">{{ $order->delivery_days }} days</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Revisions</td>
                                        <td class="text-end fw-600">
                                            {{ $order->revision_count }}/{{ $order->max_revisions }}</td>
                                    </tr>
                                    @if ($order->expected_delivery_at)
                                        <tr>
                                            <td class="text-muted">Expected</td>
                                            <td class="text-end">{{ $order->expected_delivery_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endif
                                    @if ($order->delivered_at)
                                        <tr class="border-top">
                                            <td class="text-muted">Delivered At</td>
                                            <td class="text-end text-success fw-600">
                                                {{ $order->delivered_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Timeline Chart --}}
                @if ($activityChart->isNotEmpty())
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-0">
                                    <h5 class="card-title mb-0"><i class="fe fe-activity me-2"></i>Order Activity Timeline
                                    </h5>
                                    <small class="text-muted">Visual representation of order progress</small>
                                </div>
                                <div class="card-body">
                                    <canvas id="activityChart" height="80"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Deliveries --}}
                @if ($order->deliveries->isNotEmpty())
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-0"><i class="fe fe-package me-2"></i>Deliveries</h5>
                                        <small class="text-muted">{{ $order->deliveries->count() }} total</small>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    @foreach ($order->deliveries as $delivery)
                                        <div class="delivery-row p-4 border-bottom">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1">Delivery #{{ $delivery->delivery_number }}</h6>
                                                    <small class="text-muted">Submitted:
                                                        {{ $delivery->submitted_at->format('M d, Y · h:i A') }}</small>
                                                </div>
                                                <span
                                                    class="badge bg-{{ $deliveryStatusColors[$delivery->status] ?? 'secondary' }} p-2">
                                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                                </span>
                                            </div>
                                            @if ($delivery->message)
                                                <div class="mb-2">
                                                    <strong>Message:</strong>
                                                    <p class="mb-0 text-muted">{{ $delivery->message }}</p>
                                                </div>
                                            @endif
                                            @if ($delivery->files)
                                                @php $files = is_string($delivery->files) ? json_decode($delivery->files, true) : $delivery->files; @endphp
                                                @if ($files && count($files) > 0)
                                                    <div>
                                                        <strong>Files:</strong>
                                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                                            @foreach ($files as $file)
                                                                <a href="{{ asset($file) }}" target="_blank"
                                                                    class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fe fe-file me-1"></i>{{ basename($file) }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            @if ($delivery->revision_reason)
                                                <div class="alert alert-warning-transparent mt-2 mb-0 small">
                                                    <strong>Revision Reason:</strong> {{ $delivery->revision_reason }}
                                                </div>
                                            @endif
                                            @if ($delivery->qa_feedback)
                                                <div class="alert alert-info-transparent mt-2 mb-0 small">
                                                    <strong>QA Feedback:</strong> {{ $delivery->qa_feedback }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Extension Requests --}}
                @if ($order->extensionRequests->isNotEmpty())
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-0">
                                    <h5 class="card-title mb-0"><i class="fe fe-calendar me-2"></i>Extension Requests</h5>
                                    <small class="text-muted">{{ $order->extensionRequests->count() }} request(s)</small>
                                </div>
                                <div class="card-body p-0">
                                    @foreach ($order->extensionRequests as $ext)
                                        <div class="p-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-semibold">+{{ $ext->additional_days }} days requested
                                                    </div>
                                                    <small class="text-muted">
                                                        By
                                                        {{ $ext->requestedBy?->profile ? trim($ext->requestedBy->profile->first_name . ' ' . ($ext->requestedBy->profile->last_name ?? '')) : $ext->requestedBy?->email }}
                                                        · {{ $ext->requested_at->diffForHumans() }}
                                                    </small>
                                                    <p class="mb-0 mt-1 text-muted small">{{ $ext->reason }}</p>
                                                </div>
                                                <span
                                                    class="badge bg-{{ $ext->status === 'approved' ? 'success' : ($ext->status === 'rejected' ? 'danger' : 'warning') }} p-2">
                                                    {{ ucfirst($ext->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Activities Feed --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0"><i class="fe fe-list me-2"></i>Activity Log</h5>
                                <small class="text-muted"
                                    style="margin-left: 15px; margin-top: 5px;">{{ $order->activities->count() }}
                                    activities</small>
                            </div>
                            <div class="card-body p-0">
                                @forelse($order->activities as $activity)
                                    <div class="activity-row d-flex gap-3 p-3 border-bottom">
                                        <div class="activity-dot-wrap flex-shrink-0">
                                            <div class="activity-dot"></div>
                                            @if (!$loop->last)
                                                <div class="activity-line"></div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                                            </div>
                                            @if ($activity->description)
                                                <p class="mb-1 text-muted small">{{ $activity->description }}</p>
                                            @endif
                                            <small
                                                class="text-muted">{{ $activity->created_at->format('M d, Y · h:i A') }}</small>
                                            @if ($activity->user)
                                                <small class="text-muted"> · by
                                                    {{ $activity->user->profile ? trim($activity->user->profile->first_name . ' ' . ($activity->user->profile->last_name ?? '')) : $activity->user->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">No activities logged yet</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Review (if exists) --}}
                @if ($order->review)
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-0">
                                    <h5 class="card-title mb-0"><i class="fe fe-star me-2"></i>Review</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-2 mb-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fe fe-star {{ $i <= $order->review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                        <span class="fw-600 ms-1">{{ $order->review->rating }}/5</span>
                                    </div>
                                    @if ($order->review->review)
                                        <p class="mb-0">{{ $order->review->review }}</p>
                                    @endif
                                    @if ($order->review->seller_reply)
                                        <div class="alert alert-info-transparent mt-3 mb-0">
                                            <strong>Seller Reply:</strong>
                                            <p class="mb-0 mt-1">{{ $order->review->seller_reply }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const activityData = @json($activityChart);
        if (activityData.length && document.getElementById('activityChart')) {
            new Chart(document.getElementById('activityChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: activityData.map(a => a.date),
                    datasets: [{
                        label: 'Activity Progress',
                        data: activityData.map((_, i) => i + 1),
                        borderColor: '#05402e',
                        backgroundColor: '#A8EDDA',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#05402e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: ctx => activityData[ctx[0].dataIndex].label,
                                label: ctx => activityData[ctx.dataIndex].date
                            }
                        }
                    },
                    scales: {
                        y: {
                            display: false
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        .delivery-row {
            transition: background .15s;
        }

        .delivery-row:hover {
            background: #f8fafc;
        }

        .activity-row {
            position: relative;
        }

        .activity-dot-wrap {
            position: relative;
            width: 20px;
        }

        .activity-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #05402e;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px rgba(0, 97, 29, 0.959);
        }

        .activity-line {
            position: absolute;
            top: 16px;
            left: 5px;
            width: 2px;
            height: calc(100% + 8px);
            background: #e9ecef;
        }

        .fw-600 {
            font-weight: 600;
        }

        .uniform-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;

            height: 30px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 500;

            border-radius: 6px;
            white-space: nowrap;
        }

        .uniform-badge i {
            font-size: 14px;
            /* icon size equal */
            line-height: 1;
        }

        .uniform-date {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            height: 38px;
            /* same height as badge */
            font-size: 13px;
            color: #6c757d;
        }
    </style>
@endpush
