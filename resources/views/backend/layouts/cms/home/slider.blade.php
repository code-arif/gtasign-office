@extends('backend.app')

@section('title', 'Partner Section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom:50px">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Home page - Partner section</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Partner section</li>
                        </ol>
                    </div>
                </div>
                {{-- PAGE-HEADER --}}


                <div class="row">
                    {{-- header manage --}}
                    <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-header bg-light">
                                <h4 class="card-title">Header</h4>
                            </div>
                            <div class="card-body">
                                <form class="form-horizontal" method="post"
                                    action="{{ route('admin.cms.home.slider.header.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">
                                        {{-- section title --}}
                                        <div class="form-group">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter title" id="title"
                                                value="{{ $data->title ?? (old('title') ?? '') }}">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">Save change</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- slider manage --}}
                </div>

                <div class="row">
                    <!-- Add New Slider Card -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title text-white">Add New Partner</h3>
                            </div>
                            <div class="card-body">
                                <form id="sliderForm" enctype="multipart/form-data">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label">Partner Logo <span class="text-danger">*</span></label>
                                        <input type="file" name="image" id="sliderImage" class="form-control"
                                            accept="image/*" required onchange="previewImage(event)">
                                        <small class="text-muted">Recommended size: 1920x1080px</small>
                                    </div>

                                    <!-- Image Preview -->
                                    <div class="mb-3">
                                        <div id="imagePreview" class="d-none">
                                            <img src="" id="previewImg" class="img-fluid border"
                                                style="max-height: 200px; width: 100%; object-fit: cover;">
                                        </div>
                                    </div>

                                    {{-- Partner web link --}}
                                    <div class="mb-3">
                                        <label for="link">Pertner Link (optional)</label>
                                        <input type="link" name="link" class="form-control" id="link">
                                    </div>

                                    <div class="mb-3" style="margin-left: 12px">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input custom-toggle" type="checkbox" name="status"
                                                id="status" value="1" checked>
                                            <label class="form-check-label" for="status"
                                                style="margin-left: 22px; margin-top: 5px;">
                                                Active Status
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                                        <span id="submitText">Add Partner</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Slider List -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h3 class="card-title">All Partners ({{ $sliders->count() }})</h3>
                            </div>
                            <div class="card-body">
                                @if ($sliders->isEmpty())
                                    <div class="text-center py-5">
                                        <i class="fe fe-image" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="text-muted mt-3">No sliders found. Add your first slider!</p>
                                    </div>
                                @else
                                    <div id="sortable-sliders" class="row">
                                        @foreach ($sliders as $slider)
                                            <div class="col-md-6 mb-3 sortable-item" data-id="{{ $slider->id }}">
                                                <div class="card border">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex align-items-center">
                                                            <!-- Drag Handle -->
                                                            <div class="me-2 drag-handle" style="cursor: move;">
                                                                <i class="fe fe-menu" style="font-size: 20px;"></i>
                                                            </div>

                                                            <!-- Image Preview -->
                                                            <div class="me-2">
                                                                <img src="{{ asset('/' . $slider->image) }}"
                                                                    class="img-fluid border"
                                                                    style="width: 80px; height: 60px; object-fit: cover;">
                                                            </div>

                                                            <!-- Slider Info -->
                                                            <div class="flex-grow-1">
                                                                <p class="mb-1 fw-bold">Slider #{{ $slider->id }}</p>
                                                                <small class="text-muted">
                                                                    Order: {{ $slider->order }}
                                                                </small>
                                                            </div>

                                                            <!-- Actions -->
                                                            <div class="d-flex align-items-center gap-3">
                                                                <!-- Status Toggle -->
                                                                <div class="form-check form-switch mb-0">
                                                                    <input
                                                                        class="form-check-input status-toggle custom-toggle"
                                                                        type="checkbox" data-id="{{ $slider->id }}"
                                                                        {{ $slider->status ? 'checked' : '' }}
                                                                        style="cursor: pointer;" value="1">
                                                                </div>

                                                                <!-- Delete Button -->
                                                                <button class="btn btn-sm btn-danger delete-slider"
                                                                    data-id="{{ $slider->id }}">
                                                                    <i class="fe fe-trash-2"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('styles')
    <style>
        /* Custom Toggle Switch Styling */
        .custom-toggle {
            width: 40px !important;
            height: 20px !important;
            cursor: pointer;
        }

        .custom-toggle:checked {
            background-color: #00AEEF !important;
            border-color: #00AEEF !important;
        }

        .custom-toggle:focus {
            box-shadow: 0 0 0 0.25rem rgba(82, 26, 172, 0.25) !important;
            border-color: #00AEEF !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        // Image Preview
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        // Add Slider Form Submit
        document.getElementById('sliderForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('submitSpinner');
            const submitText = document.getElementById('submitText');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitText.textContent = ' Adding...';

            const formData = new FormData(this);

            try {
                const response = await axios.post('{{ route('admin.cms.home.slider.store') }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });

                iziToast.success({
                    title: 'Success',
                    message: response.data.message,
                    position: 'topRight'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1000);

            } catch (error) {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                submitText.textContent = 'Add Slider';

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let errorMessage = '';
                    for (const [field, messages] of Object.entries(errors)) {
                        errorMessage += messages.join('<br>') + '<br>';
                    }
                    iziToast.error({
                        title: 'Validation Error',
                        message: errorMessage,
                        position: 'topRight',
                        timeout: 5000
                    });
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: error.response?.data?.message || 'Failed to add slider.',
                        position: 'topRight'
                    });
                }
            }
        });

        // Status Toggle with SweetAlert Confirmation
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', async function() {
                const id = this.getAttribute('data-id');
                const newStatus = this.checked;
                const statusText = newStatus ? 'activate' : 'deactivate';

                // Prevent toggle until confirmed
                this.checked = !newStatus;

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to ${statusText} this partner?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#521aac',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${statusText} it!`,
                    cancelButtonText: 'Cancel'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const url = "{{ route('admin.cms.home.slider.status', ':id') }}".replace(
                                ':id', id);

                            const response = await axios.post(url, {
                                status: newStatus ? 1 : 0
                            }, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'input[name="_token"]').value,
                                    'Content-Type': 'application/json'
                                }
                            });

                            // Update toggle to new status
                            this.checked = newStatus;

                            Swal.fire({
                                title: 'Success!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonColor: '#521aac',
                                timer: 2000,
                                showConfirmButton: false
                            });

                        } catch (error) {
                            // Keep toggle at old status
                            this.checked = !newStatus;

                            Swal.fire({
                                title: 'Error!',
                                text: error.response?.data?.message ||
                                    'Failed to update status.',
                                icon: 'error',
                                confirmButtonColor: '#521aac'
                            });
                        }
                    }
                    // If cancelled, keep toggle at old status (already set above)
                });
            });
        });

        // Delete Slider with SweetAlert
        document.querySelectorAll('.delete-slider').forEach(button => {
            button.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#521aac',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const url = "{{ route('admin.cms.home.slider.destroy', ':id') }}".replace(
                                ':id', id);

                            const response = await axios.delete(url, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'input[name="_token"]').value
                                }
                            });

                            Swal.fire({
                                title: 'Deleted!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonColor: '#521aac',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);

                        } catch (error) {
                            Swal.fire({
                                title: 'Error!',
                                text: error.response?.data?.message ||
                                    'Failed to delete slider.',
                                icon: 'error',
                                confirmButtonColor: '#521aac'
                            });
                        }
                    }
                });
            });
        });

        // Sortable (Drag & Drop)
        const sortableList = document.getElementById('sortable-sliders');
        if (sortableList && sortableList.children.length > 0) {
            new Sortable(sortableList, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: async function(evt) {
                    const orders = [];
                    document.querySelectorAll('.sortable-item').forEach((item, index) => {
                        orders.push({
                            id: item.getAttribute('data-id'),
                            position: index + 1
                        });
                    });

                    try {
                        const response = await axios.post('{{ route('admin.cms.home.slider.updateOrder') }}', {
                            orders: orders
                        }, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        });

                        iziToast.success({
                            title: 'Success',
                            message: response.data.message,
                            position: 'topRight'
                        });

                    } catch (error) {
                        iziToast.error({
                            title: 'Error',
                            message: 'Failed to update order.',
                            position: 'topRight'
                        });
                    }
                }
            });
        }
    </script>
@endpush
