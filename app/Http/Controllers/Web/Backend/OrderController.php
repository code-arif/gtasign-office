<?php
namespace App\Http\Controllers\Web\Backend;

use App\Models\Order;
use App\Helper\Helper;
use App\Models\Category;
use App\Models\Specialize;
use App\Traits\ApiResponse;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{

    use ApiResponse;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::with('items.product_item.productModel', 'user')
                ->orderBy('created_at', 'desc');

            return DataTables::of($orders)
                ->addIndexColumn()

                ->addColumn('customer', function ($order) {
                    return $order->user->name ?? 'Guest';
                })

                ->addColumn('branch_code', function ($order) {
                    return $order->user->branch_code ?? 'Guest';
                })

                ->addColumn('email', function ($order) {
                    return $order->user->email ?? 'Guest';
                })

                ->addColumn('phone_number', function ($order) {
                    return $order->user->phone_number ?? 'Guest';
                })

                ->addColumn('items', function ($order) {
                    $list = '<ul class="list-unstyled mb-0">';
                    foreach ($order->items as $item) {
                        $product = $item->product_item->productModel->name ?? '-';
                        $code    = $item->product_item->code ?? '-';
                        $list .= "<li>{$product} ({$code}) x {$item->quantity} = " . number_format($item->total, 2) . "</li>";
                    }
                    $list .= '</ul>';
                    return $list;
                })

                ->addColumn('total_amount', function ($order) {
                    return number_format($order->total_amount, 2);
                })

                ->addColumn('status', function ($order) {
                    if ($order->status == 'pending') {
                        return '<span class="badge bg-warning">Pending</span>';
                    } elseif ($order->status == 'completed') {
                        return '<span class="badge bg-success">Completed</span>';
                    } elseif ($order->status == 'canceled') {
                        return '<span class="badge bg-danger">Canceled</span>';
                    }

                    return '-';
                })

                ->addColumn('action', function ($order) {
                    return '<a href="' . route('admin.order.show', $order->id) . '" class="btn btn-sm btn-primary" target="_blank" >Invoice</a>';
                })

                ->rawColumns(['items', 'action'])
                ->make(true);
        }

        return view("backend.layouts.order.index");
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
        $order = Order::with('items.product_item.productModel', 'user')->findOrFail($id);

        return view('backend.layouts.order.edit', compact('order'));
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
