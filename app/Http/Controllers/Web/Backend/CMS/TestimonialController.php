<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Models\Review;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;
use App\Models\Testimonials;
use Yajra\DataTables\Facades\DataTables;

class TestimonialController extends Controller
{
    /**
     * show home page testimonial section data and section item
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $reviews = Testimonials::latest('id')->get();

            return DataTables::of($reviews)
                ->addIndexColumn()
                ->addColumn('author_name', fn($row) => $row->author_name)
                ->addColumn('designation', fn($row) => $row->designation)
                ->addColumn('author_avatar', function ($row) {
                    if ($row->author_avatar) {
                        return '<img src="' . asset($row->author_avatar) . '" alt="' . $row->author_name . '" width="80">';
                    }
                    return '---';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex gap-1">
                    <button type="button" class="btn btn-info btn-sm viewReview" data-id="' . $row->id . '">
                        <i class="fe fe-eye"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm editReview" data-id="' . $row->id . '">
                        <i class="fe fe-edit"></i>
                    </button>
                    <button type="button" onclick="showDeleteConfirm(' . $row->id . ')" class="btn btn-danger btn-sm">
                        <i class="fe fe-trash"></i>
                    </button>
                    </div>';
                })
                ->rawColumns(['author_avatar', 'action'])
                ->make(true);
        }

        $count = Testimonials::count();
        $data = CMS::where('page', 'home')->where('section', 'testimonial')->where('name', 'item')->first();

        return view('backend.layouts.cms.home.testimonial', compact(['count', 'data']));
    }


    /**
     * update testimonial section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'home')
                ->where('section', 'testimonial')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'testimonial',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    // store review
    public function storeReview(Request $request)
    {
        $validatedData = $request->validate([
            'author_name'   => 'required|string|max:100|unique:testimonials,author_name',
            'review_text'   => 'nullable|string',
            'designation'   => 'nullable|string|max:100',
            'author_avatar' => 'nullable|image|max:5120',
        ]);

        try {
            // Handle image upload if provided
            if ($request->hasFile('author_avatar')) {
                $validatedData['author_avatar'] = Helper::fileUpload($request->file('author_avatar'), 'testimonial/images');
            }

            Testimonials::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Testimonial created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Edit review
    public function editReview($id)
    {
        try {
            $review = Testimonials::find($id);

            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Testimonial not found.'], 404);
            }

            return response()->json(['success' => true, 'data' => $review]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch testimonial. ' . $e->getMessage()]);
        }
    }

    // View review
    public function showReview($id)
    {
        try {
            $review = Testimonials::find($id);

            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Testimonial not found.'], 404);
            }

            return response()->json(['success' => true, 'data' => $review]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch testimonial. ' . $e->getMessage()]);
        }
    }

    // update review
    public function updateReview(Request $request, $id)
    {
        $review = Testimonials::find($id);
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found.'
            ], 404);
        }

        $validatedData = $request->validate([
            'author_name'   => 'required|string|max:100|unique:testimonials,author_name,' . $review->id,
            'review_text'   => 'nullable|string',
            'designation'   => 'nullable|string|max:100',
            'author_avatar' => 'nullable|image|max:5120',
        ]);

        try {
            // Handle image upload if provided
            if ($request->hasFile('author_avatar')) {
                // Delete old image
                if ($review->author_avatar) {
                    Helper::fileDelete($review->author_avatar);
                }
                $validatedData['author_avatar'] = Helper::fileUpload($request->file('author_avatar'), 'testimonial/images');
            }

            $review->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Testimonial updated successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Testimonial.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // delete review
    public function destroyReview($id)
    {
        $review = Testimonials::find($id);
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found.'
            ], 404);
        }

        try {
            // Delete image if exists
            if ($review->author_avatar) {
                Helper::fileDelete($review->author_avatar);
            }

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Review.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
