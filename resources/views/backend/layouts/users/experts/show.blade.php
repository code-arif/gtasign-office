@extends('backend.app')

@section('title', 'Expert Details - ' . $expert->profile->full_name)

@section('content')
    <div class="container-fluid">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('admin.experts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Experts
            </a>
        </div>

        <!-- Expert Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start">
                            <img src="{{ $expert->profile->avatar_url }}" class="rounded-circle me-3" width="80"
                                height="80" alt="{{ $expert->profile->full_name }}">
                            <div>
                                <h3 class="mb-1">{{ $expert->profile->full_name }}</h3>
                                <p class="text-muted mb-2">@{{ $expert - > profile - > username }}</p>

                                @if ($expert->profile->tagline)
                                    <p class="mb-2">{{ $expert->profile->tagline }}</p>
                                @endif

                                <div class="d-flex gap-3 align-items-center">
                                    <span
                                        class="badge bg-{{ $expert->status === 'active' ? 'success' : ($expert->status === 'suspended' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($expert->status) }}
                                    </span>

                                    <div>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <strong>{{ number_format($stats['average_rating'], 1) }}</strong>
                                        <span class="text-muted">({{ $stats['total_reviews'] }} reviews)</span>
                                    </div>

                                    <span class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Joined {{ $expert->created_at->format('M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-outline-warning mb-2" data-bs-toggle="modal"
                            data-bs-target="#statusModal">
                            <i class="bi bi-gear me-1"></i> Change Status
                        </button>

                        @if (!$expert->sellerOrders()->active()->exists())
                            <form action="{{ route('admin.experts.destroy', $expert->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expert?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger mb-2">
                                    <i class="bi bi-trash me-1"></i> Delete Expert
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Gigs</h6>
                        <h3 class="mb-0">{{ $stats['total_gigs'] }}</h3>
                        <small class="text-success">{{ $stats['active_gigs'] }} active</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Orders</h6>
                        <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                        <small class="text-primary">{{ $stats['active_orders'] }} active</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Completed Orders</h6>
                        <h3 class="mb-0">{{ $stats['completed_orders'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Earnings</h6>
                        <h3 class="mb-0">${{ number_format($stats['total_earnings'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#gigs">Gigs ({{ $stats['total_gigs'] }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#orders">Orders ({{ $stats['total_orders'] }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#education">Education</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#certifications">Certifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#skills">Skills</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th width="150">Email:</th>
                                        <td>{{ $expert->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $expert->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $expert->profile->address ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email Verified:</th>
                                        <td>
                                            @if ($expert->email_verified_at)
                                                <span class="badge bg-success">Verified</span>
                                                <small
                                                    class="text-muted">{{ $expert->email_verified_at->format('M d, Y') }}</small>
                                            @else
                                                <span class="badge bg-warning">Not Verified</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Account Details</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th width="150">Status:</th>
                                        <td>
                                            <span
                                                class="badge bg-{{ $expert->status === 'active' ? 'success' : ($expert->status === 'suspended' ? 'danger' : 'secondary') }}">
                                                {{ ucfirst($expert->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Stripe Connect:</th>
                                        <td>
                                            @if ($expert->stripe_onboarding_completed)
                                                <span class="badge bg-success">Connected</span>
                                            @else
                                                <span class="badge bg-warning">Not Connected</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Available Balance:</th>
                                        <td>${{ number_format($expert->available_balance, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pending Clearance:</th>
                                        <td>${{ number_format($expert->pending_clearance, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Member Since:</th>
                                        <td>{{ $expert->created_at->format('M d, Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($expert->profile->biography)
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Biography</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $expert->profile->biography }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gigs Tab -->
            <div class="tab-pane fade" id="gigs">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Gig Title</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Orders</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expert->gigs as $gig)
                                        <tr>
                                            <td>{{ $gig->title }}</td>
                                            <td>{{ $gig->category->name }}</td>
                                            <td>${{ number_format($gig->price, 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $gig->status === 'active' ? 'success' : ($gig->status === 'pending_approval' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $gig->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $gig->orders }}</td>
                                            <td>{{ $gig->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No gigs found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Tab -->
            <div class="tab-pane fade" id="orders">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Gig</th>
                                        <th>Buyer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expert->sellerOrders->take(10) as $order)
                                        <tr>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->gig->title }}</td>
                                            <td>{{ $order->buyer->profile->full_name }}</td>
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
                                            <td colspan="6" class="text-center text-muted py-4">No orders found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education Tab -->
            <div class="tab-pane fade" id="education">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @forelse($expert->education as $edu)
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-mortarboard text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $edu->degree }} in {{ $edu->major }}</h6>
                                    <p class="text-muted mb-1">{{ $edu->institution_name }}</p>
                                    <small class="text-muted">
                                        @if ($edu->country)
                                            <i class="bi bi-geo-alt me-1"></i>{{ $edu->country }} •
                                        @endif
                                        <i class="bi bi-calendar3 me-1"></i>{{ $edu->graduation_year }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">No education records found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Certifications Tab -->
            <div class="tab-pane fade" id="certifications">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @forelse($expert->certifications as $cert)
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-award text-success fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $cert->name }}</h6>
                                    <p class="text-muted mb-1">{{ $cert->awarded_by }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $cert->year }}
                                    </small>
                                    @if ($cert->file_path)
                                        <div class="mt-2">
                                            <a href="{{ $cert->file_url }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download me-1"></i> View Certificate
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">No certifications found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Skills Tab -->
            <div class="tab-pane fade" id="skills">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @forelse($expert->experiences as $exp)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <h6 class="mb-0">{{ $exp->skill_name }}</h6>
                                </div>
                                <div>
                                    <span
                                        class="badge bg-{{ $exp->level === 'expert' ? 'success' : ($exp->level === 'intermediate' ? 'primary' : 'secondary') }}">
                                        {{ ucfirst($exp->level) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">No skills found</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Expert Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.experts.update-status', $expert->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $expert->status === 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ $expert->status === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                                <option value="suspended" {{ $expert->status === 'suspended' ? 'selected' : '' }}>
                                    Suspended</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (for suspension)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason if suspending..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
