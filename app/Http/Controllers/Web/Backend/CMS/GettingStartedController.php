<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class GettingStartedController extends Controller
{
    /**
     * Main Index - Load All Sections
     */
    public function index()
    {
        // Team Section Header
        $data = CMS::where('page', 'about')
            ->where('section', 'getting_started')
            ->where('name', 'item')
            ->first();

        return view('backend.layouts.cms.about.get-started', compact(
            'data'
        ));
    }

    /* =========================
         Getting Started SECTION
       ========================= */
    public function storePageTitle(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            CMS::updateOrCreate(
                [
                    'page'    => 'about',
                    'section' => 'getting_started',
                    'name'    => 'item',
                ],
                [
                    'title'       => $validated_data['title'],
                    'description' => $validated_data['description'] ?? null,
                ]
            );

            return back()->with('t-success', 'Content updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
