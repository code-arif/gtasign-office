{{-- Team Member Modal --}}
<div class="modal fade" id="teamMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="teamMemberForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="teamMemberID">

                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="teamMemberModalLabel">
                        <i class="fa fa-users"></i> Add Team Member
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="team_member_name" class="form-control"
                                       placeholder="e.g., Sarah Mitchell" required>
                                <span class="text-danger error-text title_error"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Position/Role <span class="text-danger">*</span></label>
                                <input type="text" name="sub_title" id="team_member_position" class="form-control"
                                       placeholder="e.g., Head of Technology" required>
                                <span class="text-danger error-text sub_title_error"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Experience/Info</label>
                                <input type="text" name="description" id="team_member_experience" class="form-control"
                                       placeholder="e.g., 15+ Years in Sports Tech">
                                <span class="text-danger error-text description_error"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-3" id="team_image_show_section">
                                <label>Profile Image</label>
                                <input type="file" name="image" id="team_member_image" class="form-control dropify"
                                    accept="image/*">
                                <small class="text-muted">Recommended size: 400x400px (Square)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-info" id="teamSubmitBtn">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
