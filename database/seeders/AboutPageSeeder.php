<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Models\OwnerInfo;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    public function run(): void
    {
        // Page Title
        CMS::updateOrCreate(
            [
                'page' => 'about',
                'section' => 'page_title',
            ],
            [
                'title' => 'About Us',
                'description' => 'Learn more about our innovative technology',
                'layout' => 'page_title_v1',
                'component' => 'PageTitle',
                'status' => 'active',
            ]
        );

        // Mission Section
        CMS::updateOrCreate(
            [
                'page' => 'about',
                'section' => 'mission',
            ],
            [
                'title' => 'Our Mission',
                'description' => 'To develop better officials through elite training, honest feedback, and innovative technology, helping referees advance their careers at every level.',
                'layout' => 'mission_box_v1',
                'component' => 'MissionBox',
                'status' => 'active',
            ]
        );

        // Key to Excellence Section
        CMS::updateOrCreate(
            [
                'page' => 'about',
                'section' => 'key_to_excellence',
            ],
            [
                'title' => 'The Key to Excellence',
                'description' => 'A key factor in advancing to the highest levels was consistent participation in elite training camps, investing in instruction, evaluation, and mentorship to continuously improve performance on the court.',
                'layout' => 'key_excellence_v1',
                'component' => 'KeyExcellence',
                'status' => 'active',
            ]
        );

        // Bottom Description
        CMS::updateOrCreate(
            [
                'page' => 'about',
                'section' => 'bottom_description',
            ],
            [
                'description' => 'Our app provides GPS game assignment integration, instant evaluations, performance feedback, and development tracking, elevating referees with the tools they need to grow faster and more confidently. This allows officials to identify strengths, address weaknesses, and take ownership of their development like never before.',
                'layout' => 'description_block_v1',
                'component' => 'DescriptionBlock',
                'status' => 'active',
            ]
        );

        // Owner Info
        OwnerInfo::updateOrCreate(
            ['status' => 'active'],
            [
                'name' => 'Drew Bontrager',
                'designation' => 'President/CEO',
                'experience' => '25+ Years Experience',
                'bio' => 'Our founder brings more than 25 years of basketball officiating experience and a lifelong commitment to growth, excellence, and development in everything we do. Throughout his career, he worked at every collegiate level, including NCAA Division I in the Missouri Valley, Southland, and Ohio Valley Conferences, along with multiple NCAA Division II Regional tournaments and conference championship games.',
                'stats' => [
                    ['value' => '25+', 'label' => 'Years Officiating'],
                    ['value' => 'NCAA', 'label' => 'Retired Division 1 Official'],
                    ['value' => '10+', 'label' => 'Conferences'],
                ],
                'status' => 'active',
            ]
        );

        // Feature Items
        $features = [
            [
                'title' => 'GPS Integration',
                'description' => 'Smart game assignment tracking',
                'order' => 1,
            ],
            [
                'title' => 'Instant Feedback',
                'description' => 'Real-time performance evaluations',
                'order' => 2,
            ],
            [
                'title' => 'Progress Tracking',
                'description' => 'Development analytics & insights',
                'order' => 3,
            ],
            [
                'title' => 'Mentorship',
                'description' => 'Expert guidance & coaching',
                'order' => 4,
            ],
        ];

        foreach ($features as $feature) {
            CMS::updateOrCreate(
                [
                    'page' => 'about',
                    'section' => 'features',
                    'title' => $feature['title'],
                ],
                [
                    'name' => 'feature_card',
                    'description' => $feature['description'],
                    'layout' => 'feature_card_v1',
                    'component' => 'FeatureCard',
                    'order' => $feature['order'],
                    'status' => 'active',
                ]
            );
        }
    }
}
