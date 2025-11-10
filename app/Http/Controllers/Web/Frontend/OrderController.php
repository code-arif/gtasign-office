<?php

namespace App\Http\Controllers\Web\Frontend;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // dd($request->all());
        $cartItems = $request->input('cart', []);

        if (empty($cartItems)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty']);
        }

        // Create order
        $order = Order::create([
            'user_id' => Auth::id() ?? null, // optional if user logged in
            'total_amount' => $request->input('total_amount'),
            'status' => 'pending',
        ]);

        // Save order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_item_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => $item['discount'],
                'total' => $item['total'],
            ]);
        }

        $user = Auth::user();

        Cart::where('user_id', $user->id)->delete();

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }


     // Optional: Thank you page
    public function thankYou()
    {
        return view('website.layouts.cart.thank');
    }
}
