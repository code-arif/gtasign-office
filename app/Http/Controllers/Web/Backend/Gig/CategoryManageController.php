<?php

namespace App\Http\Controllers\Web\Backend\Gig;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CategoryManageController extends Controller
{
    /**
     * Show all categories
     */
    public function index(Request $request)
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($request->ajax()) {
            $data = Category::with('parent')->orderBy('order')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('parent_name', function ($data) {
                    return $data->parent ? $data->parent->name : '<span class="badge bg-success">Main Category</span>';
                })
                ->addColumn('status', function ($data) {
                    $isActive = $data->is_active;

                    return '
                    <label class="custom-switch">
                        <input type="checkbox"
                            class="status-toggle"
                            data-id="' . $data->id . '"
                            ' . ($isActive ? 'checked' : '') . '>
                        <span class="switch-slider"></span>
                    </label>
                ';
                })
                ->addColumn('image', function ($data) {
                    $imageUrl = $data->image
                        ? asset('storage/' . $data->image)
                        : asset('default/no_image.webp'); // fallback

                    return '<img src="' . $imageUrl . '" alt="' . $data->name . '" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">';
                })
                ->addColumn('action', function ($data) use ($categories) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group">';

                    $buttons .= '<button type="button" onclick="openEditModal(' . $data->id . ')"
                                 class="btn btn-primary btn-sm" title="Edit">
                                 <i class="fe fe-edit"></i></button>';

                    $buttons .= '<button type="button" onclick="openViewModal(' . $data->id . ')"
                                 class="btn btn-success btn-sm" title="View">
                                 <i class="fe fe-eye"></i></button>';

                    $buttons .= '<button type="button" onclick="showDeleteConfirm(' . $data->id . ')"
                                 class="btn btn-danger btn-sm" title="Delete">
                                 <i class="fe fe-trash"></i></button>';

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['parent_name', 'status', 'action', 'image'])
                ->make(true);
        }

        return view("backend.layouts.categories.index", compact('categories'));
    }

    /**
     * Get category
     */
    public function getCategory($id)
    {
        try {
            $category = Category::with('parent', 'children')->findOrFail($id);
            $categories = Category::whereNull('parent_id')
                ->where('id', '!=', $id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'category' => $category,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:250',
            'slug' => 'required|unique:categories,slug|max:250',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {

            // Upload Image
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = Helper::fileUpload($request->file('image'), 'categories');
            }

            $category = Category::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'parent_id' => $request->parent_id,
                'description' => $request->description,
                'order' => $request->order ?? 0,
                'image' => $imagePath, // save
                'is_active' => $request->has('is_active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => $category
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:250',
            'slug' => 'required|unique:categories,slug,' . $id . '|max:250',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $id,
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {

            $imagePath = $category->image;

            // If new image uploaded → delete old + upload new
            if ($request->hasFile('image')) {
                Helper::fileDelete($category->image);
                $imagePath = Helper::fileUpload($request->file('image'), 'categories');
            }

            $category->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'parent_id' => $request->parent_id,
                'description' => $request->description,
                'order' => $request->order ?? $category->order,
                'image' => $imagePath,
                'is_active' => $request->has('is_active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        try {
            $category = Category::with('children')->findOrFail($id);

            // delete image
            Helper::fileDelete($category->image);

            foreach ($category->children as $child) {
                Helper::fileDelete($child->image);
                $child->delete();
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle category status
     */
    public function status($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $category->is_active = !$category->is_active;
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'is_active' => $category->is_active
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
