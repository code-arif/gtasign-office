<?php

namespace App\Http\Controllers\Web\Backend\User;

use App\Models\Language;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LanguageManageController extends Controller
{
    /**
     * Show languages list
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Language::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($language) {
                    return '
                        <button class="btn btn-sm btn-primary" onclick="openEditModal(' . $language->id . ')">
                            <i class="fe fe-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteLanguage(' . $language->id . ')">
                            <i class="fe fe-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.layouts.users.languages');
    }

    /**
     * Store language
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|unique:languages,name|max:100',
            'display_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ]);
        }

        Language::create([
            'name'         => $request->name,
            'display_name' => $request->display_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language created successfully'
        ]);
    }

    /**
     * Get single language data
     */
    public function getLanguage($id)
    {
        return response()->json([
            'success'  => true,
            'language' => Language::findOrFail($id)
        ]);
    }

    /**
     * Update language
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|unique:languages,name,' . $id . '|max:100',
            'display_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ]);
        }

        Language::findOrFail($id)->update([
            'name'         => $request->name,
            'display_name' => $request->display_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully'
        ]);
    }

    /**
     * Delete language
     */
    public function destroy($id)
    {
        Language::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Language deleted successfully'
        ]);
    }
}
