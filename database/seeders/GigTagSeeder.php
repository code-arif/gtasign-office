<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;

class GigTagSeeder extends Seeder
{
    public function run(): void
    {
        // all tag id
        $tagIds = Tag::pluck('id')->toArray();

        // say, gig id = 1 → 10
        for ($gigId = 1; $gigId <= 10; $gigId++) {

            // every gig has 3-5 random tags
            $randomTags = collect($tagIds)
                ->shuffle()
                ->take(rand(3, 5));

            foreach ($randomTags as $tagId) {
                DB::table('gig_tags')->insert([
                    'gig_id' => $gigId,
                    'tag_id' => $tagId,
                ]);
            }
        }
    }
}
