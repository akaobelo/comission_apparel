<?php

namespace Tests\Feature;

use App\Models\ParentOrder;
use App\Models\StoreItem;
use App\Models\TeamStore;
use App\Models\User;
use App\Services\ManufacturerExcelExportService;
use App\Services\StripePaymentSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStatusExportAndStripeSyncTest extends TestCase
{
    use RefreshDatabase;

    private function createOnlineStoreWithOrders(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'KJ Speed 2027 Indoor Uniform',
            'slug'             => 'kj-speed-2027-test',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
            'is_tax_exempt'    => false,
        ]);

        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Uniform Top',
            'type'            => 'uniform_top',
            'types'           => ['uniform_top'],
            'wholesale_price' => 50.00,
            'retail_price'    => 75.00,
        ]);

        $batchId = 'batch-kj-speed-001';

        $paidOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'batch_id'           => $batchId,
            'athlete_first_name' => 'Ethan',
            'athlete_last_name'  => 'Huntley',
            'parent_email'       => 'mspujamalani@gmail.com',
            'items_json'         => [[
                'id'         => $item->id,
                'name'       => 'Uniform Top',
                'types'      => ['uniform_top'],
                'sizes'      => ['uniform_top' => 'YM'],
                'qty'        => 1,
                'unit_price' => 75.00,
            ]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'paid',
            'subtotal'           => 75.00,
            'tax_amount'         => 5.63,
            'fee_amount'         => 2.72,
            'total_paid'         => 83.35,
            'total_retail_price' => 75.00,
            'stripe_session_id'  => 'cs_test_paid_1',
        ]);

        $unpaidOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'batch_id'           => $batchId,
            'athlete_first_name' => 'Kodi',
            'athlete_last_name'  => 'Cobb',
            'parent_email'       => 'kiarahough4@gmail.com',
            'items_json'         => [[
                'id'         => $item->id,
                'name'       => 'Uniform Top',
                'types'      => ['uniform_top'],
                'sizes'      => ['uniform_top' => 'YL'],
                'qty'        => 1,
                'unit_price' => 75.00,
            ]],
            'status'             => 'Submitted to Admin',
            'payment_status'     => 'pending',
            'subtotal'           => 75.00,
            'tax_amount'         => 5.63,
            'fee_amount'         => 2.72,
            'total_paid'         => 0.00,
            'total_retail_price' => 75.00,
            'stripe_session_id'  => 'cs_test_unpaid_1',
        ]);

        return compact('admin', 'coach', 'store', 'item', 'batchId', 'paidOrder', 'unpaidOrder');
    }

    public function test_visual_excel_full_order_roster_has_payment_status_column(): void
    {
        $data = $this->createOnlineStoreWithOrders();

        $service = new ManufacturerExcelExportService();
        $xlsxPath = $service->generateXlsx(
            collect([$data['paidOrder'], $data['unpaidOrder']]),
            $data['store'],
            $data['store']->name
        );

        $this->assertFileExists($xlsxPath);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($xlsxPath) === true);

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheet2Xml = $zip->getFromName('xl/worksheets/sheet2.xml');
        $zip->close();
        @unlink($xlsxPath);

        $this->assertNotFalse($sharedStringsXml);
        $this->assertNotFalse($sheet2Xml);

        $ssDoc = simplexml_load_string($sharedStringsXml);
        $strings = [];
        foreach ($ssDoc->si as $si) {
            $strings[] = (string) $si->t;
        }

        $sheetDoc = simplexml_load_string($sheet2Xml);
        $rows = [];
        foreach ($sheetDoc->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $val = (string) $c->v;
                if ((string) $c['t'] === 's') {
                    $val = $strings[(int) $val] ?? '';
                }
                $cells[] = $val;
            }
            $rows[] = $cells;
        }

        // Row 1 must contain "Payment Status"
        $this->assertContains('Payment Status', $rows[0]);
        $statusColIdx = array_search('Payment Status', $rows[0], true);
        $this->assertNotFalse($statusColIdx);

        // Data rows must show Paid / Unpaid
        $this->assertSame('Paid', $rows[1][$statusColIdx]);
        $this->assertSame('Unpaid', $rows[2][$statusColIdx]);
    }

    public function test_batch_roster_csv_has_payment_status_on_first_row(): void
    {
        $data = $this->createOnlineStoreWithOrders();

        $response = $this->actingAs($data['admin'])
            ->get(route('admin.batch.export', $data['batchId']));

        $response->assertOk();
        $csv = $response->streamedContent();
        $lines = array_values(array_filter(array_map('trim', explode("\n", trim($csv)))));

        $headers = str_getcsv($lines[0]);
        $this->assertContains('Payment Status', $headers);
        $statusIdx = array_search('Payment Status', $headers, true);

        $row1 = str_getcsv($lines[1]);
        $row2 = str_getcsv($lines[2]);

        $this->assertSame('Paid', $row1[$statusIdx]);
        $this->assertSame('Unpaid', $row2[$statusIdx]);
    }

    public function test_admin_finalize_store_batch_excludes_unpaid_pending_orders_on_online_store(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Independence High School',
            'slug'             => 'independence-hs-test',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
        ]);

        $paidOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Paola',
            'athlete_last_name'  => 'Campos',
            'items_json'         => [['name' => 'Warm-up Top', 'qty' => 1, 'unit_price' => 55.00]],
            'status'             => 'Submitted',
            'payment_status'     => 'paid',
            'subtotal'           => 55.00,
            'total_paid'         => 61.20,
            'total_retail_price' => 55.00,
        ]);

        $abandonedOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Abandoned',
            'athlete_last_name'  => 'Duplicate',
            'items_json'         => [['name' => 'Warm-up Top', 'qty' => 1, 'unit_price' => 55.00]],
            'status'             => 'Pending Payment',
            'payment_status'     => 'pending',
            'subtotal'           => 55.00,
            'total_paid'         => 0.00,
            'total_retail_price' => 55.00,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.stores.finalize-batch', $store));

        $response->assertRedirect();

        $paidOrder->refresh();
        $abandonedOrder->refresh();

        $this->assertNotNull($paidOrder->batch_id);
        $this->assertSame('Submitted to Admin', $paidOrder->status);

        // Abandoned unpaid order must NOT be finalized or switched to Submitted to Admin
        $this->assertNull($abandonedOrder->batch_id);
        $this->assertSame('Pending Payment', $abandonedOrder->status);
        $this->assertSame('pending', $abandonedOrder->payment_status);
    }

    public function test_payment_intent_succeeded_webhook_reconciles_unpaid_order_and_preserves_batch_status(): void
    {
        $data = $this->createOnlineStoreWithOrders();
        $unpaidOrder = $data['unpaidOrder'];

        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id'              => 'pi_test_reconciled_99',
                    'amount_received' => 8335,
                    'metadata'        => [
                        'order_id' => $unpaidOrder->id,
                        'store_id' => $data['store']->id,
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('stripe.webhook'), $payload);
        $response->assertOk();

        $unpaidOrder->refresh();
        $this->assertSame('paid', $unpaidOrder->payment_status);
        $this->assertSame('Submitted to Admin', $unpaidOrder->status);
        $this->assertSame('pi_test_reconciled_99', $unpaidOrder->stripe_payment_intent_id);
        $this->assertEquals(83.35, (float) $unpaidOrder->getRawOriginal('total_paid'));
    }

    public function test_checkout_cancel_deletes_unpaid_pending_order_only(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'TrackMasterzNc',
            'slug'             => 'trackmasterz-cancel-test',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
        ]);

        $pending = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Cancel',
            'athlete_last_name'  => 'Me',
            'items_json'         => [['name' => 'Hoodie', 'qty' => 1, 'unit_price' => 55.00]],
            'status'             => 'Pending Payment',
            'payment_status'     => 'pending',
            'subtotal'           => 55.00,
            'total_paid'         => 0.00,
        ]);

        $this->get(route('store.checkout.cancel', ['slug' => $store->slug, 'order' => $pending->id]))
            ->assertRedirect(route('store.show', $store->slug));

        $this->assertDatabaseMissing('parent_orders', ['id' => $pending->id]);
    }

    public function test_check_duplicate_email_detects_existing_active_orders_and_ignores_pending_payment(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'KJ Speed Duplicate Check',
            'slug'             => 'kj-speed-dup-check',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'online',
        ]);

        $paidOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Porter',
            'athlete_last_name'  => 'McRae',
            'parent_email'       => 'AmandaBlaireMcRae@gmail.com',
            'edit_pin'           => '4321',
            'items_json'         => [['name' => 'Crewneck', 'qty' => 2, 'unit_price' => 40.00]],
            'status'             => 'Submitted',
            'payment_status'     => 'paid',
            'subtotal'           => 80.00,
            'total_paid'         => 88.82,
        ]);

        // Abandoned duplicate should NOT be returned as an existing active order
        ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Porter',
            'athlete_last_name'  => 'McRae',
            'parent_email'       => 'amandablairemcrae@gmail.com',
            'items_json'         => [['name' => 'Crewneck', 'qty' => 1, 'unit_price' => 40.00]],
            'status'             => 'Pending Payment',
            'payment_status'     => 'pending',
            'subtotal'           => 40.00,
            'total_paid'         => 0.00,
        ]);

        $response = $this->postJson(route('store.check-duplicate-email', $store->slug), [
            'email' => '  amandablairemcrae@gmail.com ',
        ]);

        $response->assertOk()
            ->assertJson([
                'exists' => true,
            ]);

        $orders = $response->json('orders');
        $this->assertCount(1, $orders);
        $this->assertSame($paidOrder->id, $orders[0]['id']);
        $this->assertSame('Porter McRae', $orders[0]['athlete_name']);
        $this->assertSame(2, $orders[0]['items_count']);
        $this->assertTrue($orders[0]['is_paid']);
        $this->assertTrue($orders[0]['has_pin']);

        // Non-matching email returns exists = false
        $this->postJson(route('store.check-duplicate-email', $store->slug), [
            'email' => 'newparent@example.com',
        ])->assertOk()->assertJson(['exists' => false, 'orders' => []]);
    }
}
