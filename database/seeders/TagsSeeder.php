<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'PHP',
            'Laravel',
            'JavaScript',
            'React',
            'Vue.js',
            'Node.js',
            'Python',
            'Django',
            'Machine Learning',
            'Docker',
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
            ]);
        }
    }
}
