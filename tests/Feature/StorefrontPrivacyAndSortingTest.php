<?php

namespace Tests\Feature;

use App\Models\ParentOrder;
use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontPrivacyAndSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_create_store_with_custom_sport_selection()
    {
        $coach = User::factory()->create([
            'role' => 'coach',
            'sport' => 'Track & Field',
            'organization' => 'Meridian Christian Academy',
        ]);

        $response = $this->actingAs($coach)->post(route('coach.store.create'), [
            'name' => 'Meridian Cheer Store',
            'sport' => 'Cheer',
            'description' => 'Cheer team gear',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('team_stores', [
            'user_id' => $coach->id,
            'name' => 'Meridian Cheer Store',
            'sport' => 'Cheer',
            'description' => 'Cheer team gear',
        ]);
    }

    public function test_storefront_search_and_receipt_hide_coach_name_for_privacy()
    {
        $coach = User::factory()->create([
            'first_name' => 'CoachSecretFirst',
            'last_name' => 'CoachSecretLast',
            'role' => 'coach',
            'sport' => 'Track & Field',
            'organization' => 'Private High School',
        ]);

        $store = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Spartans Cheer 2026',
            'sport' => 'Cheer',
            'slug' => 'spartans-cheer-2026',
            'status' => 'approved',
            'pricing_approved' => true,
            'payment_mode' => 'online',
        ]);

        // 1. Storefront show page
        $showResponse = $this->get(route('store.show', $store->slug));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Spartans Cheer 2026');
        $showResponse->assertSee('Cheer');
        $showResponse->assertDontSee('CoachSecretFirst');
        $showResponse->assertDontSee('CoachSecretLast');

        // 2. Search page
        $searchResponse = $this->get(route('store.search', ['q' => 'Spartans']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Spartans Cheer 2026');
        $searchResponse->assertSee('Cheer');
        $searchResponse->assertDontSee('CoachSecretFirst');
        $searchResponse->assertDontSee('CoachSecretLast');
        $searchResponse->assertDontSee('Private High School');

        // 3. Receipt page
        $order = ParentOrder::create([
            'team_store_id' => $store->id,
            'user_id' => $coach->id,
            'order_number' => 'TEST-PRIVACY-001',
            'athlete_first_name' => 'Jane',
            'athlete_last_name' => 'Doe',
            'parent_email' => 'parent@example.com',
            'edit_pin' => '9988',
            'items_json' => [
                ['name' => 'Warmup Hoodie', 'qty' => 1, 'sizes' => ['Adult' => 'M'], 'price' => 50]
            ],
            'payment_status' => 'paid',
            'status' => 'Submitted to Admin',
        ]);

        $receiptResponse = $this->get(route('store.order.receipt', ['slug' => $store->slug, 'order' => $order->id]));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('Spartans Cheer 2026');
        $receiptResponse->assertDontSee('CoachSecretFirst');
        $receiptResponse->assertDontSee('CoachSecretLast');
    }

    public function test_admin_finalized_direct_orders_sort_by_latest_finalized_date()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);

        $batchOldId = (string) Str::uuid();
        $batchNewId = (string) Str::uuid();

        // Older batch created 5 days ago, finalized 4 days ago
        $oldOrder = ParentOrder::create([
            'user_id' => $coach->id,
            'batch_id' => $batchOldId,
            'order_number' => 'DIR-OLD-01',
            'athlete_first_name' => 'Alice',
            'athlete_last_name' => 'Smith',
            'status' => 'Paid',
            'payment_status' => 'paid',
            'items_json' => [['name' => 'Track Jacket', 'qty' => 1, 'price' => 45]],
            'paid_at' => now()->subDays(4),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(4),
        ]);

        // Newer batch: created 10 days ago as draft, but finalized and paid today!
        $newOrder = ParentOrder::create([
            'user_id' => $coach->id,
            'batch_id' => $batchNewId,
            'order_number' => 'DIR-NEW-01',
            'athlete_first_name' => 'Bob',
            'athlete_last_name' => 'Jones',
            'status' => 'Paid',
            'payment_status' => 'paid',
            'items_json' => [['name' => 'Cheer Bow', 'qty' => 1, 'price' => 20]],
            'paid_at' => now(),
            'created_at' => now()->subDays(10),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Check view data ordering
        $finalizedDirectOrderBatches = $response->viewData('finalizedDirectOrderBatches');
        $this->assertNotEmpty($finalizedDirectOrderBatches);
        
        $batchKeys = $finalizedDirectOrderBatches->keys()->toArray();
        $this->assertEquals($batchNewId, $batchKeys[0], 'The newly paid batch must appear first at the top');
    }
}
