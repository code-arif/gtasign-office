@extends('backend.app')

@section('title', 'About Page CMS')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="container-fluid">

                {{-- PAGE HEADER --}}
                <div class="page-header">
                    <h1 class="page-title">About Page – CMS</h1>
                </div>

                <div class="row">

                    {{-- PAGE TITLE SECTION --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Page Title Section</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.cms.about.page-title.store') }}">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <label>Page Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ old('title', $pageTitle->title ?? '') }}" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Subtitle/Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ old('description', $pageTitle->description ?? '') }}</textarea>
                                    </div>

                                    <button class="btn btn-primary">
                                        <i class="fa fa-save"></i> Save Section
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- MISSION SECTION --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Our Mission Section</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.cms.about.mission.store') }}">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <label>Mission Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ old('title', $mission->title ?? '') }}" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Mission Description <span class="text-danger">*</span></label>
                                        <textarea name="description" class="form-control" rows="4" required>{{ old('description', $mission->description ?? '') }}</textarea>
                                    </div>

                                    <button class="btn btn-primary">
                                        <i class="fa fa-save"></i> Save Section
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- KEY TO EXCELLENCE SECTION --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">The Key to Excellence Section</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.cms.about.key-to-excellence.store') }}">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <label>Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ old('title', $keyToExcellence->title ?? '') }}" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Description <span class="text-danger">*</span></label>
                                        <textarea name="description" class="form-control" rows="4" required>{{ old('description', $keyToExcellence->description ?? '') }}</textarea>
                                    </div>

                                    <button class="btn btn-primary">
                                        <i class="fa fa-save"></i> Save Section
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- BOTTOM DESCRIPTION SECTION --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Bottom Description Section</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.cms.about.bottom-description.store') }}">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <label>Description <span class="text-danger">*</span></label>
                                        <textarea name="description" class="form-control" rows="5" required>{{ old('description', $bottomDescription->description ?? '') }}</textarea>
                                        <small class="text-muted">This appears at the bottom of the about page</small>
                                    </div>

                                    <button class="btn btn-primary">
                                        <i class="fa fa-save"></i> Save Section
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- OWNER INFO SECTION --}}
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Owner/Founder Information</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.cms.about.owner-info.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Owner Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                    value="{{ old('name', $owner->name ?? '') }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Designation <span class="text-danger">*</span></label>
                                                <input type="text" name="designation" class="form-control"
                                                    value="{{ old('designation', $owner->designation ?? '') }}"
                                                    placeholder="e.g., President/CEO" required>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label>Experience</label>
                                                <input type="text" name="experience" class="form-control"
                                                    value="{{ old('experience', $owner->experience ?? '') }}"
                                                    placeholder="e.g., 25+ Years Experience">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label>Bio/Description</label>
                                                <textarea name="bio" class="form-control" rows="4">{{ old('bio', $owner->bio ?? '') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label>Owner Image</label>
                                                <input type="file" name="image" class="form-control dropify"
                                                    data-default-file="{{ isset($owner->image) ? asset($owner->image) : asset('default/placeholder-image.avif') }}"
                                                    accept="image/*">
                                            </div>
                                        </div>

                                        {{-- Statistics Section --}}
                                        <div class="col-md-12">
                                            <h5 class="mt-3 mb-3">Statistics (3 Stats Display)</h5>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label>Stat 1 Value</label>
                                                <input type="text" name="stat_1_value" class="form-control"
                                                    value="{{ old('stat_1_value', $owner->stats[0]['value'] ?? '') }}"
                                                    placeholder="e.g., 25+">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Stat 1 Label</label>
                                                <input type="text" name="stat_1_label" class="form-control"
                                                    value="{{ old('stat_1_label', $owner->stats[0]['label'] ?? '') }}"
                                                    placeholder="e.g., Years Officiating">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label>Stat 2 Value</label>
                                                <input type="text" name="stat_2_value" class="form-control"
                                                    value="{{ old('stat_2_value', $owner->stats[1]['value'] ?? '') }}"
                                                    placeholder="e.g., NCAA">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Stat 2 Label</label>
                                                <input type="text" name="stat_2_label" class="form-control"
                                                    value="{{ old('stat_2_label', $owner->stats[1]['label'] ?? '') }}"
                                                    placeholder="e.g., Retired Division 1 Official">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label>Stat 3 Value</label>
                                                <input type="text" name="stat_3_value" class="form-control"
                                                    value="{{ old('stat_3_value', $owner->stats[2]['value'] ?? '') }}"
                                                    placeholder="e.g., 10+">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Stat 3 Label</label>
                                                <input type="text" name="stat_3_label" class="form-control"
                                                    value="{{ old('stat_3_label', $owner->stats[2]['label'] ?? '') }}"
                                                    placeholder="e.g., Conferences">
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary">
                                        <i class="fa fa-save"></i> Save Owner Info
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- FEATURE ITEMS --}}
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Feature Items</h3>
                                <button class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#itemModal">
                                    <i class="fa fa-plus"></i> Add Item
                                </button>
                            </div>

                            <div class="card-body">
                                <table class="table table-bordered" id="datatable">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th width="80">Image</th>
                                            <th>Title</th>
                                            <th width="120">Action</th>
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

    @include('backend.layouts.cms.about.modal')
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
    <script>
        $(document).ready(function() {

            // Initialize Dropify
            $('.dropify').dropify();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ===============================
            // DATATABLE LOAD
            // ===============================
            let table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.cms.about.items.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // ===============================
            // ADD ITEM MODAL RESET
            // ===============================
            $('[data-bs-target="#itemModal"]').on('click', function() {
                $('#itemForm')[0].reset();
                $('#itemID').val('');
                $('#itemModalLabel').text('Add Feature Item');
                $('.error-text').text('');

                $('#image_show_section').html(`
                <label>Image (Optional)</label>
                <input type="file" name="image" id="item_image"
                       class="form-control dropify"
                       data-default-file="{{ asset('default/placeholder-image.avif') }}"
                       accept="image/*">
            `);

                $('#item_image').dropify();
            });

            // ===============================
            // STORE / UPDATE ITEM
            // ===============================
            $('#itemForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#itemID').val();
                let url = id ?
                    "{{ route('admin.cms.about.item.update', ':id') }}".replace(':id', id) :
                    "{{ route('admin.cms.about.item.store') }}";

                let formData = new FormData(this);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#submitBtn').prop('disabled', true).html(
                            '<i class="fa fa-spinner fa-spin"></i> Saving...');
                    },
                    success: function(res) {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="fa fa-save"></i> Save');

                        if (res.status === 1) {
                            $('#itemModal').modal('hide');
                            table.ajax.reload();
                            toastr.success(res.message);
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="fa fa-save"></i> Save');

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('Something went wrong');
                        }
                    }
                });
            });

            // ===============================
            // EDIT ITEM
            // ===============================
            $(document).on('click', '.editItem', function() {
                let id = $(this).data('id');
                let url = "{{ route('admin.cms.about.item.edit', ':id') }}".replace(':id', id);

                $.get(url, function(res) {
                    $('#itemModalLabel').text('Edit Feature Item');
                    $('#itemID').val(res.data.id);
                    $('#item_title').val(res.data.title);
                    $('#item_description').val(res.data.description);

                    let imageUrl = res.data.image ?
                        "{{ asset('') }}" + res.data.image :
                        "{{ asset('default/placeholder-image.avif') }}";

                    $('#image_show_section').html(`
                    <label>Image (Leave empty to keep current)</label>
                    <input type="file" name="image" id="item_image"
                           class="form-control dropify"
                           data-default-file="${imageUrl}"
                           accept="image/*">
                `);

                    $('#item_image').dropify();
                    $('#itemModal').modal('show');
                });
            });

        });
    </script>

    <script>
        // ===============================
        // DELETE ITEM
        // ===============================
        function showDeleteConfirm(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This item will be deleted permanently!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        }

        function deleteItem(id) {
            let url = "{{ route('admin.cms.about.item.destroy', ':id') }}".replace(':id', id);

            $.ajax({
                type: "DELETE",
                url: url,
                success: function(res) {
                    $('#datatable').DataTable().ajax.reload();
                    Swal.fire('Deleted!', res.message, 'success');
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete item', 'error');
                }
            });
        }
    </script>
@endpush
