{{-- About Feature Item Modal --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="itemForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="itemID">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="itemModalLabel">
                        <i class="fa fa-star"></i> Add Feature Item
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="item_title" class="form-control"
                                    placeholder="e.g., GPS Integration" required>
                                <span class="text-danger error-text title_error"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="item_description" rows="4" class="form-control"
                                    placeholder="Enter feature description" required></textarea>
                                <span class="text-danger error-text description_error"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-3" id="image_show_section">
                                <label>Image (Optional)</label>
                                <input type="file" name="image" id="item_image" class="form-control dropify"
                                    accept="image/*">
                                <small class="text-muted">Recommended size: 500x500px</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
