<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class FeaturesController extends Controller
{
    /**
     * show home page features section data and section item
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $itemData = CMS::where('page', 'home')->where('section', 'features')->where('name', 'card')->get();

            return DataTables::of($itemData)
                ->addIndexColumn()
                ->addColumn('section', fn($item) => ucfirst($item->section))
                ->addColumn('image', function ($item) {
                    $image = $item->image && file_exists(public_path($item->image))
                        ? asset($item->image)
                        : asset('default/no image.webp');
                    return '<img src="' . $image . '" alt="Image" width="40" style="border: 2px solid #eee; background: #00aeefe6;">';
                })

                ->addColumn('title', fn($item) => ucfirst($item->title))

                ->addColumn('action', function ($item) {
                    return '
                    <div class="d-flex justify-content-start align-items-center gap-1">
                   <button type="button"
                            class="btn btn-primary btn-sm editItem"
                            data-id="' . $item->id . '">
                        <i class="fe fe-edit"></i>
                    </button>

                    <button type="button" onclick="showDeleteConfirm(' . $item->id . ')" class="btn btn-danger btn-sm">
                        <i class="fe fe-trash"></i>
                    </button>
                </div>';
                })
                ->rawColumns(['image', 'action'])
                ->make();
        }

        $data = CMS::where('page', 'home')->where('section', 'features')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.features", compact("data"));
    }


    /**
     * update feature section header and description
     **/
    public function store(CmsRequest $request)
    {
        try {
            $validated = $request->validated();

            CMS::updateOrCreate(
                [
                    'page'    => 'home',
                    'section' => 'features',
                    'name'    => 'item',
                ],
                $validated
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }


    /**
     * update feature section item
     **/
    public function storeItem(CmsRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = Helper::fileUpload($request->file('image'), 'cms/home/features');
                $validatedData['image'] = $imagePath;
            }

            // Create new CMS item (no update logic)
            $heroItem = CMS::create([
                'page'    => 'home',
                'section' => 'features',
                'name'    => 'card',
            ] + $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully!',
                'data'    => $heroItem,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item. Please try again.',
            ], 500);
        }
    }


    /**
     * edit feature section item
     **/
    public function editItem($id)
    {
        $data = CMS::find($id);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Item not found.']);
        }
        return response()->json(['success' => true, 'data' => $data]);
    }


    /**
     * update feature section item
     **/
    public function updateItem(CmsRequest $request, $id)
    {
        try {
            $validated_data = $request->validated();

            $item = CMS::findOrFail($id);

            // Handle image update
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($item && $item->image) {
                    Helper::fileDelete($item->image);
                }

                // Store new image
                $image_path = Helper::fileUpload($request->file('image'), 'cms/home/features');
                $validated_data['image'] = $image_path;
            }

            // Update the item record
            $item->update($validated_data);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating the item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * delete item
     **/
    public function destroy($id)
    {
        $item = CMS::find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        // delete image
        if ($item && $item->image) {
            Helper::fileDelete($item->image);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
    }
}
