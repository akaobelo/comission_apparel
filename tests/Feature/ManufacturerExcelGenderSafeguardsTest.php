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

class ManufacturerExcelGenderSafeguardsTest extends TestCase
{
    use RefreshDatabase;

    protected function extractSharedStrings(string $xlsxPath): array
    {
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($xlsxPath) === true);
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        $this->assertNotFalse($xml);
        $sx = simplexml_load_string($xml);
        $strings = [];
        foreach ($sx->si as $si) {
            $strings[] = (string) $si->t;
        }
        return $strings;
    }

    public function test_gender_restriction_detection_handles_male_female_and_unisex_names()
    {
        $this->assertSame('Mens / Boys', StoreItem::detectGenderRestrictionFromName('PAP Uni Package #1 (Male)'));
        $this->assertSame('Womens / Girls', StoreItem::detectGenderRestrictionFromName('PAP Package #1 Female'));
        $this->assertSame('Womens / Girls', StoreItem::detectGenderRestrictionFromName('PAP Uni #1 Top (Racerback)'));
        $this->assertNull(StoreItem::detectGenderRestrictionFromName('PAP Uni Package #1'));
        $this->assertNull(StoreItem::detectGenderRestrictionFromName('PAP Warm-up Top'));
    }

    public function test_checkout_locks_gender_on_male_and_female_packages()
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'PAP Sports 2026',
            'slug'             => 'pap-sports-2026',
            'status'           => 'approved',
            'pricing_approved' => true,
            'is_archived'      => false,
            'payment_mode'     => 'check',
        ]);

        $malePkg = StoreItem::create([
            'team_store_id' => $store->id,
            'name'          => 'PAP Uni Package #1 (Male)',
            'types'         => ['uniform_top', 'uniform_bottom'],
            'retail_price'  => 120.00,
        ]);

        $femalePkg = StoreItem::create([
            'team_store_id' => $store->id,
            'name'          => 'PAP Package #1 Female',
            'types'         => ['uniform_top', 'uniform_bottom'],
            'retail_price'  => 120.00,
        ]);

        // Storefront view renders gender lock badges
        $viewResp = $this->get(route('store.show', $store->slug));
        $viewResp->assertStatus(200);
        $viewResp->assertSee("Locked to Men&#039;s / Boy&#039;s", false);
        $viewResp->assertSee("Locked to Women&#039;s / Girl&#039;s", false);

        // Even if someone posts Womens / Girls on the Male package, server enforces Mens / Boys
        $resp = $this->post(route('store.order.submit', $store->slug), [
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Runner',
            'parent_email'       => 'alex@example.com',
            'parent_phone'       => '5551112222',
            'shipping_address'   => '456 Track Ave, Austin, TX 78701',
            'gender'             => 'Womens / Girls',
            'edit_pin'           => '1234',
            'items'              => [
                $malePkg->id => [
                    'selected' => '1',
                    'qty'      => 1,
                    'units'    => [
                        0 => [
                            'gender' => 'Womens / Girls',
                            'sizes'  => [
                                'uniform_top'    => 'AM',
                                'uniform_bottom' => 'AM',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resp->assertRedirect();
        $order = ParentOrder::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('Mens / Boys', $order->items_json[0]['gender']);
    }

    public function test_admin_safety_warning_and_one_click_reassign_fixes_female_excel_export()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'PAP Sports',
            'slug'             => 'pap-sports',
            'status'           => 'approved',
            'pricing_approved' => true,
            'is_archived'      => false,
            'payment_mode'     => 'check',
        ]);

        $tankDesign = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Top (Tank)',
            'types'           => ['uniform_top'],
            'wholesale_price' => 30.00,
            'image_paths'     => ['images/tank.png'],
        ]);
        $racerbackDesign = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Top (Racerback)',
            'types'           => ['uniform_top'],
            'wholesale_price' => 30.00,
            'image_paths'     => ['images/racerback.png'],
        ]);
        $maleBottomDesign = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Bottom',
            'types'           => ['uniform_bottom'],
            'wholesale_price' => 30.00,
            'image_paths'     => ['images/male-bottom.png', 'images/female-bottom-extra.png'],
        ]);
        $femaleBottomDesign = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Bottom (Female)',
            'types'           => ['uniform_bottom'],
            'wholesale_price' => 30.00,
            'image_paths'     => ['images/female-bottom.png'],
        ]);

        $malePkg = StoreItem::create([
            'team_store_id'        => $store->id,
            'name'                 => 'PAP Uni Package #1 (Male)',
            'types'                => ['uniform_top', 'uniform_bottom'],
            'component_design_ids' => [
                'uniform_top'    => $tankDesign->id,
                'uniform_bottom' => $maleBottomDesign->id,
            ],
            'retail_price'         => 100.00,
        ]);

        $femalePkg = StoreItem::create([
            'team_store_id'        => $store->id,
            'name'                 => 'PAP Package #1 Female',
            'types'                => ['uniform_top', 'uniform_bottom'],
            'component_design_ids' => [
                'uniform_top'    => $racerbackDesign->id,
                'uniform_bottom' => $femaleBottomDesign->id,
            ],
            'retail_price'         => 100.00,
        ]);

        // 1 Female order wrongly placed on the Male package + 1 Male order properly on Male package
        $femaleOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Maya',
            'athlete_last_name'  => 'Sprinter',
            'parent_email'       => 'maya@example.com',
            'parent_phone'       => '5550001111',
            'gender'             => 'Womens / Girls',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $malePkg->id,
                'name'   => $malePkg->name,
                'types'  => ['uniform_top', 'uniform_bottom'],
                'qty'    => 1,
                'gender' => 'Womens / Girls',
                'sizes'  => ['uniform_top' => 'AS', 'uniform_bottom' => 'AS'],
            ]],
        ]);

        $maleOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Marcus',
            'athlete_last_name'  => 'Sprinter',
            'parent_email'       => 'marcus@example.com',
            'parent_phone'       => '5550002222',
            'gender'             => 'Mens / Boys',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $malePkg->id,
                'name'   => $malePkg->name,
                'types'  => ['uniform_top', 'uniform_bottom'],
                'qty'    => 1,
                'gender' => 'Mens / Boys',
                'sizes'  => ['uniform_top' => 'AM', 'uniform_bottom' => 'AM'],
            ]],
        ]);

        // Store edit page shows Production Safety Warning, Move button, and multi-photo note
        $editResp = $this->actingAs($admin)->get(route('admin.store.edit', $store));
        $editResp->assertStatus(200);
        $editResp->assertSee('Production Safety Warning');
        $editResp->assertSee('Move 1 Female Order');
        $editResp->assertSee('2 photos on catalog item');

        // Click 1-click Reassign button
        $reassignResp = $this->actingAs($admin)->post(
            route('admin.store.package.reassign-gender-orders', [$store, $malePkg]),
            [
                'target_package_id' => $femalePkg->id,
                'gender_bucket'     => 'Female',
            ]
        );
        $reassignResp->assertRedirect(route('admin.store.edit', $store));

        $femaleOrder->refresh();
        $maleOrder->refresh();
        $this->assertSame($femalePkg->id, $femaleOrder->items_json[0]['id']);
        $this->assertSame($femalePkg->name, $femaleOrder->items_json[0]['name']);
        $this->assertSame($malePkg->id, $maleOrder->items_json[0]['id']);

        // Visual Excel now includes BOTH Female Racerback/Bottom (Female) AND Male Tank/Bottom
        $service = app(ManufacturerExcelExportService::class);
        $xlsxPath = $service->generateXlsx(collect([$femaleOrder, $maleOrder]), $store->fresh(), 'PAP Sports');
        $strings = $this->extractSharedStrings($xlsxPath);
        @unlink($xlsxPath);

        $this->assertContains('PAP Uni #1 Top (Racerback)', $strings);
        $this->assertContains('PAP Uni #1 Bottom (Female)', $strings);
        $this->assertContains('PAP Uni #1 Top (Tank)', $strings);
        $this->assertContains('PAP Uni #1 Bottom', $strings);
    }

    public function test_unisex_shared_package_supports_gender_specific_piece_overrides_in_excel()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Shared Track Club',
            'slug'             => 'shared-track-club',
            'status'           => 'approved',
            'pricing_approved' => true,
            'is_archived'      => false,
            'payment_mode'     => 'check',
        ]);

        $tankDesign = DesignCatalog::create([
            'name'            => 'Club Top (Tank)',
            'types'           => ['uniform_top'],
            'wholesale_price' => 30.00,
        ]);
        $racerbackDesign = DesignCatalog::create([
            'name'            => 'Club Top (Racerback)',
            'types'           => ['uniform_top'],
            'wholesale_price' => 30.00,
        ]);
        $bottomDesign = DesignCatalog::create([
            'name'            => 'Club Bottom',
            'types'           => ['uniform_bottom'],
            'wholesale_price' => 30.00,
        ]);

        $sharedPkg = StoreItem::create([
            'team_store_id' => $store->id,
            'name'          => 'Club Uniform Package #1',
            'types'         => ['uniform_top', 'uniform_bottom'],
            'retail_price'  => 90.00,
        ]);

        // Save base + female/male overrides via the controller endpoint
        $saveResp = $this->actingAs($admin)->postJson(
            route('admin.store.package.map-pieces', [$store, $sharedPkg]),
            [
                'component_design_ids' => [
                    'uniform_top'         => $tankDesign->id,
                    'uniform_top__female' => $racerbackDesign->id,
                    'uniform_top__male'   => $tankDesign->id,
                    'uniform_bottom'      => $bottomDesign->id,
                ],
            ]
        );
        $saveResp->assertStatus(200)->assertJson(['success' => true]);

        $femaleOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Chloe',
            'athlete_last_name'  => 'Fast',
            'parent_email'       => 'chloe@example.com',
            'parent_phone'       => '5550003333',
            'gender'             => 'Womens / Girls',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $sharedPkg->id,
                'name'   => $sharedPkg->name,
                'types'  => ['uniform_top', 'uniform_bottom'],
                'qty'    => 1,
                'gender' => 'Womens / Girls',
                'sizes'  => ['uniform_top' => 'AS', 'uniform_bottom' => 'AS'],
            ]],
        ]);

        $maleOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Devon',
            'athlete_last_name'  => 'Fast',
            'parent_email'       => 'devon@example.com',
            'parent_phone'       => '5550004444',
            'gender'             => 'Mens / Boys',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $sharedPkg->id,
                'name'   => $sharedPkg->name,
                'types'  => ['uniform_top', 'uniform_bottom'],
                'qty'    => 1,
                'gender' => 'Mens / Boys',
                'sizes'  => ['uniform_top' => 'AL', 'uniform_bottom' => 'AL'],
            ]],
        ]);

        $service = app(ManufacturerExcelExportService::class);
        $xlsxPath = $service->generateXlsx(collect([$femaleOrder, $maleOrder]), $store->fresh(), 'Shared Track Club');
        $strings = $this->extractSharedStrings($xlsxPath);
        @unlink($xlsxPath);

        $this->assertContains('Club Top (Racerback)', $strings);
        $this->assertContains('Club Top (Tank)', $strings);
        $this->assertContains('Club Bottom', $strings);
    }

    protected function extractSheet1Rows(string $xlsxPath): array
    {
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($xlsxPath) === true);
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertNotFalse($sharedXml);
        $this->assertNotFalse($sheetXml);

        $sxShared = simplexml_load_string($sharedXml);
        $shared = [];
        foreach ($sxShared->si as $si) {
            $shared[] = (string) $si->t;
        }

        $sxSheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sxSheet->sheetData->row as $row) {
            $rowValues = [];
            foreach ($row->c as $cell) {
                $type = (string) ($cell['t'] ?? '');
                $val = (string) ($cell->v ?? '');
                if ($type === 's' && $val !== '' && isset($shared[(int) $val])) {
                    $rowValues[] = $shared[(int) $val];
                } else {
                    $rowValues[] = $val;
                }
            }
            $rows[] = $rowValues;
        }

        return $rows;
    }

    public function test_staircase_sheet_isolates_female_and_male_columns_and_handles_numbered_uniform_slots()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'PAP Sports Split',
            'slug'             => 'pap-sports-split',
            'status'           => 'approved',
            'pricing_approved' => true,
            'is_archived'      => false,
            'payment_mode'     => 'check',
        ]);

        $warmupTop = DesignCatalog::create([
            'name'            => 'PAP Warm-up Top',
            'types'           => ['warmup_top'],
            'wholesale_price' => 35.00,
        ]);
        $tankTop = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Top (Tank)',
            'types'           => ['uniform_top'],
            'wholesale_price' => 30.00,
        ]);
        $maleBottom = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Bottom',
            'types'           => ['uniform_bottom'],
            'wholesale_price' => 30.00,
        ]);
        $racerbackTop = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Top (Racerback)',
            'types'           => ['uniform_top'],
            'wholesale_price' => 30.00,
        ]);
        $femaleBottom = DesignCatalog::create([
            'name'            => 'PAP Uni #1 Bottom (Female)',
            'types'           => ['uniform_bottom'],
            'wholesale_price' => 30.00,
        ]);

        $malePkg = StoreItem::create([
            'team_store_id'        => $store->id,
            'name'                 => 'PAP Uni Package #1 (Male)',
            'types'                => ['uniform_top', 'uniform_bottom', 'warmup_top'],
            'component_design_ids' => [
                'uniform_top'    => $tankTop->id,
                'uniform_bottom' => $maleBottom->id,
                'warmup_top'     => $warmupTop->id,
            ],
            'retail_price'         => 140.00,
        ]);

        // Female package uses uniform_2_top / uniform_2_bottom slots (exact PAP Sports setup)
        $femalePkg = StoreItem::create([
            'team_store_id'        => $store->id,
            'name'                 => 'PAP Package #1 Female',
            'types'                => ['uniform_2_top', 'uniform_2_bottom', 'warmup_top'],
            'component_design_ids' => [
                'uniform_2_top'    => $racerbackTop->id,
                'uniform_2_bottom' => $femaleBottom->id,
                'warmup_top'       => $warmupTop->id,
            ],
            'retail_price'         => 140.00,
        ]);

        $femaleOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Maya',
            'athlete_last_name'  => 'Sprinter',
            'parent_email'       => 'maya2@example.com',
            'parent_phone'       => '5550001111',
            'gender'             => 'Womens / Girls',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $malePkg->id,
                'name'   => $malePkg->name,
                'types'  => ['uniform_top', 'uniform_bottom', 'warmup_top'],
                'qty'    => 1,
                'gender' => 'Womens / Girls',
                'sizes'  => ['uniform_top' => 'AS', 'uniform_bottom' => 'AS', 'warmup_top' => 'AS'],
            ]],
        ]);

        $maleOrder = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Marcus',
            'athlete_last_name'  => 'Sprinter',
            'parent_email'       => 'marcus2@example.com',
            'parent_phone'       => '5550002222',
            'gender'             => 'Mens / Boys',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $malePkg->id,
                'name'   => $malePkg->name,
                'types'  => ['uniform_top', 'uniform_bottom', 'warmup_top'],
                'qty'    => 1,
                'gender' => 'Mens / Boys',
                'sizes'  => ['uniform_top' => 'AM', 'uniform_bottom' => 'AM', 'warmup_top' => 'AM'],
            ]],
        ]);

        // Reassign Female order from Male package (uniform_top/bottom) to Female package (uniform_2_top/bottom)
        $this->actingAs($admin)->post(
            route('admin.store.package.reassign-gender-orders', [$store, $malePkg]),
            [
                'target_package_id' => $femalePkg->id,
                'gender_bucket'     => 'Female',
            ]
        )->assertRedirect(route('admin.store.edit', $store));

        $femaleOrder->refresh();
        $this->assertSame($femalePkg->id, $femaleOrder->items_json[0]['id']);
        $this->assertArrayHasKey('uniform_2_top', $femaleOrder->items_json[0]['sizes']);
        $this->assertArrayHasKey('uniform_2_bottom', $femaleOrder->items_json[0]['sizes']);
        $this->assertSame('AS', $femaleOrder->items_json[0]['sizes']['uniform_2_top']);

        // Generate Visual Excel and inspect each gender section's header row independently
        $service = app(ManufacturerExcelExportService::class);
        $xlsxPath = $service->generateXlsx(collect([$femaleOrder, $maleOrder]), $store->fresh(), 'PAP Sports Split');
        $rows = $this->extractSheet1Rows($xlsxPath);
        @unlink($xlsxPath);

        $headerRows = array_values(array_filter($rows, fn($r) => ($r[0] ?? '') === 'Order Type'));
        $this->assertCount(2, $headerRows, 'Expected separate Female and Male section headers.');

        $femaleHeaders = $headerRows[0];
        $maleHeaders = $headerRows[1];

        // Female section must ONLY have Female + shared items (NO Male Tank or Male Bottom)
        $this->assertContains('PAP Uni #1 Top (Racerback)', $femaleHeaders);
        $this->assertContains('PAP Uni #1 Bottom (Female)', $femaleHeaders);
        $this->assertContains('PAP Warm-up Top', $femaleHeaders);
        $this->assertNotContains('PAP Uni #1 Top (Tank)', $femaleHeaders);
        $this->assertNotContains('PAP Uni #1 Bottom', $femaleHeaders);

        // Male section must ONLY have Male + shared items (NO Female Racerback or Female Bottom)
        $this->assertContains('PAP Uni #1 Top (Tank)', $maleHeaders);
        $this->assertContains('PAP Uni #1 Bottom', $maleHeaders);
        $this->assertContains('PAP Warm-up Top', $maleHeaders);
        $this->assertNotContains('PAP Uni #1 Top (Racerback)', $maleHeaders);
        $this->assertNotContains('PAP Uni #1 Bottom (Female)', $maleHeaders);
    }

    public function test_uniform_3_and_4_top_and_bottom_piece_linking_recommendations_and_excel_export(): void
    {
        $service = app(ManufacturerExcelExportService::class);

        // 1. Normalization & compatibility for Uniform #2, #3, #4 (Top & Bottom)
        foreach (['uniform_2_top', 'uniform_3_top', 'uniform_4_top'] as $topSlot) {
            $this->assertSame('uniform_top', ManufacturerExcelExportService::normalizeBaseGarmentType($topSlot));
            $this->assertTrue($service->isGarmentTypeCompatible($topSlot, ['uniform_top'], 'Jersey Top'));
            $this->assertTrue($service->isGarmentTypeCompatible($topSlot, [$topSlot], 'Jersey Top'));
            $this->assertFalse($service->isGarmentTypeCompatible($topSlot, ['uniform_bottom'], 'Jersey Bottom'));
        }

        foreach (['uniform_2_bottom', 'uniform_3_bottom', 'uniform_4_bottom'] as $bottomSlot) {
            $this->assertSame('uniform_bottom', ManufacturerExcelExportService::normalizeBaseGarmentType($bottomSlot));
            $this->assertTrue($service->isGarmentTypeCompatible($bottomSlot, ['uniform_bottom'], 'Jersey Shorts'));
            $this->assertTrue($service->isGarmentTypeCompatible($bottomSlot, [$bottomSlot], 'Jersey Shorts'));
            $this->assertFalse($service->isGarmentTypeCompatible($bottomSlot, ['uniform_top'], 'Jersey Top'));
        }

        // 2. Admin store edit shows ★ recommendations and Visual Excel exports linked Uniform #3 & #4 pieces
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id'            => $coach->id,
            'name'               => 'Multi-Uniform Club',
            'slug'               => 'multi-uniform-club-' . uniqid(),
            'status'             => 'active',
            'payment_mode'       => 'coach_pays',
            'fulfillment_method' => 'bulk',
        ]);

        $uni3Top = DesignCatalog::create([
            'name'            => 'Club Uni #3 Top',
            'category'        => 'individual',
            'type'            => 'uniform_top',
            'types'           => ['uniform_top'],
            'wholesale_price' => 35.00,
        ]);
        $uni3Bottom = DesignCatalog::create([
            'name'            => 'Club Uni #3 Bottom',
            'category'        => 'individual',
            'type'            => 'uniform_bottom',
            'types'           => ['uniform_bottom'],
            'wholesale_price' => 30.00,
        ]);
        $uni4Top = DesignCatalog::create([
            'name'            => 'Club Uni #4 Top',
            'category'        => 'individual',
            'type'            => 'uniform_4_top',
            'types'           => ['uniform_4_top'],
            'wholesale_price' => 35.00,
        ]);
        $uni4Bottom = DesignCatalog::create([
            'name'            => 'Club Uni #4 Bottom',
            'category'        => 'individual',
            'type'            => 'uniform_4_bottom',
            'types'           => ['uniform_4_bottom'],
            'wholesale_price' => 30.00,
        ]);

        foreach ([$uni3Top, $uni3Bottom, $uni4Top, $uni4Bottom] as $d) {
            StoreItem::create([
                'team_store_id'     => $store->id,
                'design_catalog_id' => $d->id,
                'name'              => $d->name,
                'types'             => $d->types,
                'retail_price'      => 40.00,
            ]);
        }

        $pkg = StoreItem::create([
            'team_store_id'        => $store->id,
            'name'                 => 'Club Full Uniform Package',
            'types'                => ['uniform_3_top', 'uniform_3_bottom', 'uniform_4_top', 'uniform_4_bottom'],
            'component_design_ids' => [
                'uniform_3_top'    => $uni3Top->id,
                'uniform_3_bottom' => $uni3Bottom->id,
                'uniform_4_top'    => $uni4Top->id,
                'uniform_4_bottom' => $uni4Bottom->id,
            ],
            'retail_price'         => 160.00,
        ]);

        // Verify ★ recommendations render on Admin Store Edit page for Uniform #3 & #4 slots
        $editResponse = $this->actingAs($admin)->get(route('admin.store.edit', $store));
        $editResponse->assertOk();
        $editResponse->assertSee('Uniform #3 (top)');
        $editResponse->assertSee('Uniform #3 (bottom)');
        $editResponse->assertSee('Uniform #4 (top)');
        $editResponse->assertSee('Uniform #4 (bottom)');
        $editResponse->assertSee('★ Club Uni #3 Top');
        $editResponse->assertSee('★ Club Uni #3 Bottom');
        $editResponse->assertSee('★ Club Uni #4 Top');
        $editResponse->assertSee('★ Club Uni #4 Bottom');

        // Verify Visual Excel resolves all four linked pieces into their own columns
        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Runner',
            'parent_email'       => 'alex@example.com',
            'parent_phone'       => '5550003333',
            'gender'             => 'Mens / Boys',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $pkg->id,
                'name'   => $pkg->name,
                'types'  => ['uniform_3_top', 'uniform_3_bottom', 'uniform_4_top', 'uniform_4_bottom'],
                'qty'    => 1,
                'gender' => 'Mens / Boys',
                'sizes'  => [
                    'uniform_3_top'    => 'AM',
                    'uniform_3_bottom' => 'AM',
                    'uniform_4_top'    => 'AL',
                    'uniform_4_bottom' => 'AL',
                ],
            ]],
        ]);

        $xlsxPath = $service->generateXlsx(collect([$order]), $store->fresh(), 'Multi-Uniform Export');
        $rows = $this->extractSheet1Rows($xlsxPath);
        @unlink($xlsxPath);

        $headerRows = array_values(array_filter($rows, fn($r) => ($r[0] ?? '') === 'Order Type'));
        $this->assertCount(1, $headerRows);

        $headers = $headerRows[0];
        $this->assertContains('Club Uni #3 Top', $headers);
        $this->assertContains('Club Uni #3 Bottom', $headers);
        $this->assertContains('Club Uni #4 Top', $headers);
        $this->assertContains('Club Uni #4 Bottom', $headers);
    }

    public function test_warmup_set_and_warmup_set_2_package_slots_respect_linked_single_warmup_pieces_in_excel(): void
    {
        $service = app(ManufacturerExcelExportService::class);

        // Compatibility checks: warmup_set / warmup_set_2 accept single warm-up top/bottom pieces,
        // while still rejecting unrelated garment types like uniform_top or backpack.
        $this->assertTrue($service->isGarmentTypeCompatible('warmup_set', ['warmup_bottom'], 'DWTC Warm-up (bottom)'));
        $this->assertTrue($service->isGarmentTypeCompatible('warmup_set_2', ['warmup_top'], 'DWTC Warm-up Top (jogger)'));
        $this->assertTrue($service->isGarmentTypeCompatible('uniform_set', ['uniform_top'], 'DWTC Uni #2 Top'));
        $this->assertTrue($service->isGarmentTypeCompatible('uniform_set_2', ['uniform_bottom'], 'DWTC Uni #2 (bottom)'));
        $this->assertFalse($service->isGarmentTypeCompatible('warmup_set', ['uniform_top'], 'DWTC Uni #2 Top'));
        $this->assertFalse($service->isGarmentTypeCompatible('warmup_set_2', ['backpack'], 'DWTC Backpack'));

        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'            => $coach->id,
            'name'               => 'Dayton Wolverines',
            'slug'               => 'dayton-wolverines-' . uniqid(),
            'status'             => 'active',
            'payment_mode'       => 'coach_pays',
            'fulfillment_method' => 'bulk',
        ]);

        $warmupTop = DesignCatalog::create([
            'name'            => 'DWTC Warm-up Top (jogger)',
            'category'        => 'individual',
            'type'            => 'warmup_top',
            'types'           => ['warmup_top'],
            'wholesale_price' => 45.00,
        ]);
        $warmupBottom = DesignCatalog::create([
            'name'            => 'DWTC Warm-up (bottom)',
            'category'        => 'individual',
            'type'            => 'warmup_bottom',
            'types'           => ['warmup_bottom'],
            'wholesale_price' => 40.00,
        ]);

        foreach ([$warmupTop, $warmupBottom] as $idx => $d) {
            StoreItem::create([
                'team_store_id'     => $store->id,
                'design_catalog_id' => $d->id,
                'name'              => $d->name,
                'types'             => $d->types,
                'sort_order'        => $idx + 1,
                'retail_price'      => 50.00,
            ]);
        }

        $pkg3 = StoreItem::create([
            'team_store_id'        => $store->id,
            'name'                 => 'DWTC Uni Package #3',
            'types'                => ['warmup_set', 'warmup_set_2'],
            'component_design_ids' => [
                'warmup_set'   => $warmupBottom->id,
                'warmup_set_2' => $warmupTop->id,
            ],
            'sort_order'           => 10,
            'retail_price'         => 180.00,
        ]);

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Wolverine',
            'parent_email'       => 'jordan@example.com',
            'parent_phone'       => '5550004444',
            'gender'             => 'Female',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $pkg3->id,
                'name'   => $pkg3->name,
                'types'  => ['warmup_set', 'warmup_set_2'],
                'qty'    => 1,
                'gender' => 'Female',
                'sizes'  => [
                    'warmup_set'   => 'YM',
                    'warmup_set_2' => 'YM',
                ],
            ]],
        ]);

        $xlsxPath = $service->generateXlsx(collect([$order]), $store->fresh(), 'Dayton Wolverines Export');
        $rows = $this->extractSheet1Rows($xlsxPath);
        @unlink($xlsxPath);

        $headerRows = array_values(array_filter($rows, fn($r) => ($r[0] ?? '') === 'Order Type'));
        $this->assertCount(1, $headerRows);

        $headers = $headerRows[0];
        $this->assertContains('DWTC Warm-up Top (jogger)', $headers);
        $this->assertContains('DWTC Warm-up (bottom)', $headers);
        $this->assertNotContains('DWTC Uni Package #3 - Warm-up (top/bottom)', $headers);
        $this->assertNotContains('DWTC Uni Package #3 - Warm-up #2 (set)', $headers);

        // Also verify when the package itself is later updated from warmup_set / warmup_set_2
        // to warmup_top / warmup_bottom, existing orders that still have warmup_set / warmup_set_2
        // in items_json still resolve to the linked Warm-up Top and Warm-up Bottom columns.
        $pkg3->update([
            'types'                => ['warmup_top', 'warmup_bottom'],
            'component_design_ids' => [
                'warmup_top'    => $warmupTop->id,
                'warmup_bottom' => $warmupBottom->id,
            ],
        ]);

        $xlsxPath2 = $service->generateXlsx(collect([$order]), $store->fresh(), 'Dayton Wolverines Export After Package Fix');
        $rows2 = $this->extractSheet1Rows($xlsxPath2);
        $sharedStrings2 = $this->extractSharedStrings($xlsxPath2);
        @unlink($xlsxPath2);

        $headerRows2 = array_values(array_filter($rows2, fn($r) => ($r[0] ?? '') === 'Order Type'));
        $this->assertCount(1, $headerRows2);
        $headers2 = $headerRows2[0];
        $this->assertContains('DWTC Warm-up Top (jogger)', $headers2);
        $this->assertContains('DWTC Warm-up (bottom)', $headers2);
        $this->assertNotContains('DWTC Uni Package #3 - Warm-up (top/bottom)', $headers2);
        $this->assertNotContains('DWTC Uni Package #3 - Warm-up #2 (set)', $headers2);

        // Sheet 2 (Full Order Roster) must also display the updated Warm-up (top) / Warm-up (bottom) labels
        // instead of the stale Warm-up (top/bottom) / Warm-up #2 (set) keys saved on older orders.
        $allRosterText = implode("\n", $sharedStrings2);
        $this->assertStringContainsString('Warm-up (top), Warm-up (bottom)', $allRosterText);
        $this->assertStringContainsString('Warm-up (top) (DWTC Warm-up Top (jogger)): YM | Warm-up (bottom) (DWTC Warm-up (bottom)): YM', $allRosterText);
        $this->assertStringNotContainsString('Warm-up #2 (set)', $allRosterText);
        $this->assertStringNotContainsString('Warm-up (top/bottom)', $allRosterText);
    }

    public function test_uniform_singlet_orders_and_package_export_in_manufacturer_visual_excel(): void
    {
        $service = app(ManufacturerExcelExportService::class);

        // 1. Compatibility & formatting checks for uniform_singlet
        $this->assertSame('Uniform (Singlet)', DesignCatalog::formatTypeLabel('uniform_singlet'));
        $this->assertContains('uniform_singlet', DesignCatalog::sizedTypes());
        $this->assertTrue($service->isGarmentTypeCompatible('uniform_singlet', ['uniform_singlet'], 'Elevate V2 Singlet'));
        $this->assertTrue($service->isGarmentTypeCompatible('uniform_singlet', ['uniform_top'], 'Legacy Singlet'));
        $this->assertFalse($service->isGarmentTypeCompatible('uniform_singlet', ['warmup_top'], 'Warm-up Top'));

        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'            => $coach->id,
            'name'               => 'Stoic Strength Wrestling',
            'slug'               => 'stoic-strength-wrestling-' . uniqid(),
            'status'             => 'active',
            'payment_mode'       => 'coach_pays',
            'fulfillment_method' => 'bulk',
        ]);

        $singletDesign = DesignCatalog::create([
            'name'             => 'Stoic Elevate V2 Singlet',
            'category'         => 'individual',
            'type'             => null,
            'types'            => ['uniform_singlet'],
            'has_name_field'   => true,
            'has_number_field' => false,
            'wholesale_price'  => 55.00,
        ]);
        $warmupDesign = DesignCatalog::create([
            'name'            => 'Stoic Warm-up Hoodie',
            'category'        => 'individual',
            'type'            => null,
            'types'           => ['warmup_top'],
            'wholesale_price' => 45.00,
        ]);

        $singletStoreItem = StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $singletDesign->id,
            'name'              => $singletDesign->name,
            'types'             => ['uniform_singlet'],
            'retail_price'      => 65.00,
            'sort_order'        => 1,
        ]);
        StoreItem::create([
            'team_store_id'     => $store->id,
            'design_catalog_id' => $warmupDesign->id,
            'name'              => $warmupDesign->name,
            'types'             => ['warmup_top'],
            'retail_price'      => 55.00,
            'sort_order'        => 2,
        ]);

        $singletPkg = StoreItem::create([
            'team_store_id'        => $store->id,
            'name'                 => 'Stoic Singlet Kit Package',
            'types'                => ['uniform_singlet', 'warmup_top'],
            'component_design_ids' => [
                'uniform_singlet' => $singletDesign->id,
                'warmup_top'      => $warmupDesign->id,
            ],
            'retail_price'         => 110.00,
            'sort_order'           => 3,
        ]);

        // Standalone singlet order + package order containing a singlet
        $order1 = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Carter',
            'parent_email'       => 'alex@example.com',
            'parent_phone'       => '5550005551',
            'gender'             => 'Mens / Boys',
            'jersey_name'        => 'CARTER',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $singletStoreItem->id,
                'name'   => $singletStoreItem->name,
                'types'  => ['uniform_singlet'],
                'qty'    => 2,
                'gender' => 'Mens / Boys',
                'sizes'  => ['uniform_singlet' => 'AL'],
            ]],
        ]);

        $order2 = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Maya',
            'athlete_last_name'  => 'Brooks',
            'parent_email'       => 'maya@example.com',
            'parent_phone'       => '5550005552',
            'gender'             => 'Womens / Girls',
            'jersey_name'        => 'BROOKS',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'items_json'         => [[
                'id'     => $singletPkg->id,
                'name'   => $singletPkg->name,
                'types'  => ['uniform_singlet', 'warmup_top'],
                'qty'    => 1,
                'gender' => 'Womens / Girls',
                'sizes'  => [
                    'uniform_singlet' => 'AS',
                    'warmup_top'      => 'AM',
                ],
            ]],
        ]);

        $xlsxPath = $service->generateXlsx(collect([$order1, $order2]), $store->fresh(), 'Stoic Singlet Export');
        $sheet1Rows = $this->extractSheet1Rows($xlsxPath);
        $sharedStrings = $this->extractSharedStrings($xlsxPath);
        @unlink($xlsxPath);

        $headerRows = array_values(array_filter($sheet1Rows, fn($r) => ($r[0] ?? '') === 'Order Type'));
        $this->assertCount(2, $headerRows, 'Expected separate Female and Male staircase sections.');

        // Both Female (via linked package) and Male (standalone) sections must include the Singlet column
        $this->assertContains('Stoic Elevate V2 Singlet', $headerRows[0]);
        $this->assertContains('Stoic Warm-up Hoodie', $headerRows[0]);
        $this->assertContains('Stoic Elevate V2 Singlet', $headerRows[1]);

        // Full Order Roster & summary strings must show the human-readable "Uniform (Singlet)" label
        $allText = implode("\n", $sharedStrings);
        $this->assertStringContainsString('Uniform (Singlet)', $allText);
        $this->assertStringContainsString('Uniform (Singlet) (Stoic Elevate V2 Singlet): AS | Warm-up (top) (Stoic Warm-up Hoodie): AM', $allText);
    }

    public function test_athlete_visual_chart_is_exported_as_third_tab_with_mockups_and_size_summary(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $collection = \App\Models\DesignCollection::create(['name' => 'Legacy Runners Visual Test']);

        // Create a real 1x1 PNG in public/images so drawing embedding is exercised end-to-end
        $tmpImgDir = public_path('images/test_excel_mockups');
        if (!is_dir($tmpImgDir)) {
            mkdir($tmpImgDir, 0777, true);
        }
        $imgRelPath = 'images/test_excel_mockups/test_hoodie.png';
        $imgAbsPath = public_path($imgRelPath);
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($imgAbsPath, $pngData);

        try {
            $hoodieDesign = DesignCatalog::create([
                'design_collection_id' => $collection->id,
                'name'                 => 'Legacy Hoodie',
                'type'                 => 'hoodie',
                'types'                => ['hoodie'],
                'image_paths'          => [$imgRelPath],
                'wholesale_price'      => 28.00,
                'sort_order'           => 1,
            ]);

            $backpackDesign = DesignCatalog::create([
                'design_collection_id'    => $collection->id,
                'name'                    => 'Legacy Backpack',
                'type'                    => 'backpack',
                'types'                   => ['backpack'],
                'has_backpack_name_field' => true,
                'image_paths'             => [$imgRelPath],
                'wholesale_price'         => 35.00,
                'sort_order'              => 2,
            ]);

            $store = TeamStore::create([
                'user_id' => $coach->id,
                'name'    => 'Legacy Runners Elite',
                'slug'    => 'legacy-runners-visual-tab-test',
                'status'  => 'approved',
            ]);

            $hoodieItem = StoreItem::create([
                'team_store_id'     => $store->id,
                'design_catalog_id' => $hoodieDesign->id,
                'name'              => $hoodieDesign->name,
                'type'              => 'hoodie',
                'types'             => ['hoodie'],
                'image_paths'       => [$imgRelPath],
                'retail_price'      => 45.00,
                'sort_order'        => 1,
            ]);

            $backpackItem = StoreItem::create([
                'team_store_id'     => $store->id,
                'design_catalog_id' => $backpackDesign->id,
                'name'              => $backpackDesign->name,
                'type'              => 'backpack',
                'types'             => ['backpack'],
                'image_paths'       => [$imgRelPath],
                'retail_price'      => 55.00,
                'sort_order'        => 2,
            ]);

            $order1 = ParentOrder::create([
                'team_store_id'      => $store->id,
                'athlete_first_name' => 'Jershawnti',
                'athlete_last_name'  => 'Walton',
                'parent_email'       => 'walton@example.com',
                'parent_phone'       => '5550009991',
                'gender'             => 'Womens / Girls',
                'backpack_name'      => 'Walton',
                'status'             => 'Submitted',
                'payment_status'     => 'not_applicable',
                'items_json'         => [
                    [
                        'id'     => $hoodieItem->id,
                        'name'   => $hoodieItem->name,
                        'types'  => ['hoodie'],
                        'qty'    => 2,
                        'gender' => 'Womens / Girls',
                        'sizes'  => ['hoodie' => 'AM'],
                    ],
                    [
                        'id'     => $backpackItem->id,
                        'name'   => $backpackItem->name,
                        'types'  => ['backpack'],
                        'qty'    => 1,
                        'gender' => 'Womens / Girls',
                        'sizes'  => [],
                    ],
                ],
            ]);

            $order2 = ParentOrder::create([
                'team_store_id'      => $store->id,
                'athlete_first_name' => 'Andrew',
                'athlete_last_name'  => 'Joshua',
                'parent_email'       => 'joshua@example.com',
                'parent_phone'       => '5550009992',
                'gender'             => 'Mens / Boys',
                'status'             => 'Submitted',
                'payment_status'     => 'not_applicable',
                'items_json'         => [
                    [
                        'id'     => $hoodieItem->id,
                        'name'   => $hoodieItem->name,
                        'types'  => ['hoodie'],
                        'qty'    => 1,
                        'gender' => 'Mens / Boys',
                        'sizes'  => ['hoodie' => 'YL'],
                    ],
                ],
            ]);

            $service = app(ManufacturerExcelExportService::class);
            $xlsxPath = $service->generateXlsx(collect([$order1, $order2]), $store->fresh(), 'Legacy Runners Elite');

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($xlsxPath) === true);

            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $workbookRelsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
            $contentTypesXml = $zip->getFromName('[Content_Types].xml');
            $sheet3Xml = $zip->getFromName('xl/worksheets/sheet3.xml');
            $sheet3RelsXml = $zip->getFromName('xl/worksheets/_rels/sheet3.xml.rels');
            $drawing2Xml = $zip->getFromName('xl/drawings/drawing2.xml');
            $drawing2RelsXml = $zip->getFromName('xl/drawings/_rels/drawing2.xml.rels');
            $sharedStrings = $this->extractSharedStrings($xlsxPath);

            $zip->close();
            @unlink($xlsxPath);

            // Verify 3 tabs in exact order: Quantity by Size Chart, Full Order Roster, Athlete Visual Chart
            $wbDoc = new \SimpleXMLElement($workbookXml);
            $wbDoc->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $sheets = $wbDoc->xpath('//m:sheets/m:sheet');
            $this->assertCount(3, $sheets);
            $this->assertSame('Quantity by Size Chart', (string) $sheets[0]['name']);
            $this->assertSame('Full Order Roster', (string) $sheets[1]['name']);
            $this->assertSame('Athlete Visual Chart', (string) $sheets[2]['name']);

            // Verify workbook rels, content types, and Sheet 3 drawings wiring
            $this->assertStringContainsString('Target="worksheets/sheet3.xml"', $workbookRelsXml);
            $this->assertStringContainsString('PartName="/xl/worksheets/sheet3.xml"', $contentTypesXml);
            $this->assertStringContainsString('PartName="/xl/drawings/drawing2.xml"', $contentTypesXml);
            $this->assertNotFalse($sheet3Xml);
            $this->assertNotFalse($sheet3RelsXml);
            $this->assertStringContainsString('Target="../drawings/drawing2.xml"', $sheet3RelsXml);
            $this->assertNotFalse($drawing2Xml);
            $this->assertNotFalse($drawing2RelsXml);

            // Parse Sheet 3 rows and verify headers, athlete data, and size breakdown summary
            $s3Doc = new \SimpleXMLElement($sheet3Xml);
            $s3Doc->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $sheet3Rows = [];
            foreach ($s3Doc->xpath('//m:sheetData/m:row') as $row) {
                $rData = [];
                foreach ($row->c as $c) {
                    $ref = (string) $c['r'];
                    $letters = preg_replace('/[0-9]+/', '', $ref);
                    $colIdx = 0;
                    for ($i = 0; $i < strlen($letters); $i++) {
                        $colIdx = $colIdx * 26 + (ord($letters[$i]) - 64);
                    }
                    $colIdx--;
                    $type = (string) $c['t'];
                    $val = isset($c->v) ? (string) $c->v : '';
                    if ($type === 's' && $val !== '') {
                        $val = $sharedStrings[(int) $val] ?? '';
                    }
                    $rData[$colIdx] = $val;
                }
                $sheet3Rows[] = $rData;
            }

            // Row 1: Banner, Row 2: Product Mockups, Row 3: Column Headers
            $this->assertStringContainsString('Orders: 2', $sheet3Rows[0][0] ?? '');
            $this->assertStringContainsString('Total Garment Units: 4', $sheet3Rows[0][0] ?? '');
            $this->assertSame('PRODUCT MOCKUPS', $sheet3Rows[1][0] ?? '');
            $this->assertSame('First Name', $sheet3Rows[2][0] ?? '');
            $this->assertSame('Last Name', $sheet3Rows[2][1] ?? '');
            $this->assertSame('Legacy Hoodie', $sheet3Rows[2][7] ?? '');
            $this->assertSame('Legacy Backpack', $sheet3Rows[2][8] ?? '');

            // Row 4: Jershawnti Walton (2x AM hoodie + Walton backpack)
            $this->assertSame('Jershawnti', $sheet3Rows[3][0] ?? '');
            $this->assertSame('Walton', $sheet3Rows[3][1] ?? '');
            $this->assertSame('Walton', $sheet3Rows[3][5] ?? '');
            $this->assertSame('AM (x2)', $sheet3Rows[3][7] ?? '');
            $this->assertSame('Walton', $sheet3Rows[3][8] ?? '');

            // Row 5: Andrew Joshua (1x YL hoodie)
            $this->assertSame('Andrew', $sheet3Rows[4][0] ?? '');
            $this->assertSame('YL', $sheet3Rows[4][7] ?? '');
            $this->assertSame('', $sheet3Rows[4][8] ?? '');

            // Size Breakdown Summary & Grand Total row at bottom
            $lastRow = end($sheet3Rows);
            $this->assertSame('TOTAL QUANTITY', $lastRow[5] ?? '');
            $this->assertSame('4', $lastRow[6] ?? '');
            $this->assertSame('3', $lastRow[7] ?? '');
            $this->assertSame('1', $lastRow[8] ?? '');
        } finally {
            @unlink($imgAbsPath);
            @rmdir($tmpImgDir);
        }
    }
}
