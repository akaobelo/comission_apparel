<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesAgent;

class SalesAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // US Agents
        SalesAgent::create([
            'name' => 'John Doe',
            'title' => 'East Coast Regional Sales Manager',
            'state' => 'New York',
            'country' => 'USA',
            'bio' => 'John has over 10 years of experience in the custom team sports apparel industry, helping schools and organizations establish modern athletic brands.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SalesAgent::create([
            'name' => 'Sarah Connor',
            'title' => 'West Coast Sales Rep',
            'state' => 'California',
            'country' => 'USA',
            'bio' => 'Sarah specializes in athletic performance wear and works directly with athletic directors to design custom uniform systems.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        SalesAgent::create([
            'name' => 'Michael Jordan',
            'title' => 'Midwest Regional Representative',
            'state' => 'Illinois',
            'country' => 'USA',
            'bio' => 'Michael focuses on youth leagues and club organizations, delivering outstanding service and premium apparel layouts.',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // International Agents
        SalesAgent::create([
            'name' => 'Pierre Dupont',
            'title' => 'European Territory Representative',
            'state' => null,
            'country' => 'France',
            'bio' => 'Pierre handles European partnerships and club stores, helping teams transition to fully digital online order tracking.',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        SalesAgent::create([
            'name' => 'Liam Neeson',
            'title' => 'UK & Ireland Account Executive',
            'state' => null,
            'country' => 'United Kingdom',
            'bio' => 'Liam coordinates with football academies and sports leagues across Great Britain and Ireland to supply premium performance gear.',
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }
}
