<?php

namespace App\Http\Resources\Gig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigAnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'orders' => $this->orders,
            'cancellations' => $this->cancellations,
            'cancellation_rate' => $this->orders > 0
                ? round(($this->cancellations / $this->orders) * 100, 2)
                : 0,
        ];
    }
}
