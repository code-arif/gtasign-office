<?php

namespace App\Http\Resources\Gig;

use App\Http\Resources\Order\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// class GigListResource extends JsonResource
// {
//     /**
//      * Transform the resource into an array.
//      *
//      * @return array<string, mixed>
//      */
//     public function toArray(Request $request): array
//     {
//         return [
//             'id' => $this->id,
//             'title' => $this->title,
//             'category' => $this->category?->name,
//             'price' => $this->price ? (float) $this->price : null,
//             'delivery_days' => $this->delivery_days,
//             'thumbnail' => $this->images && count($this->images) > 0
//                 ? asset('storage/' . $this->images[0])
//                 : null,
//             'status' => $this->status,
//             'analytics' => [
//                 'impressions' => $this->impressions,
//                 'clicks' => $this->clicks,
//                 'orders' => $this->orders,
//                 'cancellations' => $this->cancellations,
//                 'cancellation_rate' => $this->cancellation_rate,
//             ],
//             'user' => $this->when($this->relationLoaded('user'), [
//                 'id' => $this->user?->id,
//                 'email' => $this->user?->email,
//             ]),
//             'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
//         ];
//     }
// }

class GigListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'sub_category' => new CategoryResource($this->whenLoaded('subCategory')),

            'price' => (float) $this->price,
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'delivery_days' => $this->delivery_days,
            'status' => $this->status,

            'reviews_count' => $this->reviews_count ?? 0,
            'average_rating' => $this->reviews_avg_rating
                ? round((float) $this->reviews_avg_rating, 2)
                : 0,

            'primary_image' => new GigImageResource(
                $this->whenLoaded(
                    'images',
                    fn() =>
                    $this->images->where('is_primary', true)->first()
                )
            ),

            'user' => new GigUserResource($this->whenLoaded('user')),

            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
        ];
    }
}
