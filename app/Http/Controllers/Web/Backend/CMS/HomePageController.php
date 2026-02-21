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
    public function aiSystemIndex(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'ai-system')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.ai-system", compact(["data"]));
    }

    /**
     * update training camp section
     **/
    public function aiSystemUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'ai-system')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'ai-system',
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
     * show home page operation section data
     */
    public function operationIndex(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'operations')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.operations", compact(["data"]));
    }


    /**
     * update operation section
     **/
    public function operationUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'operations')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'operations',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
