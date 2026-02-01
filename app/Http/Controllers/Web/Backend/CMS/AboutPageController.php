<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use App\Models\CMS;
use App\Helpers\Helper;
use App\Models\OwnerInfo;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class AboutPageController extends Controller
{
    /**
     * Main Index - Load All Sections
     */
    public function index()
    {
        // Page Title & Subtitle
        $pageTitle = CMS::where('page', 'about')
            ->where('section', 'page_title')
            ->first();

        // Mission Section
        $mission = CMS::where('page', 'about')
            ->where('section', 'mission')
            ->first();

        // Key to Excellence Section
        $keyToExcellence = CMS::where('page', 'about')
            ->where('section', 'key_to_excellence')
            ->first();

        // Bottom Description
        $bottomDescription = CMS::where('page', 'about')
            ->where('section', 'bottom_description')
            ->first();

        // Owner Info
        $owner = OwnerInfo::where('status', 'active')->first();

        return view('backend.layouts.cms.about.index', compact(
            'pageTitle',
            'mission',
            'keyToExcellence',
            'bottomDescription',
            'owner'
        ));
    }

    /* =========================
       PAGE TITLE SECTION
       ========================= */
    public function storePageTitle(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        CMS::updateOrCreate(
            [
                'page'    => 'about',
                'section' => 'page_title',
            ],
            [
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'layout'      => 'page_title_v1',
                'component'   => 'PageTitle',
            ]
        );

        return back()->with('success', 'Page title updated successfully');
    }

    /* =========================
       MISSION SECTION
       ========================= */
    public function storeMission(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        CMS::updateOrCreate(
            [
                'page'    => 'about',
                'section' => 'mission',
            ],
            [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'layout'      => 'mission_box_v1',
                'component'   => 'MissionBox',
            ]
        );

        return back()->with('success', 'Mission section updated successfully');
    }

    /* =========================
       KEY TO EXCELLENCE SECTION
       ========================= */
    public function storeKeyToExcellence(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        CMS::updateOrCreate(
            [
                'page'    => 'about',
                'section' => 'key_to_excellence',
            ],
            [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'layout'      => 'key_excellence_v1',
                'component'   => 'KeyExcellence',
            ]
        );

        return back()->with('success', 'Key to Excellence updated successfully');
    }

    /* =========================
       BOTTOM DESCRIPTION SECTION
       ========================= */
    public function storeBottomDescription(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        CMS::updateOrCreate(
            [
                'page'    => 'about',
                'section' => 'bottom_description',
            ],
            [
                'description' => $validated['description'],
                'layout'      => 'description_block_v1',
                'component'   => 'DescriptionBlock',
            ]
        );

        return back()->with('success', 'Bottom description updated successfully');
    }

    /* =========================
       OWNER INFO SECTION
       ========================= */
    public function storeOwnerInfo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'experience' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'stat_1_value' => 'nullable|string',
            'stat_1_label' => 'nullable|string',
            'stat_2_value' => 'nullable|string',
            'stat_2_label' => 'nullable|string',
            'stat_3_value' => 'nullable|string',
            'stat_3_label' => 'nullable|string',
        ]);

        $owner = OwnerInfo::where('status', 'active')->first();

        $data = [
            'name' => $validated['name'],
            'designation' => $validated['designation'],
            'experience' => $validated['experience'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'stats' => [
                [
                    'value' => $validated['stat_1_value'] ?? null,
                    'label' => $validated['stat_1_label'] ?? null,
                ],
                [
                    'value' => $validated['stat_2_value'] ?? null,
                    'label' => $validated['stat_2_label'] ?? null,
                ],
                [
                    'value' => $validated['stat_3_value'] ?? null,
                    'label' => $validated['stat_3_label'] ?? null,
                ],
            ],
        ];

        if ($request->hasFile('image')) {
            $data['image'] = Helper::fileUpload($request->file('image'), 'cms/about/owner');
        }

        if ($owner) {
            $owner->update($data);
        } else {
            OwnerInfo::create($data);
        }

        return back()->with('success', 'Owner info updated successfully');
    }

    /* =========================
       FEATURE ITEMS (CARDS)
       ========================= */
    public function items()
    {
        $query = CMS::where('page', 'about')
            ->where('section', 'features')
            ->orderBy('order');

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('image', function ($row) {
                if ($row->image) {
                    return '<img src="'.asset($row->image).'" width="50">';
                }
                return '<span class="badge bg-secondary">No Image</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-primary editItem" data-id="'.$row->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm('.$row->id.')">Delete</button>
                ';
            })
            ->rawColumns(['image','action'])
            ->make(true);
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = Helper::fileUpload($request->file('image'), 'cms/about/features');
        }

        // Get max order
        $maxOrder = CMS::where('page', 'about')
            ->where('section', 'features')
            ->max('order') ?? 0;

        $item = CMS::create([
            'page'      => 'about',
            'section'   => 'features',
            'name'      => 'feature_card',
            'layout'    => 'feature_card_v1',
            'component' => 'FeatureCard',
            'order'     => $maxOrder + 1,
        ] + $data);

        return response()->json([
            'status'  => 1,
            'message' => 'Feature item added successfully',
            'data'    => $item
        ]);
    }

    public function editItem($id)
    {
        return response()->json([
            'data' => CMS::findOrFail($id)
        ]);
    }

    public function updateItem(Request $request, $id)
    {
        $item = CMS::findOrFail($id);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image && file_exists(public_path($item->image))) {
                unlink(public_path($item->image));
            }
            $data['image'] = Helper::fileUpload($request->file('image'), 'cms/about/features');
        }

        $item->update($data);

        return response()->json([
            'status'  => 1,
            'message' => 'Item updated successfully'
        ]);
    }

    public function destroyItem($id)
    {
        $item = CMS::findOrFail($id);

        // Delete image if exists
        if ($item->image && file_exists(public_path($item->image))) {
            unlink(public_path($item->image));
        }

        $item->delete();

        return response()->json([
            'message' => 'Item deleted successfully'
        ]);
    }
}
