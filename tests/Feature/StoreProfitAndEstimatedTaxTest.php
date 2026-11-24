<?php

namespace Tests\Feature;

use App\Models\ParentOrder;
use App\Models\StoreItem;
use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreProfitAndEstimatedTaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpaid_in_house_store_shows_estimated_sales_tax_on_admin_store_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Bronx Tigers',
            'slug'             => 'bronx-tigers',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'in_house',
            'is_tax_exempt'    => false,
        ]);

        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Track Kit',
            'wholesale_price' => 100.00,
            'retail_price'    => 100.00,
        ]);

        ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Sprinter',
            'parent_email'       => 'jordan@example.com',
            'gender'             => 'Male',
            'items_json'         => [['id' => $item->id, 'name' => 'Track Kit', 'qty' => 2, 'unit_price' => 100.00, 'wholesale_price' => 100.00]],
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'subtotal'           => 200.00,
            'wholesale_cost'     => 200.00,
            'tax_amount'         => 15.00,
            'total_paid'         => 0.00,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.store.edit', $store));
        $response->assertStatus(200);
        $response->assertSee('Estimated Sales Tax (7.5%)');
        $response->assertSee('$15.00');
        $response->assertDontSee('Online Payment (Stripe)');

        // Tax-exempt store shows $0.00 (501(c)(3) Waived)
        $store->update(['is_tax_exempt' => true]);
        $exemptResponse = $this->actingAs($admin)->get(route('admin.store.edit', $store));
        $exemptResponse->assertStatus(200);
        $exemptResponse->assertSee('Estimated Sales Tax (501(c)(3) Waived)');
        $exemptResponse->assertSee('$0.00');
    }

    public function test_coach_dashboard_shows_store_profit_in_sales_tab_and_batch_summary(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Mustangs Track',
            'slug'             => 'mustangs-track',
            'status'           => 'submitted_to_admin',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
            'is_tax_exempt'    => false,
        ]);

        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Singlet',
            'wholesale_price' => 40.00,
            'retail_price'    => 65.00,
        ]);

        ParentOrder::create([
            'team_store_id'      => $store->id,
            'batch_id'           => (string) Str::uuid(),
            'athlete_first_name' => 'Maya',
            'athlete_last_name'  => 'Runner',
            'parent_email'       => 'maya@example.com',
            'gender'             => 'Female',
            'items_json'         => [['id' => $item->id, 'name' => 'Singlet', 'qty' => 2, 'unit_price' => 65.00, 'wholesale_price' => 40.00]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'paid',
            'subtotal'           => 130.00,
            'wholesale_cost'     => 80.00,
            'tax_amount'         => 9.75,
            'fee_amount'         => 4.49,
            'total_paid'         => 144.24,
            'stripe_session_id'  => 'cs_test_mustangs_1',
            'submitted_at'       => now(),
            'paid_at'            => now(),
        ]);

        $response = $this->actingAs($coach)->get(route('coach.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Store Profit');
        $response->assertSee('Store Profit Owed');
        $response->assertSee('$50.00');
    }
}
