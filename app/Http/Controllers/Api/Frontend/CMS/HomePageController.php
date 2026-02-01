<?php

namespace App\Http\Controllers\Api\Frontend\CMS;

use App\Http\Resources\HomePagePartnerResource;
use App\Models\CMS;
use App\Models\Slider;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Testimonials;

class HomePageController extends Controller
{
    use ApiResponse;
    // get home page all cms data
    public function home()
    {
        $hero = CMS::where('page', 'home')->where('section', 'hero')->get();
        $training_camp = CMS::where('page', 'home')->where('section', 'training-camp')->get();
        $partners = CMS::where('page', 'home')->where('section', 'partner')->where('name', 'item')->get();
        $sliderData = Slider::where('status', true)->get();
        $features = CMS::where('page', 'home')->where('section', 'features')->where('name', 'item')->get();
        $features_item = CMS::where('page', 'home')->where('section', 'features')->where('name', 'card')->get();
        $operations = CMS::where('page', 'home')->where('section', 'operations')->where('name', 'item')->get();
        $testimonial = CMS::where('page', 'home')->where('section', 'testimonial')->where('name', 'item')->get();
        $testimonial_card = Testimonials::get();
        return $this->success([
            'hero' => $hero->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                ];
            }),

            'training_camp' => $training_camp->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                ];
            }),

            'partners' => $partners->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                ];
            }),
            'partners_logo' => HomePagePartnerResource::collection($sliderData),
            'features' => $features->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                ];
            }),
            'features_item' => $features_item->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'image' => $item->image ? asset($item->image) : null,
                ];
            }),
            'operations' => $operations->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                ];
            }),
            'testimonial' => $testimonial->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                ];
            }),
            'testimonial_card' => $testimonial_card->map(function ($item) {
                return [
                    'id' => $item->id,
                    'author_name' => $item->author_name,
                    'review_text' => $item->review_text,
                    'designation' => $item->designation,
                    'author_avatar' => $item->author_avatar ? asset($item->author_avatar) : null,
                ];
            }),
        ], 'Home data retrieved successfully');
    }
}
