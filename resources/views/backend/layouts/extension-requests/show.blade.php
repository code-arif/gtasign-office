@extends('backend.app')

@section('title', 'Extension Request Details')

@section('content')
    @php
        $requestedBy = $extension->requestedBy?->profile
            ? trim(
                $extension->requestedBy->profile->first_name .
                    ' ' .
                    ($extension->requestedBy->profile->last_name ?? ''),
            )
            : $extension->requestedBy?->email ?? 'N/A';
        $buyerName = $extension->order->buyer?->profile
            ? trim(
                $extension->order->buyer->profile->first_name .
                    ' ' .
                    ($extension->order->buyer->profile->last_name ?? ''),
            )
            : $extension->order->buyer?->email ?? 'N/A';
        $sellerName = $extension->order->seller?->profile
            ? trim(
                $extension->order->seller->profile->first_name .
                    ' ' .
                    ($extension->order->seller->profile->last_name ?? ''),
            )
            : $extension->order->seller?->email ?? 'N/A';
    @endphp

    <div class="app-content main-content mt-0">
        <div class="side-app pb-5">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Extension Request Details</h1>
                        <p class="text-muted mb-0">Order #{{ $extension->order->order_number }}</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item"><a href="{{ route('admin.extension-requests.index') }}">Extension
                                    Requests</a></li>
                            <span>&nbsp; &gt;&gt; &nbsp;</span>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </div>
                </div>

                <!-- Header Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h4 class="mb-2">+{{ $extension->additional_days }} Days Extension Request</h4>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span
                                        class="badge bg-{{ $extension->status === 'approved' ? 'success' : ($extension->status === 'rejected' ? 'danger' : 'warning') }} p-3">
                                        {{ ucfirst($extension->status) }}
                                    </span>
                                    <span class="text-muted small">
                                        <i class="fe fe-calendar me-1"></i>Requested
                                        {{ $extension->requested_at->diffForHumans() }}
                                    </span>
                                    @if ($extension->responded_at)
                                        <span class="text-muted small">
                                            <i class="fe fe-check me-1"></i>Responded
                                            {{ $extension->responded_at->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                @if ($extension->status === 'pending')
                                    <button class="btn btn-success d-inline-flex align-items-center" onclick="approveExtension()">
                                        <i class="fe fe-check me-1"></i> Approve
                                    </button>
                                    <button class="btn btn-danger d-inline-flex align-items-center" onclick="rejectExtension()">
                                        <i class="fe fe-x me-1"></i> Reject
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Request Details -->
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0"><i class="fe fe-info text-primary me-2"></i>Request Details</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted fw-600" width="50%">Requested By</td>
                                        <td>{{ $requestedBy }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-600">Additional Days</td>
                                        <td class="fw-bold text-warning">+{{ $extension->additional_days }} days</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-600">Requested At</td>
                                        <td>{{ $extension->requested_at->format('M d, Y · h:i A') }}</td>
                                    </tr>
                                    @if ($extension->responded_at)
                                        <tr>
                                            <td class="text-muted fw-600">Responded At</td>
                                            <td>{{ $extension->responded_at->format('M d, Y · h:i A') }}</td>
                                        </tr>
                                    @endif
                                </table>

                                <div class="mt-3">
                                    <strong>Reason:</strong>
                                    <p class="mb-0 text-muted mt-1">{{ $extension->reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header border-0">
                                <h5 class="card-title mb-0"><i class="fe fe-shopping-bag text-info me-2"></i>Order
                                    Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted fw-600" width="50%">Order Number</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $extension->order_id) }}"
                                                class="fw-600">
                                                #{{ $extension->order->order_number }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-600">Gig</td>
                                        <td>{{ $extension->order->gig?->title ?? 'Custom Offer' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-600">Buyer</td>
                                        <td>{{ $buyerName }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-600">Seller</td>
                                        <td>{{ $sellerName }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-600">Order Status</td>
                                        <td>
                                            <span
                                                class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $extension->order->status)) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-600">Current Delivery Days</td>
                                        <td class="fw-600">{{ $extension->order->delivery_days }} days</td>
                                    </tr>
                                    @if ($extension->order->expected_delivery_at)
                                        <tr>
                                            <td class="text-muted fw-600">Expected Delivery</td>
                                            <td>{{ $extension->order->expected_delivery_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endif
                                </table>

                                @if ($extension->status === 'approved')
                                    <div class="alert alert-success-transparent mt-3 mb-0 small">
                                        <i class="fe fe-check-circle me-1"></i>
                                        Extension approved — delivery timeline extended
                                    </div>
                                @elseif($extension->status === 'rejected')
                                    <div class="alert alert-danger-transparent mt-3 mb-0 small">
                                        <i class="fe fe-x-circle me-1"></i>
                                        Extension request was rejected
                                    </div>
                                @endif
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
        function approveExtension() {
            if (!confirm(
                    'Approve this extension request? This will add {{ $extension->additional_days }} days to the order delivery time.'
                )) return;

            NProgress.start();
            $.ajax({
                url: '{{ route('admin.extension-requests.approve', $extension->id) }}',
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: res => {
                    NProgress.done();
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                },
                error: xhr => {
                    NProgress.done();
                    toastr.error(xhr.responseJSON?.message || 'Failed to approve');
                }
            });
        }

        function rejectExtension() {
            if (!confirm('Reject this extension request?')) return;

            NProgress.start();
            $.ajax({
                url: '{{ route('admin.extension-requests.reject', $extension->id) }}',
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: res => {
                    NProgress.done();
                    toastr.warning(res.message);
                    setTimeout(() => location.reload(), 800);
                },
                error: xhr => {
                    NProgress.done();
                    toastr.error(xhr.responseJSON?.message || 'Failed to reject');
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        .fw-600 {
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
@endpush
