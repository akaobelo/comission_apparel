<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LandingCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collections = [
            [
                'tab_name' => 'Tackle Football',
                'title' => 'Springfield Secondary School',
                'description' => '4-Way stretch compression fit with sublimated side panels and robust stitching.',
                'image_path' => '/images/soccer-model.png',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'tab_name' => 'Track & Field',
                'title' => 'Springfield Secondary School',
                'description' => '4-Way stretch compression fit with sublimated side panels and robust stitching.',
                'image_path' => '/images/gatlin.png',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'tab_name' => 'Basketball',
                'title' => 'Springfield Secondary School',
                'description' => 'Engineered for the court with moisture-wicking tech and unlimited mobility.',
                'image_path' => '/images/basketball.png',
                'sort_order' => 3,
                'is_active' => true,
            ]
        ];

        foreach ($collections as $collection) {
            \App\Models\LandingCollection::create($collection);
        }
    }
}
