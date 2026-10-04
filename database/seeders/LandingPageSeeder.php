<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;
use App\Models\NewsArticle;
use App\Models\LandingCollection;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Site Settings for Dynamic Landing Page
        $settings = [
            'hero_title'                 => 'CUSTOM GEAR BUILT FOR THE COMMITTED',
            'hero_subtitle'              => 'Dominate the competition with elite performance apparel designed for champion athletes. Elevate your team\'s game with custom uniforms crafted with speed and precision.',
            'hero_cta_primary_text'      => 'START DESIGNING',
            'hero_cta_primary_url'       => '/quote',
            'hero_cta_secondary_text'    => 'VIEW CATALOG',
            'hero_cta_secondary_url'     => '/catalog',
            'hero_banner_image'          => '/images/hero-models.png',

            'proof_heading'              => 'From Vision to Victory: Concept to Reality',
            'proof_subheading'           => 'Precision craftsmanship from 3D digital blueprint to final sublimated uniform.',
            'proof_concept_image'        => '/images/concept-spartan.png',
            'proof_reality_image'        => '/images/reality-spartan.png',
            'proof_feature_1'            => '1. Full Custom Graphics',
            'proof_feature_2'            => '2. Premium Moisture-Wicking Fabric',
            'proof_feature_3'            => '3. Reinforced Athletic Stitching',

            'team_store_heading'         => 'LAUNCH YOUR TEAM STORE',
            'team_store_subheading'      => 'Empower your program with a custom online store that eliminates coach hassle and generates revenue.',
            'team_store_image'           => '/images/team-store-background-v2.png',
            'team_store_bullet_1'        => 'Streamlined Direct Ordering for Parents',
            'team_store_bullet_2'        => 'Custom Fan Gear & Official Team Packages',
            'team_store_bullet_3'        => 'Fast Direct-to-Door Delivery',
            'team_store_bullet_4'        => 'Centralized Coach & Athletic Director Portal',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 2. Initial News Articles
        if (NewsArticle::count() === 0) {
            NewsArticle::create([
                'title'         => "The Journey: Coach Mike's Championship Run",
                'slug'          => 'the-journey-coach-mikes-championship-run',
                'category'      => 'CHAMPIONSHIP RUN',
                'author'        => 'The Commission Editorial',
                'summary'       => 'How Coach Mike led his program to a historic state championship victory wearing bespoke Commission performance uniforms.',
                'content'       => '<p class="text-base md:text-lg leading-relaxed text-slate-200 mb-6 font-normal">When Coach Mike stepped onto the court this season, his mission was clear: instill a champion mindset into every single athlete in the program. But greatness isn\'t just about tactical execution—it\'s about the identity, pride, and armor you wear into battle.</p><h3 class="text-xl md:text-2xl font-black uppercase text-white tracking-wide mt-8 mb-4">Engineered for Peak Performance</h3><p class="text-slate-200 text-base leading-relaxed mb-6 font-normal">"Our athletes noticed the difference on day one," says Coach Mike. "The lightweight moisture-wicking fabrication kept our players cool in double overtime, and the custom sublimated graphics gave our school an unmistakable presence on the regional stage."</p><blockquote class="p-6 bg-slate-900/90 border-l-4 border-[#cd202c] rounded-r-xl my-8 font-medium text-white text-base md:text-lg italic">"When you look like champions and feel comfortable in your gear, you play with an entirely different level of swagger and confidence."</blockquote><p class="text-slate-200 text-base leading-relaxed font-normal">The Commission Apparel is proud to partner with elite programs nationwide, providing turnkey team stores, rapid turnaround times, and world-class uniform design.</p>',
                'cover_image'   => '/images/basketball.png',
                'video_url'     => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'gallery_images'=> ['/images/basketball.png', '/images/group.jpg', '/images/concept-spartan.png', '/images/reality-spartan.png'],
                'cta_text'      => 'Gear Up Your Program Like Coach Mike',
                'cta_url'       => '/quote',
                'published_at'  => now()->subDays(2),
                'is_featured'   => true,
                'is_active'     => true,
                'sort_order'    => 1,
            ]);

            NewsArticle::create([
                'title'         => 'Legacy Athletics: Modernizing High School Programs',
                'slug'          => 'legacy-athletics-modernizing-high-school-programs',
                'category'      => 'PROGRAM SPOTLIGHT',
                'author'        => 'The Commission Editorial',
                'summary'       => 'Discover how Legacy Athletics equipped over 400 student-athletes across 6 varsity sports without a single paper order form.',
                'content'       => '<p class="text-base md:text-lg leading-relaxed text-slate-200 mb-6 font-normal">Managing apparel for an entire athletic department used to be a logistical nightmare. Sizing sheets, cash envelopes, and delayed shipments plagued coaches every season.</p><h3 class="text-xl md:text-2xl font-black uppercase text-white tracking-wide mt-8 mb-4">Zero Paperwork, 100% Direct Ordering</h3><p class="text-slate-200 text-base leading-relaxed mb-6 font-normal">By launching dedicated team stores for football, basketball, track, and soccer under one school profile, parents ordered directly from their phones. The athletic director tracked production status visually and received customized team fundraising kickbacks automatically.</p>',
                'cover_image'   => '/images/group.jpg',
                'gallery_images'=> ['/images/group.jpg', '/images/hero-models.png'],
                'cta_text'      => 'Launch Your School Team Store',
                'cta_url'       => '/store/search',
                'published_at'  => now()->subDays(5),
                'is_featured'   => false,
                'is_active'     => true,
                'sort_order'    => 2,
            ]);

            NewsArticle::create([
                'title'         => 'Justin Gatlin Signature Track & Field Collection Revealed',
                'slug'          => 'justin-gatlin-signature-track-collection',
                'category'      => 'UNIFORM REVEAL',
                'author'        => 'The Commission Editorial',
                'summary'       => 'Olympic gold medalist Justin Gatlin partners with The Commission to introduce ultra-aerodynamic singlets and speed suits for youth track clubs.',
                'content'       => '<p class="text-base md:text-lg leading-relaxed text-slate-200 mb-6 font-normal">Speed requires precision. Designed in collaboration with Olympic champion Justin Gatlin and Speed Capital, this new collection brings elite aerodynamic fabrics and laser-cut ventilation to competitive youth track clubs worldwide.</p>',
                'cover_image'   => '/images/gatlin.png',
                'gallery_images'=> ['/images/gatlin.png', '/images/soccer-model.png'],
                'cta_text'      => 'View Track & Field Designs',
                'cta_url'       => '/catalog',
                'published_at'  => now()->subDays(8),
                'is_featured'   => false,
                'is_active'     => true,
                'sort_order'    => 3,
            ]);
        }

        // 3. Ensure Sports Category Tiles in LandingCollections
        if (LandingCollection::count() === 0) {
            $sports = [
                ['tab_name' => 'Track & Field', 'title' => 'Speed Capital Elite Singlets & Speed Suits', 'description' => 'Ultra-aerodynamic fabrics crafted for sprinters and field athletes.', 'image_path' => '/images/gatlin.png', 'sort_order' => 1],
                ['tab_name' => 'Basketball', 'title' => 'Varsity Pro Reversible Kits & Warmups', 'description' => 'Lightweight moisture-wicking mesh engineered for high-flying agility.', 'image_path' => '/images/basketball.png', 'sort_order' => 2],
                ['tab_name' => 'Football', 'title' => 'Tackle & 7v7 High-Impact Jerseys', 'description' => 'Heavy-duty 4-way stretch compression with reinforced collar & shoulders.', 'image_path' => '/images/hero-models.png', 'sort_order' => 3],
                ['tab_name' => 'Soccer', 'title' => 'Championship Match Kits & Training Tops', 'description' => 'Breathable technical poly with custom sublimated badge integration.', 'image_path' => '/images/soccer-model.png', 'sort_order' => 4],
            ];

            foreach ($sports as $sport) {
                LandingCollection::create($sport);
            }
        }
    }
}
