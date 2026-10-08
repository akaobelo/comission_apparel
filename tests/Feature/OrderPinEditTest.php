<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TeamStore;
use App\Models\ParentOrder;
use App\Models\StoreItem;

class OrderPinEditTest extends TestCase
{
    use RefreshDatabase;

    protected function createApprovedStore(): array
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Varsity Track Store',
            'slug'             => 'varsity-track-store',
            'status'           => 'approved',
            'pricing_approved' => true,
            'is_archived'      => false,
            'payment_mode'     => 'online',
        ]);

        $item = StoreItem::create([
            'team_store_id' => $store->id,
            'name'          => 'Performance Jersey',
            'type'          => 'jersey',
            'types'         => ['jersey'],
            'retail_price'  => 45.00,
        ]);

        return [$coach, $store, $item];
    }

    public function test_submit_order_requires_4_digit_pin()
    {
        [$coach, $store, $item] = $this->createApprovedStore();

        // Missing PIN
        $response = $this->post(route('store.order.submit', $store->slug), [
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Smith',
            'parent_email'       => 'parent@example.com',
            'parent_phone'       => '5551234567',
            'gender'             => 'Mens / Boys',
            'items'              => [
                $item->id => [
                    'selected' => '1',
                    'qty'      => 1,
                    'sizes'    => ['jersey' => 'AS'],
                ],
            ],
        ]);

        $response->assertSessionHasErrors('edit_pin');

        // Invalid non-4-digit PIN
        $response2 = $this->post(route('store.order.submit', $store->slug), [
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Smith',
            'parent_email'       => 'parent@example.com',
            'parent_phone'       => '5551234567',
            'gender'             => 'Mens / Boys',
            'edit_pin'           => '12',
            'items'              => [
                $item->id => [
                    'selected' => '1',
                    'qty'      => 1,
                    'sizes'    => ['jersey' => 'AS'],
                ],
            ],
        ]);

        $response2->assertSessionHasErrors('edit_pin');
    }

    public function test_order_created_with_valid_4_digit_pin()
    {
        [$coach, $store, $item] = $this->createApprovedStore();

        $response = $this->post(route('store.order.submit', $store->slug), [
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Smith',
            'parent_email'       => 'parent@example.com',
            'parent_phone'       => '5551234567',
            'gender'             => 'Mens / Boys',
            'edit_pin'           => '5821',
            'items'              => [
                $item->id => [
                    'selected' => '1',
                    'qty'      => 1,
                    'sizes'    => ['jersey' => 'AS'],
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('parent_orders', [
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Smith',
            'edit_pin'           => '5821',
        ]);
    }

    public function test_verify_pin_endpoint()
    {
        [$coach, $store, $item] = $this->createApprovedStore();

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Morgan',
            'parent_email'       => 'alex@example.com',
            'parent_phone'       => '5559876543',
            'edit_pin'           => '4321',
            'items_json'         => [
                ['id' => $item->id, 'name' => 'Performance Jersey', 'sizes' => ['jersey' => 'AS'], 'qty' => 1]
            ],
            'status'             => 'Submitted',
            'payment_status'     => 'pending',
        ]);

        // Wrong PIN -> 403
        $resp = $this->postJson(route('store.order.verify-pin', ['slug' => $store->slug, 'order' => $order->id]), [
            'pin' => '9999',
        ]);
        $resp->assertStatus(403);
        $resp->assertJson(['success' => false]);

        // Correct PIN -> 200
        $respCorrect = $this->postJson(route('store.order.verify-pin', ['slug' => $store->slug, 'order' => $order->id]), [
            'pin' => '4321',
        ]);
        $respCorrect->assertStatus(200);
        $respCorrect->assertJson(['success' => true]);
    }

    public function test_parent_can_update_sizes_with_valid_pin()
    {
        [$coach, $store, $item] = $this->createApprovedStore();

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Morgan',
            'parent_email'       => 'alex@example.com',
            'parent_phone'       => '5559876543',
            'edit_pin'           => '1234',
            'items_json'         => [
                ['id' => $item->id, 'name' => 'Performance Jersey', 'sizes' => ['jersey' => 'AS'], 'qty' => 1]
            ],
            'status'             => 'Submitted',
            'payment_status'     => 'pending',
        ]);

        $updateResp = $this->postJson(route('store.order.update-sizes', ['slug' => $store->slug, 'order' => $order->id]), [
            'pin'   => '1234',
            'items' => [
                0 => [
                    'sizes' => ['jersey' => 'AL']
                ]
            ],
            'jersey_name'   => 'MORGAN',
            'jersey_number' => '13',
        ]);

        $updateResp->assertStatus(200);
        $updateResp->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('AL', $order->items_json[0]['sizes']['jersey']);
        $this->assertEquals('MORGAN', $order->jersey_name);
        $this->assertEquals('13', $order->jersey_number);
        $this->assertTrue((bool)$order->is_edited);
        $this->assertEquals('parent (via PIN)', $order->edited_by);
    }

    public function test_coach_can_update_edit_pin()
    {
        [$coach, $store, $item] = $this->createApprovedStore();

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Taylor',
            'athlete_last_name'  => 'Swift',
            'parent_email'       => 'taylor@example.com',
            'parent_phone'       => '5550001111',
            'edit_pin'           => '1111',
            'items_json'         => [
                ['id' => $item->id, 'name' => 'Performance Jersey', 'sizes' => ['jersey' => 'AS'], 'qty' => 1]
            ],
            'status'             => 'Submitted',
            'payment_status'     => 'pending',
        ]);

        $response = $this->actingAs($coach)->post(route('coach.order.update', $order), [
            'athlete_first_name' => 'Taylor',
            'athlete_last_name'  => 'Swift',
            'edit_pin'           => '8888',
            'items'              => [
                0 => [
                    'id'    => $item->id,
                    'name'  => 'Performance Jersey',
                    'sizes' => ['jersey' => 'AM'],
                    'qty'   => 1,
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $order->refresh();
        $this->assertEquals('8888', $order->edit_pin);
        $this->assertEquals('coach', $order->edited_by);
    }

    public function test_admin_can_update_edit_pin()
    {
        [$coach, $store, $item] = $this->createApprovedStore();
        $admin = User::factory()->create(['role' => 'admin']);

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Chris',
            'athlete_last_name'  => 'Paul',
            'parent_email'       => 'chris@example.com',
            'parent_phone'       => '5552223333',
            'edit_pin'           => '1212',
            'items_json'         => [
                ['id' => $item->id, 'name' => 'Performance Jersey', 'sizes' => ['jersey' => 'AS'], 'qty' => 1]
            ],
            'status'             => 'Submitted',
            'payment_status'     => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.order.update', $order), [
            'athlete_first_name' => 'Chris',
            'athlete_last_name'  => 'Paul',
            'edit_pin'           => '9090',
            'items'              => [
                0 => [
                    'id'    => $item->id,
                    'name'  => 'Performance Jersey',
                    'sizes' => ['jersey' => 'AXL'],
                    'qty'   => 1,
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $order->refresh();
        $this->assertEquals('9090', $order->edit_pin);
        $this->assertEquals('admin', $order->edited_by);
    }

    public function test_parent_cannot_update_sizes_when_store_is_closed()
    {
        [$coach, $store, $item] = $this->createApprovedStore();
        $store->update(['status' => 'submitted_to_admin']);

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Morgan',
            'parent_email'       => 'alex@example.com',
            'parent_phone'       => '5559876543',
            'edit_pin'           => '1234',
            'items_json'         => [
                ['id' => $item->id, 'name' => 'Performance Jersey', 'sizes' => ['jersey' => 'AS'], 'qty' => 1]
            ],
            'status'             => 'Submitted',
            'payment_status'     => 'pending',
        ]);

        $updateResp = $this->postJson(route('store.order.update-sizes', ['slug' => $store->slug, 'order' => $order->id]), [
            'pin'   => '1234',
            'items' => [
                0 => [
                    'sizes' => ['jersey' => 'AL']
                ]
            ],
        ]);

        $updateResp->assertStatus(422);
        $updateResp->assertJson(['success' => false]);
    }
}
