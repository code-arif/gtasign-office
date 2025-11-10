@extends('backend.app', ['title' => 'Subscription Plans'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- PAGE HEADER --}}
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h1 class="page-title mb-0 me-3">Subscription Plans</h1>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Subscription</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Plans</li>
                        </ol>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal"
                            data-bs-target="#createPlanModal">
                            <i class="fa fa-plus me-1"></i> Create New Plan
                        </button>
                    </div>
                </div>

                {{-- PAGE HEADER --}}

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table id="plansTable" class="table table-bordered text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Plan Name</th>
                                                <th>Price</th>
                                                <th>Duration</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- DataTables will load plans data here --}}
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Create Plan Modal --}}
                <div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.subscriptions-plans.store') }}" method="POST" id="createPlanForm">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createPlanModalLabel">Create New Subscription Plan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Plan Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input type="number" step="0.01" class="form-control" id="price"
                                            name="price" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration</label>
                                        <select class="form-select" id="duration" name="interval" required>
                                            <option value="">Select duration</option>
                                            <option value="day">Daily</option>
                                            <option value="month">Monthly</option>
                                            <option value="week">Weekly</option>

                                            <option value="year">Yearly</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Create Plan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- End Create Plan Modal --}}

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#plansTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.subscriptions-plans.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'interval',
                        name: 'interval'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                lengthMenu: [10, 25, 50],
                responsive: true,
            });
        });
    </script>
@endpush
