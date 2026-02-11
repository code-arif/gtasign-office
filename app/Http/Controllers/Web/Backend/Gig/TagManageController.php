<?php

namespace App\Http\Controllers\Web\Backend\Gig;

use Exception;
use App\Models\Tag;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TagManageController extends Controller
{
    /**
     * Show tags list
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Tag::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($tag) {
                    return '
                        <button class="btn btn-sm btn-primary" onclick="openEditModal(' . $tag->id . ')">
                            <i class="fe fe-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTag(' . $tag->id . ')">
                            <i class="fe fe-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.layouts.tags.index');
    }

    /**
     * Store tag
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tags,name|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        Tag::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully'
        ]);
    }

    /**
     * Get tag
     */
    public function getTag($id)
    {
        return response()->json([
            'success' => true,
            'tag' => Tag::findOrFail($id)
        ]);
    }

    /**
     * Update tag
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tags,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        Tag::findOrFail($id)->update([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully'
        ]);
    }

    /**
     * Delete tag
     */
    public function destroy($id)
    {
        Tag::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully'
        ]);
    }
}
