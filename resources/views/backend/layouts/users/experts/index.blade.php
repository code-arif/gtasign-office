@extends('backend.app')

@section('title', 'Experts Management')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Experts Management</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.experts.export', request()->query()) }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.experts.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Name, email, username..."
                                value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>
                                    Suspended</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.experts.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Experts Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Expert</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Gigs</th>
                                <th>Orders</th>
                                <th>Earnings</th>
                                <th>Joined</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($experts as $expert)
                                <tr>
                                    <td>{{ $expert->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $expert->profile->avatar_url }}" class="rounded-circle me-2"
                                                width="40" height="40" alt="{{ $expert->profile->full_name }}">
                                            <div>
                                                <div class="fw-semibold">{{ $expert->profile->full_name }}</div>
                                                <small class="text-muted">@{{ $expert - > profile - > username }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $expert->email }}</div>
                                        @if ($expert->phone)
                                            <small class="text-muted">{{ $expert->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $expert->status === 'active' ? 'success' : ($expert->status === 'suspended' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($expert->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $expert->gigs()->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $expert->sellerOrders()->count() }}</span>
                                    </td>
                                    <td>
                                        ${{ number_format($expert->sellerOrders()->completed()->sum('seller_earnings'), 2) }}
                                    </td>
                                    <td>
                                        <small>{{ $expert->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('admin.experts.show', $expert->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal" data-bs-target="#statusModal{{ $expert->id }}"
                                                title="Change Status">
                                                <i class="bi bi-gear"></i>
                                            </button>

                                            @if (!$expert->sellerOrders()->active()->exists())
                                                <form action="{{ route('admin.experts.destroy', $expert->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this expert?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Status Update Modal -->
                                <div class="modal fade" id="statusModal{{ $expert->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Expert Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.experts.update-status', $expert->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="active"
                                                                {{ $expert->status === 'active' ? 'selected' : '' }}>Active
                                                            </option>
                                                            <option value="inactive"
                                                                {{ $expert->status === 'inactive' ? 'selected' : '' }}>
                                                                Inactive</option>
                                                            <option value="suspended"
                                                                {{ $expert->status === 'suspended' ? 'selected' : '' }}>
                                                                Suspended</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason (for suspension)</label>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason if suspending..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No experts found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($experts->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $experts->firstItem() }} to {{ $experts->lastItem() }} of {{ $experts->total() }}
                            experts
                        </div>
                        {{ $experts->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
