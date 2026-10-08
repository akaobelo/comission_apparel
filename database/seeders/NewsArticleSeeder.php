<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsArticle;

class NewsArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title'         => "The Journey: Coach Mike's Championship Run",
                'slug'          => 'the-journey-coach-mikes-championship-run',
                'category'      => 'CHAMPIONSHIP RUN',
                'author'        => 'The Commission Editorial',
                'summary'       => 'How Coach Mike led his program to a historic state championship victory wearing bespoke Commission performance uniforms.',
                'content'       => '<p class="text-base md:text-lg leading-relaxed text-slate-700 dark:text-slate-200 mb-6 font-normal">When Coach Mike stepped onto the court this season, his mission was clear: instill a champion mindset into every single athlete in the program. But greatness isn\'t just about tactical execution—it\'s about the identity, pride, and armor you wear into battle.</p><h3 class="text-xl md:text-2xl font-black uppercase text-slate-900 dark:text-white tracking-wide mt-8 mb-4">Engineered for Peak Performance</h3><p class="text-slate-700 dark:text-slate-200 text-base leading-relaxed mb-6 font-normal">"Our athletes noticed the difference on day one," says Coach Mike. "The lightweight moisture-wicking fabrication kept our players cool in double overtime, and the custom sublimated graphics gave our school an unmistakable presence on the regional stage."</p><blockquote class="p-6 bg-slate-900/90 border-l-4 border-[#cd202c] rounded-r-xl my-8 font-medium text-white text-base md:text-lg italic">"When you look like champions and feel comfortable in your gear, you play with an entirely different level of swagger and confidence."</blockquote><p class="text-slate-700 dark:text-slate-200 text-base leading-relaxed font-normal">The Commission Apparel is proud to partner with elite programs nationwide, providing turnkey team stores, rapid turnaround times, and world-class uniform design.</p>',
                'cover_image'   => '/images/showcase/item_46_2026-09-05_23-49-42_3979582608923175071_48532943110_1.jpg',
                'video_url'     => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'gallery_images'=> [
                    '/images/showcase/item_46_2026-09-05_23-49-42_3979582608923175071_48532943110_1.jpg',
                    '/images/showcase/item_48_2026-09-05_23-49-42_3979582616506596974_48532943110_3.jpg',
                    '/images/showcase/item_50_2026-09-05_23-49-42_3979582622110191924_48532943110_5.jpg',
                    '/images/showcase/item_03_2026-07-10_02-06-52_3937614417536018151_48532943110_1.jpg'
                ],
                'cta_text'      => 'Gear Up Your Program Like Coach Mike',
                'cta_url'       => '/quote',
                'published_at'  => now()->subDays(2),
                'is_featured'   => true,
                'is_active'     => true,
                'sort_order'    => 1,
            ],
            [
                'title'         => 'Legacy Athletics: Modernizing High School Programs',
                'slug'          => 'legacy-athletics-modernizing-high-school-programs',
                'category'      => 'PROGRAM SPOTLIGHT',
                'author'        => 'The Commission Editorial',
                'summary'       => 'Discover how Legacy Athletics equipped over 400 student-athletes across 6 varsity sports without a single paper order form.',
                'content'       => '<p class="text-base md:text-lg leading-relaxed text-slate-700 dark:text-slate-200 mb-6 font-normal">Managing apparel for an entire athletic department used to be a logistical nightmare. Sizing sheets, cash envelopes, and delayed shipments plagued coaches every season.</p><h3 class="text-xl md:text-2xl font-black uppercase text-slate-900 dark:text-white tracking-wide mt-8 mb-4">Zero Paperwork, 100% Direct Ordering</h3><p class="text-slate-700 dark:text-slate-200 text-base leading-relaxed mb-6 font-normal">By launching dedicated team stores for football, basketball, track, and soccer under one school profile, parents ordered directly from their phones. The athletic director tracked production status visually and received customized team fundraising kickbacks automatically.</p><p class="text-slate-700 dark:text-slate-200 text-base leading-relaxed font-normal">"We will never go back to paper order forms again," noted Legacy Athletics Director. "Commission Apparel turned uniform distribution day from a 6-hour headache into a 15-minute celebration."</p>',
                'cover_image'   => '/images/showcase/item_68_2026-09-29_01-11-27_3996294216890271917_48532943110_1.jpg',
                'video_url'     => null,
                'gallery_images'=> [
                    '/images/showcase/item_68_2026-09-29_01-11-27_3996294216890271917_48532943110_1.jpg',
                    '/images/showcase/item_69_2026-09-29_01-11-27_3996294224465349872_48532943110_2.jpg',
                    '/images/showcase/item_70_2026-09-29_01-11-27_3996294227510288179_48532943110_3.jpg',
                    '/images/showcase/item_29_2026-08-16_00-24-59_3964380543221602811_48532943110_1.jpg'
                ],
                'cta_text'      => 'Launch Your School Team Store',
                'cta_url'       => '/store/search',
                'published_at'  => now()->subDays(5),
                'is_featured'   => false,
                'is_active'     => true,
                'sort_order'    => 2,
            ],
            [
                'title'         => 'Justin Gatlin Signature Track & Field Collection Revealed',
                'slug'          => 'justin-gatlin-signature-track-collection',
                'category'      => 'UNIFORM REVEAL',
                'author'        => 'The Commission Editorial',
                'summary'       => 'Olympic gold medalist Justin Gatlin partners with The Commission to introduce ultra-aerodynamic singlets and speed suits for youth track clubs.',
                'content'       => '<p class="text-base md:text-lg leading-relaxed text-slate-700 dark:text-slate-200 mb-6 font-normal">Speed requires precision. Designed in collaboration with Olympic champion Justin Gatlin and Speed Capital, this new collection brings elite aerodynamic fabrics and laser-cut ventilation to competitive youth track clubs worldwide.</p><h3 class="text-xl md:text-2xl font-black uppercase text-slate-900 dark:text-white tracking-wide mt-8 mb-4">Aerodynamic Technology</h3><p class="text-slate-700 dark:text-slate-200 text-base leading-relaxed mb-6 font-normal">Every seam is heat-bonded with ultra-low friction thread to eliminate drag at maximum acceleration. Tested at national invitational meets, athletes wearing the collection recorded personal records in sprint and hurdle disciplines.</p>',
                'cover_image'   => '/images/speedcapital/sc_66_speedcapital_2026-09-29_07-25-31_3996475974495891108_5520807274_1.jpg',
                'video_url'     => null,
                'gallery_images'=> [
                    '/images/speedcapital/sc_66_speedcapital_2026-09-29_07-25-31_3996475974495891108_5520807274_1.jpg',
                    '/images/speedcapital/sc_68_speedcapital_2026-09-29_07-25-31_3996475986315574486_5520807274_4.jpg',
                    '/images/speedcapital/sc_67_speedcapital_2026-09-29_07-25-31_3996475980913209287_5520807274_2.jpg',
                    '/images/speedcapital/sc_71_speedcapital_2026-09-29_07-25-31_3996476005122823996_5520807274_7.jpg'
                ],
                'cta_text'      => 'View Track & Field Designs',
                'cta_url'       => '/catalog',
                'published_at'  => now()->subDays(8),
                'is_featured'   => false,
                'is_active'     => true,
                'sort_order'    => 3,
            ],
            [
                'title'         => 'The Science of Sublimation: Why Modern Teams Ditch Screenprint',
                'slug'          => 'science-of-sublimation-modern-teams',
                'category'      => 'CRAFTSMANSHIP',
                'author'        => 'The Commission Technical Lab',
                'summary'       => 'Why heavy screen printed numbers crack and peel in the wash, and how molecular dye-sublimation preserves athletic breathability forever.',
                'content'       => '<p class="text-base md:text-lg leading-relaxed text-slate-700 dark:text-slate-200 mb-6 font-normal">Traditional screen printing lays thick rubberized ink on top of fabric, sealing the pores and causing heavy sweat build-up that cracks after several washes. Dye-sublimation changes everything: heat gasification infuses vibrant pigments directly into the polyester fibers.</p><p class="text-slate-700 dark:text-slate-200 text-base leading-relaxed font-normal">The result is featherweight, completely breathable game gear with colors that remain vibrant through hundreds of high-heat wash cycles.</p>',
                'cover_image'   => '/images/reality-spartan.png',
                'video_url'     => null,
                'gallery_images'=> [
                    '/images/concept-spartan.png',
                    '/images/reality-spartan.png'
                ],
                'cta_text'      => 'Explore Custom Dye-Sublimation',
                'cta_url'       => '/how-it-works',
                'published_at'  => now()->subDays(12),
                'is_featured'   => false,
                'is_active'     => true,
                'sort_order'    => 4,
            ]
        ];

        foreach ($articles as $data) {
            NewsArticle::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
