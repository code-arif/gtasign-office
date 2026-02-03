<?php

namespace App\Http\Controllers\Api\Gig;

use Exception;
use App\Traits\ApiResponse;
use App\Services\GigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Gig\GigResource;
use App\Http\Resources\Gig\GigListResource;
use App\Http\Requests\Gig\GigGalleryRequest;
use App\Http\Requests\Gig\GigPricingRequest;
use App\Http\Requests\Gig\GigOverviewRequest;
use App\Http\Requests\Gig\GigRequirementsRequest;

class GigController extends Controller
{
    use ApiResponse;

    protected $gigService;

    public function __construct(GigService $gigService)
    {
        $this->gigService = $gigService;
    }

    /**
     * List all gigs (for browsing)
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'status' => $request->input('status'),
                'category_id' => $request->input('category_id'),
                'sub_category_id' => $request->input('sub_category_id'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'delivery_days' => $request->input('delivery_days'),
            ];

            $perPage = $request->input('per_page', 15);
            $gigs = $this->gigService->getAllGigs(array_filter($filters), $perPage);

            return $this->success(
                'Gigs retrieved successfully',
                GigListResource::collection($gigs)->response()->getData(true)
            );
        } catch (Exception $e) {
            Log::error('Gig index error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve gigs',
                500
            );
        }
    }

    /**
     * List user's own gigs
     */
    public function myGigs(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $filters = [
                'status' => $request->input('status'),
                'last_days' => $request->input('last_days', 30),
            ];

            $perPage = $request->input('per_page', 15);
            $gigs = $this->gigService->getUserGigs($user->id, array_filter($filters), $perPage);

            return $this->success(
                'Your gigs retrieved successfully',
                GigListResource::collection($gigs)->response()->getData(true)
            );
        } catch (Exception $e) {
            Log::error('My gigs error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve your gigs',
                500
            );
        }
    }

    /**
     * Get active gigs (marketplace)
     */
    public function active(Request $request)
    {
        try {
            $filters = [
                'category_id' => $request->input('category_id'),
                'sub_category_id' => $request->input('sub_category_id'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'delivery_days' => $request->input('delivery_days'),
            ];

            $perPage = $request->input('per_page', 15);
            $gigs = $this->gigService->getActiveGigs(array_filter($filters), $perPage);

            return $this->success(
                'Active gigs retrieved successfully',
                GigListResource::collection($gigs)->response()->getData(true)
            );
        } catch (Exception $e) {
            Log::error('Active gigs error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve active gigs',
                500
            );
        }
    }

    /**
     * Search gigs
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');

            if (empty($query)) {
                return $this->error(null, 'Search query is required', 400);
            }

            $filters = [
                'category_id' => $request->input('category_id'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
            ];

            $perPage = $request->input('per_page', 15);
            $gigs = $this->gigService->searchGigs($query, array_filter($filters), $perPage);

            return $this->success(
                'Search results retrieved successfully',
                GigListResource::collection($gigs)->response()->getData(true)
            );
        } catch (Exception $e) {
            Log::error('Gig search error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to search gigs',
                500
            );
        }
    }

    /**
     * Show single gig
     */
    public function show($id)
    {
        try {
            $gig = $this->gigService->getGigById($id);

            if (!$gig) {
                return $this->error(null, 'Gig not found', 404);
            }

            // Track impression
            $this->gigService->trackImpression($id);

            return $this->success(
                'Gig retrieved successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig show error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve gig',
                500
            );
        }
    }

    /**
     * Step 1: Create gig overview
     */
    public function createOverview(GigOverviewRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $gig = $this->gigService->createOverview($user->id, $request->validated());

            return $this->success(
                'Gig overview created successfully',
                new GigResource($gig),
                201
            );
        } catch (Exception $e) {
            Log::error('Gig create overview error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to create gig overview',
                500
            );
        }
    }

    /**
     * Step 2: Update gig pricing
     */
    public function updatePricing(GigPricingRequest $request, $id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $gig = $this->gigService->updatePricing($id, $user->id, $request->validated());

            return $this->success(
                'Gig pricing updated successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig update pricing error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage() === 'Gig not found' ? 'Gig not found' : 'Failed to update gig pricing',
                $e->getMessage() === 'Gig not found' ? 404 : 500
            );
        }
    }

    /**
     * Step 3: Update gig requirements
     */
    public function updateRequirements(GigRequirementsRequest $request, $id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $gig = $this->gigService->updateRequirements($id, $user->id, $request->validated());

            return $this->success(
                'Gig requirements updated successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig update requirements error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage() === 'Gig not found' ? 'Gig not found' : 'Failed to update gig requirements',
                $e->getMessage() === 'Gig not found' ? 404 : 500
            );
        }
    }

    /**
     * Step 4: Update gig gallery
     */
    public function updateGallery(GigGalleryRequest $request, $id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $data = [];
            if ($request->hasFile('images')) {
                $data['images'] = $request->file('images');
            }
            if ($request->hasFile('documents')) {
                $data['documents'] = $request->file('documents');
            }

            $gig = $this->gigService->updateGallery($id, $user->id, $data);

            return $this->success(
                'Gig gallery updated successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig update gallery error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage() === 'Gig not found' ? 'Gig not found' : 'Failed to update gig gallery',
                $e->getMessage() === 'Gig not found' ? 404 : 500
            );
        }
    }

    /**
     * Delete gig image
     */
    public function deleteImage(Request $request, $id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $request->validate([
                'image_path' => 'required|string',
            ]);

            $gig = $this->gigService->deleteImage($id, $user->id, $request->input('image_path'));

            return $this->success(
                'Image deleted successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig delete image error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage() === 'Gig not found' ? 'Gig not found' : 'Failed to delete image',
                $e->getMessage() === 'Gig not found' ? 404 : 500
            );
        }
    }

    /**
     * Delete gig document
     */
    public function deleteDocument(Request $request, $id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $request->validate([
                'document_path' => 'required|string',
            ]);

            $gig = $this->gigService->deleteDocument($id, $user->id, $request->input('document_path'));

            return $this->success(
                'Document deleted successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig delete document error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage() === 'Gig not found' ? 'Gig not found' : 'Failed to delete document',
                $e->getMessage() === 'Gig not found' ? 404 : 500
            );
        }
    }

    /**
     * Publish gig (submit for approval)
     */
    public function publish($id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $gig = $this->gigService->publishGig($id, $user->id);

            return $this->success(
                'Gig submitted for approval successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig publish error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Gig not found' ? 404 : 400
            );
        }
    }

    /**
     * Update gig (general update)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $gig = $this->gigService->updateGig($id, $user->id, $request->all());

            return $this->success(
                'Gig updated successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig update error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage() === 'Gig not found' ? 'Gig not found' : 'Failed to update gig',
                $e->getMessage() === 'Gig not found' ? 404 : 500
            );
        }
    }

    /**
     * Delete gig
     */
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            $this->gigService->deleteGig($id, $user->id);

            return $this->success('Gig deleted successfully');
        } catch (Exception $e) {
            Log::error('Gig delete error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage() === 'Gig not found' ? 'Gig not found' : 'Failed to delete gig',
                $e->getMessage() === 'Gig not found' ? 404 : 500
            );
        }
    }

    /**
     * Track gig click
     */
    public function trackClick($id)
    {
        try {
            $this->gigService->trackClick($id);

            return $this->success('Click tracked successfully');
        } catch (Exception $e) {
            Log::error('Gig track click error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to track click',
                500
            );
        }
    }

    /**
     * Admin: Approve gig
     */
    public function approve($id)
    {
        try {
            $this->gigService->approveGig($id);

            $gig = $this->gigService->getGigById($id);

            return $this->success(
                'Gig approved successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig approve error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to approve gig',
                500
            );
        }
    }

    /**
     * Admin: Reject gig
     */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:1000',
            ]);

            $this->gigService->rejectGig($id, $request->input('reason'));

            $gig = $this->gigService->getGigById($id);

            return $this->success(
                'Gig rejected successfully',
                new GigResource($gig)
            );
        } catch (Exception $e) {
            Log::error('Gig reject error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to reject gig',
                500
            );
        }
    }
}
