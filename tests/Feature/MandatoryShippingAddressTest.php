<?php

namespace Tests\Feature;

use App\Models\ParentOrder;
use App\Models\StoreItem;
use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MandatoryShippingAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_store_order_allows_optional_shipping_address()
    {
        Mail::fake();
        Notification::fake();

        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Shipping Test Store',
            'slug'             => 'shipping-test-store',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'in_house',
        ]);
        $item = StoreItem::create([
            'team_store_id'   => $store->id,
            'name'            => 'Game Jersey',
            'type'            => 'jersey',
            'types'           => ['jersey'],
            'wholesale_price' => 30.00,
            'retail_price'    => 45.00,
        ]);

        // Missing shipping_address should succeed for parent/athlete team store orders
        $missingResponse = $this->post(route('store.order.submit', $store->slug), [
            'athlete_first_name' => 'Jordan',
            'athlete_last_name'  => 'Smith',
            'parent_email'       => 'parent@example.com',
            'parent_phone'       => '555-123-4567',
            'gender'             => 'Mens / Boys',
            'edit_pin'           => '1234',
            'items'              => [
                $item->id => [
                    'selected' => '1',
                    'qty'      => 1,
                    'sizes'    => ['jersey' => 'AM'],
                ],
            ],
        ]);

        $missingResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('parent_orders', [
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Jordan',
            'shipping_address'   => null,
        ]);

        // Providing shipping_address optionally should also succeed and persist trimmed value
        $validResponse = $this->post(route('store.order.submit', $store->slug), [
            'athlete_first_name' => 'Taylor',
            'athlete_last_name'  => 'Smith',
            'parent_email'       => 'parent2@example.com',
            'parent_phone'       => '555-123-4568',
            'shipping_address'   => '  123 Main St, Tampa, FL 33602  ',
            'gender'             => 'Mens / Boys',
            'edit_pin'           => '5678',
            'items'              => [
                $item->id => [
                    'selected' => '1',
                    'qty'      => 1,
                    'sizes'    => ['jersey' => 'AM'],
                ],
            ],
        ]);

        $validResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('parent_orders', [
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Taylor',
            'shipping_address'   => '123 Main St, Tampa, FL 33602',
        ]);
    }

    public function test_coach_master_order_requires_shipping_address()
    {
        Mail::fake();
        Notification::fake();

        $coach = User::factory()->create(['role' => 'coach']);
        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Master Shipping Store',
            'slug'             => 'master-shipping-store',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'check',
        ]);

        ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_first_name' => 'Alex',
            'athlete_last_name'  => 'Taylor',
            'parent_email'       => 'alex@example.com',
            'parent_phone'       => '555-222-3333',
            'shipping_address'   => '100 First St, Orlando, FL 32801',
            'status'             => 'Submitted',
            'payment_status'     => 'not_applicable',
            'subtotal'           => 50.00,
            'total_retail_price' => 50.00,
            'items_json'         => [['name' => 'Jersey', 'qty' => 1, 'unit_price' => 50]],
        ]);

        // Missing shipping_address should fail validation
        $missingResponse = $this->actingAs($coach)->post(route('coach.store.submit', $store), [
            'acknowledge_unpaid' => '1',
        ]);

        $missingResponse->assertSessionHasErrors('shipping_address');
        $this->assertEquals('approved', $store->fresh()->status);

        // Providing shipping_address should succeed
        $validResponse = $this->actingAs($coach)->post(route('coach.store.submit', $store), [
            'shipping_address'   => '500 Stadium Way, Tampa, FL 33602',
            'acknowledge_unpaid' => '1',
        ]);

        $validResponse->assertSessionHasNoErrors();
        $this->assertEquals('submitted_to_admin', $store->fresh()->status);
        $this->assertEquals('500 Stadium Way, Tampa, FL 33602', $store->fresh()->shipping_address);
    }

    public function test_coach_direct_order_finalization_requires_shipping_address()
    {
        Mail::fake();
        Notification::fake();

        $coach = User::factory()->create([
            'role'         => 'coach',
            'organization' => 'Tampa Track Club',
        ]);

        $store = TeamStore::create([
            'user_id'          => $coach->id,
            'name'             => 'Tampa Track Store',
            'slug'             => 'tampa-track-store',
            'status'           => 'approved',
            'pricing_approved' => true,
            'payment_mode'     => 'check',
        ]);

        $draftOrder = ParentOrder::create([
            'user_id'            => $coach->id,
            'team_store_id'      => $store->id,
            'order_type'         => 'direct',
            'athlete_first_name' => 'Chris',
            'athlete_last_name'  => 'Lee',
            'status'             => 'Draft',
            'payment_status'     => 'pending_payment',
            'subtotal'           => 60.00,
            'total_retail_price' => 60.00,
            'items_json'         => [['name' => 'Singlet', 'qty' => 2, 'unit_price' => 30.00]],
        ]);

        // Missing shipping_address should fail validation and keep draft intact
        $missingResponse = $this->actingAs($coach)->post(route('coach.direct-order.finalize'), []);

        $missingResponse->assertSessionHasErrors('shipping_address');
        $this->assertEquals('Draft', $draftOrder->fresh()->status);
        $this->assertNull($draftOrder->fresh()->batch_id);

        // Providing shipping_address should succeed and save it on the finalized order
        $validResponse = $this->actingAs($coach)->post(route('coach.direct-order.finalize'), [
            'shipping_address' => '  789 Track Blvd, Tampa, FL 33610  ',
        ]);

        $validResponse->assertSessionHasNoErrors();
        $draftOrder->refresh();
        $this->assertEquals('Submitted to Admin', $draftOrder->status);
        $this->assertNotNull($draftOrder->batch_id);
        $this->assertEquals('789 Track Blvd, Tampa, FL 33610', $draftOrder->shipping_address);
    }
}
