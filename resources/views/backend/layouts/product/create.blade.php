@extends('backend.app', ['title' => 'Manage Product Items'])

@section('title', 'Dashboard || Manage Product Items')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">Manage Product Items</h1>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
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

                                <form method="POST" action="{{ route('admin.product.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="form-group mt-4">
                                        <label for="product_model_id">Product Models</label>
                                        <select name="product_model_id" id="product_model_id" class="form-control" required>
                                            <option value="">Select Model</option>
                                            @foreach ($product_models as $model)
                                                <option value="{{ $model->id }}"
                                                    {{ isset($selected_model_id) && $selected_model_id == $model->id ? 'selected' : '' }}>
                                                    Model {{ $model->name }} || Size {{ $model->size }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <table class="table table-bordered" id="items_table">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>Mark as Stock Clearance</th>
                                                <th>Discount (%)</th>
                                                <th>Stock</th>
                                                <th>Image</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($existing_items))
                                                @foreach ($existing_items as $index => $item)
                                                    <tr>
                                                        <td>
                                                            <input type="text" name="items[{{ $index }}][code]"
                                                                value="{{ $item->code }}" class="form-control" required>
                                                            <input type="hidden" name="items[{{ $index }}][id]"
                                                                value="{{ $item->id }}">
                                                        </td>
                                                        <td><input type="text" name="items[{{ $index }}][name]"
                                                                value="{{ $item->name }}" class="form-control" required>
                                                        </td>
                                                        <td><input type="number" name="items[{{ $index }}][price]"
                                                                value="{{ $item->price }}" step="0.01"
                                                                class="form-control" required></td>
                                                        <td>
                                                            <input type="hidden" name="items[{{ $index }}][is_clearance]" value="0">
                                                            <input type="checkbox" name="items[{{ $index }}][is_clearance]" value="1" {{ $item->is_clearance ? 'checked' : '' }}>
                                                        </td>


                                                        <td><input type="number"
                                                                name="items[{{ $index }}][discount_percentage]"
                                                                value="{{ $item->discount_percentage }}"
                                                                class="form-control"></td>
                                                        <td><input type="number" name="items[{{ $index }}][stock]"
                                                                value="{{ $item->stock }}" class="form-control"></td>
                                                        <td>
                                                            <input type="file"
                                                                name="items[{{ $index }}][image_url]"
                                                                class="form-control">
                                                            @if ($item->image_url)
                                                                <img src="{{ asset($item->image_url) }}" width="50"
                                                                    class="mt-1">
                                                            @endif
                                                        </td>
                                                        <td><button type="button"
                                                                class="btn btn-danger btn-sm removeRow">X</button></td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td><input type="text" name="items[0][code]" class="form-control"
                                                            required></td>
                                                    <td><input type="text" name="items[0][name]" class="form-control"
                                                            required></td>
                                                    <td><input type="number" name="items[0][price]" step="0.01"
                                                            class="form-control" required></td>
                                                    <td><input type="checkbox" name="items[0][is_clearance]"></td>
                                                    <td><input type="number" name="items[0][discount_percentage]"
                                                            class="form-control"></td>
                                                    <td><input type="number" required name="items[0][stock]"
                                                            class="form-control"></td>
                                                    <td><input type="file" required name="items[0][image_url]"
                                                            class="form-control"></td>
                                                    <td><button type="button"
                                                            class="btn btn-danger btn-sm removeRow">X</button></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>

                                    <button type="button" id="addRow" class="btn btn-secondary mt-2">+ Add More</button>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">Save Items</button>
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
        let rowIndex = {{ isset($existing_items) ? count($existing_items) : 1 }};

        document.getElementById('addRow').addEventListener('click', function() {
            const table = document.getElementById('items_table').getElementsByTagName('tbody')[0];
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td><input type="text" name="items[${rowIndex}][code]" class="form-control" required></td>
                <td><input type="text" name="items[${rowIndex}][name]" class="form-control" required></td>

                <td><input type="number" name="items[${rowIndex}][price]" step="0.01" class="form-control" required></td>

                <td>
                    <input type="hidden" name="items[${rowIndex}][is_clearance]" value="0">
                    <input type="checkbox" name="items[${rowIndex}][is_clearance]" value="1">
                </td>

                <td><input type="number" name="items[${rowIndex}][discount_percentage]" class="form-control"></td>
                <td><input type="number" required name="items[${rowIndex}][stock]"  class="form-control"></td>
                <td><input type="file" required name="items[${rowIndex}][image_url]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
            `;

            table.appendChild(newRow);
            rowIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
@endpush
