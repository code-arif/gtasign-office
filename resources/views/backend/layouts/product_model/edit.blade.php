@extends('backend.app', ['title' => 'Edit Specialize'])

@section('title', 'Dashboard || Edit Specialize')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Edit Specialize</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.model.index') }}">models</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="tab-content">
                        <div class="tab-pane active show" id="editProfile">
                            <div class="card">
                                <div class="card-body border-0">
                                    <form class="form-horizontal" method="post" action="{{ route('admin.model.update', $product_model->id) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')


                                        <div class="mb-4">

                                            <div class="form-group">
                                                <label for="name" class="form-label">Select Category</label>

                                                <select name="category_id" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" {{ $product_model->category_id == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="mt-3">
                                                <label for="name" class="form-label">Name</label>
                                                <input type="text" name="name" id="name"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    value="{{ $product_model->name }}" placeholder="Enter Name">
                                                @error('name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>


                                            <div class="mt-3">
                                                <label for="size" class="form-label">Size</label>
                                                <input type="number" name="size" id="size"
                                                    class="form-control @error('size') is-invalid @enderror"
                                                    value="{{ $product_model->size }}" placeholder="Enter Size">
                                                @error('size')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="mt-3">
                                                <label for="image" class="form-label">Size Info Image</label>
                                                <input type="file" name="image" id="image"
                                                    class="form-control @error('image') is-invalid @enderror">
                                                @if ($product_model->image_url)
                                                    <div class="mt-2">
                                                        <img src="{{ asset($product_model->image_url) }}" alt="Size Info Image" width="150">
                                                    </div>
                                                @endif
                                                @error('image')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>



                                            <div class="form-group mt-4">
                                                <button class="btn btn-primary" type="submit">Update</button>
                                                <a href="{{ route('admin.model.index') }}" class="btn btn-danger">Cancel</a>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div> <!-- tab-pane -->
                    </div> <!-- tab-content -->
                </div> <!-- col -->
            </div> <!-- row -->

        </div> <!-- main-container -->
    </div> <!-- side-app -->
</div> <!-- app-content -->
@endsection

@push('scripts')
<!-- You can add page-specific scripts here if needed -->
@endpush
