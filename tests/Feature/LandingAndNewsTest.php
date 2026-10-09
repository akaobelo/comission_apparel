<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\NewsArticle;
use App\Models\SiteSetting;
use App\Models\TeamStore;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LandingAndNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads_with_dynamic_settings_and_articles()
    {
        SiteSetting::set('hero_title', 'ELITE CUSTOM ATHLETIC APPAREL');
        NewsArticle::create([
            'title' => 'Sample Championship Story',
            'slug' => 'sample-championship-story',
            'summary' => 'A brief story excerpt.',
            'content' => '<p>Full editorial story here.</p>',
            'category' => 'Championships',
            'is_featured' => true,
            'is_active' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('ELITE CUSTOM ATHLETIC APPAREL');
        $response->assertSee('Sample Championship Story');
    }

    public function test_news_archive_and_article_detail_page()
    {
        $article = NewsArticle::create([
            'title' => 'Ocoee High Track Kit Reveal',
            'slug' => 'ocoee-high-track-kit-reveal',
            'summary' => 'New uniforms for track team.',
            'content' => '<p>Detailed article content with high performance materials.</p>',
            'category' => 'Case Study',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_active' => true,
            'published_at' => now(),
        ]);

        $archiveResponse = $this->get('/news');
        $archiveResponse->assertStatus(200);
        $archiveResponse->assertSee('Ocoee High Track Kit Reveal');

        $detailResponse = $this->get('/news/ocoee-high-track-kit-reveal');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Ocoee High Track Kit Reveal');
        $detailResponse->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ');
    }

    public function test_coach_can_have_multiple_stores()
    {
        $coach = User::factory()->create([
            'role' => 'coach',
            'status' => 'approved',
            'organization' => 'Ocoee High School',
        ]);

        $store1 = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Ocoee High Varsity Football',
            'slug' => 'ocoee-football',
            'status' => 'open',
        ]);

        $store2 = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Ocoee High Track & Field',
            'slug' => 'ocoee-track',
            'status' => 'open',
        ]);

        $this->actingAs($coach);

        $response = $this->get('/coach/dashboard?store_id=' . $store2->id);
        $response->assertStatus(200);
        $response->assertSee('Ocoee High Track & Field');
        $response->assertSee('Ocoee High Varsity Football');
    }
}
