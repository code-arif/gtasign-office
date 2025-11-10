@extends('backend.app', ['title' => 'Plan Features'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- PAGE HEADER --}}
          <div class="page-header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <h1 class="page-title mb-0 me-3">Plan Features</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#">Subscription</a></li>
            <li class="breadcrumb-item active" aria-current="page">Plan Features</li>
        </ol>
    </div>
    <div>
        <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal"
            data-bs-target="#createFeatureModal">
            <i class="fa fa-plus me-1"></i> Create New Feature
        </button>
    </div>
</div>

                {{-- PAGE HEADER --}}

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table id="planFeaturesTable" class="table table-bordered text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Plan Name</th>
                                                <th>Feature</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- DataTables will populate --}}
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Create Plan Feature Modal --}}
                <div class="modal fade" id="createFeatureModal" tabindex="-1" aria-labelledby="createFeatureModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.planfeatures.store') }}" method="POST" id="createFeatureForm">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createFeatureModalLabel">Create New Plan Feature</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="plan_id" class="form-label">Plan</label>
                                        <select class="form-select" id="plan_id" name="plan_id" required>
                                            <option value="">Select Plan</option>
                                            @foreach ($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="feature" class="form-label">Feature</label>
                                        <input type="text" class="form-control" id="feature" name="feature" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Create Feature</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- End Create Plan Feature Modal --}}

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#planFeaturesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.planfeatures.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'plan_name',
                        name: 'plan_name'
                    },
                    {
                        data: 'feature',
                        name: 'feature'
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
