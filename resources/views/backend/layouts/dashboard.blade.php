@extends('backend.app')

@section('content')
    <!--app-content open-->
   hello
    <!-- CONTAINER CLOSED -->
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
