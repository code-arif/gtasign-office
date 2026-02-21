@extends('backend.app')

@section('title', 'Tags')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tags</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <span> &nbsp; >> &nbsp;</span>
                            <li class="breadcrumb-item active" aria-current="page">Tags</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">Tags List</h3>
                                <div class="card-options">
                                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                                        onclick="openCreateModal()">
                                        <i class="fe fe-plus me-1"></i> Add Tags
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom w-100" id="tags-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row -->
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="tagModal">
        <div class="modal-dialog">
            <form id="tagForm">
                @csrf
                <input type="hidden" id="tag_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Create Tag</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                    </div>

                    <div class="modal-body">
                        <input type="text" class="form-control" id="name" placeholder="Tag name">
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(function() {
            let table = $('#tags-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.tags.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            window.openCreateModal = function() {
                $('#tagForm')[0].reset();
                $('#tag_id').val('');
                $('#modalTitle').text('Create Tag');
                $('#tagModal').modal('show');
            };

            window.openEditModal = function(id) {
                $.get("{{ url('admin/tags/get') }}/" + id, function(res) {
                    $('#tag_id').val(res.tag.id);
                    $('#name').val(res.tag.name);
                    $('#modalTitle').text('Edit Tag');
                    $('#tagModal').modal('show');
                });
            };

            $('#tagForm').submit(function(e) {
                e.preventDefault();

                let id = $('#tag_id').val();
                let url = id ?
                    "{{ url('admin/tags/update') }}/" + id :
                    "{{ route('admin.tags.store') }}";

                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url,
                    method,
                    data: {
                        _token: "{{ csrf_token() }}",
                        name: $('#name').val()
                    },
                    success(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#tagModal').modal('hide');
                            table.ajax.reload();
                        } else {
                            $('#name').addClass('is-invalid');
                            $('#name-error').text(res.errors.name[0]);
                        }
                    }
                });
            });

            window.deleteTag = function(id) {
                Swal.fire({
                    title: 'Delete?',
                    icon: 'warning',
                    showCancelButton: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/tags/delete') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success(res) {
                                toastr.success(res.message);
                                table.ajax.reload();
                            }
                        });
                    }
                });
            };
        });
    </script>
@endpush
