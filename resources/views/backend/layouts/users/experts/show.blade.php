@extends('backend.app')

@section('title', 'Expert Profile — ' . ($expert->profile->first_name ?? $expert->email))

@section('content')
    @php
        $profile = $expert->profile;
        $fullName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : $expert->email;
        $avatar =
            $profile && $profile->avatar
                ? asset($profile->avatar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=6366f1&color=fff&size=128';

        $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'danger'];
        $statusColor = $statusColors[$expert->status] ?? 'secondary';

        $stars = round($stats['avg_rating']);
    @endphp

    <div class="app-content main-content mt-0">
        <div class="side-app pb-5" style="margin-bottom: 30px">
            <div class="main-container container-fluid">

                <!-- BREADCRUMB -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Expert Profile</h1>
                        <p class="text-muted mb-0">Full activity & history dashboard</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <span>&nbsp; &gt&gt &nbsp;</span>
                            <li class="breadcrumb-item"><a href="{{ route('admin.experts.index') }}">Experts</a></li>
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
                            <!-- Avatar -->
                            <div class="position-relative flex-shrink-0">
                                <img src="{{ $avatar }}" alt="{{ $fullName }}" class="profile-avatar"
                                    width="88" height="88">
                                @if ($expert->email_verified_at)
                                    <span class="position-absolute bottom-0 end-0 badge bg-success rounded-circle p-1"
                                        title="Email Verified"
                                        style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fe fe-check" style="font-size:11px;"></i>
                                    </span>
                                @endif
                            </div>

                            <!-- Name & meta -->
                            <div class="flex-grow-1 pb-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="mb-0 fw-700">{{ $fullName }}</h3>
                                    @if ($expert->deleted_at)
                                        <span class="badge bg-danger">Deleted</span>
                                    @endif
                                    {{-- <span class="badge bg-{{ $statusColor }}">{{ ucfirst($expert->status) }}</span> --}}
                                    <span class="badge bg-info text-end"> {{ strtoupper($expert->profile->level) }} </span>
                                </div>
                                <div class="d-flex flex-wrap gap-3 text-muted small">
                                    <span><i class="fe fe-mail me-1"></i>{{ $expert->email }}</span>
                                    @if ($expert->phone)
                                        <span><i class="fe fe-phone me-1"></i>{{ $expert->phone }}</span>
                                    @endif
                                    @if ($profile?->username)
                                        <span><i class="fe fe-at-sign me-1"></i>{{ $profile->username }}</span>
                                    @endif
                                    <span><i class="fe fe-calendar me-1"></i>Joined
                                        {{ $expert->created_at->format('M d, Y') }}</span>
                                </div>

                                <!-- Stars -->
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="stars-wrap">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fe fe-star {{ $i <= $stars ? 'star-filled' : 'star-empty' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="fw-600">{{ number_format($stats['avg_rating'], 1) }}</span>
                                    <span class="text-muted small">({{ $stats['total_reviews'] }} reviews)</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 flex-shrink-0 pb-1">
                                @if (!$expert->deleted_at)
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-inline-flex align-items-center"
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
                                <a href="{{ route('admin.experts.index') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                                    <i class="fe fe-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== KPI CARDS ===================== -->
                <div class="row g-3 mb-4">

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #6366f1;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(99,102,241,.12); color:#6366f1;">
                                    <i class="fe fe-briefcase"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ $stats['total_gigs'] }}</h4>
                                <small class="text-muted">Total Gigs</small>
                                <div class="mt-1">
                                    <span
                                        class="badge bg-success-transparent text-success small">{{ $stats['active_gigs'] }}
                                        active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #0ea5e9;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(14,165,233,.12); color:#0ea5e9;">
                                    <i class="fe fe-shopping-bag"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ $stats['total_orders'] }}</h4>
                                <small class="text-muted">Total Orders</small>
                                <div class="mt-1">
                                    <span class="badge bg-info-transparent text-info small">{{ $stats['active_orders'] }}
                                        active</span>
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
                                    @php $complRate = $stats['total_orders'] > 0 ? round(($stats['completed_orders'] / $stats['total_orders']) * 100) : 0; @endphp
                                    <span class="badge bg-success-transparent text-success small">{{ $complRate }}%
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
                                    @php $cancelRate = $stats['total_orders'] > 0 ? round(($stats['cancelled_orders'] / $stats['total_orders']) * 100) : 0; @endphp
                                    <span class="badge bg-warning-transparent text-warning small">{{ $cancelRate }}%
                                        rate</span>
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
                                <h4 class="mb-0 fw-700">${{ number_format($stats['total_earnings'], 0) }}</h4>
                                <small class="text-muted">Total Earned</small>
                                <div class="mt-1">
                                    <span class="badge bg-warning-transparent text-warning small">
                                        ${{ number_format($stats['pending_earnings'], 0) }} pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-6">
                        <div class="card kpi-card h-100" style="border-top:3px solid #f43f5e;">
                            <div class="card-body text-center py-3">
                                <div class="kpi-icon mb-2" style="background:rgba(244,63,94,.12); color:#f43f5e;">
                                    <i class="fe fe-star"></i>
                                </div>
                                <h4 class="mb-0 fw-700">{{ number_format($stats['avg_rating'], 1) }}</h4>
                                <small class="text-muted">Avg Rating</small>
                                <div class="mt-1">
                                    <span
                                        class="badge bg-danger-transparent text-danger small">{{ $stats['total_reviews'] }}
                                        reviews</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== CHARTS ROW ===================== -->
                <div class="row g-3 mb-4">
                    <!-- Orders & Earnings Line Chart -->
                    <div class="col-xl-8">
                        <div class="card h-100">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Orders & Earnings — Last 6 Months</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="ordersEarningsChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Order Status Doughnut -->
                    <div class="col-xl-4">
                        <div class="card h-100">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Order Status Breakdown</h5>
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="statusDoughnut" style="max-height:220px;"></canvas>
                                <div id="doughnutLegend" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== GIGS + ORDERS ROW ===================== -->
                <div class="row g-3 mb-4">
                    <!-- Recent Gigs -->
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Gigs</h5>
                                <a href="{{ route('admin.gigs.index') }}?seller={{ $expert->id }}"
                                    class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                @forelse($recentGigs as $gig)
                                    <div class="gig-row d-flex align-items-center gap-3 p-3 border-bottom">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold text-truncate" title="{{ $gig->title }}">
                                                {{ $gig->title }}</div>
                                            <small class="text-muted">
                                                {{ $gig->category->name ?? 'N/A' }}
                                                @if ($gig->subCategory)
                                                    › {{ $gig->subCategory->name }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="fw-bold text-success">${{ number_format($gig->price, 2) }}</div>
                                            <span
                                                class="badge bg-{{ ['draft' => 'secondary', 'pending_approval' => 'warning', 'active' => 'success', 'rejected' => 'danger'][$gig->status] ?? 'secondary' }} small">
                                                {{ ucfirst(str_replace('_', ' ', $gig->status)) }}
                                            </span>
                                        </div>
                                        <a href="{{ route('admin.gigs.show', $gig->id) }}"
                                            class="btn btn-xs btn-outline-primary flex-shrink-0 d-inline-flex align-items-center">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-5">
                                        <i class="fe fe-briefcase fs-32 mb-2 d-block"></i>
                                        No gigs found
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Orders</h5>
                            </div>
                            <div class="card-body p-0">
                                @forelse($recentOrders as $order)
                                    @php
                                        $orderStatusColors = [
                                            'pending_payment' => 'warning',
                                            'active' => 'primary',
                                            'qa_pending' => 'info',
                                            'qa_rejected' => 'danger',
                                            'delivered' => 'info',
                                            'revision_requested' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'disputed' => 'danger',
                                        ];
                                        $oc = $orderStatusColors[$order->status] ?? 'secondary';
                                    @endphp
                                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold">#{{ $order->order_number }}</div>
                                            <small class="text-muted text-truncate d-block"
                                                title="{{ $order->gig->title ?? '' }}">
                                                {{ Str::limit($order->gig->title ?? 'N/A', 35) }}
                                            </small>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="fw-bold text-success">
                                                ${{ number_format($order->seller_earnings, 2) }}</div>
                                            <span class="badge bg-{{ $oc }} small">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </div>
                                        <small
                                            class="text-muted flex-shrink-0">{{ $order->created_at->format('M d') }}</small>
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
                </div>

                <!-- ===================== REVIEWS ===================== -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Recent Reviews</h5>
                            </div>
                            <div class="card-body">
                                @forelse($recentReviews as $review)
                                    <div class="review-card p-3 rounded-1 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="fw-semibold">{{ $review->reviewer_email }}</span>
                                                <div class="stars-wrap mt-1">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i class="fe fe-star {{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}"
                                                            style="font-size:13px;"></i>
                                                    @endfor
                                                    <span class="ms-1 fw-600 small">{{ $review->rating }}/5</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">{{ $review->gig_title }}</small>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($review->created_at)->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                        @if ($review->review)
                                            <p class="mb-0 text-muted small">{{ $review->review }}</p>
                                        @endif
                                        @if ($review->seller_reply)
                                            <div class="seller-reply mt-2 p-2 rounded-1">
                                                <small class="fw-600 text-primary"><i
                                                        class="fe fe-corner-down-right me-1"></i>Expert Reply:</small>
                                                <p class="mb-0 small text-muted mt-1">{{ $review->seller_reply }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="fe fe-star fs-32 mb-2 d-block"></i>
                                        No reviews yet
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== PROFILE INFO ===================== -->
                @if ($profile)
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
                                                <td>{{ $expert->email }}
                                                    @if ($expert->email_verified_at)
                                                        <i class="fe fe-check-circle text-success ms-1"
                                                            title="Verified"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-600">Phone</td>
                                                <td>{{ $expert->phone ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-600">Account Status</td>
                                                <td><span
                                                        class="badge bg-{{ $statusColor }}">{{ ucfirst($expert->status) }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-600">Joined</td>
                                                <td>{{ $expert->created_at->format('M d, Y · h:i A') }}</td>
                                            </tr>
                                            @if ($expert->deleted_at)
                                                <tr>
                                                    <td class="text-muted fw-600">Deleted At</td>
                                                    <td class="text-danger">
                                                        {{ $expert->deleted_at->format('M d, Y · h:i A') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if ($expert->education?->count() || $expert->certifications?->count() || $expert->experiences?->count())
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h5 class="card-title mb-0"><i class="fe fe-award me-2"></i>Background</h5>
                                    </div>
                                    <div class="card-body">
                                        @if ($expert->education?->count())
                                            <h6 class="text-muted text-uppercase small mb-2">Education</h6>
                                            @foreach ($expert->education as $edu)
                                                <div class="mb-2">
                                                    <div class="fw-semibold">{{ $edu->degree ?? '' }}</div>
                                                    <small class="text-muted">{{ $edu->institution ?? '' }}
                                                        {{ $edu->year ? '· ' . $edu->year : '' }}</small>
                                                </div>
                                            @endforeach
                                        @endif

                                        @if ($expert->experiences?->count())
                                            <h6 class="text-muted text-uppercase small mb-2 mt-3">Experience</h6>
                                            @foreach ($expert->experiences as $exp)
                                                <div class="mb-2">
                                                    <div class="fw-semibold">{{ $exp->title ?? '' }}</div>
                                                    <small class="text-muted">{{ $exp->company ?? '' }}</small>
                                                </div>
                                            @endforeach
                                        @endif

                                        @if ($expert->certifications?->count())
                                            <h6 class="text-muted text-uppercase small mb-2 mt-3">Certifications</h6>
                                            @foreach ($expert->certifications as $cert)
                                                <div class="mb-2">
                                                    <div class="fw-semibold">{{ $cert->name ?? '' }}</div>
                                                    <small class="text-muted">{{ $cert->issuer ?? '' }}</small>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

            </div>{{-- /container --}}
        </div>
    </div>

    <!-- Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Expert Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ---- Chart data from PHP ----
        const ordersChartData = @json($ordersChart);
        const statusBreakdown = @json($statusBreakdown);

        // ---- Orders & Earnings Line Chart ----
        const labels = ordersChartData.map(r => r.month);
        const orders = ordersChartData.map(r => r.total);
        const earnings = ordersChartData.map(r => parseFloat(r.earnings));

        const ctx1 = document.getElementById('ordersEarningsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                        label: 'Orders',
                        data: orders,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#6366f1',
                        pointRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Earnings ($)',
                        data: earnings,
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
                            label: ctx => ctx.dataset.label === 'Earnings ($)' ?
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
                            text: 'Earnings ($)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // ---- Status Doughnut ----
        const statusLabels = Object.keys(statusBreakdown).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, c => c
            .toUpperCase()));
        const statusCounts = Object.values(statusBreakdown);
        const statusPalette = ['#6366f1', '#22c55e', '#f59e0b', '#f43f5e', '#0ea5e9', '#8b5cf6', '#64748b', '#ec4899'];

        const ctx2 = document.getElementById('statusDoughnut').getContext('2d');
        new Chart(ctx2, {
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

        // Custom legend
        const legendEl = document.getElementById('doughnutLegend');
        statusLabels.forEach((label, i) => {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center justify-content-between mb-1';
            div.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:10px;border-radius:50%;background:${statusPalette[i]};display:inline-block;flex-shrink:0;"></span>
                <small>${label}</small>
            </div>
            <small class="fw-600">${statusCounts[i]}</small>
        `;
            legendEl.appendChild(div);
        });

        // ---- Quick Status Change ----
        function quickStatus(status) {
            event.preventDefault();
            $('#newStatus').val(status);
            if (status === 'suspended') {
                $('#suspendReasonSection').show();
                $('#confirmMessage').hide();
            } else {
                $('#suspendReasonSection').hide();
                $('#confirmMessage').show().text(`Set expert status to "${status}"?`);
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
                url: '{{ route('admin.experts.update-status', $expert->id) }}',
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
        /* ---- Profile Hero ---- */
        .profile-hero {
            overflow: visible;
            border: 1px solid #e2e8f0;
        }

        .profile-hero-banner {
            height: 90px;
            /* background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #0ea5e9 100%); */
            border-radius: 8px 8px 0 0;
        }

        .profile-avatar {
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
            object-fit: cover;
            background: #f8f9fa;
        }

        /* ---- KPI Cards ---- */
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

        /* ---- Stars ---- */
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

        /* ---- Gig Row hover ---- */
        .gig-row {
            transition: background .15s;
        }

        .gig-row:hover {
            background: #f8f9ff;
        }

        /* ---- Review Card ---- */
        .review-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .seller-reply {
            background: #eff6ff;
            border-left: 3px solid #6366f1;
        }

        /* ---- btn-xs ---- */
        .btn-xs {
            padding: 2px 8px;
            font-size: 12px;
        }

        /* ---- Table ---- */
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
    </style>
@endpush
