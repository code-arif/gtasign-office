@extends('backend.app')

@section('title', 'Edit Expert — ' . ($expert->profile->first_name ?? $expert->email))

@section('content')
    @php
        $profile = $expert->profile;
        $fullName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : $expert->email;
        $avatar =
            $profile && $profile->avatar
                ? asset($profile->avatar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=6366f1&color=fff&size=128';

        $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'danger'];
        $statusColor = $statusColors[$expert->status] ?? 'secondary';
    @endphp

    <div class="app-content main-content mt-0">
        <div class="side-app pb-5">
            <div class="main-container container-fluid" style="margin-bottom: 50px">

                {{-- BREADCRUMB --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Edit Expert</h1>
                        <p class="text-muted mb-0">Update profile, account settings & credentials</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <span>&nbsp; >> &nbsp;</span>
                            <li class="breadcrumb-item"><a href="{{ route('admin.experts.index') }}">Experts</a></li>
                            <span>&nbsp; >> &nbsp;</span>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.experts.show', $expert->id) }}">{{ $fullName }}</a>
                            </li>
                            <span>&nbsp; >> &nbsp;</span>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>

                {{-- FLASH MESSAGES --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- FORM --}}
                <form action="{{ route('admin.experts.update', $expert->id) }}" method="POST" enctype="multipart/form-data"
                    id="expertEditForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">

                        {{--LEFT COLUMN  (Avatar + Account Settings) --}}
                        <div class="col-xl-4">

                            {{-- Avatar Card --}}
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-image me-2 text-primary"></i>Profile Photo
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    {{-- Preview --}}
                                    <div class="avatar-preview-wrapper mb-3">
                                        <img id="avatarPreview" src="{{ $avatar }}" alt="{{ $fullName }}"
                                            class="avatar-preview-img">
                                        <label for="avatarInput" class="avatar-edit-overlay" title="Change photo">
                                            <i class="fe fe-camera"></i>
                                        </label>
                                    </div>

                                    <input type="file" id="avatarInput" name="avatar"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="d-none @error('avatar') is-invalid @enderror">

                                    <p class="text-muted small mb-0">JPG, PNG or WebP · Max 2 MB</p>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                        onclick="document.getElementById('avatarInput').click()">
                                        <i class="fe fe-upload me-1"></i>Upload New Photo
                                    </button>
                                    @error('avatar')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Account Settings Card --}}
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-shield me-2 text-primary"></i>Account Settings
                                    </h5>
                                </div>
                                <div class="card-body">

                                    {{-- Status --}}
                                    <div class="mb-3">
                                        <label class="form-label required-label">Account Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="active"
                                                {{ old('status', $expert->status) === 'active' ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option value="inactive"
                                                {{ old('status', $expert->status) === 'inactive' ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                            <option value="suspended"
                                                {{ old('status', $expert->status) === 'suspended' ? 'selected' : '' }}>
                                                Suspended
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Level --}}
                                    <div class="mb-3">
                                        <label class="form-label">Expert Level</label>
                                        <select name="level" class="form-select @error('level') is-invalid @enderror">
                                            <option value="">— No Level —</option>
                                            <option value="level 1"
                                                {{ old('level', $profile?->level) === 'level 1' ? 'selected' : '' }}>
                                                Level 1
                                            </option>
                                            <option value="level 2"
                                                {{ old('level', $profile?->level) === 'level 2' ? 'selected' : '' }}>
                                                Level 2
                                            </option>
                                            <option value="level 3"
                                                {{ old('level', $profile?->level) === 'level 3' ? 'selected' : '' }}>
                                                Level 3
                                            </option>
                                        </select>
                                        @error('level')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Level Display Name --}}
                                    <div class="mb-3">
                                        <label class="form-label">Level Display Name
                                            <span class="text-muted small">(optional)</span>
                                        </label>
                                        <input type="text" name="level_name"
                                            class="form-control @error('level_name') is-invalid @enderror"
                                            placeholder="e.g. Platinum Seller, Top Pro…"
                                            value="{{ old('level_name', $profile?->level_name) }}">
                                        <small class="text-muted">Shown publicly as badge / title</small>
                                        @error('level_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            {{-- Quick Info Box --}}
                            <div class="card border-0" style="background:#f8f9ff; border:1px solid #e0e7ff !important;">
                                <div class="card-body py-3">
                                    <p class="small mb-1 text-muted">
                                        <i class="fe fe-info me-1 text-primary"></i>
                                        <strong>Expert ID:</strong> #{{ $expert->id }}
                                    </p>
                                    <p class="small mb-1 text-muted">
                                        <i class="fe fe-calendar me-1 text-primary"></i>
                                        <strong>Joined:</strong> {{ $expert->created_at->format('M d, Y') }}
                                    </p>
                                    <p class="small mb-0 text-muted">
                                        <i class="fe fe-mail me-1 text-primary"></i>
                                        <strong>Verified:</strong>
                                        @if ($expert->email_verified_at)
                                            <span class="text-success">Yes
                                                ({{ $expert->email_verified_at->format('M d, Y') }})
                                            </span>
                                        @else
                                            <span class="text-danger">No</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                        </div>{{-- /left col --}}

                        {{-- RIGHT COLUMN  (Personal + Contact + Bio)--}}
                        <div class="col-xl-8">

                            {{-- Personal Information --}}
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-user me-2 text-primary"></i>Personal Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label required-label">First Name</label>
                                            <input type="text" name="first_name"
                                                class="form-control @error('first_name') is-invalid @enderror"
                                                placeholder="First name"
                                                value="{{ old('first_name', $profile?->first_name) }}">
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" name="last_name"
                                                class="form-control @error('last_name') is-invalid @enderror"
                                                placeholder="Last name"
                                                value="{{ old('last_name', $profile?->last_name) }}">
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text text-muted">@</span>
                                                <input type="text" name="username"
                                                    class="form-control @error('username') is-invalid @enderror"
                                                    placeholder="username"
                                                    value="{{ old('username', $profile?->username) }}">
                                                @error('username')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label required-label">Account Status</label>
                                            <div class="form-control-plaintext">
                                                <span class="badge bg-{{ $statusColor }} fs-13">
                                                    {{ ucfirst($expert->status) }}
                                                </span>
                                                <small class="text-muted ms-2">(change in Account Settings)</small>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- Contact Information --}}
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-phone me-2 text-primary"></i>Contact Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label required-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fe fe-mail text-muted"></i>
                                                </span>
                                                <input type="email" name="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    placeholder="expert@email.com"
                                                    value="{{ old('email', $expert->email) }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fe fe-phone text-muted"></i>
                                                </span>
                                                <input type="text" name="phone"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    placeholder="+880 1XXX-XXXXXX"
                                                    value="{{ old('phone', $expert->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- Bio / About --}}
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="fe fe-align-left me-2 text-primary"></i>Bio / About
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-1">
                                        <label class="form-label">Professional Bio
                                            <span class="text-muted small">(optional)</span>
                                        </label>
                                        <textarea name="bio" id="bioTextarea" rows="5" class="form-control @error('bio') is-invalid @enderror"
                                            placeholder="A short professional summary visible on the expert's public profile…" maxlength="1000">{{ old('bio', $profile?->bio) }}</textarea>
                                        @error('bio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="d-flex justify-content-end mt-1">
                                            <small class="text-muted">
                                                <span id="bioCount">0</span>/1000
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Buttons --}}
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.experts.show', $expert->id) }}"
                                    class="btn btn-outline-secondary px-4">
                                    <i class="fe fe-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-5" id="saveBtn">
                                    <span class="btn-text">
                                        <i class="fe fe-save me-1"></i>Save Changes
                                    </span>
                                    <span class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </div>

                        </div>{{-- /right col --}}

                    </div>{{-- /row --}}
                </form>

            </div>{{-- /container --}}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Avatar preview
        document.getElementById('avatarInput').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                toastr.error('File size must be under 2 MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        // Bio character counter
        const bioEl = document.getElementById('bioTextarea');
        const countEl = document.getElementById('bioCount');

        function updateBioCount() {
            countEl.textContent = bioEl.value.length;
        }
        bioEl.addEventListener('input', updateBioCount);
        updateBioCount(); // init on load

        // Form submit spinner
        document.getElementById('expertEditForm').addEventListener('submit', function() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.querySelector('.btn-text').classList.add('d-none');
            btn.querySelector('.spinner-border').classList.remove('d-none');
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Required field label */
        .required-label::after {
            content: ' *';
            color: #dc3545;
        }

        /* Avatar preview wrapper */
        .avatar-preview-wrapper {
            position: relative;
            display: inline-block;
        }

        .avatar-preview-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e9ecef;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
            display: block;
            margin: 0 auto;
            transition: filter .2s;
        }

        .avatar-preview-wrapper:hover .avatar-preview-img {
            filter: brightness(0.75);
        }

        .avatar-edit-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity .2s;
            font-size: 22px;
            color: #fff;
        }

        .avatar-preview-wrapper:hover .avatar-edit-overlay {
            opacity: 1;
        }

        /* Cards */
        .card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
        }

        .card-header {
            background: #fff;
            padding: 14px 20px;
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Inputs */
        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1.5px solid #dee2e6;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .input-group-text {
            border-radius: 8px 0 0 8px;
            background: #f8f9fa;
            border-color: #dee2e6;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }

        /* Table */
        .table td {
            vertical-align: middle;
        }

        .fs-13 {
            font-size: 13px;
        }
    </style>
@endpush
