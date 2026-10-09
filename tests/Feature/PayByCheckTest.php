<?php

namespace Tests\Feature;

use App\Models\ParentOrder;
use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayByCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_set_payment_mode_to_check()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'High School Track Team',
            'slug' => 'high-school-track-team',
            'status' => 'approved',
            'pricing_approved' => true,
            'payment_mode' => 'in_house',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.store.payment-mode', $store), [
            'payment_mode' => 'check',
        ]);

        $response->assertRedirect();
        $this->assertEquals('check', $store->fresh()->payment_mode);
        $this->assertTrue($store->fresh()->isCheckPayment());
        $this->assertFalse($store->fresh()->isOnlinePayment());
        $this->assertFalse($store->fresh()->isInHousePayment());
    }

    public function test_coach_cannot_override_check_payment_mode()
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'State Championship Team',
            'slug' => 'state-championship-team',
            'status' => 'approved',
            'pricing_approved' => true,
            'payment_mode' => 'check',
        ]);

        $response = $this->actingAs($coach)->post(route('coach.store.description', $store), [
            'payment_mode' => 'online',
        ]);

        $response->assertRedirect();
        $this->assertEquals('check', $store->fresh()->payment_mode);
    }

    public function test_coach_can_submit_master_order_with_waived_payment_requirements()
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $store = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Lincoln High School',
            'slug' => 'lincoln-high-school',
            'status' => 'approved',
            'pricing_approved' => true,
            'payment_mode' => 'check',
        ]);

        $order = ParentOrder::create([
            'team_store_id' => $store->id,
            'athlete_first_name' => 'John',
            'athlete_last_name' => 'Doe',
            'parent_email' => 'parent@example.com',
            'gender' => 'Male',
            'items_json' => [['name' => 'Jersey', 'qty' => 1, 'retail_price' => 50]],
            'status' => 'Submitted',
            'payment_status' => 'pending',
            'subtotal' => 50.00,
            'total_paid' => 50.00,
        ]);

        $response = $this->actingAs($coach)->post(route('coach.store.submit', $store), [
            'shipping_address' => '123 High School Rd, Dallas, TX 75001',
        ]);

        $response->assertRedirect(route('coach.dashboard'));
        $this->assertEquals('submitted_to_admin', $store->fresh()->status);
        $this->assertEquals('Submitted to Admin', $order->fresh()->status);
        $this->assertEquals('not_applicable', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->batch_id);
    }
}
