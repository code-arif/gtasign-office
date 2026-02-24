<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class HomePageController extends Controller
{
    /**
     * show home page hero section data and section item
     */
    public function heroIndex(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'hero')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.hero", compact(["data"]));
    }


    /**
     * update hero section
     **/
    public function heroUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // Get existing row
            $existing = CMS::where('page', 'home')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            // Handle Video Upload
            if ($request->hasFile('video')) {

                // delete old video
                if ($existing && $existing->video && file_exists(public_path($existing->video))) {
                    unlink(public_path($existing->video));
                }

                $file = $request->file('video');
                $filename = time() . '_hero.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/cms'), $filename);

                $validated_data['video'] = 'uploads/cms/' . $filename;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'hero',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Hero content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }


    /**
     * show home page ai system section data and section item
     */
    public function tagSectionIndex(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'tags-section')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.tags-section", compact(["data"]));
    }

    /**
     * update training camp section
     **/
    public function tagSectionUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'tags-section')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'tags-section',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }


    /**
     * Show AI Security Section
     */
    public function aiSecurityIndex()
    {
        $header = CMS::where('page', 'home')
            ->where('section', 'ai-security')
            ->where('name', 'header')
            ->first();

        return view("backend.layouts.cms.home.ai-security", compact('header'));
    }

    /*
    * Update AI Security Section
    */
    public function aiSecurityUpdate(CmsRequest $request)
    {
        try {

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'ai-security',
                    'name' => 'header'
                ],
                $request->validated()
            );

            return back()->with('t-success', 'AI Security Section Updated');
        } catch (Exception $e) {
            return back()->with('t-error', $e->getMessage());
        }
    }

    /**
     * Get all AI Security Item
     */
    public function aiSecurityItems()
    {
        $items = CMS::where('page', 'home')
            ->where('section', 'ai-security')
            ->where('name', 'item')
            ->latest()
            ->get();

        return datatables()->of($items)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                <button class="btn btn-sm btn-primary editItem" data-id="' . $row->id . '">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(' . $row->id . ')">Delete</button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * AI Security Item Store
     */
    public function aiSecurityItemStore(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'points' => 'required|array'
        ]);

        CMS::create([
            'page' => 'home',
            'section' => 'ai-security',
            'name' => 'item',
            'title' => $request->title,
            'metadata' => json_encode(['points' => $request->points])
        ]);

        return response()->json(['status' => 1, 'message' => 'Item Created']);
    }

    /**
     * Update AI Security Item
     */
    public function aiSecurityItemUpdate(Request $request, $id)
    {
        $cms = CMS::findOrFail($id);

        $cms->update([
            'title' => $request->title,
            'metadata' => json_encode(['points' => $request->points])
        ]);

        return response()->json(['status' => 1, 'message' => 'Updated']);
    }

    /**
     * Destory AI Security Item
     */
    public function aiSecurityItemDestroy($id)
    {
        CMS::findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
