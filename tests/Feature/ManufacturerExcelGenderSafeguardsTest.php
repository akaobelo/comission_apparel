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
}
