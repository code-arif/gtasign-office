@extends('backend.app')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom:60px">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <p class="text-muted">Welcome back! Here's what's happening today.</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- STATISTICS CARDS ROW -->
                <div class="row">
                    <!-- Total Users Card -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card overflow-hidden stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Users</h6>
                                        <h2 class="mb-0 fw-bold">{{ $userStats['total'] }}</h2>
                                        <small class="text-success">
                                            <i class="fa fa-arrow-up"></i> {{ $userStats['active'] }} Active
                                        </small>
                                    </div>
                                    <div class="icon-box bg-primary-transparent">
                                        <i class="fa fa-users text-primary fs-30"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Camps Card -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card overflow-hidden stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Camps</h6>
                                        <h2 class="mb-0 fw-bold">{{ $campStats['total'] }}</h2>
                                        <small class="text-info">
                                            <i class="fa fa-clock"></i> {{ $campStats['ongoing'] }} Ongoing
                                        </small>
                                    </div>
                                    <div class="icon-box bg-secondary-transparent">
                                        <i class="fa fa-campground text-secondary fs-30"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Game Slots Card -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card overflow-hidden stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Game Slots</h6>
                                        <h2 class="mb-0 fw-bold">{{ $gameSlotStats['total'] }}</h2>
                                        <small class="text-warning">
                                            <i class="fa fa-calendar"></i> {{ $gameSlotStats['today'] }} Today
                                        </small>
                                    </div>
                                    <div class="icon-box bg-info-transparent">
                                        <i class="fa fa-gamepad text-info fs-30"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Crews Card -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card overflow-hidden stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Active Crews</h6>
                                        <h2 class="mb-0 fw-bold">{{ $crewStats['active'] }}</h2>
                                        <small class="text-success">
                                            <i class="fa fa-users"></i> {{ $crewStats['total_members'] }} Members
                                        </small>
                                    </div>
                                    <div class="icon-box bg-warning-transparent">
                                        <i class="fa fa-user-friends text-warning fs-30"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DETAILED STATS ROW -->
                <div class="row">
                    <!-- User Roles Breakdown -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">User Roles</h4>
                            </div>
                            <div class="card-body">
                                <div class="role-stats">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span><i class="fa fa-user-shield text-danger"></i> Directors</span>
                                        <strong>{{ $userStats['directors'] }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span><i class="fa fa-user-check text-primary"></i> Evaluators</span>
                                        <strong>{{ $userStats['evaluators'] }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span><i class="fa fa-user-tie text-success"></i> Referees</span>
                                        <strong>{{ $userStats['referees'] }}</strong>
                                    </div>
                                </div>
                                <div class="chart-container" style="position: relative; height: 200px;">
                                    <canvas id="userRolesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Game Slot Status -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">Game Slot Status</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height: 200px;">
                                    <canvas id="gameSlotChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-success">Available</span>
                                        <strong>{{ $gameSlotStats['available'] }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-warning">Assigned</span>
                                        <strong>{{ $gameSlotStats['assigned'] }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-info">Completed</span>
                                        <strong>{{ $gameSlotStats['completed'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Camp Status -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">Camp Status</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height: 200px;">
                                    <canvas id="campStatusChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-primary">Upcoming</span>
                                        <strong>{{ $campStats['upcoming'] }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-success">Ongoing</span>
                                        <strong>{{ $campStats['ongoing'] }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">Completed</span>
                                        <strong>{{ $campStats['completed'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sports Types Distribution -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">Sports Types</h4>
                            </div>
                            <div class="card-body p-3">
                                <div class="text-center mb-5">
                                    <div class="d-flex justify-content-center align-item-center p-3 gap-2">
                                        <div class="p-3" style="border: 1px solid rgb(0, 162, 255); border-radius: 5px">
                                            <h2 class="fw-bold text-primary mb-0">{{ $sportsStats['total'] }}</h2>
                                            <small class="text-muted">Total Sports</small>
                                        </div>
                                        <div class="p-3" style="border: 1px solid rgb(0, 196, 0); border-radius: 5px;">
                                            <h2 class="fw-bold text-success mb-0">{{ $sportsStats['active'] }}</h2>
                                            <small class="text-muted">Active Sports</small>
                                        </div>
                                        <div class="p-3" style="border: 1px solid red; border-radius: 5px;">
                                            <h2 class="fw-bold text-danger mb-0">{{ $sportsStats['inactive'] }}</h2>
                                            <small class="text-muted">In Active Sports</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                                    <canvas id="sportsTypeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS ROW -->
                <div class="row mt-5">
                    <!-- Monthly Camps Trend -->
                    <div class="col-lg-12">
                        <div class="card" style="height: 90%">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title">Monthly Camps Trend</h4>
                                <span class="badge bg-primary">{{ date('Y') }}</span>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyCampsChart" height="180"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Status -->
                    {{-- <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">Schedule Overview</h4>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <h1 class="display-4 fw-bold text-primary">{{ $scheduleStats['total'] }}</h1>
                                    <p class="text-muted">Total Schedules</p>
                                </div>
                                <div class="progress mb-3" style="height: 25px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $scheduleStats['total'] > 0 ? ($scheduleStats['published'] / $scheduleStats['total']) * 100 : 0 }}%">
                                        Published: {{ $scheduleStats['published'] }}
                                    </div>
                                    <div class="progress-bar bg-warning" role="progressbar"
                                        style="width: {{ $scheduleStats['total'] > 0 ? ($scheduleStats['draft'] / $scheduleStats['total']) * 100 : 0 }}%">
                                        Draft: {{ $scheduleStats['draft'] }}
                                    </div>
                                </div>
                                <canvas id="scheduleDonutChart" height="180"></canvas>
                            </div>
                        </div>
                    </div> --}}
                </div>

                <!-- GAME SLOTS WEEKLY TREND -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title">Game Slots Activity (Last 30 Days)</h4>
                                <div>
                                    <span class="badge bg-info me-2">This Week: {{ $gameSlotStats['this_week'] }}</span>
                                    <span class="badge bg-success">Today: {{ $gameSlotStats['today'] }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="weeklyGameSlotsChart" height="80"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RECENT ACTIVITIES AND TOP CREWS -->
                <div class="row mb-3">
                    <!-- Recent Camps -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">Recent Camps</h4>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Camp Name</th>
                                                <th>Sport</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentCamps as $camp)
                                                <tr>
                                                    <td>{{ $camp->camp_name }}</td>
                                                    <td>{{ $camp->sports_type_name }}</td>
                                                    <td>{{ $camp->location }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $camp->status == 'active' ? 'success' : 'secondary' }}">
                                                            {{ ucfirst($camp->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No recent camps</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Crews by Members -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">Top Crews</h4>
                            </div>
                            <div class="card-body">
                                @forelse($crewStats['crew_distribution'] as $crew)
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                        <div>
                                            <h6 class="mb-1">{{ $crew->name }}</h6>
                                            <small class="text-muted">{{ $crew->description ?? 'No description' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary fs-14">{{ $crew->members_count }} Members</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-muted">No crews available</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
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
    </script>
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
