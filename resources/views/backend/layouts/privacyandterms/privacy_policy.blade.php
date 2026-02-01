@extends('backend.app', ['title' => 'Privacy Policy'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Privacy Policy</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Terms and Privacy</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
                        </ol>
                    </div>
                </div>
                {{-- PAGE-HEADER --}}

                <div class="row">
                    <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <form class="form form-horizontal" method="post"
                                    action="{{ route('admin.cms.privecyandterms.privacy.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('POST')

                                    <div class="row mb-4">

                                        <div class="form-group mt-3">
                                            <label for="description" class="form-label">Description:</label>
                                            <textarea class="form-control summernote @error('description') is-invalid @enderror" name="description" id="description"
                                                rows="10">{{ $privacy->description ?? (old('description') ?? '') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button class="submit btn btn-primary" type="submit">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
