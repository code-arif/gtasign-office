<?php
namespace App\Http\Controllers\Web\Backend;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\ProductModel;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ProductItem::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image_url', function ($data) {
                    if ($data->image_url) {
                        $url = asset($data->image_url);
                        return '<img src="' . $url . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';
                    } else {
                        return '<img src="' . asset('default/logo.png') . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';
                    }
                })
            // category name
                ->addColumn('model', function ($data) {
                    return $data->productModel ? $data->productModel->name : 'N/A';
                })

                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn ms-2 btn-primary fs-14 text-white delete-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>

                                 <a href="#" type="button" onclick="goToVariant(' . $data->id . ')" class="btn btn-warning ms-2 fs-14 text-white delete-icn" title="Edit">
                                    Variant List
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger ms-3 fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['image_url', 'category', 'action'])
                ->make();
        }
        return view("backend.layouts.product.index");
    }

    public function create(Request $request)
    {
        $product_models    = ProductModel::all();
        $selected_model_id = $request->product_model_id ?? null;

        $existing_items = [];
        if ($selected_model_id) {
            $existing_items = ProductItem::where('product_model_id', $selected_model_id)->get();
        }

        return view('backend.layouts.product.create', compact('product_models', 'selected_model_id', 'existing_items'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'product_model_id'  => 'required|exists:product_models,id',
            'items'             => 'required|array|min:1',
            'items.*.code'      => 'required|string|max:50',
            'items.*.name'      => 'required|string|max:255',
            'items.*.price'     => 'required|numeric|min:0',
            'items.*.is_clearance' => 'nullable|boolean',
            'items.*.discount_percentage' => 'nullable|integer|min:0|max:100',

            'items.*.stock'     => 'nullable|integer|min:0',
            'items.*.image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $product_model_id = $request->product_model_id;

        $existingIds = [];

        foreach ($request->items as $itemData) {

            if (isset($itemData['id'])) {
                // Update existing item
                $item = ProductItem::find($itemData['id']);
                if (! $item) {
                    continue;
                }
                // skip if item not found
            } else {
                // Create new item
                $item                   = new ProductItem();
                $item->product_model_id = $product_model_id;
            }

            $item->code  = $itemData['code'];
            $item->name  = $itemData['name'];
            $item->price = $itemData['price'];
            $item->is_clearance = isset($itemData['is_clearance']) ? (bool) $itemData['is_clearance'] : false;
            $item->discount_percentage = $itemData['discount_percentage'] ?? null;
            $item->stock = $itemData['stock'] ?? 0;

            // Handle image upload
            if (isset($itemData['image_url']) && $itemData['image_url'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if exists
                if ($item->image_url && file_exists(public_path($item->image_url))) {
                    Helper::fileDelete(public_path($item->image_url));
                }
                $item->image_url = Helper::uploadImage($itemData['image_url'], 'product_items');
            }


            $item->save();
            $existingIds[] = $item->id;
        }

        // Optional: delete removed items
        ProductItem::where('product_model_id', $product_model_id)
            ->whereNotIn('id', $existingIds)
            ->delete();

        return redirect()->route('admin.product.create', ['product_model_id' => $product_model_id])
            ->with('success', 'Product items saved successfully!');
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return view('backend.layouts.product.edit', compact('category'));
    }

    public function edit($id)
    {
        $product = Product::find($id);

        $categories = Category::all();

        return view('backend.layouts.product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validate the request data
        $validated = $request->validate([
            'category_id'           => 'required|exists:categories,id',
            'name'                  => 'required|string|max:255',
            'size'                  => 'required|integer',
            'description'           => 'nullable|string',
            'base_price'            => 'nullable|numeric',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'variants'              => 'required|array|min:1',
            'variants.*.code'       => 'required|string|max:255',
            'variants.*.color_name' => 'required|string|max:255',
            'variants.*.price'      => 'required|numeric',
            'variants.*.stock'      => 'required|integer|min:0',
            'variants.*.image'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Handle main product image upload
            $imageUrl = $product->image_url;

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image_url && file_exists(public_path($product->image_url))) {
                    Helper::fileDelete(public_path($product->image_url));
                }
                $imageUrl = Helper::uploadImage($request->file('image'), 'products');
            }

            // Update the product
            $product->update([
                'category_id' => $validated['category_id'],
                'name'        => $validated['name'],
                'size'        => $validated['size'],
                'description' => $validated['description'],
                'base_price'  => $validated['base_price'],
                'image_url'   => $imageUrl,
            ]);

            // Sync variants
            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $newVariants        = [];

            foreach ($validated['variants'] as $index => $variantData) {
                $variantImageUrl = null;

                if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {

                    // Update existing variant
                    $variant = ProductVariant::find($variantData['id']);
                    if ($request->hasFile("variants.{$index}.image")) {
                        // Delete old variant image if exists
                        // if ($variant->image_url && Storage::exists(str_replace('storage/', 'public/', $variant->image_url))) {
                        //     Storage::delete(str_replace('storage/', 'public/', $variant->image_url));
                        // }

                        if ($variant->image_url && file_exists(public_path($variant->image_url))) {
                            Helper::fileDelete(public_path($variant->image_url));
                        }

                        $variantImageUrl = Helper::uploadImage($request->file("variants.{$index}.image"), 'variants');
                    } else {
                        $variantImageUrl = $variant->image_url;
                    }

                    $variant->update([
                        'code'       => $variantData['code'],
                        'color_name' => $variantData['color_name'],
                        'price'      => $variantData['price'],
                        'stock'      => $variantData['stock'],
                        'image_url'  => $variantImageUrl,
                    ]);

                    $newVariants[] = $variant->id;

                } else {

                    // Create new variant
                    if ($request->hasFile("variants.{$index}.image")) {
                        $variantImageUrl = Helper::uploadImage($request->file("variants.{$index}.image"), 'variants');
                    }
                    $newVariant = ProductVariant::create([
                        'product_id' => $product->id,
                        'code'       => $variantData['code'],
                        'color_name' => $variantData['color_name'],
                        'price'      => $variantData['price'],
                        'stock'      => $variantData['stock'],
                        'image_url'  => $variantImageUrl,
                    ]);
                    $newVariants[] = $newVariant->id;

                }
            }

            // Delete variants that were removed
            $variantsToDelete = array_diff($existingVariantIds, $newVariants);
            ProductVariant::whereIn('id', $variantsToDelete)->delete();

            session()->put('t-success', 'Product updated successfully');

        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.product.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        try {
            $product = Product::with('variants')->findOrFail($id);

            // Delete product image if it exists
            if ($product->image_url && file_exists(public_path($product->image_url))) {
                Helper::fileDelete(public_path($product->image_url));
            }
            if ($product->variants) {
                foreach ($product->variants as $variant) {
                    if ($variant->image_url && file_exists(public_path($variant->image_url))) {
                        Helper::fileDelete(public_path($variant->image_url));
                    }
                }
            }

            // Delete the product
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!',
            ], 200);

        } catch (Exception $e) {
            session()->put('t-error', 'Failed to delete product: ' . $e->getMessage());
        }

        return redirect()->route('admin.product.index')->with('success', 'Product deleted successfully');
    }

    public function status(int $id): JsonResponse
    {
        $data = Category::findOrFail($id);

        if (! $data) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Category not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status'  => 'success',
            'message' => 'Status Changed successful!',
        ]);
    }

    public function variant(Request $request, $id)
    {

        if ($request->ajax()) {
            $product = Product::with('variants')->findOrFail($id);

            return DataTables::of($product->variants)
                ->addIndexColumn()
                ->addColumn('image_url', function ($variant) {
                    return $variant->image_url ? asset($variant->image_url) : asset('default/logo.png');
                })
                ->addColumn('action', function ($variant) use ($id) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                        <a href="#" type="button" onclick="showDeleteConfirm(' . $variant->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                            <i class="fe fe-trash"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['image_url', 'action'])
                ->make(true);

        }

        $product = Product::with('variants')->findOrFail($id);

        return view('backend.layouts.product.variant', compact('product'));

    }

    public function destroyVariant($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);

            // Delete variant image if it exists
            if ($variant->image_url && file_exists(public_path($variant->image_url))) {
                Helper::fileDelete(public_path($variant->image_url));
            }

            // Delete the variant
            $variant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product variant deleted successfully!',
            ], 200);

        } catch (Exception $e) {
            session()->put('t-error', 'Failed to delete product variant: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Product variant deleted successfully');

    }

}
