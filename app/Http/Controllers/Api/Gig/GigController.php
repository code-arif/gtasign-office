<?php

namespace App\Http\Controllers\Api\Gig;

use Exception;
use App\Traits\ApiResponse;
use App\Services\GigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gig\GigStoreRequest;
use App\Http\Requests\Gig\GigUpdateRequest;
use App\Http\Resources\Gig\GigDetailResource;
use App\Http\Resources\Gig\GigResource;
use App\Http\Resources\Gig\GigListResource;

class GigController extends Controller
{
    use ApiResponse;

    protected $gigService;

    public function __construct(GigService $gigService)
    {
        $this->gigService = $gigService;
    }

    /**
     * List all gigs (browsing)
     */
    public function index(Request $request)
    {
        try {
            $filters = $this->buildFilters($request);
            $perPage = $request->input('per_page', 15);

            $gigs = $this->gigService->getAllGigs($filters, $perPage);

            return $this->success('Gigs retrieved successfully', [
                'gigs' => GigListResource::collection($gigs),
                'pagination' => $this->getPaginationMeta($gigs)
            ]);
        } catch (Exception $e) {
            Log::error('Gig index error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to retrieve gigs', 500);
        }
    }

    /**
     * List user's own gigs
     */
    public function myGigs(Request $request)
    {
        try {
            $user = auth('api')->user();
            $filters = [
                'status' => $request->input('status'),
                'last_days' => $request->input('last_days', 30),
            ];
            $perPage = $request->input('per_page', 15);

            $gigs = $this->gigService->getUserGigs($user->id, array_filter($filters), $perPage);

            return $this->success('Your gigs retrieved successfully', [
                'gigs' => GigListResource::collection($gigs),
                'pagination' => $this->getPaginationMeta($gigs)
            ]);
        } catch (Exception $e) {
            Log::error('My gigs error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to retrieve your gigs', 500);
        }
    }

    /**
     * Get active gigs (marketplace)
     */
    public function active(Request $request)
    {
        try {
            $filters = $this->buildFilters($request);
            $perPage = $request->input('per_page', 15);

            $gigs = $this->gigService->getActiveGigs($filters, $perPage);

            return $this->success('Active gigs retrieved successfully', [
                'gigs' => GigListResource::collection($gigs),
                'pagination' => $this->getPaginationMeta($gigs)
            ]);
        } catch (Exception $e) {
            Log::error('Active gigs error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to retrieve active gigs', 500);
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

            $filters = $this->buildFilters($request);
            $perPage = $request->input('per_page', 15);

            $gigs = $this->gigService->searchGigs($query, $filters, $perPage);

            return $this->success('Search results retrieved successfully', [
                'gigs' => GigListResource::collection($gigs),
                'pagination' => $this->getPaginationMeta($gigs)
            ]);
        } catch (Exception $e) {
            Log::error('Gig search error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to search gigs', 500);
        }
    }

    /**
     * Show single gig
     */
    public function show($id)
    {
        try {
            $authUserId = auth('api')->id(); // If guest is null

            $gig = $this->gigService->getGigById($id, $authUserId);

            if (!$gig) {
                return $this->error(null, 'Gig not found', 404);
            }

            $this->gigService->trackImpression($id);
            $this->gigService->trackClick($id);

            return $this->success('Gig retrieved successfully', new GigDetailResource($gig));
        } catch (Exception $e) {
            Log::error('Gig show error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to retrieve gig', 500);
        }
    }

    /**
     * Create new gig
     */
    public function store(GigStoreRequest $request)
    {
        try {
            $user = auth('api')->user();
            $gig = $this->gigService->createGig($user->id, $request->validated());

            return $this->success('Gig created successfully', new GigDetailResource($gig), 201);
        } catch (Exception $e) {
            Log::error('Gig create error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to create gig', 500);
        }
    }

    /**
     * Update gig
     */
    public function update(GigUpdateRequest $request, $id)
    {
        try {
            $user = auth('api')->user();
            $gig = $this->gigService->updateGig($id, $user->id, $request->validated());

            return $this->success('Gig updated successfully', new GigDetailResource($gig));
        } catch (Exception $e) {
            Log::error('Gig update error: ' . $e->getMessage());

            $statusCode = $e->getMessage() === 'Gig not found' ? 404 : 500;
            return $this->error(['exception' => $e->getMessage()], $e->getMessage(), $statusCode);
        }
    }

