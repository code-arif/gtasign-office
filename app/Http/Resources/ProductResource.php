<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'bmw_model' => $this->bmw_model ? [
                'id' => $this->bmw_model->id,
                'name' => $this->bmw_model->model_name,
            ] : null,
            'bmw_series' => $this->bmw_series ? [
                'id' => $this->bmw_series->id,
                'name' => $this->bmw_series->name,
            ] : null,
            'title' => $this->title,
            'component_type' => $this->component_type ?? null,
            'engine_family' => $this->engine_family ?? null,
            'parts_number' => $this->parts_number ?? null,
            'condition' => $this->condition,
            'price' => $this->price,
            'discount' => $this->discount ?? null,
            'listing_duration' => $this->listing_duration ?? null,
            'offer' => $this->offer ?? null,
            'parts_description' => $this->parts_description ?? null,
            'city' => $this->city ?? null,
            'state' => $this->state ?? null,
            'zip_code' => $this->zip_code ?? null,
            'shipping_option' => $this->shipping_option ?? null,
            'images' => $this->product_images ? $this->product_images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->image_url,
                ];
            }) : [],
            'keywords' => $this->product_keywords ? $this->product_keywords->map(function ($keyword) {
                return [
                    'id' => $keyword->id,
                    'keyword' => $keyword->keyword,
                ];
            }) : [],
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
