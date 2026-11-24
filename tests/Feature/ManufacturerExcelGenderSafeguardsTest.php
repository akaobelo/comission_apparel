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
        @unlink($xlsxPath2);

        $headerRows2 = array_values(array_filter($rows2, fn($r) => ($r[0] ?? '') === 'Order Type'));
        $this->assertCount(1, $headerRows2);
        $headers2 = $headerRows2[0];
        $this->assertContains('DWTC Warm-up Top (jogger)', $headers2);
        $this->assertContains('DWTC Warm-up (bottom)', $headers2);
        $this->assertNotContains('DWTC Uni Package #3 - Warm-up (top/bottom)', $headers2);
        $this->assertNotContains('DWTC Uni Package #3 - Warm-up #2 (set)', $headers2);
    }
}
