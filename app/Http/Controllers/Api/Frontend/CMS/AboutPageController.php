<?php

namespace App\Http\Controllers\Api\Frontend\CMS;

use App\Models\CMS;
use App\Models\OwnerInfo;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AboutPageController extends Controller
{
    use ApiResponse;

    // get about page all cms data
    public function about()
    {
        $hero = CMS::where('page', 'about')->where('section', 'page_title')->get();
        $mission = CMS::where('page', 'about')->where('section', 'mission')->get();
        $keyToExcellence = CMS::where('page', 'about')->where('section', 'key_to_excellence')->first();
        $bottomDescription = CMS::where('page', 'about')->where('section', 'bottom_description')->first();
        $features_item = CMS::where('page', 'about')->where('section', 'features')->where('name', 'feature_card')->get();
        $owner = OwnerInfo::where('status', 'active')->first();
        $teamHeader = CMS::where('page', 'about')
            ->where('section', 'our-team')
            ->where('name', 'item')
            ->first();
        $teamMember = CMS::where('page', 'about')
            ->where('section', 'our-team')
            ->where('name', 'card')
            ->orderBy('id')->get();
        $getStarted = CMS::where('page', 'about')
            ->where('section', 'getting_started')
            ->where('name', 'item')
            ->first();
        return $this->success('About data retrieved successfully', [
            'hero' => $hero->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                ];
            }),
            'mission' => $mission->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                ];
            }),
            'key_to_excellence' => $keyToExcellence ? [
                'id' => $keyToExcellence->id,
                'title' => $keyToExcellence->title,
                'description' => $keyToExcellence->description,
            ] : null,
            'bottom_description' => $bottomDescription ? [
                'id' => $bottomDescription->id,
                'description' => $bottomDescription->description,
            ] : null,
            'features_item' => $features_item->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'image' => $item->image ? asset($item->image) : null,
                ];
            }),
            'owner_info' => $owner ? [
                'id' => $owner->id,
                'name' => $owner->name,
                'designation' => $owner->designation,
                'experience' => $owner->experience,
                'bio' => $owner->bio,
                'image' => $owner->image ? asset($owner->image) : asset('default/profile.jpg'),
                'stats' => $owner->stats,
            ] : null,
            'team_header' => $teamHeader ? [
                'id' => $teamHeader->id,
                'title' => $teamHeader->title,
                'description' => $teamHeader->description,
            ] : null,
            'team_member' => $teamMember->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'designation' => $item->designation,
                    'image' => $item->image ? asset($item->image) : asset('default/profile.jpg'),
                ];
            }),
            'get_started' => $getStarted ? [
                'id' => $getStarted->id,
                'title' => $getStarted->title,
                'description' => $getStarted->description,
            ] : null,
        ]);
    }
}
