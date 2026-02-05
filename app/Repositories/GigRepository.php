<?php

namespace App\Repositories;

use App\Models\Gig;
use App\Repositories\Interfaces\GigRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GigRepository implements GigRepositoryInterface
{
    protected $model;

    public function __construct(Gig $model)
    {
        $this->model = $model;
    }

    /**
     * Get all gigs with filters
     */
    public function getAllGigs(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with(['user', 'category', 'subCategory']);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get user gigs
     */
    public function getUserGigs(int $userId, array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with(['category', 'subCategory'])
            ->where('user_id', $userId);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get gig by id
     */
    public function getGigById(int $id): ?Gig
    {
        return $this->model->with(['user', 'category', 'subCategory'])->find($id);
    }

    /**
     * Create gig
     */
    public function createGig(array $data): Gig
    {
        return $this->model->create($data);
    }

    /**
     * Update gig
     */
    public function updateGig(int $id, array $data): bool
    {
        $gig = $this->model->find($id);

        if (!$gig) {
            return false;
        }

        return $gig->update($data);
    }

    /**
     * Delete gig
     */
    public function deleteGig(int $id): bool
    {
        $gig = $this->model->find($id);

        if (!$gig) {
            return false;
        }

        return $gig->delete();
    }

    /**
     * Get gig by id and user
     */
    public function getGigByIdAndUser(int $id, int $userId): ?Gig
    {
        return $this->model->with(['category', 'subCategory'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Update gig status
     */
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

    /**
     * Increment gig analytics
     */
    public function incrementImpressions(int $id): void
    {
        $gig = $this->model->find($id);
        if ($gig) {
            $gig->incrementImpressions();
        }
    }

    public function incrementClicks(int $id): void
    {
        $gig = $this->model->find($id);
        if ($gig) {
            $gig->incrementClicks();
        }
    }

    public function incrementOrders(int $id): void
    {
        $gig = $this->model->find($id);
        if ($gig) {
            $gig->incrementOrders();
        }
    }

    public function incrementCancellations(int $id): void
    {
        $gig = $this->model->find($id);
        if ($gig) {
            $gig->incrementCancellations();
        }
    }

    /**
     * Get active gigs
     */
    public function getActiveGigs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'category', 'subCategory'])
            ->active();

        $query = $this->applyFilters($query, $filters);

        return $query->latest('published_at')->paginate($perPage);
    }

    /**
     * Search gigs
     */
    public function searchGigs(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $gigQuery = $this->model->with(['user', 'category', 'subCategory'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('scope', 'like', "%{$query}%")
                    ->orWhereJsonContains('search_tags', $query);
            });

        $gigQuery = $this->applyFilters($gigQuery, $filters);

        return $gigQuery->latest()->paginate($perPage);
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters)
    {
        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Category filter
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Sub category filter
        if (isset($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        // Price range filter
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Delivery days filter
        if (isset($filters['delivery_days'])) {
            $query->where('delivery_days', $filters['delivery_days']);
        }

        // Date range filter
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Last N days filter (for analytics)
        if (isset($filters['last_days'])) {
            $query->where('created_at', '>=', now()->subDays($filters['last_days']));
        }

        return $query;
    }
}
