<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialLinkController extends Controller
{
    use ApiResponse;

    /**
     * Display social links
     */
    public function index()
    {
        $socialLinks = SocialLink::latest()->get();
        return view('backend.layouts.settings.social_links', compact('socialLinks'));
    }

    /**
     * Store Social Link
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|in:facebook,twitter,instagram,linkedin,youtube,tiktok,pinterest,whatsapp|unique:social_links,name',
            'url'    => 'required|url|max:255',
            'icon'   => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {

            SocialLink::create($validated);

            return back()->with('t-success', 'Social link created successfully');
        } catch (Exception $e) {

            return back()->with('t-error', 'Failed to create social link');
        }
    }

    /**
     * Update Social Link
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'url'    => 'required|url|max:255',
            'icon'   => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {

            $social = SocialLink::findOrFail($id);
            $social->update($validated);

            return back()->with('t-success', 'Social link updated successfully');
        } catch (Exception $e) {

            return back()->with('t-error', 'Failed to update');
        }
    }

    /**
     * Delete Social Link
     */
    public function destroy($id)
    {
        try {

            $social = SocialLink::findOrFail($id);
            $social->delete();

            return back()->with('t-success', 'Deleted successfully');
        } catch (Exception $e) {

            return back()->with('t-error', 'Failed to delete');
        }
    }

    /**
     * Get all links for api
     */
    public function getSocialLinks()
    {
        try {
            $links = SocialLink::where('status', 'active')
                ->select('id', 'name', 'url')
                ->get();

            return $this->success(
                'Social links retrieved successfully',
                $links
            );
        } catch (Exception $e) {

            Log::error('Social links fetch error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve languages',
                500
            );
        }
    }
}
