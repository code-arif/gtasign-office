<?php
namespace App\Http\Controllers\Web\Backend;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductModel;
use App\Models\Specialize;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductModelController extends Controller
{

    use ApiResponse;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ProductModel::all();
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

                ->addColumn('category', function ($data) {
                    return $data->category ? $data->category->title : 'N/A';
                })

                ->addColumn('status', function ($data) {
                    $backgroundColor  = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles     = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                        <a href="' . route('admin.model.edit', ['id' => $data->id]) . '" class="btn btn-primary text-white" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="ms-2 btn btn-danger text-white" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                        <a href="' . route('admin.product.create', ['product_model_id' => $data->id]) . '" class="ms-2 btn btn-success text-white" title="Add Items">
                            Add Items
                        </a>
                        </div>';
                })

                ->rawColumns(['image_url', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.product_model.index");
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->get();

        return view('backend.layouts.product_model.create', compact('categories'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'size'        => 'required',
            'image'       => 'required|image|mimes:jpeg,png,jpg',

        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = Helper::uploadImage($request->file('image'), 'product_models');
        }

        $model = ProductModel::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'size'        => $request->size,
            'image_url'   => $imagePath,
        ]);

        return redirect()->route('admin.model.index')->with('t-success', 'Model created successfully');
    }

    public function show(Specialize $specialize, $id)
    {

        return view('backend.layouts.product_model.edit', compact('specialize'));
    }

    public function edit($id)
    {
        $categories = Category::where('status', 'active')->get();

        $product_model = ProductModel::findOrFail($id);

        return view('backend.layouts.product_model.edit', compact('product_model', 'categories'));

    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'size'        => 'required',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg',

        ]);

        $product_model = ProductModel::findOrFail($id);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product_model->image_url && file_exists(public_path($product_model->image_url))) {
                unlink(public_path($product_model->image_url));
            }
            $imagePath                = Helper::uploadImage($request->file('image'), 'product_models');
            $product_model->image_url = $imagePath;
        }

        $product_model->category_id = $request->category_id;
        $product_model->name        = $request->name;
        $product_model->size        = $request->size;
        $product_model->save();

        return redirect()->route('admin.model.index')->with('success', 'Specialize updated successfully');
    }

    public function destroy(string $id)
    {

        $data = ProductModel::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Model not found.',
            ], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Model deleted successfully!',
        ], 200);
    }

}
