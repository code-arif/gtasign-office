<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helpers\Helper;
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

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'hero',
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
     * show home page training camp section data and section item
     */
    public function trainingCampIndex(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'training-camp')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.training-camp", compact(["data"]));
    }


    /**
     * update training camp section
     **/
    public function trainingCampUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'training-camp')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'training-camp',
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
