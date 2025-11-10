<?php
namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCategories = Category::count();

        $total_items = ProductItem::count();
        $total_users = User::where('role', 'user')->count();

        //  product items with their stock
        $stockItems = ProductItem::with('productModel')
            ->select('id', 'name', 'code', 'stock', 'product_model_id')
            ->orderBy('stock', 'desc')
            ->get();

        return view('backend.layouts.dashboard', compact('totalCategories', 'total_items','stockItems', 'total_users'));
    }
}
