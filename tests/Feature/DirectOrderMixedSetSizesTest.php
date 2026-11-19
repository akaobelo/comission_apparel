<?php

namespace Tests\Feature;

use App\Models\DesignCatalog;
use App\Models\ParentOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectOrderMixedSetSizesTest extends TestCase
{
    use RefreshDatabase;

    public function test_uniform_and_warmup_sets_expand_to_separate_top_and_bottom_types(): void
    {
        $uniformSet = new DesignCatalog(['type' => 'uniform_set', 'types' => ['uniform_set']]);
        $warmupSet = new DesignCatalog(['type' => 'warmup_set', 'types' => ['warmup_set']]);
        $uniformSet2 = new DesignCatalog(['type' => 'uniform_set_2', 'types' => ['uniform_set_2']]);
        $warmupSet2 = new DesignCatalog(['type' => 'warmup_set_2', 'types' => ['warmup_set_2']]);

        $this->assertSame(['uniform_top', 'uniform_bottom'], $uniformSet->expanded_types);
        $this->assertSame(['warmup_top', 'warmup_bottom'], $warmupSet->expanded_types);
        $this->assertSame(['uniform_2_top', 'uniform_2_bottom'], $uniformSet2->expanded_types);
        $this->assertSame(['warmup_2_top', 'warmup_2_bottom'], $warmupSet2->expanded_types);
    }

    public function test_coach_dashboard_renders_mixed_top_and_bottom_selectors_and_submits_mixed_sizes(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $uniformSet = DesignCatalog::create([
            'name'            => 'Flyers Uniform Set',
            'category'        => 'individual',
            'type'            => 'uniform_set',
            'types'           => ['uniform_set'],
            'wholesale_price' => 85.00,
        ]);

        $warmupSet = DesignCatalog::create([
            'name'            => 'Flyers Warm-Up Set',
            'category'        => 'individual',
            'type'            => 'warmup_set',
            'types'           => ['warmup_set'],
            'wholesale_price' => 95.00,
        ]);

        $coach->designCatalog()->attach([$uniformSet->id, $warmupSet->id]);

        // Dashboard renders separate Top & Bottom size inputs for both sets
        $dashboardResponse = $this->actingAs($coach)->get(route('coach.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee("items[{$uniformSet->id}][package_sizes][uniform_top]", false);
        $dashboardResponse->assertSee("items[{$uniformSet->id}][package_sizes][uniform_bottom]", false);
        $dashboardResponse->assertSee("items[{$warmupSet->id}][package_sizes][warmup_top]", false);
        $dashboardResponse->assertSee("items[{$warmupSet->id}][package_sizes][warmup_bottom]", false);
        $dashboardResponse->assertSee("items[{$uniformSet->id}][units][0][package_sizes][uniform_top]", false);
        $dashboardResponse->assertSee("items[{$uniformSet->id}][units][0][package_sizes][uniform_bottom]", false);

        // Submit By Person with mixed top/bottom sizes
        $personResponse = $this->actingAs($coach)->post(route('coach.direct-order.submit'), [
            'order_type'         => 'person',
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Lee',
            'items'              => [
                $uniformSet->id => [
                    'selected'      => '1',
                    'gender'        => 'Male',
                    'qty'           => 1,
                    'package_sizes' => [
                        'uniform_top'    => 'AM',
                        'uniform_bottom' => 'AL',
                    ],
                ],
                $warmupSet->id => [
                    'selected'      => '1',
                    'gender'        => 'Male',
                    'qty'           => 1,
                    'package_sizes' => [
                        'warmup_top'    => 'AL',
                        'warmup_bottom' => 'AXL',
                    ],
                ],
            ],
        ]);
        $personResponse->assertRedirect();
        $personResponse->assertSessionHasNoErrors();

        $draft = ParentOrder::where('user_id', $coach->id)->latest('id')->first();
        $this->assertNotNull($draft);
        $this->assertCount(2, $draft->items_json);
        $this->assertSame(['uniform_top' => 'AM', 'uniform_bottom' => 'AL'], $draft->items_json[0]['sizes']);
        $this->assertSame(['warmup_top' => 'AL', 'warmup_bottom' => 'AXL'], $draft->items_json[1]['sizes']);

        // Submit By Item (bulk) with 2 units having different mixed top/bottom sizes
        $itemResponse = $this->actingAs($coach)->post(route('coach.direct-order.submit'), [
            'order_type' => 'item',
            'items'      => [
                $uniformSet->id => [
                    'selected' => '1',
                    'qty'      => 2,
                    'units'    => [
                        [
                            'gender'        => 'Female',
                            'package_sizes' => ['uniform_top' => 'AS', 'uniform_bottom' => 'AM'],
                        ],
                        [
                            'gender'        => 'Male',
                            'package_sizes' => ['uniform_top' => 'AL', 'uniform_bottom' => 'AXL'],
                        ],
                    ],
                ],
            ],
        ]);
        $itemResponse->assertRedirect();
        $itemResponse->assertSessionHasNoErrors();

        $bulkDraft = ParentOrder::where('user_id', $coach->id)->latest('id')->first();
        $this->assertNotEquals($draft->id, $bulkDraft->id);
        $this->assertCount(2, $bulkDraft->items_json);
        $this->assertSame(['uniform_top' => 'AS', 'uniform_bottom' => 'AM'], $bulkDraft->items_json[0]['sizes']);
        $this->assertSame(['uniform_top' => 'AL', 'uniform_bottom' => 'AXL'], $bulkDraft->items_json[1]['sizes']);
    }
}
