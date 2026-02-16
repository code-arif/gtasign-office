<?php

namespace App\Repositories;

use App\Models\CustomOffer;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomOfferRepository
{
    protected $model;

    public function __construct(CustomOffer $model)
    {
        $this->model = $model;
    }

    /**
     * Create new custom offer
     */
    public function create(array $data): CustomOffer
    {
        return $this->model->create($data);
    }

    /**
     * Get offer by ID
     */
    public function getById(int $id): ?CustomOffer
    {
        return $this->model
            ->with(['gig', 'expert.profile', 'client.profile', 'room'])
            ->find($id);
    }

    /**
     * Get offer by ID for specific user
     */
    public function getByIdForUser(int $id, int $userId): ?CustomOffer
    {
        return $this->model
            ->with(['gig', 'expert.profile', 'client.profile', 'room'])
            ->where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('expert_id', $userId)
                      ->orWhere('client_id', $userId);
            })
            ->first();
    }

    /**
     * Get offers in room
     */
    public function getOffersInRoom(int $roomId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['gig', 'expert.profile', 'client.profile'])
            ->where('room_id', $roomId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Update offer
     */
    public function update(int $id, array $data): bool
    {
        $offer = $this->model->find($id);

        if (!$offer) {
            return false;
        }

        return $offer->update($data);
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(int $id): bool
    {
        return $this->update($id, [
            'status' => 'expired'
        ]);
    }

    /**
     * Get expired offers
     */
    public function getExpiredOffers()
    {
        return $this->model
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->get();
    }
}
