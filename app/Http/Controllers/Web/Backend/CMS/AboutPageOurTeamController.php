<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use App\Models\CMS;
use App\Models\OwnerInfo;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class AboutPageOurTeamController extends Controller
{
    /**
     * Main Index - Load All Sections
     */
    public function index()
    {
        // Team Section Header
        $teamHeader = CMS::where('page', 'about')
            ->where('section', 'our-team')
            ->where('name', 'item')
            ->first();

        return view('backend.layouts.cms.about.our-team', compact(
            'teamHeader'
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
       OUR TEAM SECTION HEADER
       ========================= */
    public function storeTeamHeader(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        CMS::updateOrCreate(
            [
                'page'    => 'about',
                'section' => 'our-team',
                'name'    => 'item',
            ],
            [
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'layout'      => 'team_header_v1',
                'component'   => 'TeamHeader',
            ]
        );

        return back()->with('success', 'Team section header updated successfully');
    }

    /* =========================
       TEAM MEMBERS (CARDS)
       ========================= */
    public function teamMembers()
    {
        $query = CMS::where('page', 'about')
            ->where('section', 'our-team')
            ->where('name', 'card')
            ->orderBy('id');

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('image', function ($row) {
                if ($row->image) {
                    return '<img src="' . asset($row->image) . '" width="50" class="rounded">';
                }
                return '<span class="badge bg-secondary">No Image</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-primary editTeamMember" data-id="' . $row->id . '">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="showDeleteTeamMember(' . $row->id . ')">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                ';
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
    }

    public function storeTeamMember(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255', // Name
            'sub_title'   => 'required|string|max:255', // Position/Role
            'description' => 'nullable|string|max:500', // Experience/Info
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = Helper::fileUpload($request->file('image'), 'cms/about/team');
        }

        $member = CMS::create([
            'page'      => 'about',
            'section'   => 'our-team',
            'name'      => 'card',
            'layout'    => 'team_card_v1',
            'component' => 'TeamCard',
        ] + $data);

        return response()->json([
            'status'  => 1,
            'message' => 'Team member added successfully',
            'data'    => $member
        ]);
    }

    public function editTeamMember($id)
    {
        return response()->json([
            'data' => CMS::findOrFail($id)
        ]);
    }

    public function updateTeamMember(Request $request, $id)
    {
        $member = CMS::findOrFail($id);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'sub_title'   => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($member->image && file_exists(public_path($member->image))) {
                unlink(public_path($member->image));
            }
            $data['image'] = Helper::fileUpload($request->file('image'), 'cms/about/team');
        }

        $member->update($data);

        return response()->json([
            'status'  => 1,
            'message' => 'Team member updated successfully'
        ]);
    }

    public function destroyTeamMember($id)
    {
        $member = CMS::findOrFail($id);

        // Delete image
        if ($member->image && file_exists(public_path($member->image))) {
            unlink(public_path($member->image));
        }

        $member->delete();

        return response()->json([
            'message' => 'Team member deleted successfully'
        ]);
    }
}
