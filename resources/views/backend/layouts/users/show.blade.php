@extends('backend.app', ['title' => 'User Details'])

@push('styles')
    <style>
        .profile-cover {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px 8px 0 0;
            position: relative;
        }

        .profile-avatar {
            position: absolute;
            bottom: -50px;
            left: 30px;
            border: 5px solid #fff;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .profile-header {
            padding-top: 60px;
            padding-left: 30px;
            padding-bottom: 20px;
        }

        .info-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .info-card:hover {
            transform: translateX(5px);
        }

        .badge-role {
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 20px;
        }

        .activity-timeline {
            position: relative;
            padding-left: 30px;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }

        .activity-item {
            position: relative;
            margin-bottom: 20px;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #007bff;
        }

        .stats-box {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.3s;
        }

        .stats-box:hover {
            background: #e9ecef;
            transform: translateY(-3px);
        }

        .stats-number {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px ">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">User Details</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.manage.index') }}">Users</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View</li>
                        </ol>
                    </div>
                </div>

                <!-- PROFILE CARD -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="profile-cover">
                                @php
                                    $fullName = trim($user->first_name . ' ' . $user->last_name);
                                    $avatar = $user->avatar
                                        ? asset($user->avatar)
                                        : 'https://ui-avatars.com/api/?name=' .
                                            urlencode($fullName) .
                                            '&size=200&background=random';
                                @endphp
                                <img src="{{ $avatar }}" class="profile-avatar" alt="{{ $fullName }}">
                            </div>
                            <div class="card-body profile-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h3 class="mb-1">{{ $fullName ?: 'N/A' }}</h3>
                                        <p class="text-muted mb-2">
                                            <i class="fe fe-at me-1"></i>{{ $user->username }}
                                        </p>
                                        <div class="mb-3">
                                            @forelse ($user->roles as $role)
                                                @php
                                                    $colors = [
                                                        'Admin' => 'danger',
                                                        'Director' => 'primary',
                                                        'Referee' => 'success',
                                                        'Evaluator' => 'info',
                                                    ];
                                                    $color = $colors[$role->name] ?? 'secondary';
                                                @endphp
                                                <span
                                                    class="badge badge-role bg-{{ $color }}">{{ $role->name }}</span>
                                            @empty
                                                <span class="badge badge-role bg-secondary">No Role Assigned</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.manage.index') }}" class="btn btn-secondary">
                                                <i class="fe fe-arrow-left me-1"></i> Back to List
                                            </a>
                                            @if ($user->status == 'active')
                                                <button onclick="showStatusChangeAlert({{ $user->id }})"
                                                    class="btn btn-warning">
                                                    <i class="fe fe-lock me-1"></i> Deactivate
                                                </button>
                                            @else
                                                <button onclick="showStatusChangeAlert({{ $user->id }})"
                                                    class="btn btn-success">
                                                    <i class="fe fe-unlock me-1"></i> Activate
                                                </button>
                                            @endif
                                            <button onclick="showDeleteConfirm({{ $user->id }})"
                                                class="btn btn-danger">
                                                <i class="fe fe-trash-2 me-1"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STATISTICS -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stats-box">
                            <div class="stats-number">0</div>
                            <div class="text-muted">Sports Participated</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stats-box">
                            <div class="stats-number">0</div>
                            <div class="text-muted">Camps Attended</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stats-box">
                            <div class="stats-number">0</div>
                            <div class="text-muted">Events Joined</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stats-box">
                            <div class="stats-number">{{ $user->status == 'active' ? 'Active' : 'Inactive' }}</div>
                            <div class="text-muted">Account Status</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- LEFT COLUMN -->
                    <div class="col-xl-4 col-lg-5">
                        <!-- BASIC INFO -->
                        <div class="card info-card mb-4" style="border-left-color: #007bff;">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-user me-2"></i>Basic Information
                                </h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    {{-- <tr>
                                        <td class="text-muted" style="width: 40%;"><i class="fe fe-user me-2"></i>First Name
                                        </td>
                                        <td class="fw-semibold">{{ $user->first_name ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="fe fe-user me-2"></i>Last Name</td>
                                        <td class="fw-semibold">{{ $user->last_name ?: 'N/A' }}</td>
                                    </tr> --}}
                                    <tr>
                                        <td class="text-muted"><i class="fe fe-user-plus me-2"></i>Full Name</td>
                                        <td class="fw-semibold">{{ $user->first_name . ' ' . $user->last_name ?: 'N/A' }} </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="fa-solid fa-at me-2"></i>Username</td>
                                        <td class="fw-semibold">{{ $user->username }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- CONTACT INFO -->
                        <div class="card info-card mb-4" style="border-left-color: #28a745;">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-phone me-2"></i>Contact Information
                                </h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 30%;"><i class="fe fe-mail me-2"></i>Email</td>
                                        <td class="fw-semibold text-break">{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="fe fe-phone me-2"></i>Phone</td>
                                        <td class="fw-semibold">{{ $user->phone ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="fe fe-map-pin me-2"></i>Address</td>
                                        <td class="fw-semibold">{{ $user->address ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- AFFILIATE INFO -->
                        <div class="card info-card" style="border-left-color: #ffc107;">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-link me-2"></i>Affiliate Information
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Affiliate Link</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="affiliateLink"
                                            value="{{ $user->slug ? url('affiliate/' . $user->slug) : 'N/A' }}" readonly>
                                        <button class="btn btn-primary copy-btn"
                                            data-clipboard-text="{{ $user->slug ? url('affiliate/' . $user->slug) : '' }}"
                                            {{ $user->slug ? '' : 'disabled' }}>
                                            <i class="fe fe-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-xl-8 col-lg-7">
                        <!-- ACCOUNT DETAILS -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-settings me-2"></i>Account Details
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted">Account Status</label>
                                        <div class="mt-1">
                                            @if ($user->status == 'active')
                                                <span class="badge bg-success fs-14 px-3 py-2">
                                                    <i class="fe fe-check-circle me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger fs-14 px-3 py-2">
                                                    <i class="fe fe-x-circle me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted">Last Activity</label>
                                        <div class="mt-1 fw-semibold">
                                            @if ($user->last_activity_at)
                                                {{ $user->last_activity_at->format('d M Y, h:i A') }}
                                                <small
                                                    class="text-muted">({{ $user->last_activity_at->diffForHumans() }})</small>
                                            @else
                                                <span class="text-muted">Never logged in</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted">Account Created</label>
                                        <div class="mt-1 fw-semibold">{{ $user->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted">Last Updated</label>
                                        <div class="mt-1 fw-semibold">{{ $user->updated_at->format('d M Y, h:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BIOGRAPHY -->
                        @if ($user->biography)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="fe fe-file-text me-2"></i>Biography
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $user->biography }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- STRIPE INFORMATION -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-credit-card me-2"></i>Payment Information
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted">Stripe Customer ID</label>
                                        <div class="mt-1">
                                            @if ($user->stripe_customer_id)
                                                <code>{{ $user->stripe_customer_id }}</code>
                                            @else
                                                <span class="text-muted">Not connected</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted">Stripe Account ID</label>
                                        <div class="mt-1">
                                            @if ($user->stripe_account_id)
                                                <code>{{ $user->stripe_account_id }}</code>
                                            @else
                                                <span class="text-muted">Not connected</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ROLES & PERMISSIONS -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-shield me-2"></i>Roles & Permissions
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="text-muted mb-2">Assigned Roles</label>
                                    <div>
                                        @forelse ($user->roles as $role)
                                            @php
                                                $colors = [
                                                    'Admin' => 'danger',
                                                    'Director' => 'primary',
                                                    'Referee' => 'success',
                                                    'Evaluator' => 'info',
                                                ];
                                                $color = $colors[$role->name] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} fs-14 px-3 py-2 me-2">
                                                <i class="fe fe-award me-1"></i>{{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-muted">No roles assigned</span>
                                        @endforelse
                                    </div>
                                </div>
                                @if ($user->roles->isNotEmpty())
                                    <div>
                                        <label class="text-muted mb-2">Permissions</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @php
                                                $permissions = $user->getAllPermissions();
                                            @endphp
                                            @forelse($permissions as $permission)
                                                <span
                                                    class="badge bg-light text-dark border">{{ $permission->name }}</span>
                                            @empty
                                                <span class="text-muted">No specific permissions</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- PLACEHOLDER FOR FUTURE SECTIONS -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-activity me-2"></i>Recent Activity
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="activity-timeline">
                                    <div class="activity-item">
                                        <div class="text-muted small">Coming soon...</div>
                                        <div class="text-muted small mt-1">Sports participation history will appear here
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="text-muted small">Coming soon...</div>
                                        <div class="text-muted small mt-1">Camp attendance records will appear here</div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="text-muted small">Coming soon...</div>
                                        <div class="text-muted small mt-1">Event participation will appear here</div>
                                    </div>
                                </div>
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
        // Copy to Clipboard
        const copyBtns = document.querySelectorAll(".copy-btn");
        if (copyBtns.length > 0) {
            copyBtns.forEach(copyBtn => {
                copyBtn.addEventListener("click", async function() {
                    try {
                        const copyText = this.dataset.clipboardText;
                        if (copyText) {
                            await navigator.clipboard.writeText(copyText);
                            toastr.success('Copied to clipboard!');
                        }
                    } catch (error) {
                        console.error("Error copying text: ", error);
                        toastr.error('Failed to copy!');
                    }
                });
            });
        }

        // Status Change
        function showStatusChangeAlert(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to change the user status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    statusChange(id);
                }
            });
        }

        function statusChange(id) {
            NProgress.start();
            let url = "{{ route('admin.users.manage.status', ':id') }}";
            $.ajax({
                type: "GET",
                url: url.replace(':id', id),
                success: function(resp) {
                    NProgress.done();
                    if (resp.success) {
                        toastr.success(resp.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.responseJSON?.message || 'Something went wrong!');
                }
            });
        }

        // Delete User
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This user will be deleted permanently!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteUser(id);
                }
            });
        }

        function deleteUser(id) {
            NProgress.start();
            let url = "{{ route('admin.users.manage.destroy', ':id') }}";
            let csrfToken = '{{ csrf_token() }}';
            $.ajax({
                type: "DELETE",
                url: url.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    NProgress.done();
                    if (resp.success) {
                        toastr.success(resp.message);
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.users.manage.index') }}";
                        }, 1000);
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.responseJSON?.message || 'Failed to delete user!');
                }
            });
        }
    </script>
@endpush
