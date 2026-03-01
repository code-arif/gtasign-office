<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Gig;
use App\Models\Room;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GigService
{
    /**
     * Get all gigs with filters and pagination
     */
    public function getAllGigs(array $filters = [], int $perPage = 15)
    {
        $query = Gig::where('status', 'active')->with(['user', 'category', 'subCategory', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');
        $query = $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get user's gigs
     */
    public function getUserGigs(int $userId, array $filters = [], int $perPage = 15)
    {
        $query = Gig::with(['category', 'subCategory', 'images', 'documents'])
            ->where('user_id', $userId);

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }


    /**
     * Get active gigs (marketplace)
     */
    public function getActiveGigs(array $filters = [], int $perPage = 15)
    {
        $query = Gig::with(['user', 'category', 'subCategory', 'images'])
            ->where('status', 'active');

        $query = $this->applyFilters($query, $filters);

        return $query->latest('published_at')->paginate($perPage);
    }

    /**
     * Search gigs
     */
    public function searchGigs(string $searchQuery, array $filters = [], int $perPage = 15)
    {
        $query = Gig::with(['user', 'category', 'subCategory', 'images'])
            ->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('scope', 'like', "%{$searchQuery}%");
            })
            ->orWhereHas('tags', function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%");
            });

        $query = $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get single gig by ID
     */
    public function getGigById(int $id, ?int $authUserId = null)
    {
        $gig = Gig::with([
            'user.profile',
            'user' => function ($query) {
                $query->withCount([
                    'sellerOrders as active_orders_count' => function ($q) {
                        $q->whereIn('status', ['active', 'revision_requested', 'delivered']);
                    }
                ]);
            },
            'category',
            'subCategory',
            'images',
            'documents',
            'tags',
            'reviews.reviewer'
        ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->find($id);

        if (!$gig) {
            return null;
        }

        // If the authenticated user is the owner, no room is needed.
        if ($authUserId && $authUserId !== $gig->user_id) {
            $gigOwnerId = $gig->user_id;

            $roomId = Room::where(function ($q) use ($authUserId, $gigOwnerId) {
                $q->where('first_user_id', $authUserId)
                    ->where('second_user_id', $gigOwnerId);
            })
                ->orWhere(function ($q) use ($authUserId, $gigOwnerId) {
                    $q->where('first_user_id', $gigOwnerId)
                        ->where('second_user_id', $authUserId);
                })
                ->value('id');

            $gig->room_id = $roomId;
        } else {
            $gig->room_id = null;
        }

        return $gig;
    }

    /**
     * Create new gig (all data at once)
     */
    public function createGig(int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            // Create gig
            $gig = Gig::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'scope' => $data['scope'],
                'price' => $data['price'],
                'delivery_days' => $data['delivery_days'],
                'system_questions' => $data['system_questions'] ?? null,
                'custom_questions' => $data['custom_questions'] ?? null,
                'status' => 'pending_approval',
                'is_agreed' => $data['is_agreed'] ?? false,
            ]);

            // Attach tags
            // if (!empty($data['tag_ids'])) {
            //     $gig->tags()->sync($data['tag_ids']);
            // }
            if (!empty($data['tag_ids'])) {
                $gig->tags()->sync(array_map('intval', $data['tag_ids']));
            }

            // Upload images
            // if (!empty($data['images'])) {
            //     $this->uploadImages($gig, $data['images']);
            // }

            if (!empty($data['images'])) {
                $images = is_array($data['images']) ? $data['images'] : $data['images']->all();
                $this->uploadImages($gig, $images);
            }

            // Upload documents
            // if (!empty($data['documents'])) {
            //     $this->uploadDocuments($gig, $data['documents']);
            // }

            if (!empty($data['documents'])) {
                $documents = is_array($data['documents']) ? $data['documents'] : $data['documents']->all();
                $this->uploadDocuments($gig, $documents);
            }

            // dd($data);

            DB::commit();

            return $gig->load(['category', 'subCategory', 'images', 'documents', 'tags']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update gig
     */
    public function updateGig(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = Gig::where('id', $gigId)
                ->where('user_id', $userId)
                ->first();

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Update basic fields
            $allowedFields = [
                'title',
                'category_id',
                'sub_category_id',
                'scope',
                'price',
                'delivery_days',
                'system_questions',
                'custom_questions'
            ];

            $updateData = array_intersect_key($data, array_flip($allowedFields));
            $gig->update($updateData);

            // Update tags
            if (isset($data['tag_ids'])) {
                $gig->tags()->sync($data['tag_ids']);
            }

            // Add new images
            if (!empty($data['images'])) {
                $this->uploadImages($gig, $data['images']);
            }

            // Add new documents
            if (!empty($data['documents'])) {
                $this->uploadDocuments($gig, $data['documents']);
            }

            DB::commit();

            return $gig->fresh(['category', 'subCategory', 'images', 'documents', 'tags']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Publish gig
     */
    public function publishGig(int $gigId, int $userId)
    {
        DB::beginTransaction();
        try {
            $gig = Gig::where('id', $gigId)
                ->where('user_id', $userId)
                ->first();

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Validate gig is ready for publishing
            $this->validateGigForPublish($gig);

            $gig->update([
                'status' => 'active',
                'published_at' => $gig->published_at ?? now(),
            ]);

            DB::commit();

            return $gig->fresh(['category', 'subCategory', 'images', 'documents']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete gig
     */
    public function deleteGig(int $gigId, int $userId)
    {
        DB::beginTransaction();
        try {
            $gig = Gig::where('id', $gigId)
                ->where('user_id', $userId)
                ->first();

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Delete associated files
            $this->deleteGigFiles($gig);

            $gig->delete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete gig image
     */
    public function deleteImage(int $gigId, int $userId, int $imageId)
    {
        DB::beginTransaction();
        try {
            $gig = Gig::where('id', $gigId)
                ->where('user_id', $userId)
                ->first();

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $image = $gig->images()->where('id', $imageId)->first();

            if (!$image) {
                throw new Exception('Image not found');
            }

            // Delete file from storage
            if (Storage::exists($image->path)) {
                Storage::delete($image->path);
            }

            $image->delete();

            DB::commit();

            return $gig->fresh(['images', 'documents']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete gig document
     */
    public function deleteDocument(int $gigId, int $userId, int $documentId)
    {
        DB::beginTransaction();
        try {
            $gig = Gig::where('id', $gigId)
                ->where('user_id', $userId)
                ->first();

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $document = $gig->documents()->where('id', $documentId)->first();

            if (!$document) {
                throw new Exception('Document not found');
            }

            // Delete file from storage
            if (Storage::exists($document->path)) {
                Storage::delete($document->path);
            }

            $document->delete();

            DB::commit();

            return $gig->fresh(['images', 'documents']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Track impression
     */
    public function trackImpression(int $gigId)
    {
        Gig::where('id', $gigId)->increment('impressions');
    }

    /**
     * Track click
     */
    public function trackClick(int $gigId)
    {
        Gig::where('id', $gigId)->increment('clicks');
    }

    /**
     * Upload images for gig
     */
    private function uploadImages(Gig $gig, array $images)
    {
        $existingCount = $gig->images()->count();

        foreach ($images as $index => $image) {
            $path = Helper::fileUpload($image, 'gigs/images');

            if ($path) {
                $gig->images()->create([
                    'path' => $path,
                    'is_primary' => ($existingCount === 0 && $index === 0),
                    'sort_order' => $existingCount + $index,
                ]);
            }
        }
    }

    /**
     * Upload documents for gig
     */
    private function uploadDocuments(Gig $gig, array $documents)
    {
        foreach ($documents as $document) {
            $path = Helper::fileUpload($document, 'gigs/documents');

            if ($path) {
                $gig->documents()->create([
                    'path' => $path,
                ]);
            }
        }
    }

    /**
     * Delete all files associated with gig
     */
    private function deleteGigFiles(Gig $gig)
    {
        // Delete images
        foreach ($gig->images as $image) {
            if (Storage::exists($image->path)) {
                Storage::delete($image->path);
            }
        }

        // Delete documents
        foreach ($gig->documents as $document) {
            if (Storage::exists($document->path)) {
                Storage::delete($document->path);
            }
        }
    }

    /**
     * Validate gig is ready for publishing
     */
    private function validateGigForPublish(Gig $gig)
    {
        if (empty($gig->title)) {
            throw new Exception('Gig title is required');
        }

        if (empty($gig->category_id)) {
            throw new Exception('Category is required');
        }

        if (empty($gig->scope)) {
            throw new Exception('Gig description is required');
        }

        if (empty($gig->price)) {
            throw new Exception('Price is required');
        }

        if (empty($gig->delivery_days)) {
            throw new Exception('Delivery timeline is required');
        }

        if ($gig->images()->count() === 0) {
            throw new Exception('At least one gig image is required');
        }
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters)
    {
        // Keyword search (title + scope)
        if (isset($filters['keyword'])) {
            $keyword = trim($filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('scope', 'LIKE', "%{$keyword}%");
            });
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['delivery_days'])) {
            $query->where('delivery_days', '<=', $filters['delivery_days']);
        }

        if (isset($filters['last_days'])) {
            $query->where('created_at', '>=', now()->subDays($filters['last_days']));
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
