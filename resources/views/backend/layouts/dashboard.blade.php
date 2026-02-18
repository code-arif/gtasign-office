@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $admin = auth()->user();
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
        $adminName = $admin->profile->first_name ?? explode('@', $admin->email)[0];

        $orderStatusMap = [
            'pending_payment' => ['Pending Payment', '#f59e0b'],
            'active' => ['Active', '#6366f1'],
            'qa_pending' => ['QA Pending', '#0ea5e9'],
            'qa_rejected' => ['QA Rejected', '#ef4444'],
            'delivered' => ['Delivered', '#10b981'],
            'revision_requested' => ['Revision Requested', '#f97316'],
            'completed' => ['Completed', '#22c55e'],
            'cancelled' => ['Cancelled', '#94a3b8'],
            'disputed' => ['Disputed', '#dc2626'],
        ];
    @endphp

    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin: 20px 0 40px 0;">
            <div class="main-container container-fluid pb-5">

                {{-- ===================== GREETING HERO ===================== --}}
                <div class="dash-hero mb-4">
                    <div class="row align-items-center">
                        <div class="col-lg-7">
                            <div class="hero-greeting">
                                <p class="hero-time mb-1">
                                    <i class="fe fe-sun me-1 text-warning"></i>
                                    {{ now()->format('l, F j, Y') }}
                                </p>
                                <h1 class="hero-title mb-1">{{ $greeting }}, {{ ucfirst($adminName) }}! 👋</h1>
                                <p class="hero-sub mb-0">Here's what's happening on your platform today.</p>
                            </div>

                            {{-- Today's Quick Numbers --}}
                            <div class="d-flex flex-wrap gap-4 mt-3">
                                <div class="hero-quick-stat">
                                    <span class="hero-qs-num">{{ $stats['new_orders_today'] }}</span>
                                    <span class="hero-qs-label">New Orders Today</span>
                                </div>
                                <div class="hero-quick-stat">
                                    <span class="hero-qs-num text-warning">{{ $stats['pending_gigs'] }}</span>
                                    <span class="hero-qs-label">Gigs Awaiting Review</span>
                                </div>
                                <div class="hero-quick-stat">
                                    <span
                                        class="hero-qs-num text-success">${{ number_format($stats['revenue_week'], 0) }}</span>
                                    <span class="hero-qs-label">Revenue This Week</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 d-none d-lg-block text-end">
                            <div class="hero-illustration">
                                <div class="hero-blob-1"></div>
                                <div class="hero-blob-2"></div>
                                <div class="hero-chart-preview">
                                    <canvas id="heroMiniChart" height="80"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================== KPI CARDS ===================== --}}
                <div class="row g-3 mb-4">

                    {{-- Experts --}}
                    <div class="col-xl col-lg-4 col-md-6">
                        <a href="{{ route('admin.experts.index') }}" class="kpi-card kpi-indigo">
                            <div class="kpi-icon-wrap">
                                <i class="fe fe-user-check"></i>
                            </div>
                            <div class="kpi-body">
                                <div class="kpi-number">{{ number_format($stats['total_experts']) }}</div>
                                <div class="kpi-label">Total Experts</div>
                                <div class="kpi-sub">
                                    <span class="kpi-active">{{ $stats['active_experts'] }} active</span>
                                    <span class="kpi-badge kpi-badge-up">
                                        <i class="fe fe-trending-up"></i> +{{ $stats['new_experts_week'] }} this week
                                    </span>
                                </div>
                            </div>
                            <div class="kpi-bg-shape"></div>
                        </a>
                    </div>

                    {{-- Clients --}}
                    <div class="col-xl col-lg-4 col-md-6">
                        <a href="{{ route('admin.clients.index') }}" class="kpi-card kpi-sky">
                            <div class="kpi-icon-wrap">
                                <i class="fe fe-users"></i>
                            </div>
                            <div class="kpi-body">
                                <div class="kpi-number">{{ number_format($stats['total_clients']) }}</div>
                                <div class="kpi-label">Total Clients</div>
                                <div class="kpi-sub">
                                    <span class="kpi-active">{{ $stats['active_clients'] }} active</span>
                                    <span class="kpi-badge kpi-badge-up">
                                        <i class="fe fe-trending-up"></i> +{{ $stats['new_clients_week'] }} this week
                                    </span>
                                </div>
                            </div>
                            <div class="kpi-bg-shape"></div>
                        </a>
                    </div>

                    {{-- Gigs --}}
                    <div class="col-xl col-lg-4 col-md-6">
                        <a href="{{ route('admin.gigs.index') }}" class="kpi-card kpi-violet">
                            <div class="kpi-icon-wrap">
                                <i class="fe fe-briefcase"></i>
                            </div>
                            <div class="kpi-body">
                                <div class="kpi-number">{{ number_format($stats['total_gigs']) }}</div>
                                <div class="kpi-label">Total Gigs</div>
                                <div class="kpi-sub">
                                    <span class="kpi-active">{{ $stats['active_gigs'] }} active</span>
                                    @if ($stats['pending_gigs'] > 0)
                                        <span class="kpi-badge kpi-badge-warn">
                                            <i class="fe fe-clock"></i> {{ $stats['pending_gigs'] }} pending
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="kpi-bg-shape"></div>
                        </a>
                    </div>

                    {{-- Orders --}}
                    <div class="col-xl col-lg-4 col-md-6">
                        <div class="kpi-card kpi-emerald">
                            <div class="kpi-icon-wrap">
                                <i class="fe fe-shopping-bag"></i>
                            </div>
                            <div class="kpi-body">
                                <div class="kpi-number">{{ number_format($stats['total_orders']) }}</div>
                                <div class="kpi-label">Total Orders</div>
                                <div class="kpi-sub">
                                    <span class="kpi-active">{{ $stats['active_orders'] }} active</span>
                                    <span class="kpi-badge kpi-badge-up">
                                        <i class="fe fe-check"></i> {{ $stats['completed_orders'] }} done
                                    </span>
                                </div>
                            </div>
                            <div class="kpi-bg-shape"></div>
                        </div>
                    </div>

                    {{-- Revenue --}}
                    <div class="col-xl col-lg-4 col-md-6">
                        <div class="kpi-card kpi-amber">
                            <div class="kpi-icon-wrap">
                                <i class="fe fe-dollar-sign"></i>
                            </div>
                            <div class="kpi-body">
                                <div class="kpi-number">${{ number_format($stats['total_revenue'], 0) }}</div>
                                <div class="kpi-label">Platform Revenue</div>
                                <div class="kpi-sub">
                                    <span class="kpi-active">${{ number_format($stats['revenue_month'], 0) }} this
                                        month</span>
                                    <span class="kpi-badge kpi-badge-up">
                                        <i class="fe fe-trending-up"></i> fees earned
                                    </span>
                                </div>
                            </div>
                            <div class="kpi-bg-shape"></div>
                        </div>
                    </div>

                </div>

                {{-- ===================== CHARTS ROW ===================== --}}
                <div class="row g-3 mb-4">

                    {{-- Daily Orders Line Chart --}}
                    <div class="col-xl-8">
                        <div class="card dash-card h-100">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="card-title mb-0">Order Activity</h5>
                                        <small class="text-muted">Last 30 days</small>
                                    </div>
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="chart-legend-item">
                                            <span class="legend-dot" style="background:#6366f1;"></span>
                                            <small>Orders</small>
                                        </div>
                                        <div class="chart-legend-item">
                                            <span class="legend-dot" style="background:#22c55e;"></span>
                                            <small>Revenue ($)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                <canvas id="orderActivityChart" height="90"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Order Status Doughnut --}}
                    <div class="col-xl-4">
                        <div class="card dash-card h-100">
                            <div class="card-header border-0 pb-0">
                                <h5 class="card-title mb-0">Order Status</h5>
                                <small class="text-muted">All time breakdown</small>
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center pt-2">
                                @if ($orderStatusBreakdown->isEmpty())
                                    <div class="text-center text-muted py-4">
                                        <i class="fe fe-pie-chart" style="font-size:40px;"></i>
                                        <p class="mt-2 mb-0">No orders yet</p>
                                    </div>
                                @else
                                    <div style="position:relative; max-width:200px; width:100%;">
                                        <canvas id="statusDoughnut"></canvas>
                                        <div class="doughnut-center-label">
                                            <div class="fw-700" style="font-size:22px;">
                                                {{ number_format($stats['total_orders']) }}</div>
                                            <div class="text-muted" style="font-size:11px;">Total</div>
                                        </div>
                                    </div>
                                    <div id="statusLegend" class="w-100 mt-3"></div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ===================== REVENUE BAR + PENDING GIGS ===================== --}}
                <div class="row g-3 mb-4">

                    {{-- Monthly Revenue Bar --}}
                    <div class="col-xl-5">
                        <div class="card dash-card h-100">
                            <div class="card-header border-0 pb-0">
                                <h5 class="card-title mb-0">Monthly Revenue</h5>
                                <small class="text-muted">Last 6 months — platform fees</small>
                            </div>
                            <div class="card-body pt-3">
                                <canvas id="revenueBarChart" height="140"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Pending Gigs — Action Required --}}
                    <div class="col-xl-7">
                        <div class="card dash-card h-100">
                            <div class="card-header border-0 pb-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                                        Gigs Awaiting Approval
                                        @if ($stats['pending_gigs'] > 0)
                                            <span class="badge bg-warning text-dark">{{ $stats['pending_gigs'] }}</span>
                                        @endif
                                    </h5>
                                    <small class="text-muted">Review and approve or reject</small>
                                </div>
                                <a href="{{ route('admin.gigs.index') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                                    View All <i class="fe fe-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="card-body p-0">
                                @forelse($pendingGigs as $gig)
                                    <div class="pending-gig-row d-flex align-items-center gap-3 px-4 py-3">
                                        <div class="pending-gig-dot"></div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold text-truncate" title="{{ $gig->title }}">
                                                {{ $gig->title }}
                                            </div>
                                            <div class="d-flex gap-2 mt-1">
                                                <small class="text-muted">
                                                    <i class="fe fe-user me-1"></i>
                                                    {{ $gig->user->profile->first_name ?? $gig->user->email }}
                                                </small>
                                                @if ($gig->category)
                                                    <small class="text-muted">·</small>
                                                    <small class="text-muted">
                                                        <i class="fe fe-tag me-1"></i>{{ $gig->category->name }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-shrink-0">
                                            <a href="{{ route('admin.gigs.show', $gig->id) }}"
                                                class="btn btn-xs btn-soft-primary">Review</a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-5">
                                        <i class="fe fe-check-circle text-success" style="font-size:36px;"></i>
                                        <p class="mt-2 mb-0">All gigs reviewed — you're up to date!</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ===================== RECENT ORDERS + RECENT EXPERTS ===================== --}}
                <div class="row g-3">

                    {{-- Recent Orders Table --}}
                    <div class="col-xl-8">
                        <div class="card dash-card">
                            <div class="card-header border-0 pb-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Recent Orders</h5>
                                    <small class="text-muted">Latest 8 orders</small>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover dash-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Order</th>
                                                <th>Client</th>
                                                <th>Expert</th>
                                                <th class="text-center">Amount</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentOrders as $order)
                                                @php
                                                    $sm = $orderStatusMap[$order->status] ?? [
                                                        ucfirst($order->status),
                                                        '#94a3b8',
                                                    ];
                                                    $buyerName = $order->buyer?->profile
                                                        ? $order->buyer->profile->first_name .
                                                            ' ' .
                                                            ($order->buyer->profile->last_name ?? '')
                                                        : $order->buyer?->email ?? '—';
                                                    $sellerName = $order->seller?->profile
                                                        ? $order->seller->profile->first_name .
                                                            ' ' .
                                                            ($order->seller->profile->last_name ?? '')
                                                        : $order->seller?->email ?? '—';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold" style="font-size:13px;">
                                                            #{{ $order->order_number }}</div>
                                                        <small class="text-muted text-truncate d-block"
                                                            style="max-width:160px;" title="{{ $order->gig?->title }}">
                                                            {{ Str::limit($order->gig?->title ?? 'N/A', 22) }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            @php
                                                                $ba = $order->buyer?->profile?->avatar
                                                                    ? asset($order->buyer->profile->avatar)
                                                                    : 'https://ui-avatars.com/api/?name=' .
                                                                        urlencode(trim($buyerName)) .
                                                                        '&size=32&background=0ea5e9&color=fff';
                                                            @endphp
                                                            <img src="{{ $ba }}" class="rounded-circle"
                                                                width="28" height="28" style="object-fit:cover;"
                                                                alt="">
                                                            <small class="text-truncate"
                                                                style="max-width:90px;">{{ trim($buyerName) }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small class="text-truncate d-block" style="max-width:100px;">
                                                            {{ trim($sellerName) }}
                                                        </small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="fw-600 text-success">${{ number_format($order->price, 2) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="dash-status-badge"
                                                            style="background:{{ $sm[1] }}18; color:{{ $sm[1] }}; border:1px solid {{ $sm[1] }}30;">
                                                            {{ $sm[0] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <small
                                                            class="text-muted">{{ $order->created_at->format('M d') }}</small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">No orders yet
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Experts --}}
                    <div class="col-xl-4">
                        <div class="card dash-card">
                            <div class="card-header border-0 pb-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Recent Experts</h5>
                                    <small class="text-muted">Newly joined</small>
                                </div>
                                <a href="{{ route('admin.experts.index') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                                    All <i class="fe fe-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="card-body pt-2 pb-0">
                                @forelse($recentExperts as $expert)
                                    @php
                                        $ep = $expert->profile;
                                        $eName = $ep
                                            ? trim($ep->first_name . ' ' . ($ep->last_name ?? ''))
                                            : $expert->email;
                                        $eAvatar = $ep?->avatar
                                            ? asset($ep->avatar)
                                            : 'https://ui-avatars.com/api/?name=' .
                                                urlencode($eName) .
                                                '&size=40&background=6366f1&color=fff';
                                    @endphp
                                    <div class="expert-row d-flex align-items-center gap-3 py-3 border-bottom">
                                        <img src="{{ $eAvatar }}" class="rounded-circle flex-shrink-0"
                                            width="40" height="40"
                                            style="object-fit:cover; border:2px solid #e9ecef;" alt="">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold text-truncate">{{ $eName }}</div>
                                            <small class="text-muted">
                                                {{ $ep?->username ?? Str::before($expert->email, '@') }}
                                            </small>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <span
                                                class="badge {{ $expert->status === 'active' ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary' }}"
                                                style="font-size:11px;">
                                                {{ ucfirst($expert->status) }}
                                            </span>
                                            <div>
                                                <small
                                                    class="text-muted">{{ $expert->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">No experts yet</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ── Data from PHP ────────────────────────────────────────────────
        const chartDays = @json($chartDays);
        const chartOrders = @json($chartOrders);
        const chartRevenue = @json($chartRevenue);
        const chartMonths = @json($chartMonths);
        const chartMonthRev = @json($chartMonthRevenue);
        const chartMonthOrd = @json($chartMonthOrders);
        const statusData = @json($orderStatusBreakdown);

        Chart.defaults.font.family = "'DM Sans', sans-serif";
        Chart.defaults.color = '#64748b';

        // ── Hero Mini Sparkline ──────────────────────────────────────────
        const heroCtx = document.getElementById('heroMiniChart')?.getContext('2d');
        if (heroCtx) {
            new Chart(heroCtx, {
                type: 'line',
                data: {
                    labels: chartDays.slice(-14),
                    datasets: [{
                        data: chartOrders.slice(-14),
                        borderColor: 'rgba(255,255,255,0.8)',
                        backgroundColor: 'rgba(255,255,255,0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    }
                }
            });
        }

        // ── Order Activity Line Chart ────────────────────────────────────
        const ctx1 = document.getElementById('orderActivityChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: chartDays,
                datasets: [{
                        label: 'Orders',
                        data: chartOrders,
                        borderColor: '#6366f1',
                        backgroundColor: (ctx) => {
                            const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                            g.addColorStop(0, 'rgba(99,102,241,0.2)');
                            g.addColorStop(1, 'rgba(99,102,241,0)');
                            return g;
                        },
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#6366f1',
                        yAxisID: 'y',
                    },
                    {
                        label: 'Revenue ($)',
                        data: chartRevenue,
                        borderColor: '#22c55e',
                        backgroundColor: (ctx) => {
                            const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                            g.addColorStop(0, 'rgba(34,197,94,0.12)');
                            g.addColorStop(1, 'rgba(34,197,94,0)');
                            return g;
                        },
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#22c55e',
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
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.9)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: c => c.dataset.label === 'Revenue ($)' ?
                                ` $${c.parsed.y.toFixed(2)}` : ` ${c.parsed.y} orders`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxTicksLimit: 10,
                            maxRotation: 0,
                        }
                    },
                    y: {
                        position: 'left',
                        grid: {
                            color: 'rgba(0,0,0,0.04)',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    y1: {
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: v => '$' + v
                        }
                    }
                }
            }
        });

        // ── Status Doughnut ──────────────────────────────────────────────
        if (document.getElementById('statusDoughnut') && Object.keys(statusData).length) {
            const statusPalette = {
                pending_payment: '#f59e0b',
                active: '#6366f1',
                qa_pending: '#0ea5e9',
                qa_rejected: '#ef4444',
                delivered: '#10b981',
                revision_requested: '#f97316',
                completed: '#22c55e',
                cancelled: '#94a3b8',
                disputed: '#dc2626',
            };
            const statusLabels = Object.keys(statusData).map(k =>
                k.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
            const statusCounts = Object.values(statusData);
            const colors = Object.keys(statusData).map(k => statusPalette[k] || '#64748b');

            new Chart(document.getElementById('statusDoughnut').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: colors,
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.9)',
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            padding: 10,
                            cornerRadius: 8,
                        }
                    }
                }
            });

            const leg = document.getElementById('statusLegend');
            Object.keys(statusData).forEach((k, i) => {
                const d = document.createElement('div');
                d.className = 'd-flex align-items-center justify-content-between mb-1';
                d.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <span style="width:9px;height:9px;border-radius:50%;background:${colors[i]};display:inline-block;flex-shrink:0;"></span>
                    <small style="font-size:12px;">${statusLabels[i]}</small>
                </div>
                <small class="fw-600" style="font-size:12px;">${statusCounts[i]}</small>`;
                leg.appendChild(d);
            });
        }

        // ── Monthly Revenue Bar Chart ────────────────────────────────────
        const ctx3 = document.getElementById('revenueBarChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: chartMonths,
                datasets: [{
                    label: 'Revenue ($)',
                    data: chartMonthRev,
                    backgroundColor: chartMonths.map((_, i) =>
                        i === chartMonths.length - 1 ? '#6366f1' : 'rgba(99,102,241,0.25)'),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.9)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: c => ` $${c.parsed.y.toFixed(2)}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,0.04)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: v => '$' + v
                        }
                    }
                }
            }
        });
    </script>
