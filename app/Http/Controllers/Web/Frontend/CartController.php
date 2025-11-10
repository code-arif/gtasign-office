<?php
namespace App\Http\Controllers\Web\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    public function index()
    {
        $carts = Cart::with('productItem.productModel')->where('user_id', Auth::id())->get();

        return view('website.layouts.cart.index', compact('carts'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();

        // Role check
        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can add items to the cart.'
            ], 403);
        }

        $request->validate([
            'items'            => 'required|array',
            'items.*.id'       => 'required|exists:product_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {

                // lock the row to prevent race conditions
                $product_item = ProductItem::where('id', $item['id'])->lockForUpdate()->firstOrFail();

                if ($product_item->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product item {$item['id']}");
                }

                Cart::updateOrCreate(
                    [
                        'user_id'         => $user->id,
                        'product_item_id' => $item['id'],
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'price'    => $product_item->price,
                    ]
                );

                // reduce the stock of the product item
                $product_item->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'       => 'required|exists:carts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('id', $request->id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        $product_item = ProductItem::find($cart->product_item_id);

        $oldQuantity = $cart->quantity;
        $newQuantity = $request->quantity;
        $diff        = $newQuantity - $oldQuantity; // positive if adding, negative if reducing

        // Check stock if adding items
        if ($diff > 0 && $product_item->stock < $diff) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock for the requested quantity.',
            ], 422);
        }

        // Update cart quantity
        $cart->update(['quantity' => $newQuantity]);

        // Adjust stock
        if ($diff != 0) {
            $product_item->decrement('stock', $diff); // will decrement if diff>0, increment if diff<0
        }

        return response()->json([
            'success'    => true,
            'item_total' => number_format($cart->price * $cart->quantity, 2),
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:carts,id',
        ]);

        $cart = Cart::where('id', $request->id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        $product_item = ProductItem::find($cart->product_item_id);
        // Restore stock
        $product_item->increment('stock', $cart->quantity);

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully.',
        ]);
    }

}
