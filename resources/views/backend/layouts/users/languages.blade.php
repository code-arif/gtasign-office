@extends('backend.app')

@section('title','Languages')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Languages</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <span> &nbsp; >> &nbsp;</span>
                            <li class="breadcrumb-item active" aria-current="page">Languages</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">Languages List</h3>
                                <div class="card-options">
                                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                                        onclick="openCreateModal()">
                                        <i class="fe fe-plus me-1"></i> Add Language
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom w-100"
                                        id="languages-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Display Name</th>
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
    <div class="modal fade" id="languageModal">
        <div class="modal-dialog">
            <form id="languageForm">
                @csrf
                <input type="hidden" id="language_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Create Language</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Language Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="e.g. English, বাংলা">
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="display_name" class="form-label">Display Name (optional)</label>
                            <input type="text" class="form-control" id="display_name"
                                placeholder="e.g. English (US), Bangla">
                        </div>
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
            let table = $('#languages-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.languages.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'display_name'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            window.openCreateModal = function() {
                $('#languageForm')[0].reset();
                $('#language_id').val('');
                $('#modalTitle').text('Create Language');
                $('#languageModal').modal('show');
            };

            window.openEditModal = function(id) {
                $.get("{{ url('admin/languages/get') }}/" + id, function(res) {
                    $('#language_id').val(res.language.id);
                    $('#name').val(res.language.name);
                    $('#display_name').val(res.language.display_name || '');
                    $('#modalTitle').text('Edit Language');
                    $('#languageModal').modal('show');
                });
            };

            $('#languageForm').submit(function(e) {
                e.preventDefault();

                let id = $('#language_id').val();
                let url = id ?
                    "{{ url('admin/languages/update') }}/" + id :
                    "{{ route('admin.languages.store') }}";

                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url,
                    method,
                    data: {
                        _token: "{{ csrf_token() }}",
                        name: $('#name').val(),
                        display_name: $('#display_name').val()
                    },
                    success(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#languageModal').modal('hide');
                            table.ajax.reload();
                        } else {
                            $('#name').addClass('is-invalid');
                            $('#name-error').text(res.errors.name ? res.errors.name[0] : '');
                        }
                    },
                    error(xhr) {
                        // optional: better error handling
                        toastr.error('Something went wrong!');
                    }
                });
            });

            window.deleteLanguage = function(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/languages/delete') }}/" + id,
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
