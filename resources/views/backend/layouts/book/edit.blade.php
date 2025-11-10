@extends('backend.app', ['title' => 'Edit Product'])

@section('title', 'Dashboard || Edit Product')
@section('content')

    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Products</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Products</a></li>
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
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <form class="form-horizontal" method="post"
                                            action="{{ route('admin.product.update', $product->id) }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <!-- Category -->
                                            <div class="form-group mt-4">
                                                <label for="category_id" class="form-label">Category</label>
                                                <select name="category_id" id="category_id" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Product Name -->
                                            <div class="form-group mt-4">
                                                <label for="name" class="form-label">Product Name (e.g., FS-1)</label>
                                                <input type="text" name="name" id="name" class="form-control"
                                                    value="{{ old('name', $product->name) }}" required>
                                            </div>

                                            <!-- Size -->
                                            <div class="form-group mt-4">
                                                <label for="size" class="form-label">Size (e.g., 53)</label>
                                                <input type="number" name="size" id="size" class="form-control"
                                                    value="{{ old('size', $product->size) }}" required>
                                            </div>

                                            <!-- Description -->
                                            <div class="form-group mt-4">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                                            </div>

                                            <!-- Base Price -->
                                            <div class="form-group mt-4">
                                                <label for="base_price" class="form-label">Base Price</label>
                                                <input type="number" name="base_price" id="base_price" step="0.01"
                                                    class="form-control"
                                                    value="{{ old('base_price', $product->base_price) }}" required>
                                            </div>

                                            <!-- Main Product Image -->
                                            <div class="form-group mt-4">
                                                <label for="image" class="form-label">Main Product Image</label>
                                                <input type="file" name="image" id="image" class="form-control"
                                                    accept="image/*">
                                                @if ($product->image_url)
                                                    <img src="{{ asset($product->image_url) }}" alt="Current Image"
                                                        style="max-width: 200px; margin-top: 10px;">
                                                    <p>Leave empty to keep current image.</p>
                                                @endif
                                            </div>

                                            <!-- Variants Section -->
                                            <div class="form-group mt-4">
                                                <h4>Variants</h4>
                                                <div id="variants-container">
                                                    @foreach ($product->variants as $index => $variant)
                                                        <div class="variant-row {{ $loop->first ? '' : 'mt-3' }}">
                                                            <div class="row">
                                                                <div class="col-md-2">
                                                                    <label for="variants[{{ $index }}][code]"
                                                                        class="form-label">Code</label>
                                                                    <input type="text"
                                                                        name="variants[{{ $index }}][code]"
                                                                        class="form-control"
                                                                        value="{{ old("variants.$index.code", $variant->code) }}"
                                                                        required>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label for="variants[{{ $index }}][color_name]"
                                                                        class="form-label">Color Name</label>
                                                                    <input type="text"
                                                                        name="variants[{{ $index }}][color_name]"
                                                                        class="form-control"
                                                                        value="{{ old("variants.$index.color_name", $variant->color_name) }}"
                                                                        required>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label for="variants[{{ $index }}][price]"
                                                                        class="form-label">Price</label>
                                                                    <input type="number"
                                                                        name="variants[{{ $index }}][price]"
                                                                        step="0.01" class="form-control"
                                                                        value="{{ old("variants.$index.price", $variant->price) }}"
                                                                        required>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label for="variants[{{ $index }}][stock]"
                                                                        class="form-label">Stock</label>
                                                                    <input type="number"
                                                                        name="variants[{{ $index }}][stock]"
                                                                        class="form-control"
                                                                        value="{{ old("variants.$index.stock", $variant->stock) }}"
                                                                        required>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="variants[{{ $index }}][image]"
                                                                        class="form-label">Variant Image</label>
                                                                    <input type="file"
                                                                        name="variants[{{ $index }}][image]"
                                                                        class="form-control" accept="image/*">
                                                                    @if ($variant->image_url)
                                                                        <img src="{{ asset($variant->image_url) }}"
                                                                            alt="Current Variant Image"
                                                                            style="max-width: 200px; margin-top: 10px;">
                                                                        <p>Leave empty to keep current image.</p>
                                                                    @endif
                                                                </div>
                                                                @if (!$loop->first)
                                                                    <div class="col-md-1">
                                                                        <button type="button" class="btn btn-danger mt-4"
                                                                            onclick="removeVariantRow(this)">Remove</button>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-secondary mt-2"
                                                    id="add-variant-btn" onclick="addVariantRow()">Add Another
                                                    Variant</button>
                                            </div>

                                            <!-- Submit and Cancel Buttons -->
                                            <div class="form-group mt-4">
                                                <button class="btn btn-primary" type="submit">Update</button>
                                                <a href="{{ route('admin.product.index') }}"
                                                    class="btn btn-danger">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <script>
        let variantIndex = {{ $product->variants->count() - 1 }};

        // Function to check if all required fields in the last variant are filled
        function checkVariantFields() {
            const rows = document.querySelectorAll('.variant-row');
            if (rows.length === 0) return true; // Allow adding first variant
            const lastRow = rows[rows.length - 1];
            const requiredInputs = lastRow.querySelectorAll('input[required]');
            return Array.from(requiredInputs).every(input => input.value.trim() !== '');
        }

        // Enable/disable the Add button based on field validation
        function updateAddButton() {
            const addButton = document.getElementById('add-variant-btn');
            addButton.disabled = !checkVariantFields();
        }

        // Add a new variant row
        function addVariantRow() {
            if (!checkVariantFields()) return; // Prevent adding if fields are not filled
            variantIndex++;
            const container = document.getElementById('variants-container');
            const row = document.createElement('div');
            row.className = 'variant-row mt-3';
            row.innerHTML = `
            <div class="row">
                <div class="col-md-2">
                    <label for="variants[${variantIndex}][code]" class="form-label">Code</label>
                    <input type="text" name="variants[${variantIndex}][code]" class="form-control" id="variant-${variantIndex}-code" required>
                </div>
                <div class="col-md-2">
                    <label for="variants[${variantIndex}][color_name]" class="form-label">Color Name</label>
                    <input type="text" name="variants[${variantIndex}][color_name]" class="form-control" id="variant-${variantIndex}-color_name" required>
                </div>
                <div class="col-md-2">
                    <label for="variants[${variantIndex}][price]" class="form-label">Price</label>
                    <input type="number" name="variants[${variantIndex}][price]" step="0.01" class="form-control" id="variant-${variantIndex}-price" required>
                </div>
                <div class="col-md-2">
                    <label for="variants[${variantIndex}][stock]" class="form-label">Stock</label>
                    <input type="number" name="variants[${variantIndex}][stock]" class="form-control" id="variant-${variantIndex}-stock" required>
                </div>
                <div class="col-md-3">
                    <label for="variants[${variantIndex}][image]" class="form-label">Variant Image</label>
                    <input type="file" name="variants[${variantIndex}][image]" class="form-control" id="variant-${variantIndex}-image">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger mt-4" onclick="removeVariantRow(this)">Remove</button>
                </div>
            </div>
        `;
            container.appendChild(row);
            // Reattach event listeners for new inputs
            const newInputs = row.querySelectorAll('input');
            newInputs.forEach(input => {
                input.addEventListener('input', updateAddButton);
            });
        }

        // Remove a variant row
        function removeVariantRow(button) {
            const row = button.closest('.variant-row');
            if (row) {
                row.remove();
                // Renumber remaining variant indices
                renumberVariants();
                updateAddButton(); // Update button state after removal
            }
        }

        // Renumber variant indices
        function renumberVariants() {
            const rows = document.querySelectorAll('.variant-row');
            rows.forEach((row, index) => {
                const inputs = row.querySelectorAll('input');
                inputs.forEach(input => {
                    const name = input.getAttribute('name').replace(/\[\d+\]/, `[${index}]`);
                    input.setAttribute('name', name);
                    input.setAttribute('id', input.getAttribute('id').replace(/\[\d+\]/, `[${index}]`));
                });
            });
            variantIndex = rows.length - 1;
        }

        // Initial setup: Add event listeners to existing inputs
        document.addEventListener('DOMContentLoaded', () => {
            const initialInputs = document.querySelectorAll('#variants-container input[required]');
            initialInputs.forEach(input => {
                input.addEventListener('input', updateAddButton);
            });
            updateAddButton(); // Initial check
        });
    </script>
@endpush
