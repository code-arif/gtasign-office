<?php

namespace App\Http\Controllers\Api\Gig;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tag;

class GigTagController extends Controller
{
    use ApiResponse;

    // Get all gig categories
    public function index()
    {
        $tags = Tag::get(['id', 'name']);

        return $this->success(
            'Gig tags retrieved successfully',
            $tags
        );
    }
}
