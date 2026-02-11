@extends('backend.app', ['title' => 'Categories Management'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Categories</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Categories</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">Categories List</h3>
                                <div class="card-options">
                                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                                        onclick="openCreateModal()">
                                        <i class="fe fe-plus me-1"></i> Add Category
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom w-100"
                                        id="categories-datatable">
                                        <thead>
                                            <tr>
                                                <th class="wd-10p">ID</th>
                                                <th class="wd-20p">Name</th>
                                                <th class="wd-20p">Slug</th>
                                                <th class="wd-15p">Parent</th>
                                                <th class="wd-10p">Order</th>
                                                <th class="wd-10p">Status</th>
                                                <th class="wd-15p">Action</th>
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

    <!-- Create/Edit Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Create Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <form id="categoryForm" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="category_id">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name"
                                        placeholder="Enter category name" required>
                                    <div class="invalid-feedback" id="name-error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="slug" id="slug"
                                        placeholder="category-slug" required>
                                    <div class="invalid-feedback" id="slug-error"></div>
                                    <small class="text-muted">URL-friendly version (auto-generated)</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Parent Category</label>
                                    <select class="form-control" name="parent_id" id="parent_id">
                                        <option value="">Select Parent Category (Optional)</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="parent_id-error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" class="form-control" name="order" id="order"
                                        placeholder="0" min="0" value="0">
                                    <div class="invalid-feedback" id="order-error"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="description" rows="3"
                                        placeholder="Enter category description"></textarea>
                                    <div class="invalid-feedback" id="description-error"></div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-check" style="display: flex; align-items: center;">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                        value="1" checked
                                        style="width: 24px; height: 24px; cursor: pointer; margin-right: 10px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitBtnText">Create Category</span>
                            <span id="submitBtnSpinner" class="spinner-border spinner-border-sm d-none"
                                role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Category Modal -->
    <div class="modal fade" id="viewCategoryModal" tabindex="-1" role="dialog"
        aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCategoryModalLabel">Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row" id="categoryDetails">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#categories-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.categories.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '10%'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        width: '20%'
                    },
                    {
                        data: 'slug',
                        name: 'slug',
                        width: '20%'
                    },
                    {
                        data: 'parent_name',
                        name: 'parent_name',
                        orderable: false,
                        width: '15%'
                    },
                    {
                        data: 'order',
                        name: 'order',
                        width: '10%'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        width: '10%'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '15%'
                    },
                ],
                order: [
                    [4, 'asc']
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                }
            });

            // Auto-generate slug from name
            $('#name').on('keyup', function() {
                var name = $(this).val();
                var slug = name.toLowerCase()
                    .replace(/[^\w ]+/g, '')
                    .replace(/ +/g, '-');
                $('#slug').val(slug);
            });

            // Initialize Select2
            function initSelect2() {
                $('#parent_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select Parent Category (Optional)',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#categoryModal')
                });
            }

            // Update parent categories dropdown
            function updateParentCategoriesDropdown(categories) {
                var select = $('#parent_id');
                var currentVal = select.val();

                // Clear existing options except the first one
                select.find('option:not(:first)').remove();

                // Add new options
                $.each(categories, function(index, category) {
                    select.append($('<option>', {
                        value: category.id,
                        text: category.name
                    }));
                });

                // Restore selected value if it exists
                if (currentVal) {
                    select.val(currentVal).trigger('change');
                }

                // Reinitialize Select2
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                initSelect2();
            }

            // Clear form errors
            function clearFormErrors() {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }

            // Reset form
            function resetForm() {
                $('#categoryForm')[0].reset();
                $('#category_id').val('');
                $('#categoryModalLabel').text('Create Category');
                $('#submitBtnText').text('Create Category');
                clearFormErrors();

                // Reset Select2
                if ($('#parent_id').hasClass('select2-hidden-accessible')) {
                    $('#parent_id').val('').trigger('change');
                }
            }

            // Open Create Modal
            window.openCreateModal = function() {
                resetForm();
                initSelect2();
                $('#categoryModal').modal('show');
            }

            // Open Edit Modal
            window.openEditModal = function(id) {
                clearFormErrors();
                $('#submitBtnText').text('Updating...');
                $('#submitBtnSpinner').removeClass('d-none');

                $.ajax({
                    url: "{{ url('admin/categories/get') }}/" + id,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var category = response.category;

                            $('#category_id').val(category.id);
                            $('#name').val(category.name);
                            $('#slug').val(category.slug);
                            $('#parent_id').val(category.parent_id);
                            $('#order').val(category.order);
                            $('#description').val(category.description);
                            $('#is_active').prop('checked', category.is_active);

                            // Update parent categories dropdown
                            if (response.categories) {
                                updateParentCategoriesDropdown(response.categories);
                            }

                            $('#categoryModalLabel').text('Edit Category');
                            $('#submitBtnText').text('Update Category');
                            $('#categoryModal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                        $('#submitBtnText').text('Update Category');
                        $('#submitBtnSpinner').addClass('d-none');
                    },
                    error: function() {
                        toastr.error('Failed to load category data');
                        $('#submitBtnText').text('Update Category');
                        $('#submitBtnSpinner').addClass('d-none');
                    }
                });
            }

            // Open View Modal
            window.openViewModal = function(id) {
                $.ajax({
                    url: "{{ url('admin/categories/get') }}/" + id,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var category = response.category;
                            var subCategories = category.children;

                            var html = `
                        <div class="col-md-6">
                            <h6><strong>Name:</strong></h6>
                            <p>${category.name}</p>
                        </div>

                        <div class="col-md-6">
                            <h6><strong>Slug:</strong></h6>
                            <p>${category.slug}</p>
                        </div>

                        <div class="col-md-6">
                            <h6><strong>Parent Category:</strong></h6>
                            <p>${category.parent ? category.parent.name : '<span class="badge bg-info">Main Category</span>'}</p>
                        </div>

                        <div class="col-md-6">
                            <h6><strong>Display Order:</strong></h6>
                            <p>${category.order}</p>
                        </div>

                        <div class="col-md-6">
                            <h6><strong>Status:</strong></h6>
                            <p>${category.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</p>
                        </div>

                        <div class="col-md-12">
                            <h6><strong>Description:</strong></h6>
                            <p>${category.description || 'No description provided'}</p>
                        </div>

                        <div class="col-md-6">
                            <h6><strong>Created At:</strong></h6>
                            <p>${new Date(category.created_at).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</p>
                        </div>

                        <div class="col-md-6">
                            <h6><strong>Updated At:</strong></h6>
                            <p>${new Date(category.updated_at).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</p>
                        </div>
                    `;

                            if (subCategories.length > 0) {
                                html += `
                            <div class="col-md-12">
                                <hr>
                                <h6><strong>Sub Categories (${subCategories.length}):</strong></h6>
                                <div class="list-group mt-2">
                        `;

                                subCategories.forEach(function(subCat) {
                                    html += `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${subCat.name}</h6>
                                        <small>
                                            ${subCat.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}
                                        </small>
                                    </div>
                                    <small class="text-muted">Order: ${subCat.order} | Slug: ${subCat.slug}</small>
                                </div>
                            `;
                                });

                                html += `</div></div>`;
                            }

                            $('#categoryDetails').html(html);
                            $('#viewCategoryModalLabel').text(category.name + ' - Details');
                            $('#viewCategoryModal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load category details');
                    }
                });
            }

            // Handle form submission
            $('#categoryForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var categoryId = $('#category_id').val();
                var url = categoryId ? "{{ url('admin/categories') }}/" + categoryId :
                    "{{ route('admin.categories.store') }}";
                var method = categoryId ? 'PUT' : 'POST';

                clearFormErrors();
                $('#submitBtn').prop('disabled', true);
                $('#submitBtnText').text(categoryId ? 'Updating...' : 'Creating...');
                $('#submitBtnSpinner').removeClass('d-none');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#categoryModal').modal('hide');

                            // Update parent categories dropdown dynamically
                            if (response.parent_categories) {
                                updateParentCategoriesDropdown(response.parent_categories);
                            }

                            table.ajax.reload(null, false);
                        } else {
                            // Display validation errors
                            if (response.errors) {
                                $.each(response.errors, function(key, value) {
                                    $('#' + key).addClass('is-invalid');
                                    $('#' + key + '-error').text(value[0]);
                                });
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred. Please try again.');
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false);
                        $('#submitBtnText').text(categoryId ? 'Update Category' :
                            'Create Category');
                        $('#submitBtnSpinner').addClass('d-none');
                    }
                });
            });

            // Status toggle handler - FIXED
            $(document).on('click', '.form-check.form-switch', function(e) {
                // Prevent multiple triggers if clicking directly on checkbox
                if ($(e.target).hasClass('status-toggle')) {
                    return;
                }

                var checkbox = $(this).find('.status-toggle');
                var id = checkbox.data('id');
                var isChecked = checkbox.is(':checked');

                // Toggle the checkbox visually
                checkbox.prop('checked', !isChecked);

                Swal.fire({
                    title: 'Change Status?',
                    text: 'Are you sure you want to change the status?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/categories/status') }}/" + id,
                            type: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    // Update the visual switch
                                    var switchDiv = checkbox.closest(
                                        '.form-check.form-switch');
                                    if (response.is_active) {
                                        switchDiv.css('background-color', '#05402e');
                                        switchDiv.find('span').css('transform',
                                            'translateX(26px)');
                                    } else {
                                        switchDiv.css('background-color', '#ccc');
                                        switchDiv.find('span').css('transform',
                                            'translateX(2px)');
                                    }
                                } else {
                                    toastr.error(response.message);
                                    // Revert if failed
                                    checkbox.prop('checked', isChecked);
                                }
                            },
                            error: function() {
                                toastr.error('An error occurred!');
                                // Revert if error
                                checkbox.prop('checked', isChecked);
                            }
                        });
                    } else {
                        // If cancelled, revert the checkbox
                        checkbox.prop('checked', isChecked);
                    }
                });
            });

            // Modal hidden event
            $('#categoryModal').on('hidden.bs.modal', function() {
                resetForm();
                // Destroy Select2 when modal is closed
                if ($('#parent_id').hasClass('select2-hidden-accessible')) {
                    $('#parent_id').select2('destroy');
                }
            });
        });

        // Delete Confirmation
        function showDeleteConfirm(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! This will also delete all sub-categories.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/categories') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                $('#categories-datatable').DataTable().ajax.reload(null, false);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('An error occurred while deleting!');
                        }
                    });
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        /* Custom Switch Styles */
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 70px;
            height: 36px;
        }

        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .custom-switch-label {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .custom-switch-label:before {
            position: absolute;
            content: "";
            height: 28px;
            width: 28px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.custom-switch-label {
            background-color: #28a745;
        }

        input:checked+.custom-switch-label:before {
            transform: translateX(34px);
        }

        /* Fix for checkbox in modal */
        .modal-body .form-check {
            display: flex !important;
            align-items: center !important;
            padding-left: 0 !important;
            margin-left: 0 !important;
        }

        .modal-body .form-check-input {
            margin-right: 10px !important;
            margin-left: 0 !important;
            width: 24px !important;
            height: 24px !important;
        }

        .modal-body .form-check-label {
            margin-left: 500 !important;
        }

        /* Select2 custom styling */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
            padding: 5px 11px;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
        }

        /* Table action buttons */
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endpush
