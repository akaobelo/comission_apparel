<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTestimonials = [
            [
                'client_name' => 'Justin Gatlin',
                'organization' => 'USA Track & Field',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                'sort_order' => 1,
            ],
            [
                'client_name' => 'Jason Jacobs',
                'organization' => 'Long Jump',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                'sort_order' => 2,
            ]
        ];

        foreach ($defaultTestimonials as $testimonial) {
            \App\Models\Testimonial::create($testimonial);
        }
    }
}
