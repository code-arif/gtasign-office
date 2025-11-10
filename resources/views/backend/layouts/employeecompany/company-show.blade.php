@extends('backend.app', ['title' => 'Company Profile'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- Page Header --}}
                <div class="page-header">
                    <h1 class="page-title">Company Details</h1>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('admin.company.index') }}" class="btn btn-primary btn-sm">Back to List</a>
                    </div>
                </div>

                {{-- Company Card --}}
                <div class="card">
                    <div class="card-body">

                        {{-- Profile Section --}}
                        <div class="row mb-4">
                            <div class="col-md-3 text-center mb-3">
                                <img src="{{ asset($company->company->image_url ?? 'default/avatar.png') }}"
                                    alt="Company Image" class="img-thumbnail rounded-circle"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                            </div>

                            <div class="col-md-9">
                                <div class="mb-3">
                                    <h4>{{ $company->company->name ?? 'Company Name' }}</h4>
                                </div>

                                {{-- Display Name --}}
                                <p class="mb-1">
                                    <i class="fe fe-activity me-2"></i>
                                    <strong>Display Name:</strong> {{ $company->company->display_name ?? 'N/A' }}
                                </p>

                                {{-- Location --}}
                                <p class="mb-1">
                                    <i class="fe fe-map-pin me-2"></i>
                                    <strong>Location:</strong> {{ $company->company->location ?? 'N/A' }}
                                </p>

                                {{-- Website --}}
                                <p class="mb-1">
                                    <i class="fe fe-globe me-2"></i>
                                    <strong>Website:</strong>
                                    @if (isset($company->company->website_url))
                                        <a href="{{ $company->company->website_url }}"
                                            target="_blank">{{ $company->company->website_url }}</a>
                                    @else
                                        N/A
                                    @endif
                                </p>

                                {{-- Bio / Description --}}
                                <p class="mb-1">
                                    <i class="fe fe-user me-2"></i>
                                    <strong>About:</strong> {{ $company->company->bio ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Projects Section --}}
                        <div class="mb-4">
                            <h4 class="section-title">
                                <i class="fe fe-briefcase me-2 text-info"></i> Projects
                            </h4>

                            @if (isset($company->company->company_projects) && $company->company->company_projects->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach ($company->company->company_projects as $project)
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start gap-3">
                                                {{-- Project Image --}}
                                                <img src="{{ $project->image_url}}"
                                                    alt="{{ $project->title ?? 'Project Image' }}"
                                                    style="width: 100px; height: 75px; object-fit: cover; border-radius: 5px;">
                                               <div>
                                                    <strong>{{ $project->title ?? 'Untitled Project' }}</strong><br>
                                                    <p class="mb-0">
                                                        {{ $project->description ?? 'No description available.' }}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No projects added.</p>
                            @endif
                        </div>


                        <hr class="my-4">

                        {{-- Specializations Section --}}
                        <div class="mb-4">
                            <h4 class="section-title">
                                <i class="fe fe-star me-2 text-warning"></i> Specializations
                            </h4>
                            @if ($company->specializations && $company->specializations->count() > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($company->specializations as $specialization)
                                        <span
                                            class="badge bg-success">{{ $specialization->specialize->name ?? 'N/A' }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No specializations specified.</p>
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Styling --}}
    <style>
        .section-title {
            font-size: 1.1rem;
            border-bottom: 2px solid #f0f2f8;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .img-thumbnail {
            border: 3px solid #f0f2f8;
        }

        @media print {

            .page-header,
            .card-header,
            .btn {
                display: none !important;
            }

            body {
                background: white !important;
                color: black !important;
            }

            .card {
                border: none !important;
            }
        }
    </style>
@endsection
