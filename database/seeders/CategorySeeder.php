<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['title' => 'FLEKSWISS' , 'slug' => 'flekswiss'],
            ['title' => 'PENADA' , 'slug' => 'penada'],
            ['title' => 'YOUNG' , 'slug' => 'young'],
            ['title' => 'SNEAKERS' , 'slug' => 'sneakers'],
            ['title' => 'FORMAL SHOES' , 'slug' => 'formal-shoes'],
            ['title' => 'BOOTS' , 'slug' => 'boots'],
            ['title' => 'CASUAL SHOES' , 'slug' => 'casual-shoes'],
            ['title' => 'SPORTS SHOES' , 'slug' => 'sports-shoes'],


        ]);
    }
}
