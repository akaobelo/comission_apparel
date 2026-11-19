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
}