    /**
     * Delete gig
     */
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();
            $this->gigService->deleteGig($id, $user->id);

            return $this->success('Gig deleted successfully');
        } catch (Exception $e) {
            Log::error('Gig delete error: ' . $e->getMessage());

            $statusCode = $e->getMessage() === 'Gig not found' ? 404 : 500;
            return $this->error(['exception' => $e->getMessage()], $e->getMessage(), $statusCode);
        }
    }

    /**
     * Publish gig (submit for approval)
     */
    public function publish($id)
    {
        try {
            $user = auth('api')->user();
            $gig = $this->gigService->publishGig($id, $user->id);

            return $this->success('Gig published successfully', new GigResource($gig));
        } catch (Exception $e) {
            Log::error('Gig publish error: ' . $e->getMessage());

            $statusCode = $e->getMessage() === 'Gig not found' ? 404 : 400;
            return $this->error(['exception' => $e->getMessage()], $e->getMessage(), $statusCode);
        }
    }

    /**
     * Delete gig image
     */
    public function deleteImage(Request $request, $id)
    {
        try {
            $user = auth('api')->user();

            $request->validate([
                'image_id' => 'required|integer|exists:gig_images,id',
            ]);

            $gig = $this->gigService->deleteImage($id, $user->id, $request->input('image_id'));

            return $this->success('Image deleted successfully', new GigResource($gig));
        } catch (Exception $e) {
            Log::error('Gig delete image error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], $e->getMessage(), 500);
        }
    }

    /**
     * Delete gig document
     */
    public function deleteDocument(Request $request, $id)
    {
        try {
            $user = auth('api')->user();

            $request->validate([
                'document_id' => 'required|integer|exists:gig_documents,id',
            ]);

            $gig = $this->gigService->deleteDocument($id, $user->id, $request->input('document_id'));

            return $this->success('Document deleted successfully', new GigResource($gig));
        } catch (Exception $e) {
            Log::error('Gig delete document error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], $e->getMessage(), 500);
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
            return $this->error(['exception' => $e->getMessage()], 'Failed to track click', 500);
        }
    }

    /**
     * Get gigs by tag
     */
    public function byTag(Request $request, $tagId)
    {
        try {
            $perPage = $request->input('per_page', 15);

            $gigs = $this->gigService->getGigsByTag((int) $tagId, $perPage);

            if ($gigs->isEmpty()) {
                return $this->error(null, 'No gigs found for this tag', 404);
            }

            return $this->success('Gigs retrieved successfully', [
                'gigs'       => GigListResource::collection($gigs),
                'pagination' => $this->getPaginationMeta($gigs),
            ]);
        } catch (Exception $e) {
            Log::error('Gig by tag error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to retrieve gigs', 500);
        }
    }

    /**
     * Get gigs by category
     */
    public function byCategory(Request $request, $categoryId)
    {
        try {
            $perPage        = $request->input('per_page', 15);
            $subCategoryId  = $request->input('sub_category_id');

            $gigs = $this->gigService->getGigsByCategory(
                (int) $categoryId,
                $subCategoryId ? (int) $subCategoryId : null,
                $perPage
            );

            if ($gigs->isEmpty()) {
                return $this->error(null, 'No gigs found for this category', 404);
            }

            return $this->success('Gigs retrieved successfully', [
                'gigs'       => GigListResource::collection($gigs),
                'pagination' => $this->getPaginationMeta($gigs),
            ]);
        } catch (Exception $e) {
            Log::error('Gig by category error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to retrieve gigs', 500);
        }
    }

    /**
     * Build filters from request
     */
    private function buildFilters(Request $request): array
    {
        return array_filter([
            'keyword'         => $request->input('keyword'),
            'category_id' => $request->input('category_id'),
            'sub_category_id' => $request->input('sub_category_id'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'delivery_days' => $request->input('delivery_days'),
            'tag_ids' => $request->input('tag_ids'),
        ]);
    }

    /**
     * Get pagination metadata
     */
    private function getPaginationMeta($paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
