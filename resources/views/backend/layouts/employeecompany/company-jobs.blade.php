@extends('backend.app', ['title' => 'Company Job Details'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            {{-- Page Header --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title mb-1">Jobs at {{ $company->company->name ?? 'Company' }}</h1>
                    <p class="mb-0 text-muted">{{ $jobs->count() }} active job postings</p>
                </div>
                <a href="{{ route('admin.company.show', $company->id) }}" class="btn btn-outline-primary">
                    <i class="fe fe-arrow-left me-2"></i>Back to Profile
                </a>
            </div>

            {{-- Job List --}}
            <div class="row">
                @forelse ($jobs as $job)
                <div class="col-md-6 col-xl-4 mb-5">
                    <div class="card job-card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1">{{ $job->title ?? 'Untitled Job' }}</h4>
                                    <span class="badge bg-primary-transparent text-primary">
                                        {{ $job->job_category->title ?? 'General' }}
                                    </span>
                                </div>
                                <span class="text-muted">{{ $job->created_at->diffForHumans() }}</span>
                            </div>

                            <div class="job-meta mb-4">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="job-meta-item">
                                        <i class="fe fe-map-pin me-1"></i> {{ $job->location ?? 'Remote' }}
                                    </span>
                                    <span class="job-meta-item">
                                        <i class="fe fe-briefcase me-1"></i> {{ $job->job_type ?? 'Full-time' }}
                                    </span>
                                    <span class="job-meta-item">
                                        <i class="fe fe-dollar-sign me-1"></i>
                                        {{ $job->salary ?? 'Negotiable' }} {{ $job->salary_type ? "($job->salary_type)" : '' }}
                                    </span>
                                </div>
                            </div>

                            <div class="job-details mb-4">
                                <h6 class="text-uppercase fw-bold mb-2">Requirements</h6>
                                <ul class="list-unstyled mb-3">
                                    @if($job->year_of_experience)
                                    <li class="mb-1">
                                        <i class="fe fe-award me-1 text-success"></i>
                                        {{ $job->year_of_experience }} years experience
                                    </li>
                                    @endif
                                    @if($job->education)
                                    <li class="mb-1">
                                        <i class="fe fe-book me-1 text-info"></i>
                                        {{ $job->education }}
                                    </li>
                                    @endif
                                    @if($job->certification)
                                    <li class="mb-1">
                                        <i class="fe fe-check-circle me-1 text-warning"></i>
                                        {{ $job->certification }}
                                    </li>
                                    @endif
                                </ul>

                                @if($job->benefits)
                                <h6 class="text-uppercase fw-bold mb-2">Benefits</h6>
                                <div class="benefits-text mb-3">
                                    {!! nl2br(e($job->benefits)) !!}
                                </div>
                                @endif
                            </div>

                            <div class="job-description">
                                <h6 class="text-uppercase fw-bold mb-2">Job Description</h6>
                                <div class="description-text">
                                    {!! nl2br(e($job->description)) !!}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="display-4 text-muted mb-4">
                                <i class="fe fe-briefcase"></i>
                            </div>
                            <h3>No Job Postings Available</h3>
                            <p class="text-muted">This company hasn't posted any jobs yet.</p>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

<style>
    .job-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        height: 100%;
    }
    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .job-meta-item {
        background: #f8f9fa;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
    }
    .benefits-text, .description-text {
        font-size: 0.9rem;
        line-height: 1.6;
        color: #495057;
    }
    .page-title {
        font-weight: 600;
        font-size: 1.75rem;
    }
    .job-card h4 {
        font-weight: 600;
        color: #2d3748;
    }
    .card-footer {
        border-top: 1px dashed #e9ecef;
    }
</style>
@endsection
