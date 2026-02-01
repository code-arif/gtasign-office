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
                    Gtting started
                ======================================== --}}
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h3 class="card-title">
                                    <i class="fa fa-users"></i> Getting started
                                </h3>
                            </div>
                        </div>
                    </div>

                    {{-- Getting stated section HEADER --}}
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Gettign Started Header</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.cms.about.getting-started-header.store') }}">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <label>Section Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ old('title', $data->title ?? 'Ready to Elevate Your Officiating Career?') }}" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Section Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ old('description', $data->description ?? '') }}</textarea>
                                        <small class="text-muted">This appears below the "Meet Our Team" heading</small>
                                    </div>

                                    <button class="btn btn-info">
                                        <i class="fa fa-save"></i> Save Team Header
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
