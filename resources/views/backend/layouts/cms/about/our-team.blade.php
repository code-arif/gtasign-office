@extends('backend.app')

@section('title', 'About Page CMS')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="container-fluid">

                {{-- PAGE HEADER --}}
                <div class="page-header">
                    <h1 class="page-title">About Page – CMS</h1>
                    <div class="ms-auto">
                        <span class="badge bg-primary">All Sections Editable</span>
                    </div>
                </div>
                {{-- ========================================
                    OUR TEAM SECTION
                ======================================== --}}
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h3 class="card-title">
                                    <i class="fa fa-users"></i> Meet Our Team Section
                                </h3>
                            </div>
                        </div>
                    </div>

                    {{-- TEAM SECTION HEADER --}}
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Team Section Header</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.cms.about.team-header.store') }}">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <label>Section Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ old('title', $teamHeader->title ?? 'Meet Our Team') }}" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Section Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ old('description', $teamHeader->description ?? '') }}</textarea>
                                        <small class="text-muted">This appears below the "Meet Our Team" heading</small>
                                    </div>

                                    <button class="btn btn-info">
                                        <i class="fa fa-save"></i> Save Team Header
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- TEAM MEMBERS LIST --}}
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Team Members</h3>
                                <button class="btn btn-sm btn-info ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#teamMemberModal">
                                    <i class="fa fa-user-plus"></i> Add Team Member
                                </button>
                            </div>

                            <div class="card-body">
                                <table class="table table-bordered" id="teamMembersTable">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th width="80">Image</th>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th width="150">Action</th>
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
    @include('backend.layouts.cms.about.team_member_modal')
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
        // TEAM MEMBERS DATATABLE
        // ===============================
        let teamTable = $('#teamMembersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.cms.about.team-members.index') }}",
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
                    data: 'sub_title'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });


        // ===============================
        // ADD TEAM MEMBER MODAL RESET
        // ===============================
        $('[data-bs-target="#teamMemberModal"]').on('click', function() {
            $('#teamMemberForm')[0].reset();
            $('#teamMemberID').val('');
            $('#teamMemberModalLabel').text('Add Team Member');
            $('.error-text').text('');

            $('#team_image_show_section').html(`
                <label>Profile Image</label>
                <input type="file" name="image" id="team_member_image"
                       class="form-control dropify"
                       data-default-file="{{ asset('default/placeholder-image.avif') }}"
                       accept="image/*">
                <small class="text-muted">Recommended size: 400x400px (Square)</small>
            `);

            $('#team_member_image').dropify();
        });

        // ===============================
        // STORE / UPDATE TEAM MEMBER
        // ===============================
        $('#teamMemberForm').on('submit', function(e) {
            e.preventDefault();

            let id = $('#teamMemberID').val();
            let url = id ?
                "{{ route('admin.cms.about.team-member.update', ':id') }}".replace(':id', id) :
                "{{ route('admin.cms.about.team-member.store') }}";

            let formData = new FormData(this);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#teamSubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                },
                success: function(res) {
                    $('#teamSubmitBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');

                    if (res.status === 1) {
                        $('#teamMemberModal').modal('hide');
                        teamTable.ajax.reload();
                        toastr.success(res.message);
                    }
                },
                error: function(xhr) {
                    $('#teamSubmitBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');

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
        // EDIT TEAM MEMBER
        // ===============================
        $(document).on('click', '.editTeamMember', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.cms.about.team-member.edit', ':id') }}".replace(':id', id);

            $.get(url, function(res) {
                $('#teamMemberModalLabel').text('Edit Team Member');
                $('#teamMemberID').val(res.data.id);
                $('#team_member_name').val(res.data.title);
                $('#team_member_position').val(res.data.sub_title);
                $('#team_member_experience').val(res.data.description);

                let imageUrl = res.data.image ?
                    "{{ asset('') }}" + res.data.image :
                    "{{ asset('default/placeholder-image.avif') }}";

                $('#team_image_show_section').html(`
                    <label>Profile Image (Leave empty to keep current)</label>
                    <input type="file" name="image" id="team_member_image"
                           class="form-control dropify"
                           data-default-file="${imageUrl}"
                           accept="image/*">
                    <small class="text-muted">Recommended size: 400x400px (Square)</small>
                `);

                $('#team_member_image').dropify();
                $('#teamMemberModal').modal('show');
            });
        });

    });
</script>

<script>
    // ===============================
    // DELETE TEAM MEMBER
    // ===============================
    function showDeleteTeamMember(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This team member will be deleted permanently!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteTeamMember(id);
            }
        });
    }

    function deleteTeamMember(id) {
        let url = "{{ route('admin.cms.about.team-member.destroy', ':id') }}".replace(':id', id);

        $.ajax({
            type: "DELETE",
            url: url,
            success: function(res) {
                $('#teamMembersTable').DataTable().ajax.reload();
                Swal.fire('Deleted!', res.message, 'success');
            },
            error: function() {
                Swal.fire('Error!', 'Failed to delete team member', 'error');
            }
        });
    }
</script>
@endpush
