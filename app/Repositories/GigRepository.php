<?php

namespace App\Repositories;

use App\Models\Gig;
use Illuminate\Pagination\LengthAwarePaginator;

namespace App\Repositories;

use App\Models\Gig;
use App\Models\GigImage;
use App\Models\GigDocument;
use Illuminate\Pagination\LengthAwarePaginator;

class GigRepository
{
    protected $model;

    public function __construct(Gig $model)
    {
        $this->model = $model;
    }

    public function getAllGigs(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with(['user', 'category', 'subCategory', 'images']);
        $query = $this->applyFilters($query, $filters);
        return $query->latest()->paginate($perPage);
    }

    public function getUserGigs(int $userId, array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with(['category', 'subCategory', 'images', 'documents'])
            ->where('user_id', $userId);
        $query = $this->applyFilters($query, $filters);
        return $query->latest()->paginate($perPage);
    }

    public function getGigById(int $id): ?Gig
    {
        return $this->model
            ->with(['user', 'category', 'subCategory', 'images', 'documents'])
            ->find($id);
    }

    public function getGigByIdAndUser(int $id, int $userId): ?Gig
    {
        return $this->model
            ->with(['category', 'subCategory', 'images', 'documents'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function createGig(array $data): Gig
    {
        return $this->model->create($data);
    }

    public function updateGig(int $id, array $data): bool
    {
        $gig = $this->model->find($id);
        if (!$gig) {
            return false;
        }
        return $gig->update($data);
    }

    public function deleteGig(int $id): bool
    {
        $gig = $this->model->find($id);
        if (!$gig) {
            return false;
        }
        // Images & Documents will be deleted via model event
        return $gig->delete();
    }

    public function updateStatus(int $id, string $status, ?string $reason = null): bool
    {
        $gig = $this->model->find($id);
        if (!$gig) {
            return false;
        }

        $data = ['status' => $status];

        if ($status === 'rejected' && $reason) {
            $data['rejection_reason'] = $reason;
        }

        if ($status === 'active' && !$gig->published_at) {
            $data['published_at'] = now();
        }

        return $gig->update($data);
    }

    public function getActiveGigs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'category', 'subCategory', 'images'])
            ->active();
        $query = $this->applyFilters($query, $filters);
        return $query->latest('published_at')->paginate($perPage);
    }

    public function searchGigs(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $gigQuery = $this->model->with(['user', 'category', 'subCategory', 'images'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('scope', 'like', "%{$query}%");
            })
            ->orWhereHas('tags', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            });

        $gigQuery = $this->applyFilters($gigQuery, $filters);
        return $gigQuery->latest()->paginate($perPage);
    }

    // Image Operations
    public function addImage(int $gigId, array $imageData): GigImage
    {
        $gig = $this->model->find($gigId);
        return $gig->images()->create($imageData);
    }

    public function deleteImage(int $imageId): bool
    {
        $image = GigImage::find($imageId);
        if (!$image) {
            return false;
        }
        return $image->delete(); // Will auto-delete file via model event
    }

    // Document Operations
    public function addDocument(int $gigId, array $documentData): GigDocument
    {
        $gig = $this->model->find($gigId);
        return $gig->documents()->create($documentData);
    }

    public function deleteDocument(int $documentId): bool
    {
        $document = GigDocument::find($documentId);
        if (!$document) {
            return false;
        }
        return $document->delete(); // Will auto-delete file via model event
    }

    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
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

        return $query;
    }
}
