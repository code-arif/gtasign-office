<?php

namespace App\Services;

use App\Repositories\Interfaces\GigRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class GigService
{
    protected $gigRepository;

    public function __construct(GigRepositoryInterface $gigRepository)
    {
        $this->gigRepository = $gigRepository;
    }

    /**
     * Get all gigs
     */
    public function getAllGigs(array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->getAllGigs($filters, $perPage);
    }

    /**
     * Get user gigs
     */
    public function getUserGigs(int $userId, array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->getUserGigs($userId, $filters, $perPage);
    }

    /**
     * Get gig by id
     */
    public function getGigById(int $id)
    {
        return $this->gigRepository->getGigById($id);
    }

    /**
     * Create gig - Overview step
     */
    public function createOverview(int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gigData = [
                'user_id' => $userId,
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'search_tags' => $data['search_tags'] ?? [],
                'status' => 'draft',
            ];

            $gig = $this->gigRepository->createGig($gigData);

            DB::commit();
            return $gig;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update gig - Pricing step
     */
    public function updatePricing(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $updateData = [
                'scope' => $data['scope'] ?? null,
                'price' => $data['price'],
                'delivery_days' => $data['delivery_days'],
            ];

            $this->gigRepository->updateGig($gigId, $updateData);

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update gig - Requirements step
     */
    public function updateRequirements(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $updateData = [
                'secaax_questions' => $data['secaax_questions'] ?? [],
                'custom_questions' => $data['custom_questions'] ?? [],
            ];

            $this->gigRepository->updateGig($gigId, $updateData);

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update gig - Gallery step
     */
    public function updateGallery(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $updateData = [];

            // Handle images upload
            if (isset($data['images']) && is_array($data['images'])) {
                $imagePaths = [];
                foreach ($data['images'] as $image) {
                    if ($image->isValid()) {
                        $path = $image->store('gigs/images', 'public');
                        $imagePaths[] = $path;
                    }
                }
                $updateData['images'] = $imagePaths;
            }

            // Handle documents upload
            if (isset($data['documents']) && is_array($data['documents'])) {
                $documentPaths = [];
                foreach ($data['documents'] as $document) {
                    if ($document->isValid()) {
                        $path = $document->store('gigs/documents', 'public');
                        $documentPaths[] = $path;
                    }
                }
                $updateData['documents'] = $documentPaths;
            }

            $this->gigRepository->updateGig($gigId, $updateData);

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete gig image
     */
    public function deleteImage(int $gigId, int $userId, string $imagePath)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $images = $gig->images ?? [];
            $key = array_search($imagePath, $images);

            if ($key !== false) {
                // Delete from storage
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }

                // Remove from array
                unset($images[$key]);
                $images = array_values($images); // Re-index array

                $this->gigRepository->updateGig($gigId, ['images' => $images]);
            }

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete gig document
     */
    public function deleteDocument(int $gigId, int $userId, string $documentPath)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $documents = $gig->documents ?? [];
            $key = array_search($documentPath, $documents);

            if ($key !== false) {
                // Delete from storage
                if (Storage::disk('public')->exists($documentPath)) {
                    Storage::disk('public')->delete($documentPath);
                }

                // Remove from array
                unset($documents[$key]);
                $documents = array_values($documents); // Re-index array

                $this->gigRepository->updateGig($gigId, ['documents' => $documents]);
            }

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
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
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Validate gig is complete
            $this->validateGigForPublish($gig);

            // Update status to pending approval
            $this->gigRepository->updateStatus($gigId, 'pending_approval');

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update gig (complete update)
     */
    public function updateGig(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $this->gigRepository->updateGig($gigId, $data);

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
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
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Delete associated files
            $this->deleteGigFiles($gig);

            $this->gigRepository->deleteGig($gigId);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve gig (Admin only)
     */
    public function approveGig(int $gigId)
    {
        return $this->gigRepository->updateStatus($gigId, 'active');
    }

    /**
     * Reject gig (Admin only)
     */
    public function rejectGig(int $gigId, string $reason)
    {
        return $this->gigRepository->updateStatus($gigId, 'rejected', $reason);
    }

    /**
     * Get active gigs
     */
    public function getActiveGigs(array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->getActiveGigs($filters, $perPage);
    }

    /**
     * Search gigs
     */
    public function searchGigs(string $query, array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->searchGigs($query, $filters, $perPage);
    }

    /**
     * Track gig impression
     */
    public function trackImpression(int $gigId)
    {
        $this->gigRepository->incrementImpressions($gigId);
    }

    /**
     * Track gig click
     */
    public function trackClick(int $gigId)
    {
        $this->gigRepository->incrementClicks($gigId);
    }

    /**
     * Helper: Validate gig for publish
     */
    protected function validateGigForPublish($gig)
    {
        if (empty($gig->title)) {
            throw new Exception('Gig title is required');
        }

        if (empty($gig->category_id)) {
            throw new Exception('Category is required');
        }

        if (empty($gig->price)) {
            throw new Exception('Price is required');
        }

        if (empty($gig->delivery_days)) {
            throw new Exception('Delivery timeline is required');
        }

        if (empty($gig->images) || count($gig->images) === 0) {
            throw new Exception('At least one gig image is required');
        }
    }

    /**
     * Helper: Delete gig files
     */
    protected function deleteGigFiles($gig)
    {
        // Delete images
        if ($gig->images && is_array($gig->images)) {
            foreach ($gig->images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        // Delete documents
        if ($gig->documents && is_array($gig->documents)) {
            foreach ($gig->documents as $document) {
                if (Storage::disk('public')->exists($document)) {
                    Storage::disk('public')->delete($document);
                }
            }
        }
    }
}
