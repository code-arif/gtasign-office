<?php

namespace App\Http\Controllers\Api\Gig;

use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GigCategoryController extends Controller
{
    use ApiResponse;

    // Get all gig categories
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with(['children:id,name,parent_id'])
            ->get(['id', 'name']);

        return $this->success(
            'Gig categories retrieved successfully',
            $categories
        );
    }
}
