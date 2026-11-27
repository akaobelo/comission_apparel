<?php

namespace Tests\Feature;

use App\Models\DesignCatalog;
use App\Models\ParentOrder;
use App\Models\StoreItem;
use App\Models\TeamStore;
use App\Models\User;
use App\Services\ManufacturerExcelExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiBatchSelectionAndPersonalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function parseSheet2Rows(string $xlsxPath): array
    {
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($xlsxPath) === true);
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheet2Xml = $zip->getFromName('xl/worksheets/sheet2.xml');
        $zip->close();

        $this->assertNotFalse($sharedXml);
        $this->assertNotFalse($sheet2Xml);

        $sharedStrings = [];
        $sx = simplexml_load_string($sharedXml);
        foreach ($sx->si as $si) {
            $sharedStrings[] = (string) $si->t;
        }

        $s2 = simplexml_load_string($sheet2Xml);
        $rows = [];
        foreach ($s2->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $col = preg_replace('/[0-9]+/', '', $ref);
                $type = (string) $c['t'];
                $val = (string) $c->v;
                if ($type === 's' && $val !== '') {
                    $cells[$col] = $sharedStrings[(int) $val] ?? '';
                } else {
                    $cells[$col] = $val;
                }
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    public function test_multi_batch_checkbox_selection_filters_view_exports_and_updates_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rep = User::factory()->create(['role' => 'sales_rep']);
        $coach = User::factory()->create([
            'role'         => 'coach',
            'organization' => 'KJ Speed',
            'sales_rep_id' => $rep->id,
        ]);

        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'sales_rep_id'     => $rep->id,
            'name'             => 'KJ Speed 2027 Indoor Uniform',
            'slug'             => 'kj-speed-2027-indoor',
            'status'           => 'submitted_to_admin',
            'pricing_approved' => true,
        ]);

        $topDesign = DesignCatalog::create([
            'name'             => 'KJ Speed Singlet',
            'type'             => 'uniform_top',
            'types'            => ['uniform_top'],
            'has_name_field'   => true,
            'has_number_field' => true,
            'wholesale_price'  => 25.00,
        ]);

        $topItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $topDesign->id,
            'name'              => 'KJ Speed Singlet',
            'type'              => 'uniform_top',
            'types'             => ['uniform_top'],
            'retail_price'      => 50.00,
        ]);

        $itemPayload = [[
            'id'     => $topItem->id,
            'name'   => $topItem->name,
            'types'  => ['uniform_top'],
            'sizes'  => ['uniform_top' => 'M'],
            'qty'    => 1,
            'gender' => 'Mens / Boys',
        ]];

        $o1 = ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'sales_rep_id'       => $rep->id,
            'batch_id'           => 'batch-run-1',
            'athlete_first_name' => 'Alpha',
            'athlete_last_name'  => 'Runner',
            'items_json'         => $itemPayload,
            'status'             => 'Submitted to Admin',
            'submitted_at'       => now()->subDays(3),
        ]);

        $o2 = ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'sales_rep_id'       => $rep->id,
            'batch_id'           => 'batch-run-2',
            'athlete_first_name' => 'Bravo',
            'athlete_last_name'  => 'Sprinter',
            'items_json'         => $itemPayload,
            'status'             => 'Submitted to Admin',
            'submitted_at'       => now()->subDays(2),
        ]);

        $o3 = ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'sales_rep_id'       => $rep->id,
            'batch_id'           => 'batch-run-3',
            'athlete_first_name' => 'Charlie',
            'athlete_last_name'  => 'Jumper',
            'items_json'         => $itemPayload,
            'status'             => 'Submitted to Admin',
            'submitted_at'       => now()->subDay(),
        ]);

        ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'sales_rep_id'       => $rep->id,
            'batch_id'           => null,
            'athlete_first_name' => 'Delta',
            'athlete_last_name'  => 'Unbatched',
            'items_json'         => $itemPayload,
            'status'             => 'Open',
        ]);

        // 1. Rep/Admin viewing store with 2 checked batches sees only those 2 batches' athletes
        $viewResp = $this->actingAs($rep)->get(route('admin.store.edit', [
            'store'     => $store,
            'batch_ids' => ['batch-run-1', 'batch-run-2'],
        ]));
        $viewResp->assertOk();
        $viewResp->assertSee('Selected Batches (2 runs');
        $viewResp->assertSee('Alpha Runner');
        $viewResp->assertSee('Bravo Sprinter');
        $viewResp->assertDontSee('Charlie Jumper');
        $viewResp->assertDontSee('Delta Unbatched');
        $viewResp->assertSee('Selected Visual Excel (2)');

        // 2. Combined Manufacturer Visual Excel contains only batch-run-1 & batch-run-2
        $xlsxResp = $this->actingAs($rep)->get(route('admin.stores.export-manufacturer', [
            'store'     => $store,
            'batch_ids' => ['batch-run-1', 'batch-run-2'],
        ]));
        $xlsxResp->assertOk();
        $tmpFile = $xlsxResp->getFile()->getPathname();
        $rows = $this->parseSheet2Rows($tmpFile);
        $firstNames = array_column(array_slice($rows, 1), 'C');
        $this->assertContains('Alpha', $firstNames);
        $this->assertContains('Bravo', $firstNames);
        $this->assertNotContains('Charlie', $firstNames);
        $this->assertNotContains('Delta', $firstNames);

        // 3. Combined Roster CSV contains only batch-run-1 & batch-run-2
        $csvResp = $this->actingAs($rep)->get(route('admin.stores.export', [
            'store'     => $store,
            'batch_ids' => ['batch-run-1', 'batch-run-2'],
        ]));
        $csvResp->assertOk();
        $csvContent = $csvResp->streamedContent();
        $this->assertStringContainsString('Alpha', $csvContent);
        $this->assertStringContainsString('Bravo', $csvContent);
        $this->assertStringNotContainsString('Charlie', $csvContent);
        $this->assertStringNotContainsString('Delta', $csvContent);

        // 4. Combined status update updates only selected batches
        $statusResp = $this->actingAs($rep)->post(route('admin.stores.batches.status.update', $store), [
            'batch_ids' => ['batch-run-1', 'batch-run-2'],
            'status'    => 'In Production',
        ]);
        $statusResp->assertRedirect();
        $this->assertSame('In Production', $o1->fresh()->status);
        $this->assertSame('In Production', $o2->fresh()->status);
        $this->assertSame('Submitted to Admin', $o3->fresh()->status);
    }

    public function test_manufacturer_excel_only_shows_name_and_jersey_number_on_items_that_require_them()
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Aurora Flyers Team Store',
            'slug'             => 'aurora-flyers-store',
            'status'           => 'submitted_to_admin',
            'pricing_approved' => true,
        ]);

        $singletDesign = DesignCatalog::create([
            'name'             => 'Flyers Singlet',
            'type'             => 'uniform_top',
            'types'            => ['uniform_top'],
            'has_name_field'   => true,
            'has_number_field' => true,
            'wholesale_price'  => 20.00,
        ]);

        $shortsDesign = DesignCatalog::create([
            'name'             => 'Flyers Shorts',
            'type'             => 'shorts',
            'types'            => ['shorts'],
            'has_name_field'   => false,
            'has_number_field' => false,
            'wholesale_price'  => 15.00,
        ]);

        $hoodieDesign = DesignCatalog::create([
            'name'             => 'Flyers Hoodie',
            'type'             => 'hoodie',
            'types'            => ['hoodie'],
            'has_name_field'   => true,
            'has_number_field' => false,
            'wholesale_price'  => 30.00,
        ]);

        $backpackDesign = DesignCatalog::create([
            'name'                    => 'Flyers Backpack',
            'type'                    => 'backpack',
            'types'                   => ['backpack'],
            'has_name_field'          => false,
            'has_number_field'        => false,
            'has_backpack_name_field' => true,
            'wholesale_price'         => 25.00,
        ]);

        $singletItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $singletDesign->id,
            'name'              => 'Flyers Singlet',
            'type'              => 'uniform_top',
            'types'             => ['uniform_top'],
            'retail_price'      => 40.00,
        ]);

        $shortsItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $shortsDesign->id,
            'name'              => 'Flyers Shorts',
            'type'              => 'shorts',
            'types'             => ['shorts'],
            'retail_price'      => 30.00,
        ]);

        $hoodieItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $hoodieDesign->id,
            'name'              => 'Flyers Hoodie',
            'type'              => 'hoodie',
            'types'             => ['hoodie'],
            'retail_price'      => 55.00,
        ]);

        $backpackItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $backpackDesign->id,
            'name'              => 'Flyers Backpack',
            'type'              => 'backpack',
            'types'             => ['backpack'],
            'retail_price'      => 50.00,
        ]);

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'batch_id'           => 'batch-aurora-1',
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Smith',
            'jersey_name'        => 'SMITH',
            'jersey_number'      => '23',
            'backpack_name'      => 'J. SMITH',
            'status'             => 'In Production',
            'items_json'         => [
                [
                    'id'     => $singletItem->id,
                    'name'   => 'Flyers Singlet',
                    'types'  => ['uniform_top'],
                    'sizes'  => ['uniform_top' => 'L'],
                    'qty'    => 1,
                    'gender' => 'Mens / Boys',
                ],
                [
                    'id'     => $shortsItem->id,
                    'name'   => 'Flyers Shorts',
                    'types'  => ['shorts'],
                    'sizes'  => ['shorts' => 'L'],
                    'qty'    => 1,
                    'gender' => 'Mens / Boys',
                ],
                [
                    'id'     => $hoodieItem->id,
                    'name'   => 'Flyers Hoodie',
                    'types'  => ['hoodie'],
                    'sizes'  => ['hoodie' => 'XL'],
                    'qty'    => 1,
                    'gender' => 'Mens / Boys',
                ],
                [
                    'id'     => $backpackItem->id,
                    'name'   => 'Flyers Backpack',
                    'types'  => ['backpack'],
                    'sizes'  => [],
                    'qty'    => 1,
                    'gender' => 'Mens / Boys',
                ],
            ],
        ]);

        $service = app(ManufacturerExcelExportService::class);
        $xlsxPath = $service->generateXlsx(collect([$order]), $store, 'Aurora Flyers Test');

        $rows = $this->parseSheet2Rows($xlsxPath);
        @unlink($xlsxPath);

        // Columns in Sheet 2: F=Jersey Name, G=Jersey Number, H=Backpack Name, I=Item Name
        $byItem = [];
        foreach (array_slice($rows, 1) as $row) {
            $byItem[$row['I'] ?? ''] = $row;
        }

        // Singlet requires Name + Number (not Backpack Name)
        $this->assertSame('SMITH', $byItem['Flyers Singlet']['F'] ?? '');
        $this->assertSame('23', $byItem['Flyers Singlet']['G'] ?? '');
        $this->assertSame('', $byItem['Flyers Singlet']['H'] ?? '');

        // Shorts require neither Name nor Number nor Backpack Name
        $this->assertSame('', $byItem['Flyers Shorts']['F'] ?? '');
        $this->assertSame('', $byItem['Flyers Shorts']['G'] ?? '');
        $this->assertSame('', $byItem['Flyers Shorts']['H'] ?? '');

        // Hoodie requires Name only (not Number, not Backpack Name)
        $this->assertSame('SMITH', $byItem['Flyers Hoodie']['F'] ?? '');
        $this->assertSame('', $byItem['Flyers Hoodie']['G'] ?? '');
        $this->assertSame('', $byItem['Flyers Hoodie']['H'] ?? '');

        // Backpack requires Backpack Name only (not Jersey Name or Number)
        $this->assertSame('', $byItem['Flyers Backpack']['F'] ?? '');
        $this->assertSame('', $byItem['Flyers Backpack']['G'] ?? '');
        $this->assertSame('J. SMITH', $byItem['Flyers Backpack']['H'] ?? '');
    }

    public function test_ready_to_order_section_only_collects_core_athlete_fields_and_personalization_is_per_item()
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Clean Ready To Order Store',
            'slug'             => 'clean-ready-to-order-store',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'in_house',
        ]);

        $singletDesign = DesignCatalog::create([
            'name'             => 'Pro Singlet',
            'type'             => 'uniform_top',
            'types'            => ['uniform_top'],
            'has_name_field'   => true,
            'has_number_field' => true,
            'wholesale_price'  => 20.00,
        ]);

        $shortsDesign = DesignCatalog::create([
            'name'             => 'Plain Shorts',
            'type'             => 'shorts',
            'types'            => ['shorts'],
            'has_name_field'   => false,
            'has_number_field' => false,
            'wholesale_price'  => 15.00,
        ]);

        $singletItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $singletDesign->id,
            'name'              => 'Pro Singlet',
            'type'              => 'uniform_top',
            'types'             => ['uniform_top'],
            'retail_price'      => 40.00,
        ]);

        $shortsItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $shortsDesign->id,
            'name'              => 'Plain Shorts',
            'type'              => 'shorts',
            'types'             => ['shorts'],
            'retail_price'      => 30.00,
        ]);

        $resp = $this->get(route('store.show', $store->slug));
        $resp->assertOk();

        // Ready to Order section has the 5 core fields only (no global jersey/backpack/shipping inputs)
        $resp->assertSee('name="athlete_first_name"', false);
        $resp->assertSee('name="athlete_last_name"', false);
        $resp->assertSee('name="parent_email"', false);
        $resp->assertSee('name="parent_phone"', false);
        $resp->assertSee('name="edit_pin"', false);
        $resp->assertDontSee('name="jersey_name"', false);
        $resp->assertDontSee('name="jersey_number"', false);
        $resp->assertDontSee('name="backpack_name"', false);
        $resp->assertDontSee('name="shipping_address"', false);

        // Per-item personalization inputs exist ONLY for Pro Singlet, NOT for Plain Shorts
        $resp->assertSee("name=\"items[{$singletItem->id}][jersey_name]\"", false);
        $resp->assertSee("name=\"items[{$singletItem->id}][jersey_number]\"", false);
        $resp->assertDontSee("name=\"items[{$shortsItem->id}][jersey_name]\"", false);
        $resp->assertDontSee("name=\"items[{$shortsItem->id}][jersey_number]\"", false);

        // Submitting order with per-item personalization persists it and scopes Excel output
        $submit = $this->post(route('store.order.submit', $store->slug), [
            'athlete_first_name' => 'Maya',
            'athlete_last_name'  => 'Lin',
            'parent_email'       => 'maya@example.com',
            'parent_phone'       => '555-222-3333',
            'edit_pin'           => '4321',
            'items'              => [
                $singletItem->id => [
                    'selected'      => '1',
                    'qty'           => 1,
                    'jersey_name'   => 'LIN',
                    'jersey_number' => '7',
                    'units'         => [
                        0 => ['gender' => 'Womens / Girls', 'sizes' => ['uniform_top' => 'AS']],
                    ],
                ],
                $shortsItem->id => [
                    'selected' => '1',
                    'qty'      => 1,
                    'units'    => [
                        0 => ['gender' => 'Womens / Girls', 'sizes' => ['shorts' => 'AS']],
                    ],
                ],
            ],
        ]);
        $submit->assertSessionHasNoErrors();

        $order = ParentOrder::where('athlete_first_name', 'Maya')->firstOrFail();
        $this->assertSame('LIN', $order->jersey_name);
        $this->assertSame('7', $order->jersey_number);

        $service = app(ManufacturerExcelExportService::class);
        $xlsxPath = $service->generateXlsx(collect([$order]), $store, 'Clean Test');
        $rows = $this->parseSheet2Rows($xlsxPath);
        @unlink($xlsxPath);

        $byItem = [];
        foreach (array_slice($rows, 1) as $row) {
            $byItem[$row['I'] ?? ''] = $row;
        }

        $this->assertSame('LIN', $byItem['Pro Singlet']['F'] ?? '');
        $this->assertSame('7', $byItem['Pro Singlet']['G'] ?? '');
        $this->assertSame('', $byItem['Plain Shorts']['F'] ?? '');
        $this->assertSame('', $byItem['Plain Shorts']['G'] ?? '');
    }

    public function test_coach_dashboard_shows_reopen_button_when_deadline_passed_and_reopen_clears_expired_deadline()
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Hampton Boys Basketball',
            'slug'             => 'hampton-boys-basketball',
            'status'           => 'approved',
            'pricing_approved' => true,
            'order_deadline'   => now()->subDays(8),
        ]);

        // Store is closed because its deadline is in the past
        $this->assertTrue($store->isClosed());

        // Coach dashboard accurately shows Closed — Deadline Passed and the Re-Open Store button
        $dashResp = $this->actingAs($coach)->get(route('coach.dashboard', ['store_id' => $store->id]));
        $dashResp->assertOk();
        $dashResp->assertSee('Closed — Deadline Passed');
        $dashResp->assertSee('Re-Open Store');

        // Re-opening without a new deadline clears the expired deadline so the store is actually open again
        $reopenResp = $this->actingAs($coach)->post(route('coach.store.reopen', $store));
        $reopenResp->assertRedirect();

        $store->refresh();
        $this->assertSame('approved', $store->status);
        $this->assertNull($store->order_deadline);
        $this->assertFalse($store->isClosed());

        // Re-opening a submitted_to_admin store with a future deadline sets that deadline and opens the store
        $store->update([
            'status'         => 'submitted_to_admin',
            'order_deadline' => now()->subDays(3),
        ]);
        $futureDate = now()->addDays(10)->format('Y-m-d');
        $this->actingAs($coach)->post(route('coach.store.reopen', $store), [
            'order_deadline' => $futureDate,
        ])->assertRedirect();

        $store->refresh();
        $this->assertSame('approved', $store->status);
        $this->assertSame($futureDate, $store->order_deadline->format('Y-m-d'));
        $this->assertFalse($store->isClosed());
    }
}
