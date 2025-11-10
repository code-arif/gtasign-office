<?php
namespace App\Http\Controllers\Web\Frontend;

use App\Models\Category;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index($slug)
    {
        $category = Category::where('slug', $slug)
            ->with([
                'productModels' => function ($q) {
                    $q->with(['items' => function ($q2) {
                        $q2->where('is_clearance', 0); // only non-clearance items
                    }]);
                },
            ])
            ->first();

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $product_models = $category->productModels;
        $category = $category->title;


        return view('website.layouts.product.index', compact('category', 'product_models'));
    }

    public function search(Request $request)
    {
        $keyword = $request->search;

        // dd($keyword);

        // Search in model name, size, and item code/name
        $product_models = ProductModel::where('name', 'like', "%$keyword%")
            ->orWhere('size', 'like', "%$keyword%")
            ->orWhereHas('items', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('code', 'like', "%$keyword%");
            })
            ->with('items')
            ->get();

        // dd($product_models);

        $category =  "Search Results";

        return view('website.layouts.product.index', compact('category', 'product_models'));
    }

    public function clearance()
    {
        $category = "Stock Clearance";

        // product models need with item is clearance
        $product_models = ProductModel::whereHas('items', function ($q) {
            $q->where('is_clearance', 1);
        })->with(['items' => function ($q) {
            $q->where('is_clearance', 1);
        }])->get();

        // dd($product_models);

        return view('website.layouts.product.index', compact('category', 'product_models'));
    }

}
