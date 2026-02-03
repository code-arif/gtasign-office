<?php

namespace App\Http\Controllers\Web\Backend\Gig;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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
                    $backgroundColor = $isActive ? '#05402e' : '#ccc';
                    $sliderTranslateX = $isActive ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input type="checkbox" class="form-check-input status-toggle"
                            data-id="' . $data->id . '"
                            ' . ($isActive ? 'checked' : '') . '
                            style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '</div>';

                    return $status;
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
                ->rawColumns(['parent_name', 'status', 'action'])
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $category = Category::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'parent_id' => $request->parent_id,
                'description' => $request->description,
                'order' => $request->order ?? 0,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // Get updated parent categories
            $parentCategories = Category::whereNull('parent_id')
                ->where('is_active', true)
                ->where('id', '!=', $category->id)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => $category,
                'parent_categories' => $parentCategories
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $category->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'parent_id' => $request->parent_id,
                'description' => $request->description,
                'order' => $request->order ?? $category->order,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // Get updated parent categories
            $parentCategories = Category::whereNull('parent_id')
                ->where('is_active', true)
                ->where('id', '!=', $id)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category,
                'parent_categories' => $parentCategories
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

            if ($category->children->isNotEmpty()) {
                foreach ($category->children as $child) {
                    $child->delete();
                }
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
