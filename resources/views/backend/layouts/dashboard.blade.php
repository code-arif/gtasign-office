@extends('backend.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Dashboard</h1>
            <div>
                <span class="text-muted">{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <!-- Experts Stats -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Experts</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_experts']) }}</h3>
                                <small class="text-success">
                                    <i class="bi bi-arrow-up"></i>
                                    {{ $stats['active_experts'] }} active
                                </small>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients Stats -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Clients</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_clients']) }}</h3>
                                <small class="text-success">
                                    <i class="bi bi-arrow-up"></i>
                                    {{ $stats['active_clients'] }} active
                                </small>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-person-badge text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gigs Stats -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Gigs</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_gigs']) }}</h3>
                                <small class="text-warning">
                                    <i class="bi bi-clock"></i>
                                    {{ $stats['pending_gigs'] }} pending
                                </small>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-briefcase text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Stats -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Revenue</h6>
                                {{-- <h3 class="mb-0">${{ number_format($stats['total_revenue'], 2) }}</h3> --}}
                                <small class="text-success">
                                    <i class="bi bi-arrow-up"></i>
                                    Platform fees
                                </small>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-currency-dollar text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div <div class="row g-3">
        <!-- Recent Experts -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Experts</h5>
                        {{-- <a href="{{ route('admin.experts.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a> --}}
                        <a href="/" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Expert</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExperts as $expert)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $expert->profile?->avatar }}" class="rounded-circle me-2"
                                                    width="32" height="32" alt="{{ $expert->profile?->first_name }}">
                                                <span>{{ $expert->profile?->first_name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $expert->email }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $expert->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($expert->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $expert->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No experts found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="#" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            {{-- <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="#" class="text-decoration-none">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>${{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'active' ? 'primary' : 'secondary') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No orders found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody> --}}
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Gigs -->
    @if ($pendingGigs->isNotEmpty())
        <div class="row mt-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">Pending Gig Approvals</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Gig Title</th>
                                        <th>Expert</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingGigs as $gig)
                                        <tr>
                                            <td>{{ $gig->title }}</td>
                                            <td>{{ $gig->user->profile->full_name }}</td>
                                            <td>{{ $gig->category->name }}</td>
                                            <td>${{ number_format($gig->price, 2) }}</td>
                                            <td>{{ $gig->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-success">Approve</a>
                                                <a href="#" class="btn btn-sm btn-danger">Reject</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    {{-- <script>
        // User Roles Doughnut Chart
        const userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
        new Chart(userRolesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Directors', 'Evaluators', 'Referees'],
                datasets: [{
                    data: [{{ $userStats['directors'] }}, {{ $userStats['evaluators'] }},
                        {{ $userStats['referees'] }}
                    ],
                    backgroundColor: ['#dc3545', '#0d6efd', '#198754'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Game Slot Status Pie Chart
        const gameSlotCtx = document.getElementById('gameSlotChart').getContext('2d');
        new Chart(gameSlotCtx, {
            type: 'pie',
            data: {
                labels: ['Available', 'Assigned', 'Completed', 'Blocked'],
                datasets: [{
                    data: [
                        {{ $gameSlotStats['available'] }},
                        {{ $gameSlotStats['assigned'] }},
                        {{ $gameSlotStats['completed'] }},
                        {{ $gameSlotStats['blocked'] }}
                    ],
                    backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Camp Status Doughnut Chart
        const campStatusCtx = document.getElementById('campStatusChart').getContext('2d');
        new Chart(campStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Upcoming', 'Ongoing', 'Completed'],
                datasets: [{
                    data: [{{ $campStats['upcoming'] }}, {{ $campStats['ongoing'] }},
                        {{ $campStats['completed'] }}
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Sports Types Bar Chart
        const sportsTypeCtx = document.getElementById('sportsTypeChart').getContext('2d');
        new Chart(sportsTypeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($sportsStats['camps_by_sport']->pluck('sports_type_name')) !!},
                datasets: [{
                    label: 'Camps',
                    data: {!! json_encode($sportsStats['camps_by_sport']->pluck('total')) !!},
                    backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545', '#0d6efd', '#6c757d',
                        '#fd7e14', '#6610f2', '#20c997', '#adb5bd', '#fd7e14', '#20c997'
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Monthly Camps Line Chart
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthlyCampsData = Array(12).fill(0);
        @foreach ($campStats['monthly'] as $month)
            monthlyCampsData[{{ $month->month - 1 }}] = {{ $month->count }};
        @endforeach

        const monthlyCampsCtx = document.getElementById('monthlyCampsChart').getContext('2d');
        new Chart(monthlyCampsCtx, {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Camps Created',
                    data: monthlyCampsData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Schedule Donut Chart
        const scheduleDonutCtx = document.getElementById('scheduleDonutChart').getContext('2d');
        new Chart(scheduleDonutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Published', 'Draft'],
                datasets: [{
                    data: [{{ $scheduleStats['published'] }}, {{ $scheduleStats['draft'] }}],
                    backgroundColor: ['#198754', '#ffc107'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Weekly Game Slots Area Chart
        const weeklyLabels = {!! json_encode($gameSlotStats['weekly_slots']->pluck('date')->map(fn($d) => date('M d', strtotime($d)))) !!};
        const weeklyData = {!! json_encode($gameSlotStats['weekly_slots']->pluck('count')) !!};

        const weeklyGameSlotsCtx = document.getElementById('weeklyGameSlotsChart').getContext('2d');
        new Chart(weeklyGameSlotsCtx, {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Game Slots',
                    data: weeklyData,
                    borderColor: '#0dcaf0',
                    backgroundColor: 'rgba(13, 202, 240, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0dcaf0',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script> --}}
@endpush

@push('styles')
    <style>
        <styl>.stats-card {
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-primary-transparent {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .bg-secondary-transparent {
            background-color: rgba(108, 117, 125, 0.1);
        }

        .bg-info-transparent {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .bg-warning-transparent {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
        }
    </style>
@endpush
