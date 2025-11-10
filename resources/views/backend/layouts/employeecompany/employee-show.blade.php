@extends('backend.app', ['title' => 'Employee CV'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">Employee Details</h1>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>

                <!-- Profile Header Card -->
                <div class="card profile-header mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="profile-image-wrapper">
                                    <img src="{{ asset($user_employee->image_url ?? 'default/avatar.png') }}"
                                        alt="Employee Image"
                                        class="profile-image">
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h2 class="employee-name">{{ $user_employee->name ?? 'Employee Name' }}</h2>
                                <div class="job-title-badge mb-3">
                                    <span class="badge bg-primary-gradient">
                                        {{ $user_employee->job_title ?? 'Employee' }}
                                    </span>
                                </div>

                                <div class="employee-info">
                                    <div class="info-item">
                                        <i class="fe fe-map-pin text-muted me-2"></i>
                                        <span>{{ $user_employee->location ?? 'Location not specified' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fe fe-briefcase text-muted me-2"></i>
                                        <span>{{ $user_employee->year_of_experice ?? '0' }} years experience</span>
                                    </div>
                                    @if($user_employee->bio)
                                    <div class="info-item bio">
                                        <i class="fe fe-user text-muted me-2"></i>
                                        <span>{{ $user_employee->bio }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-6">

                        <!-- Education Section -->
                        <div class="card section-card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fe fe-award text-primary me-2"></i>Education
                                </h4>
                            </div>
                            <div class="card-body">
                                @forelse($user_employee->qualifications as $qualification)
                                    <div class="timeline-item">
                                        <div class="timeline-content">
                                            <h5 class="qualification-title">{{ $qualification->qualification ?? 'Degree' }}</h5>
                                            <p class="institution-name">{{ $qualification->institute_name }}</p>
                                            <div class="timeline-date">
                                                {{ date('M Y', strtotime($qualification->start_date)) }} -
                                                {{ $qualification->end_date ? date('M Y', strtotime($qualification->end_date)) : 'Present' }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fe fe-book text-muted"></i>
                                        <p class="text-muted">No qualifications added yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Certifications Section -->
                        <div class="card section-card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fe fe-file-text text-success me-2"></i>Certifications
                                </h4>
                            </div>
                            <div class="card-body">
                                @forelse($user_employee->certifications as $certification)
                                    <div class="timeline-item">
                                        <div class="timeline-content">
                                            <h5 class="certification-title">{{ $certification->name }}</h5>
                                            <p class="organization-name">{{ $certification->issue_organization }}</p>
                                            <div class="certification-details">
                                                <small class="text-muted">
                                                    Issued: {{ date('M Y', strtotime($certification->date_issue)) }}
                                                </small>
                                                @if ($certification->creadential_id)
                                                    <small class="text-muted ms-3">
                                                        ID: {{ $certification->creadential_id }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fe fe-award text-muted"></i>
                                        <p class="text-muted">No certifications listed yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-6">

                        <!-- Experience Section -->
                        <div class="card section-card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fe fe-briefcase text-info me-2"></i>Experience
                                </h4>
                            </div>
                            <div class="card-body">
                                @forelse($user_employee->experiences as $experience)
                                    <div class="timeline-item">
                                        <div class="timeline-content">
                                            <h5 class="position-title">{{ $experience->job_title ?? 'Position' }}</h5>
                                            <p class="company-name">{{ $experience->company_name }}</p>
                                            <div class="experience-details">
                                                <div class="experience-date">
                                                    {{ date('M Y', strtotime($experience->start_date)) }} -
                                                    {{ $experience->end_date ? date('M Y', strtotime($experience->end_date)) : 'Present' }}
                                                </div>
                                                <span class="job-type-badge">{{ $experience->job_type }}</span>
                                            </div>
                                            @if ($experience->job_location)
                                                <div class="job-location">
                                                    <i class="fe fe-map-pin text-muted me-1"></i>
                                                    {{ $experience->job_location }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fe fe-briefcase text-muted"></i>
                                        <p class="text-muted">No experience records yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Job Categories Section -->
                        <div class="card section-card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fe fe-layers text-primary me-2"></i>Job Categories
                                </h4>
                            </div>
                            <div class="card-body">
                                @forelse($user_employee->employee_job_categories as $category)
                                    <span class="skill-badge bg-primary">
                                        {{ $category->job_category->title ?? 'Category' }}
                                    </span>
                                @empty
                                    <div class="empty-state">
                                        <i class="fe fe-layers text-muted"></i>
                                        <p class="text-muted">No categories assigned yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Specializations Section -->
                        <div class="card section-card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fe fe-feather text-warning me-2"></i>Specializations
                                </h4>
                            </div>
                            <div class="card-body">
                                @forelse($user_employee->specializations as $spec)
                                    <span class="skill-badge bg-success">
                                        {{ $spec->specialize->name }}
                                    </span>
                                @empty
                                    <div class="empty-state">
                                        <i class="fe fe-star text-muted"></i>
                                        <p class="text-muted">No specialization specified yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-image-wrapper {
            position: relative;
            display: inline-block;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .employee-name {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .job-title-badge .badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .bg-primary-gradient {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
        }

        .employee-info {
            margin-top: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .info-item.bio {
            margin-top: 1rem;
        }

        /* Section Cards */
        .section-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
            overflow: hidden;
        }

        .section-card .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
        }

        .section-card .card-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
        }

        .section-card .card-body {
            padding: 1.5rem;
        }

        /* Timeline Items */
        .timeline-item {
            position: relative;
            padding-left: 20px;
            margin-bottom: 1.5rem;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            width: 8px;
            height: 8px;
            background: #007bff;
            border-radius: 50%;
        }

        .timeline-item:not(:last-child):after {
            content: '';
            position: absolute;
            left: 3px;
            top: 16px;
            width: 2px;
            height: calc(100% + 8px);
            background: #e9ecef;
        }

        .timeline-content h5 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            font-weight: 600;
            color: #495057;
        }

        .timeline-content p {
            margin: 0 0 0.5rem 0;
            color: #6c757d;
            font-weight: 500;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        /* Experience specific styles */
        .experience-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .experience-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .job-type-badge {
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .job-location {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Skill Badges */
        .skill-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            margin: 0.25rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            color: white;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 2rem;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .empty-state p {
            margin: 0;
            font-style: italic;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-header .row {
                text-align: center;
            }

            .profile-header .col-md-9 {
                margin-top: 1rem;
            }

            .employee-name {
                font-size: 1.5rem;
            }

            .experience-details {
                flex-direction: column;
                align-items: flex-start;
            }

            .job-type-badge {
                margin-top: 0.5rem;
            }
        }

        /* Print Styles */
        @media print {
            .page-header,
            .btn {
                display: none !important;
            }

            body {
                background: white !important;
                color: black !important;
            }

            .profile-header {
                background: #f8f9fa !important;
                color: #495057 !important;
            }

            .employee-name {
                color: #495057 !important;
            }

            .info-item {
                color: #495057 !important;
            }

            .section-card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }

            .timeline-item:before {
                background: #495057 !important;
            }

            .timeline-item:after {
                background: #dee2e6 !important;
            }
        }
    </style>
@endsection
