@extends('backend.app', ['title' => 'Products Variant'])

@section('title', 'Dashboard || Products Variant')
@section('content')

    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Products</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Products</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Variant List</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">

                        <div class="tab-content">
                            <div class="tab-pane active show" id="editProfile">
                                <div class="card">
                                    <div class="card-body border-0">
                                        @if (session('t-success'))
                                            <div class="alert alert-success">
                                                {{ session('t-success') }}
                                            </div>
                                        @endif
                                        @if (session('t-error'))
                                            <div class="alert alert-danger">
                                                {{ session('t-error') }}
                                            </div>
                                        @endif

                                        <h4>Variants for Product: {{ $product->name }}</h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="variant-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Code</th>
                                                        <th>Color Name</th>
                                                        <th>Price</th>
                                                        <th>Stock</th>
                                                        <th>Image</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
<script>
    $(document).ready(function() {
        $('#variant-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.product.variant', $product->id) }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'code', name: 'code' },
                { data: 'color_name', name: 'color_name' },
                { data: 'price', name: 'price' },
                { data: 'stock', name: 'stock' },
                {
                    data: 'image_url',
                    name: 'image_url',
                    render: function(data, type, row) {
                        if (data) {
                            return '<img src="' + data + '" alt="Variant Image" width="50px" height="50px" style="margin-left:20px;">';
                        } else {
                            return '<img src="' + '{{ asset('default/logo.png') }}' + '" alt="No Image" width="50px" height="50px" style="margin-left:20px;">';
                        }
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });
    });

    // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this record?',
                text: 'If you delete this, it will be gone forever.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        }

        // Delete Button
        function deleteItem(id) {
            NProgress.start();
            let url = "{{ route('admin.product.variant.destroy', ':id') }}";
            let csrfToken = '{{ csrf_token() }}';
            $.ajax({
                type: "GET",
                url: url.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    NProgress.done();
                    toastr.success(resp.message);
                    $('#variant-table').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }


</script>
@endpush
