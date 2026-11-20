<?php

namespace Tests\Feature;

use App\Models\ParentOrder;
use App\Models\StoreItem;
use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlinePaymentTotalPaidAndBatchScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_webhook_records_total_paid_for_parent_order_created_with_zero(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Aurora Flyers Team Store',
            'slug'             => 'aurora-flyers-test',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
            'is_tax_exempt'    => true,
        ]);

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Aarya',
            'athlete_last_name'  => 'Devineni',
            'parent_email'       => 'parent@example.com',
            'items_json'         => [['name' => 'Jersey', 'qty' => 2, 'unit_price' => 60.00, 'line_total' => 120.00]],
            'status'             => 'Pending Payment',
            'payment_status'     => 'pending',
            'subtotal'           => 120.00,
            'tax_amount'         => 0.00,
            'fee_amount'         => 3.89,
            'total_paid'         => 0.00,
            'total_retail_price' => 120.00,
            'stripe_session_id'  => 'cs_test_aurora_1',
        ]);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id'             => 'cs_test_aurora_1',
                    'payment_intent' => 'pi_test_aurora_1',
                    'amount_total'   => 12389,
                    'metadata'       => [
                        'order_id' => $order->id,
                        'store_id' => $store->id,
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('stripe.webhook'), $payload);
        $response->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertEquals(123.89, (float) $order->getRawOriginal('total_paid'));
        $this->assertEquals(123.89, (float) $order->total_paid);
    }

    public function test_paid_order_with_zero_total_paid_resolves_to_subtotal_plus_tax_and_fee_while_deposit_stays_zero(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Aurora Flyers',
            'slug'             => 'aurora-flyers-fallback',
            'status'           => 'submitted_to_admin',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
            'is_tax_exempt'    => true,
        ]);

        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Singlet',
            'wholesale_price' => 100.00,
            'retail_price'    => 100.00,
        ]);

        // Paid online order stuck at total_paid = 0.00 (no deposit)
        $stuckOrder = ParentOrder::create([
            'team_store_id'          => $store->id,
            'athlete_first_name'     => 'Jack',
            'athlete_last_name'      => 'Rossi',
            'items_json'             => [['id' => $item->id, 'name' => 'Singlet', 'qty' => 1, 'unit_price' => 100.00]],
            'status'                 => 'Submitted to Admin',
            'payment_status'         => 'paid',
            'subtotal'               => 100.00,
            'tax_amount'             => 0.00,
            'fee_amount'             => 3.30,
            'deposit_credit_applied' => 0.00,
            'total_paid'             => 0.00,
            'total_retail_price'     => 100.00,
        ]);

        // Paid order 100% covered by deposit credit should still show 0.00
        $depositCoveredOrder = ParentOrder::create([
            'team_store_id'          => $store->id,
            'athlete_first_name'     => 'Deposit',
            'athlete_last_name'      => 'Athlete',
            'items_json'             => [['id' => $item->id, 'name' => 'Singlet', 'qty' => 1, 'unit_price' => 100.00]],
            'status'                 => 'Submitted to Admin',
            'payment_status'         => 'paid',
            'subtotal'               => 100.00,
            'tax_amount'             => 0.00,
            'fee_amount'             => 0.00,
            'deposit_credit_applied' => 100.00,
            'total_paid'             => 0.00,
            'total_retail_price'     => 100.00,
        ]);

        $this->assertEquals(103.30, (float) $stuckOrder->total_paid);
        $this->assertEquals(0.00, (float) $depositCoveredOrder->total_paid);

        $financials = ParentOrder::calculateBatchFinancials(collect([$stuckOrder]), $store);
        $this->assertEquals(100.00, $financials['total_sales']);
        $this->assertEquals(3.30, $financials['total_fees']);
        $this->assertEquals(103.30, $financials['total_online_paid']);
    }

    public function test_admin_review_store_scopes_orders_financials_and_exports_to_selected_batch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Aurora Flyers Multi-Batch',
            'slug'             => 'aurora-flyers-batches',
            'status'           => 'submitted_to_admin',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
            'is_tax_exempt'    => true,
        ]);

        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Singlet',
            'wholesale_price' => 100.00,
            'retail_price'    => 100.00,
        ]);

        // Batch 1 order
        ParentOrder::create([
            'team_store_id'      => $store->id,
            'batch_id'           => 'batch-run-1',
            'athlete_first_name' => 'First',
            'athlete_last_name'  => 'RunAthlete',
            'items_json'         => [['id' => $item->id, 'name' => 'Singlet', 'qty' => 1]],
            'status'             => 'In Production',
            'payment_status'     => 'paid',
            'subtotal'           => 100.00,
            'tax_amount'         => 0.00,
            'fee_amount'         => 3.30,
            'total_paid'         => 103.30,
            'total_retail_price' => 100.00,
            'submitted_at'       => now()->subDays(5),
        ]);

        // Batch 2 order
        ParentOrder::create([
            'team_store_id'      => $store->id,
            'batch_id'           => 'batch-run-2',
            'athlete_first_name' => 'Second',
            'athlete_last_name'  => 'RunAthlete',
            'items_json'         => [['id' => $item->id, 'name' => 'Singlet', 'qty' => 2]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'paid',
            'subtotal'           => 200.00,
            'tax_amount'         => 0.00,
            'fee_amount'         => 6.28,
            'total_paid'         => 0.00, // Also tests fallback rendering!
            'total_retail_price' => 200.00,
            'submitted_at'       => now(),
        ]);

        // Viewing Batch 2 specifically
        $response = $this->actingAs($admin)->get(route('admin.store.edit', [
            'store'    => $store,
            'batch_id' => 'batch-run-2',
        ]));

        $response->assertOk();
        $response->assertSee('Second RunAthlete');
        $response->assertDontSee('First RunAthlete');
        $response->assertSee('Batch Orders (1 of 2 athletes)');
        $response->assertSee('$206.28');
        $response->assertSee(route('admin.batch.export-manufacturer', 'batch-run-2'), false);
        $response->assertSee(route('admin.batch.export', 'batch-run-2'), false);

        // Viewing All Runs (no batch_id)
        $allResponse = $this->actingAs($admin)->get(route('admin.store.edit', ['store' => $store]));
        $allResponse->assertOk();
        $allResponse->assertSee('First RunAthlete');
        $allResponse->assertSee('Second RunAthlete');
        $allResponse->assertSee('$309.58');
    }

    public function test_online_store_with_stamped_manufacturing_cost_preserves_retail_total_paid_and_coach_profit(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Hampton Basketball',
            'slug'             => 'hampton-basketball-regression',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
            'is_tax_exempt'    => false,
        ]);

        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Basketball Jersey',
            'wholesale_price' => 50.00,
            'retail_price'    => 65.00,
        ]);

        // Parent paid retail ($130) + 7.5% tax ($9.75) + Stripe fee ($4.49) = $144.24,
        // and calculateAndApplyCommission() later stamped manufacturing_cost = $100.00.
        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Smith',
            'items_json'         => [['id' => $item->id, 'name' => 'Basketball Jersey', 'qty' => 2, 'unit_price' => 65.00]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'paid',
            'subtotal'           => 130.00,
            'total_retail_price' => 130.00,
            'manufacturing_cost' => 100.00,
            'tax_amount'         => 9.75,
            'fee_amount'         => 4.49,
            'total_paid'         => 144.24,
            'stripe_session_id'  => 'cs_test_hampton_1',
        ]);

        // Must NOT clamp parent-paid online order down to wholesale ($114.24)
        $this->assertEquals(144.24, (float) $order->total_paid);

        $financials = ParentOrder::calculateBatchFinancials(collect([$order]), $store);
        $this->assertEquals(130.00, $financials['total_sales']);
        $this->assertEquals(100.00, $financials['total_wholesale']);
        $this->assertEquals(30.00, $financials['coach_profit_owed']);
        $this->assertEquals(144.24, $financials['total_online_paid']);
    }

    public function test_in_house_coach_wholesale_master_order_still_clamps_to_wholesale_and_zero_profit(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Cash Collection Club',
            'slug'             => 'cash-collection-wholesale-check',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'in_house',
            'is_tax_exempt'    => false,
        ]);

        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Basketball Jersey',
            'wholesale_price' => 50.00,
            'retail_price'    => 65.00,
        ]);

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Taylor',
            'items_json'         => [['id' => $item->id, 'name' => 'Basketball Jersey', 'qty' => 2, 'unit_price' => 65.00]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'paid',
            'subtotal'           => 130.00,
            'total_retail_price' => 130.00,
            'manufacturing_cost' => 100.00,
            'tax_amount'         => 7.50,
            'fee_amount'         => 3.52,
            'total_paid'         => 144.24, // phantom retail stored on an in-house wholesale order
            'stripe_session_id'  => 'cs_test_inhouse_1',
        ]);

        // In-house master order paid by coach at wholesale still clamps to wholesale ($100 + $7.50 + $3.52 = $111.02)
        $this->assertEquals(111.02, (float) $order->total_paid);

        $financials = ParentOrder::calculateBatchFinancials(collect([$order]), $store);
        $this->assertEquals(100.00, $financials['total_sales']);
        $this->assertEquals(100.00, $financials['total_wholesale']);
        $this->assertEquals(0.00, $financials['coach_profit_owed']);
        $this->assertEquals(111.02, $financials['total_online_paid']);
    }

    public function test_paid_online_order_honors_higher_recorded_wholesale_and_checkout_snapshot_without_letting_lower_factory_cost_reduce_due_to_tca(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Hampton Basketball Wholesale Guard',
            'slug'             => 'hampton-wholesale-guard',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
            'is_tax_exempt'    => false,
        ]);

        // Store item wholesale_price was lowered after order placement (from $2,150 total down to $1,745)
        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Team Package',
            'wholesale_price' => 1745.00,
            'retail_price'    => 2385.00,
        ]);

        $hamptonOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Hampton',
            'athlete_last_name'  => 'Player',
            'items_json'         => [['id' => $item->id, 'name' => 'Team Package', 'qty' => 1, 'unit_price' => 2385.00]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'paid',
            'subtotal'           => 2385.00,
            'total_retail_price' => 2385.00,
            'manufacturing_cost' => 2150.00,
            'tax_amount'         => 178.88,
            'fee_amount'         => 82.12,
            'total_paid'         => 2646.00,
        ]);

        $hamptonFin = ParentOrder::calculateBatchFinancials(collect([$hamptonOrder]), $store);
        $this->assertEquals(2385.00, $hamptonFin['total_sales']);
        $this->assertEquals(2150.00, $hamptonFin['total_wholesale']);
        $this->assertEquals(235.00, $hamptonFin['coach_profit_owed']);
        $this->assertEquals(2646.00, $hamptonFin['total_online_paid']);

        // When admin enters a LOWER factory cost ($65 vs $100 wholesale), Due to TCA must stay $100
        $jersey = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Jersey',
            'wholesale_price' => 100.00,
            'retail_price'    => 130.00,
        ]);

        $factoryCostOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Factory',
            'athlete_last_name'  => 'Test',
            'items_json'         => [['id' => $jersey->id, 'name' => 'Jersey', 'qty' => 1, 'unit_price' => 130.00, 'wholesale_price' => 100.00]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'paid',
            'subtotal'           => 130.00,
            'total_retail_price' => 130.00,
            'manufacturing_cost' => 65.00,
            'tax_amount'         => 9.75,
            'fee_amount'         => 4.49,
            'total_paid'         => 144.24,
        ]);

        // Even if store item wholesale_price is later edited down to $50, checkout snapshot ($100) stays locked
        $jersey->update(['wholesale_price' => 50.00]);
        $store->load('items');

        $factoryFin = ParentOrder::calculateBatchFinancials(collect([$factoryCostOrder]), $store);
        $this->assertEquals(130.00, $factoryFin['total_sales']);
        $this->assertEquals(100.00, $factoryFin['total_wholesale']);
        $this->assertEquals(30.00, $factoryFin['coach_profit_owed']);
    }
}
