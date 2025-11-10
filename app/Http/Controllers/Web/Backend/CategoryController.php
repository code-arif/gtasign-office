<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Helper\Helper;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Category::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    if ($data->image) {
                        $url = asset($data->image);
                        return '<img src="' . $url . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';
                    } else {
                        return '<img src="' . asset('default/logo.png') . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';
                    }
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns([ 'image' ,'status', 'action'])
                ->make();
        }
        return view("backend.layouts.category.index");
    }


    public function create()
    {
        return view('backend.layouts.category.create');
    }


    public function store(Request $request)
    {

        $validate = $request->validate([
            'title' => 'required|unique:categories,title',
            'image' => 'required',
        ]);

        try {
            if ($request->hasFile('image')) {

                $validate['image']  = Helper::uploadImage($request->image, 'category');
            }

            $slug = strtolower(str_replace(' ', '-', $request->title));
            $validate['slug'] = $slug;

            Category::create($validate);


            session()->put('t-success', 'Category created successfully');

        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.category.index')->with('success', 'Category created successfully');
    }


    // public function show(Category $category, $id)
    // {
    //     $category = Category::findOrFail($id);
    //     return view('backend.layouts.category.edit', compact('category'));
    // }


    public function edit(Category $category, $id)
    {
        $category = Category::find($id);

        if(!$category){
            return response()->json([
               'success' => false,
               'message' => 'Category not found.',
            ], 404);
        }

        return response()->json($category);
    }


    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'title' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $category = Category::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($category->image && file_exists(public_path($category->image))) {
                    Helper::deleteImage(public_path($category->image));
                }
                // $validate['image'] = Helper::uploadImage($request->file('image'), 'category', time() . '_' . Helper::getFileName($request->file('image')));
                $validate['image']  = Helper::uploadImage($request->image, 'category');
            }

            $category->update($validate);
            session()->put('t-success', 'Category updated successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.category.index');
    }


    public function destroy(string $id)
    {

        $data = Category::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        if ($data->image) {
            $oldImagePath = public_path($data->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }


        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!',
        ],200);
    }

    public function status(int $id): JsonResponse
    {
        $data = Category::findOrFail($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Status Changed successful!',
        ]);
    }
}
