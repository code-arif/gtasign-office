@extends('backend.app')

@section('title', 'Hero section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Home page - Hero section</h1>
                        <small>Home page hero section all data</small>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <span>&nbsp; &gt&gt &nbsp;</span>
                            <li class="breadcrumb-item active" aria-current="page">Hero section</li>
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
                                    action="{{ route('admin.cms.home.hero.section.update') }}"
                                    enctype="multipart/form-data">
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

                                        {{-- section description --}}
                                        <div class="form-group mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                                                placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Hero Video Upload --}}
                                        <div class="form-group mb-3">
                                            <label for="video" class="form-label">Hero Video</label>

                                            <input type="file" name="video" id="video"
                                                class="form-control dropify @error('video') is-invalid @enderror"
                                                data-default-file="{{ isset($data->video) ? asset($data->video) : '' }}"
                                                data-allowed-file-extensions="mp4 mov avi webm" data-max-file-size="50M" />

                                            @error('video')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        @if (!empty($data->video))
                                            <div class="mb-3">
                                                <label class="form-label">Current Video</label>
                                                <div>
                                                    <video width="320" height="180" controls
                                                        style="border-radius:8px;">
                                                        <source src="{{ asset($data->video) }}" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">Save change</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection
