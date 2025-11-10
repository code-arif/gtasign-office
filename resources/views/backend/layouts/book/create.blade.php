@extends('backend.app', ['title' => 'Create Book'])
@section('title', 'Admin || Create Book')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Create Book</h1>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.book.index') }}">Books</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <!-- CREATE BOOK FORM -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">New Book</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.book.store') }}" enctype="multipart/form-data">
                                    @csrf

                                    <!-- Title & Author -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="title" class="form-label">Title:</label>
                                            <input type="text" name="title" id="title"
                                                class="form-control @error('title') is-invalid @enderror"
                                                placeholder="Enter book title" value="{{ old('title') }}">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="author" class="form-label">Author:</label>
                                            <input type="text" name="author" id="author"
                                                class="form-control @error('author') is-invalid @enderror"
                                                placeholder="Enter author name" value="{{ old('author') }}">
                                            @error('author')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Category & ISBN -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="category_id" class="form-label">Category:</label>
                                            <select name="category_id" id="category_id"
                                                class="form-control @error('category_id') is-invalid @enderror">
                                                <option value="">Select Category</option>
                                                @if (!empty($categories))
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->title }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="isbn" class="form-label">ISBN:</label>
                                            <input type="text" name="isbn" id="isbn"
                                                class="form-control @error('isbn') is-invalid @enderror"
                                                placeholder="Enter ISBN" value="{{ old('isbn') }}">
                                            @error('isbn')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Premium Access -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-check mt-4">
                                                <input type="checkbox" name="is_premium" id="is_premium"
                                                    class="form-check-input" value="1"
                                                    {{ old('is_premium') ? 'checked' : '' }}>
                                                <label for="is_premium" class="form-check-label"><strong>Require
                                                        Subscription</strong></label>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <strong>OFF</strong> → Free for all <br>
                                                <strong>ON</strong> → Only subscribers
                                            </small>
                                        </div>


                                        <div class="col-md-6">
                                            <label for="published_at" class="form-label">Published At:</label>
                                            <input type="datetime-local" name="published_at" id="published_at"
                                                class="form-control @error('published_at') is-invalid @enderror"
                                                value="{{ old('published_at') }}">
                                            @error('published_at')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr>


                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="ebook_file" class="form-label">Ebook File (PDF/EPUB):</label>
                                            <input type="file" name="ebook_file" id="ebook_file"
                                                class="form-control @error('ebook_file') is-invalid @enderror"
                                                accept=".pdf,.epub">
                                            @error('ebook_file')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="file_format" class="form-label">File Format:</label>
                                            <select name="file_format" id="file_format"
                                                class="form-control @error('file_format') is-invalid @enderror">
                                                <option value="pdf" {{ old('file_format') == 'pdf' ? 'selected' : '' }}>
                                                    PDF</option>
                                                <option value="epub"
                                                    {{ old('file_format') == 'epub' ? 'selected' : '' }}>EPUB</option>
                                            </select>
                                            @error('file_format')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr>









                                    <!-- Description -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label for="description" class="form-label">Description:</label>
                                            <textarea name="description" id="description"
                                                class="form-control summernote @error('description') is-invalid @enderror" rows="4"
                                                placeholder="Enter book description">{{ old('description') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Cover Image -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="cover_image" class="form-label">Cover Image:</label>
                                            <input type="file" name="cover_image" id="cover_image"
                                                class="dropify form-control @error('cover_image') is-invalid @enderror">
                                            @error('cover_image')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Multiple Images -->
                                        <div class="col-md-6">
                                            <label for="images" class="form-label">Additional Images:</label>
                                            <input type="file" name="images[]" id="images"
                                                class="form-control @error('images') is-invalid @enderror" multiple>
                                            @error('images')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <!-- Preview -->
                                            <div id="imagePreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary">Create Book</button>
                                        <a href="{{ route('admin.book.index') }}" class="btn btn-secondary">Cancel</a>
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

@push('scripts')
    <script>
        // Multiple images preview
        document.getElementById('images').addEventListener('change', function() {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            const files = this.files;

            if (files) {
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '80px';
                        img.style.height = '80px';
                        img.style.objectFit = 'cover';
                        img.style.border = '1px solid #ddd';
                        img.style.padding = '2px';
                        img.style.borderRadius = '5px';
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
@endpush
