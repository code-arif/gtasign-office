<?php

namespace App\Services;

use Exception;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use App\Repositories\GigRepository;

class GigService
{
    protected $gigRepository;

    public function __construct(GigRepository $gigRepository)
    {
        $this->gigRepository = $gigRepository;
    }

    public function getAllGigs(array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->getAllGigs($filters, $perPage);
    }

    public function getUserGigs(int $userId, array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->getUserGigs($userId, $filters, $perPage);
    }

    public function getGigById(int $id)
    {
        return $this->gigRepository->getGigById($id);
    }

    public function createOverview(int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gigData = [
                'user_id' => $userId,
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'status' => 'draft',
            ];

            $gig = $this->gigRepository->createGig($gigData);

            // Attach tags if provided
            if (!empty($data['tag_ids'])) {
                $gig->tags()->sync($data['tag_ids']);
            }

            DB::commit();
            return $gig->load(['category', 'subCategory']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

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

    public function updateRequirements(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $updateData = [
                'system_questions' => $data['system_questions'] ?? [],
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

    public function updateGallery(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Upload images
            if (!empty($data['images']) && is_array($data['images'])) {
                $existingImagesCount = $gig->images()->count();

                foreach ($data['images'] as $index => $image) {
                    $path = Helper::fileUpload($image, 'gigs/images');

                    if ($path) {
                        $this->gigRepository->addImage($gigId, [
                            'path' => $path,
                            'is_primary' => ($existingImagesCount === 0 && $index === 0),
                            'sort_order' => $existingImagesCount + $index,
                        ]);
                    }
                }
            }

            // Upload documents
            if (!empty($data['documents']) && is_array($data['documents'])) {
                foreach ($data['documents'] as $document) {
                    $path = Helper::fileUpload($document, 'gigs/documents');

                    if ($path) {
                        $this->gigRepository->addDocument($gigId, [
                            'path' => $path,
                        ]);
                    }
                }
            }

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteImage(int $gigId, int $userId, int $imageId)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Check if image belongs to this gig
            $image = $gig->images()->where('id', $imageId)->first();

            if (!$image) {
                throw new Exception('Image not found');
            }

            $this->gigRepository->deleteImage($imageId);

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteDocument(int $gigId, int $userId, int $documentId)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Check if document belongs to this gig
            $document = $gig->documents()->where('id', $documentId)->first();

            if (!$document) {
                throw new Exception('Document not found');
            }

            $this->gigRepository->deleteDocument($documentId);

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateGig(int $gigId, int $userId, array $data)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            // Only allow updating certain fields
            $allowedFields = [
                'title',
                'scope',
                'price',
                'delivery_days',
                'system_questions',
                'custom_questions'
            ];

            $updateData = array_intersect_key($data, array_flip($allowedFields));

            $this->gigRepository->updateGig($gigId, $updateData);

            // Update tags if provided
            if (isset($data['tag_ids'])) {
                $gig->tags()->sync($data['tag_ids']);
            }

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function publishGig(int $gigId, int $userId)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $this->validateGigForPublish($gig);

            $this->gigRepository->updateStatus($gigId, 'active');

            DB::commit();
            return $this->gigRepository->getGigById($gigId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteGig(int $gigId, int $userId)
    {
        DB::beginTransaction();
        try {
            $gig = $this->gigRepository->getGigByIdAndUser($gigId, $userId);

            if (!$gig) {
                throw new Exception('Gig not found');
            }

            $this->gigRepository->deleteGig($gigId);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getActiveGigs(array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->getActiveGigs($filters, $perPage);
    }

    public function searchGigs(string $query, array $filters = [], int $perPage = 15)
    {
        return $this->gigRepository->searchGigs($query, $filters, $perPage);
    }

    public function trackImpression(int $gigId)
    {
        $gig = $this->gigRepository->getGigById($gigId);
        if ($gig) {
            $gig->incrementImpressions();
        }
    }

    public function trackClick(int $gigId)
    {
        $gig = $this->gigRepository->getGigById($gigId);
        if ($gig) {
            $gig->incrementClicks();
        }
    }

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

        if ($gig->images()->count() === 0) {
            throw new Exception('At least one gig image is required');
        }
    }
}
