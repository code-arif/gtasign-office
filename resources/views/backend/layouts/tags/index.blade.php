@extends('backend.app', ['title' => 'Tags'])

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h3>Tags</h3>
            <button class="btn btn-primary btn-sm" onclick="openCreateModal()">Add Tag</button>
        </div>

        <div class="card-body">
            <table class="table table-bordered" id="tags-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
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
                        <h5 id="modalTitle">Create Tag</h5>
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
