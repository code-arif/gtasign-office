@extends('backend.app')

@section('title', 'AI Security Section')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- PAGE HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Home - AI Security Section</h1>
                    </div>
                </div>

                <div class="row">

                    {{-- SECTION HEADER UPDATE --}}
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">

                                <form method="POST" action="{{ route('admin.cms.home.ai-security.update') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label>Main Title</label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ $header->title ?? '' }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Subtitle</label>
                                        <textarea name="sub_title" class="form-control" rows="5">{{ $header->sub_title ?? '' }}</textarea>
                                    </div>

                                    <button class="btn btn-primary">Save Change</button>
                                </form>

                            </div>
                        </div>
                    </div>


                    {{-- ITEM LIST --}}
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Card Items</h3>
                                <div class="card-options ms-auto">
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#itemModal" id="addItemBtn">
                                        Add Card
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Points</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



    {{-- MODAL --}}
    <div class="modal fade" id="itemModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="itemForm">
                    <input type="hidden" id="itemID">

                    <div class="modal-header">
                        <h5 class="modal-title">Add Card</h5>
                    </div>

                    <div class="modal-body">

                        <div class="form-group">
                            <label>Card Title</label>
                            <input type="text" class="form-control" id="item_title">
                        </div>

                        <div class="form-group">
                            <label>Bullet Points (One Per Line)</label>
                            <textarea class="form-control" id="item_points" rows="6"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <script>
        $(function() {

            let table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.cms.home.ai-security.items') }}",
                columns: [{
                        data: 'DT_RowIndex'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'points'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // ADD
            $('#addItemBtn').click(function() {
                $('#itemID').val('');
                $('#itemForm')[0].reset();
            });

            // SUBMIT
            $('#itemForm').submit(function(e) {
                e.preventDefault();

                let id = $('#itemID').val();

                let url = id ?
                    "{{ route('admin.cms.home.ai-security.item.update', ':id') }}".replace(':id', id) :
                    "{{ route('admin.cms.home.ai-security.item.store') }}";

                $.post(url, {
                    _token: "{{ csrf_token() }}",
                    title: $('#item_title').val(),
                    points: $('#item_points').val().split('\n')
                }, function() {
                    $('#itemModal').modal('hide');
                    table.ajax.reload();
                });
            });

        });

        function editItem(id) {
            $.get("{{ route('admin.cms.home.ai-security.item.edit', ':id') }}".replace(':id', id), function(res) {

                $('#itemID').val(res.id);
                $('#item_title').val(res.title);
                $('#item_points').val(res.points.join("\n"));

                $('#itemModal').modal('show');
            });
        }

        function deleteItem(id) {
            if (confirm('Delete?')) {
                $.ajax({
                    url: "{{ route('admin.cms.home.ai-security.item.destroy', ':id') }}".replace(':id', id),
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#datatable').DataTable().ajax.reload();
                    }
                });
            }
        }
    </script>
@endpush
