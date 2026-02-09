<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Web Development' => [
                'Frontend Development',
                'Backend Development',
                'Full Stack Development',
                'API Development',
                'Web Security',
            ],
            'Mobile App Development' => [
                'Android Development',
                'iOS Development',
                'Flutter Development',
                'React Native',
                'Mobile App Testing',
            ],
            'Software Engineering' => [
                'System Design',
                'Object Oriented Programming',
                'Clean Code',
                'Software Architecture',
                'Design Patterns',
            ],
            'DevOps & Cloud' => [
                'AWS',
                'Docker',
                'Kubernetes',
                'CI/CD',
                'Server Management',
            ],
            'Artificial Intelligence' => [
                'Machine Learning',
                'Deep Learning',
                'Natural Language Processing',
                'Computer Vision',
                'AI Model Training',
            ],
            'Data Science' => [
                'Data Analysis',
                'Data Visualization',
                'Big Data',
                'Data Mining',
                'Statistics',
            ],
            'Cyber Security' => [
                'Ethical Hacking',
                'Network Security',
                'Application Security',
                'Penetration Testing',
                'Security Auditing',
            ],
            'Blockchain' => [
                'Smart Contracts',
                'Ethereum',
                'Solidity',
                'Web3 Development',
                'Crypto Wallets',
            ],
            'Game Development' => [
                'Unity',
                'Unreal Engine',
                '2D Game Development',
                '3D Game Development',
                'Game Physics',
            ],
            'UI/UX Design' => [
                'User Research',
                'Wireframing',
                'Prototyping',
                'Interaction Design',
                'Usability Testing',
            ],
        ];

        foreach ($categories as $parentName => $subCategories) {

            $parent = Category::create([
                'name'        => $parentName,
                'slug'        => Str::slug($parentName),
                'description' => $parentName . ' related services',
                'is_active'   => true,
                'order'       => 0,
            ]);

            foreach ($subCategories as $index => $subName) {
                Category::create([
                    'name'        => $subName,
                    'slug'        => Str::slug($subName),
                    'parent_id'   => $parent->id,
                    'description' => $subName . ' under ' . $parentName,
                    'is_active'   => true,
                    'order'       => $index + 1,
                ]);
            }
        }
    }
}
