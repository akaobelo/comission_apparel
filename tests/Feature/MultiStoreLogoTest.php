<?php

namespace Tests\Feature;

use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiStoreLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_update_logo_for_one_store_without_affecting_other_stores()
    {
        Storage::fake('public');

        $coach = User::factory()->create([
            'role' => 'coach',
            'organization' => 'Apex High',
            'logo_path' => 'organization_logos/coach_default.png',
        ]);

        $storeA = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Apex Track & Field',
            'sport' => 'Track & Field',
            'slug' => 'apex-track-field',
            'status' => 'approved',
            'logo_path' => 'organization_logos/coach_default.png',
        ]);

        $storeB = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Apex Basketball',
            'sport' => 'Basketball',
            'slug' => 'apex-basketball',
            'status' => 'approved',
            'logo_path' => 'organization_logos/coach_default.png',
        ]);

        // Upload a new logo for Store A
        $newLogo = UploadedFile::fake()->image('track_logo.png', 200, 200);

        $response = $this->actingAs($coach)->post(route('coach.store.logo', $storeA), [
            'logo' => $newLogo,
        ]);

        $response->assertRedirect();
        $storeA->refresh();
        $storeB->refresh();
        $coach->refresh();

        // Store A should have the newly uploaded logo
        $this->assertNotNull($storeA->logo_path);
        $this->assertNotEquals('organization_logos/coach_default.png', $storeA->logo_path);

        // Store B should STILL have the original logo!
        $this->assertEquals('organization_logos/coach_default.png', $storeB->logo_path);

        // Coach's user logo_path should remain unchanged
        $this->assertEquals('organization_logos/coach_default.png', $coach->logo_path);
    }

    public function test_admin_can_update_logo_for_one_store_without_affecting_other_stores()
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $coach = User::factory()->create([
            'role' => 'coach',
            'organization' => 'Summit Prep',
            'logo_path' => 'organization_logos/summit_default.png',
        ]);

        $store1 = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Summit Football',
            'sport' => 'Football',
            'slug' => 'summit-football',
            'status' => 'approved',
            'logo_path' => 'organization_logos/summit_default.png',
        ]);

        $store2 = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Summit Volleyball',
            'sport' => 'Volleyball',
            'slug' => 'summit-volleyball',
            'status' => 'approved',
            'logo_path' => 'organization_logos/summit_default.png',
        ]);

        $newLogo = UploadedFile::fake()->image('football_logo.png', 200, 200);

        $response = $this->actingAs($admin)->post(route('admin.store.logo', $store1), [
            'logo' => $newLogo,
        ]);

        $response->assertRedirect();
        $store1->refresh();
        $store2->refresh();
        $coach->refresh();

        // Store 1 should have updated logo
        $this->assertNotEquals('organization_logos/summit_default.png', $store1->logo_path);

        // Store 2 should NOT be updated
        $this->assertEquals('organization_logos/summit_default.png', $store2->logo_path);

        // Coach user logo should NOT be overwritten
        $this->assertEquals('organization_logos/summit_default.png', $coach->logo_path);
    }

    public function test_storefront_displays_store_specific_logo()
    {
        $coach = User::factory()->create([
            'role' => 'coach',
            'organization' => 'Metro Sports',
            'logo_path' => 'organization_logos/coach_general.png',
        ]);

        $storeA = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Metro Soccer',
            'sport' => 'Soccer',
            'slug' => 'metro-soccer',
            'status' => 'approved',
            'pricing_approved' => true,
            'payment_mode' => 'online',
            'logo_path' => 'organization_logos/custom_soccer_logo.png',
        ]);

        $response = $this->get(route('store.show', $storeA->slug));
        $response->assertStatus(200);
        $response->assertSee('custom_soccer_logo.png');
        $response->assertDontSee('coach_general.png');
    }

    public function test_new_store_inherits_user_logo_by_default()
    {
        $coach = User::factory()->create([
            'role' => 'coach',
            'organization' => 'Oak Ridge',
            'logo_path' => 'organization_logos/oak_ridge.png',
        ]);

        $response = $this->actingAs($coach)->post(route('coach.store.create'), [
            'name' => 'Oak Ridge Baseball',
            'sport' => 'Baseball',
        ]);

        $response->assertRedirect();

        $store = TeamStore::where('name', 'Oak Ridge Baseball')->first();
        $this->assertNotNull($store);
        $this->assertEquals('organization_logos/oak_ridge.png', $store->logo_path);
    }
}
