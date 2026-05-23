<?php

namespace App\Http\Controllers\Api\Gig;

use App\Models\Category;
use App\Models\Gig;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GigCategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get all gig categories (with sub-categories)
     */
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

    /**
     * Get popular categories
     *
     * Returns up to 6 parent categories that have the most active gigs,
     * shuffled so the order is fresh on every call.
     *
     * @GET /gigs/popular-categories  (no auth required)
     */
    public function popular()
    {
        // Pull the top 12 most-used parent categories (by active gig count),
        // then shuffle and slice to 6 — giving variety while still staying popular.
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->withCount([
                'gigs as gig_count' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->having('gig_count', '>', 0)
            ->orderByDesc('gig_count')
            ->limit(12)        // fetch a wider pool first …
            ->get(['id', 'name', 'image'])
            ->shuffle()        // … then randomise …
            ->take(6)          // … and cap at 6
            ->values()
            ->map(function ($category) {
                return [
                    'id'        => $category->id,
                    'name'      => $category->name,
                    'image_url' => $category->image_url,  // uses the accessor on Category
                    'gig_count' => $category->gig_count,
                ];
            });

        return $this->success(
            'Popular categories retrieved successfully',
            $categories
        );
    }
}
