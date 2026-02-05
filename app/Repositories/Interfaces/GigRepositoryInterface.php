<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Gig;

interface GigRepositoryInterface
{
    /**
     * Get all gigs with filters
     */
    // public function getAllGigs(array $filters = [], int $perPage = 15);

    /**
     * Get user gigs
     */
    // public function getUserGigs(int $userId, array $filters = [], int $perPage = 15);

    /**
     * Get gig by id
     */
    public function getGigById(int $id): ?Gig;

    /**
     * Create gig
     */
    public function createGig(array $data): Gig;

    /**
     * Update gig
     */
    public function updateGig(int $id, array $data): bool;

    /**
     * Delete gig
     */
    public function deleteGig(int $id): bool;

    /**
     * Get gig by id and user
     */
    public function getGigByIdAndUser(int $id, int $userId): ?Gig;

    /**
     * Update gig status
     */
    public function updateStatus(int $id, string $status, ?string $reason = null): bool;

    /**
     * Increment gig analytics
     */
    public function incrementImpressions(int $id): void;
    public function incrementClicks(int $id): void;
    public function incrementOrders(int $id): void;
    public function incrementCancellations(int $id): void;

    /**
     * Get active gigs
     */
    public function getActiveGigs(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Search gigs
     */
    public function searchGigs(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