@endpush

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body,
        .app-content {
            font-family: 'DM Sans', sans-serif;
        }

        /* ── Hero ── */
        .dash-hero {
            background: linear-gradient(135deg, #1e293b 0%, #312e81 60%, #1e3a5f 100%);
            border-radius: 16px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .dash-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(99, 102, 241, 0.15);
            border-radius: 50%;
        }

        .hero-time {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            font-weight: 500;
        }

        .hero-title {
            color: #fff;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .hero-sub {
            color: rgba(255, 255, 255, 0.55);
            font-size: 14px;
        }

        .hero-quick-stat {
            display: flex;
            flex-direction: column;
        }

        .hero-qs-num {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
        }

        .hero-qs-label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .hero-illustration {
            position: relative;
            height: 90px;
        }

        .hero-chart-preview {
            position: absolute;
            inset: 0;
        }

        /* ── KPI Cards ── */
        .kpi-card {
            display: block;
            border-radius: 14px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            text-decoration: none !important;
            transition: transform .2s, box-shadow .2s;
            cursor: pointer;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        }

        .kpi-indigo {
            background: linear-gradient(135deg, #6366f1, #818cf8);
        }

        .kpi-sky {
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
        }

        .kpi-violet {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .kpi-emerald {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .kpi-amber {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .kpi-icon-wrap {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            margin-bottom: 14px;
        }

        .kpi-number {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .kpi-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-top: 4px;
            font-weight: 600;
        }

        .kpi-sub {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .kpi-active {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .kpi-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .kpi-badge-up {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .kpi-badge-warn {
            background: rgba(254, 243, 199, 0.3);
            color: #fef3c7;
        }

        .kpi-bg-shape {
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        /* ── Dash Cards ── */
        .dash-card {
            border: 1px solid #e9ecef;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .dash-card .card-header {
            background: transparent;
            padding: 18px 20px 12px;
        }

        .dash-card .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        .chart-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #64748b;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Doughnut Center ── */
        .doughnut-center-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        /* ── Pending Gigs ── */
        .pending-gig-row {
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        .pending-gig-row:last-child {
            border-bottom: none;
        }

        .pending-gig-row:hover {
            background: #fffbeb;
        }

        .pending-gig-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #f59e0b;
            flex-shrink: 0;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        /* ── Dash Table ── */
        .dash-table thead th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #94a3b8;
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 16px;
            background: #fafafa;
        }

        .dash-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .dash-table tbody tr:hover td {
            background: #f8fafc;
        }

        .dash-status-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* ── Expert Row ── */
        .expert-row {
            transition: background .15s;
        }

        .expert-row:hover {
            background: #f8fafc;
            border-radius: 8px;
        }

        .expert-row:last-child {
            border-bottom: none !important;
        }

        /* ── Btn Soft ── */
        .btn-xs {
            padding: 3px 10px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-soft-primary {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            border: 1px solid rgba(99, 102, 241, 0.2);
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 6px;
            transition: background .15s;
        }

        .btn-soft-primary:hover {
            background: #6366f1;
            color: #fff;
        }

        .fw-600 {
            font-weight: 600;
        }

        .fw-700 {
            font-weight: 700;
        }
    </style>
@endpush
