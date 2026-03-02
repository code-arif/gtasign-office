@extends('backend.app')

@section('title', 'Client Profile — ' . ($client->profile->first_name ?? $client->email))

@section('content')
    @php
        $profile = $client->profile;
        $fullName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : $client->email;
        $avatar =
            $profile && $profile->avatar
                ? asset($profile->avatar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=0ea5e9&color=fff&size=128';

        $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'danger'];
        $statusColor = $statusColors[$client->status] ?? 'secondary';
        $stars = round($stats['avg_rating_given']);

        $orderStatusColors = [
            'pending_payment' => ['label' => 'Pending Payment', 'color' => 'warning'],
            'active' => ['label' => 'Active', 'color' => 'primary'],
            'qa_pending' => ['label' => 'QA Pending', 'color' => 'info'],
            'qa_rejected' => ['label' => 'QA Rejected', 'color' => 'danger'],
            'delivered' => ['label' => 'Delivered', 'color' => 'info'],
            'revision_requested' => ['label' => 'Revision Requested', 'color' => 'warning'],
            'completed' => ['label' => 'Completed', 'color' => 'success'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'danger'],
            'disputed' => ['label' => 'Disputed', 'color' => 'danger'],
        ];

        $customOfferStatusColors = [
            'pending' => 'warning',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'secondary',
            'withdrawn' => 'secondary',
            'converted_to_order' => 'primary',
        ];
    @endphp

    <div class="app-content main-content mt-0">
        <div class="side-app pb-5" style="margin-bottom: 40px">
            <div class="main-container container-fluid">

                <!-- BREADCRUMB -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Client Profile</h1>
                        <p class="text-muted mb-0">Full order history & activity dashboard</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <span>&nbsp; &gt&gt &nbsp;</span>
                            <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">Clients</a></li>
                            <span>&nbsp; &gt&gt &nbsp;</span>
                            <li class="breadcrumb-item active">{{ $fullName }}</li>
                        </ol>
                    </div>
                </div>

                <!-- ===================== PROFILE HERO ===================== -->
                <div class="card profile-hero mb-4">
                    <div class="profile-hero-banner"></div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column flex-md-row align-items-md-end gap-4" style="margin-top:-44px;">
                            <div class="position-relative flex-shrink-0">
                                <img src="{{ $avatar }}" alt="{{ $fullName }}" class="profile-avatar"
                                    width="88" height="88">
                                @if ($client->email_verified_at)
                                    <span class="position-absolute bottom-0 end-0 badge bg-success rounded-circle p-1"
                                        title="Email Verified"
                                        style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fe fe-check" style="font-size:11px;"></i>
                                    </span>
                                @endif
                            </div>

                            <div class="flex-grow-1 pb-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="mb-0 fw-700">{{ $fullName }}</h3>
                                    @if ($client->deleted_at)
                                        <span class="badge bg-danger">Deleted</span>
                                    @endif
                                    <span class="badge bg-{{ $statusColor }}">{{ ucfirst($client->status) }}</span>
                                </div>
                                <div class="d-flex flex-wrap gap-3 text-muted small">
                                    <span><i class="fe fe-mail me-1"></i>{{ $client->email }}</span>
                                    @if ($client->phone)
                                        <span><i class="fe fe-phone me-1"></i>{{ $client->phone }}</span>
                                    @endif
                                    @if ($profile?->username)
                                        <span><i class="fe fe-at-sign me-1"></i>{{ $profile->username }}</span>
                                    @endif
                                    <span><i class="fe fe-calendar me-1"></i>Joined
                                        {{ $client->created_at->format('M d, Y') }}</span>
                                </div>

                                <!-- Avg rating client gave to experts -->
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="stars-wrap">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fe fe-star {{ $i <= $stars ? 'star-filled' : 'star-empty' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="fw-600">{{ number_format($stats['avg_rating_given'], 1) }}</span>
                                    <span class="text-muted small">avg rating given ({{ $stats['total_reviews'] }}
                                        reviews)</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 flex-shrink-0 pb-1">
                                @if (!$client->deleted_at)
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-outline-secondary btn-sm dropdown-toggle d-inline-flex align-items-center"
                                            data-bs-toggle="dropdown">
                                            <i class="fe fe-settings me-1"></i> Change Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="quickStatus('active')">
                                                    <i class="fe fe-check-circle text-success me-2"></i>Set Active</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="quickStatus('inactive')">
                                                    <i class="fe fe-pause-circle text-secondary me-2"></i>Set Inactive</a>
                                            </li>
                                            <li><a class="dropdown-item" href="#" onclick="quickStatus('suspended')">
                                                    <i class="fe fe-slash text-danger me-2"></i>Suspend</a></li>
                                        </ul>
                                    </div>
                                @endif
                                <a href="{{ route('admin.clients.index') }}"
                                    class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                                    <i class="fe fe-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== KPI CARDS ===================== -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #0ea5e9;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(14,165,233,.12); color:#0ea5e9;">
                                    <i class="fe fe-shopping-bag"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ $stats['total_orders'] }}</h4>
                                <small class="text-muted">Total Orders</small>
                                <div class="mt-1">
                                    <span class="badge bg-primary-transparent text-primary small">
                                        {{ $stats['active_orders'] }} active
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #22c55e;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(34,197,94,.12); color:#22c55e;">
                                    <i class="fe fe-check-square"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ $stats['completed_orders'] }}</h4>
                                <small class="text-muted">Completed</small>
                                <div class="mt-1">
                                    @php $rate = $stats['total_orders'] > 0 ? round(($stats['completed_orders']/$stats['total_orders'])*100) : 0; @endphp
                                    <span class="badge bg-success-transparent text-success small">{{ $rate }}%
                                        rate</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #f59e0b;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(245,158,11,.12); color:#f59e0b;">
                                    <i class="fe fe-x-square"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ $stats['cancelled_orders'] }}</h4>
                                <small class="text-muted">Cancelled</small>
                                <div class="mt-1">
                                    @php $cr = $stats['total_orders'] > 0 ? round(($stats['cancelled_orders']/$stats['total_orders'])*100) : 0; @endphp
                                    <span class="badge bg-warning-transparent text-warning small">{{ $cr }}%
                                        rate</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #f43f5e;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(244,63,94,.12); color:#f43f5e;">
                                    <i class="fe fe-alert-triangle"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ $stats['disputed_orders'] }}</h4>
                                <small class="text-muted">Disputed</small>
                                <div class="mt-1">
                                    <span class="badge bg-danger-transparent text-danger small">needs attention</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #16a34a;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(22,163,74,.12); color:#16a34a;">
                                    <i class="fe fe-dollar-sign"></i>
                                </div>
                                <h4 class="mb-0 fw-700">${{ number_format($stats['total_spent'], 0) }}</h4>
                                <small class="text-muted">Total Spent</small>
                                <div class="mt-1">
                                    <span class="badge bg-warning-transparent text-warning small">
                                        ${{ number_format($stats['pending_payment'], 0) }} pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #8b5cf6;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(139,92,246,.12); color:#8b5cf6;">
                                    <i class="fe fe-file-text"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ $stats['custom_offers'] }}</h4>
                                <small class="text-muted">Custom Offers</small>
                                <div class="mt-1">
                                    <span class="badge bg-secondary-transparent text-secondary small">requested</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== CHARTS ROW ===================== -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-8">
                        <div class="card h-100">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Orders & Spending — Last 6 Months</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="ordersSpendingChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card h-100">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Order Status Breakdown</h5>
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                @if ($statusBreakdown->isEmpty())
                                    <div class="text-center text-muted py-4">
                                        <i class="fe fe-pie-chart fs-32 mb-2 d-block"></i>
                                        No order data yet
                                    </div>
                                @else
                                    <canvas id="statusDoughnut" style="max-height:220px;"></canvas>
                                    <div id="doughnutLegend" class="mt-3 w-100"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== ORDERS + CUSTOM OFFERS ===================== -->
                <div class="row g-3 mb-4">
                    <!-- Recent Orders -->
                    <div class="col-xl-7">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Orders</h5>
                                <span class="badge bg-primary-transparent text-primary">{{ $stats['total_orders'] }}
                                    total</span>
                            </div>
                            <div class="card-body p-0">
                                @forelse($recentOrders as $order)
                                    @php
                                        $oc = $orderStatusColors[$order->status] ?? [
                                            'label' => ucfirst($order->status),
                                            'color' => 'secondary',
                                        ];
                                    @endphp
                                    <div class="order-row d-flex align-items-center gap-3 p-3 border-bottom">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold">#{{ $order->order_number }}</span>
                                                <span
                                                    class="badge bg-{{ $oc['color'] }}-transparent text-{{ $oc['color'] }} small">
                                                    {{ $oc['label'] }}
                                                </span>
                                            </div>
                                            <small class="text-muted text-truncate d-block"
                                                title="{{ $order->gig->title ?? '' }}">
                                                <i
                                                    class="fe fe-briefcase me-1"></i>{{ Str::limit($order->gig->title ?? 'N/A', 40) }}
                                            </small>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="fw-bold">${{ number_format($order->price, 2) }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-5">
                                        <i class="fe fe-shopping-bag fs-32 mb-2 d-block"></i>
                                        No orders found
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Custom Offers -->
                    <div class="col-xl-5">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Custom Offers</h5>
                                <span class="badge bg-secondary-transparent text-secondary">{{ $stats['custom_offers'] }}
                                    total</span>
                            </div>
                            <div class="card-body p-0">
                                @forelse($customOffers as $offer)
                                    @php $oc2 = $customOfferStatusColors[$offer->status] ?? 'secondary'; @endphp
                                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold text-truncate">{{ $offer->title ?? 'Custom Offer' }}
                                            </div>
                                            <small class="text-muted">
                                                <i class="fe fe-user me-1"></i>{{ $offer->expert_email }}
                                            </small>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="fw-bold text-success">${{ number_format($offer->price, 2) }}</div>
                                            <span
                                                class="badge bg-{{ $oc2 }} small">{{ ucfirst(str_replace('_', ' ', $offer->status)) }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-5">
                                        <i class="fe fe-file-text fs-32 mb-2 d-block"></i>
                                        No custom offers found
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== REVIEWS GIVEN ===================== -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Reviews Given by Client</h5>
                                <span class="badge bg-warning-transparent text-warning">{{ $stats['total_reviews'] }}
                                    reviews</span>
                            </div>
                            <div class="card-body">
                                @forelse($recentReviews as $review)
                                    <div class="review-card p-3 rounded-1 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <div class="fw-semibold">{{ $review->gig_title }}</div>
                                                <small class="text-muted">
                                                    <i class="fe fe-user me-1"></i>Expert: {{ $review->seller_email }}
                                                </small>
                                                <div class="stars-wrap mt-1">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i class="fe fe-star {{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}"
                                                            style="font-size:13px;"></i>
                                                    @endfor
                                                    <span class="ms-1 fw-600 small">{{ $review->rating }}/5</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($review->created_at)->format('M d, Y') }}
                                            </small>
                                        </div>
                                        @if ($review->review)
                                            <p class="mb-0 text-muted small">{{ $review->review }}</p>
                                        @endif
                                        @if ($review->seller_reply)
                                            <div class="seller-reply mt-2 p-2 rounded-1">
                                                <small class="fw-600 text-primary">
                                                    <i class="fe fe-corner-down-right me-1"></i>Expert Reply:
                                                </small>
                                                <p class="mb-0 small text-muted mt-1">{{ $review->seller_reply }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="fe fe-star fs-32 mb-2 d-block"></i>
                                        No reviews given yet
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== PROFILE INFO ===================== -->
                <div class="row g-3">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0"><i class="fe fe-user me-2"></i>Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted fw-600" width="40%">Full Name</td>
                                            <td>{{ $fullName }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Username</td>
                                            <td>{{ $profile->username ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Email</td>
                                            <td>
                                                {{ $client->email }}
                                                @if ($client->email_verified_at)
                                                    <i class="fe fe-check-circle text-success ms-1" title="Verified"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Phone</td>
                                            <td>{{ $client->phone ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Account Status</td>
                                            <td><span
                                                    class="badge bg-{{ $statusColor }}">{{ ucfirst($client->status) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Joined</td>
                                            <td>{{ $client->created_at->format('M d, Y · h:i A') }}</td>
                                        </tr>
                                        @if ($client->deleted_at)
                                            <tr>
                                                <td class="text-muted fw-600">Deleted At</td>
                                                <td class="text-danger">
                                                    {{ $client->deleted_at->format('M d, Y · h:i A') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0"><i class="fe fe-bar-chart-2 me-2"></i>Spending Summary</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted fw-600" width="55%">Total Orders Placed</td>
                                            <td class="fw-700">{{ $stats['total_orders'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Completed Orders</td>
                                            <td class="fw-700 text-success">{{ $stats['completed_orders'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Active Orders</td>
                                            <td class="fw-700 text-primary">{{ $stats['active_orders'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Cancelled Orders</td>
                                            <td class="fw-700 text-warning">{{ $stats['cancelled_orders'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Disputed Orders</td>
                                            <td class="fw-700 text-danger">{{ $stats['disputed_orders'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Total Amount Spent</td>
                                            <td class="fw-700 text-success">${{ number_format($stats['total_spent'], 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Custom Offers Requested</td>
                                            <td class="fw-700">{{ $stats['custom_offers'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Reviews Given</td>
                                            <td class="fw-700">{{ $stats['total_reviews'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-600">Avg Rating Given</td>
                                            <td class="fw-700">
                                                {{ number_format($stats['avg_rating_given'], 1) }}
                                                <span class="text-warning ms-1">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i class="fe fe-star {{ $i <= $stars ? 'star-filled' : 'star-empty' }}"
                                                            style="font-size:11px;"></i>
                                                    @endfor
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Client Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <input type="hidden" id="newStatus">
                    <div class="modal-body">
                        <div id="suspendReasonSection" style="display:none;">
                            <label class="form-label">Suspension Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="suspendReason" rows="4"
                                placeholder="Provide a reason for suspending this client..."></textarea>
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ordersChartData = @json($ordersChart);
        const statusBreakdown = @json($statusBreakdown);

        // ---- Orders & Spending Line Chart ----
        const labels = ordersChartData.map(r => r.month);
        const orders = ordersChartData.map(r => r.total);
        const spending = ordersChartData.map(r => parseFloat(r.spent || 0));

        const ctx1 = document.getElementById('ordersSpendingChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                        label: 'Orders',
                        data: orders,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14,165,233,0.08)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#0ea5e9',
                        pointRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Amount Spent ($)',
                        data: spending,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.08)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#22c55e',
                        pointRadius: 4,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label === 'Amount Spent ($)' ?
                                ` $${ctx.parsed.y.toFixed(2)}` : ` ${ctx.parsed.y} orders`
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Orders'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Spent ($)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // ---- Status Doughnut ----
        const hasData = Object.keys(statusBreakdown).length > 0;
        if (hasData && document.getElementById('statusDoughnut')) {
            const statusLabels = Object.keys(statusBreakdown).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, c => c
                .toUpperCase()));
            const statusCounts = Object.values(statusBreakdown);
            const statusPalette = ['#0ea5e9', '#22c55e', '#f59e0b', '#f43f5e', '#8b5cf6', '#64748b', '#ec4899', '#14b8a6',
                '#6366f1'
            ];

            new Chart(document.getElementById('statusDoughnut').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: statusPalette.slice(0, statusLabels.length),
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            const legendEl = document.getElementById('doughnutLegend');
            statusLabels.forEach((label, i) => {
                const div = document.createElement('div');
                div.className = 'd-flex align-items-center justify-content-between mb-1';
                div.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <span style="width:10px;height:10px;border-radius:50%;background:${statusPalette[i]};display:inline-block;flex-shrink:0;"></span>
                    <small>${label}</small>
                </div>
                <small class="fw-600">${statusCounts[i]}</small>`;
                legendEl.appendChild(div);
            });
        }

        // ---- Quick Status Change ----
        function quickStatus(status) {
            event.preventDefault();
            $('#newStatus').val(status);
            if (status === 'suspended') {
                $('#suspendReasonSection').show();
                $('#confirmMessage').hide();
            } else {
                $('#suspendReasonSection').hide();
                $('#confirmMessage').show().text(`Set client status to "${status}"?`);
            }
            $('#statusModal').modal('show');
        }

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
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
                url: '{{ route('admin.clients.update-status', $client->id) }}',
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    status,
                    reason
                },
                success: function(res) {
                    NProgress.done();
                    $('#submitStatusBtn').prop('disabled', false);
                    $('#submitStatusBtn .btn-text').removeClass('d-none');
                    $('#submitStatusBtn .spinner-border').addClass('d-none');
                    if (res.success) {
                        toastr.success(res.message);
                        $('#statusModal').modal('hide');
                        setTimeout(() => location.reload(), 800);
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
        .profile-hero {
            overflow: visible;
            border: 1px solid #e2e8f0;
        }

        .profile-hero-banner {
            height: 90px;
            /* background: linear-gradient(135deg, #0ea5e9 0%, #22c55e 60%, #0ea5e9 100%); */
            border-radius: 8px 8px 0 0;
        }

        .profile-avatar {
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
            object-fit: cover;
            background: #f8f9fa;
        }

        .kpi-card {
            border: 1px solid #e9ecef;
            transition: transform .2s, box-shadow .2s;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto;
        }

        .stars-wrap {
            display: inline-flex;
            gap: 2px;
        }

        .star-filled {
            color: #f59e0b;
        }

        .star-empty {
            color: #d1d5db;
        }

        .order-row {
            transition: background .15s;
        }

        .order-row:hover {
            background: #f0f9ff;
        }

        .review-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .seller-reply {
            background: #eff6ff;
            border-left: 3px solid #0ea5e9;
        }

        .table td {
            vertical-align: middle;
        }

        .fw-600 {
            font-weight: 600;
        }

        .fw-700 {
            font-weight: 700;
        }

        .fs-32 {
            font-size: 32px;
        }

        .bg-primary-transparent {
            background: rgba(14, 165, 233, .12) !important;
        }
    </style>
@endpush
