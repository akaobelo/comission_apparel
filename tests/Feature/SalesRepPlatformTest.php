<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TeamStore;
use App\Models\ParentOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SalesRepPlatformTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can create a sales rep with custom tier and commission rate.
     */
    public function test_admin_can_create_and_manage_sales_partner()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/admin/sales-reps', [
            'first_name'       => 'Marcus',
            'last_name'        => 'Robinson',
            'email'            => 'marcus@partner.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'partner_category' => 'authorized_business',
            'commission_rate'  => 40.0,
            'referral_slug'    => 'marcus-apparel',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email'            => 'marcus@partner.com',
            'role'             => 'sales_rep',
            'partner_category' => 'authorized_business',
            'commission_rate'  => 40.0,
            'referral_slug'    => 'marcus-apparel',
        ]);
    }

    /**
     * Test coach registration via referral slug links to the sales rep.
     */
    public function test_coach_registration_via_referral_slug_links_sales_rep()
    {
        $rep = User::factory()->create([
            'first_name'       => 'Marcus',
            'last_name'        => 'Robinson',
            'role'             => 'sales_rep',
            'commission_rate'  => 40.0,
            'referral_slug'    => 'marcus',
        ]);

        $response = $this->post('/register', [
            'first_name'            => 'Coach',
            'last_name'             => 'Carter',
            'email'                 => 'carter@richmond.edu',
            'email_confirmation'    => 'carter@richmond.edu',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'organization'          => 'Richmond High',
            'phone'                 => '555-123-4567',
            'sport'                 => 'Basketball',
            'sales_rep'             => 'marcus',
        ]);

        $response->assertRedirect('/coach/dashboard');
        $this->assertDatabaseHas('users', [
            'email'        => 'carter@richmond.edu',
            'sales_rep_id' => $rep->id,
            'sales_rep'    => 'Marcus Robinson',
        ]);
    }

    /**
     * Test rep portal access and client creation.
     */
    public function test_sales_rep_can_access_dashboard_and_create_client()
    {
        $rep = User::factory()->create([
            'role'             => 'sales_rep',
            'commission_rate'  => 50.0,
            'referral_slug'    => 'speed-rep',
        ]);

        $response = $this->actingAs($rep)->get('/rep/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Authorized Sales Partner');

        // Create client via rep portal
        $postResponse = $this->actingAs($rep)->post('/rep/client', [
            'first_name'   => 'John',
            'last_name'    => 'Wooden',
            'organization' => 'Bruins Athletics',
            'phone'        => '555-987-6543',
            'sport'        => 'Basketball',
            'email'        => 'wooden@ucla.edu',
            'password'     => 'BruinChampion1!',
        ]);

        $postResponse->assertRedirect(route('rep.dashboard'));
        $this->assertDatabaseHas('users', [
            'email'        => 'wooden@ucla.edu',
            'sales_rep_id' => $rep->id,
            'role'         => 'coach',
        ]);
    }

    /**
     * Test sales rep can log out successfully.
     */
    public function test_sales_rep_can_logout()
    {
        $rep = User::factory()->create([
            'role' => 'sales_rep',
        ]);

        $response = $this->actingAs($rep)->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test manual tariff, fee deduction, and net margin calculation based on Luke's red notes.
     */
    public function test_luke_manual_tariffs_and_net_commission_calculation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rep = User::factory()->create([
            'role'            => 'sales_rep',
            'commission_rate' => 40.0, // 40% Tier
        ]);

        $coach = User::factory()->create([
            'role'         => 'coach',
            'sales_rep_id' => $rep->id,
        ]);

        // Client orders $1000.00 worth of products
        $order = ParentOrder::create([
            'user_id'            => $coach->id,
            'sales_rep_id'       => $rep->id,
            'athlete_first_name' => 'Michael',
            'athlete_last_name'  => 'Jordan',
            'subtotal'           => 1000.00,
            'total_paid'         => 1000.00,
            'status'             => 'Processing',
            'order_type'         => 'direct',
            'items_json'         => [],
        ]);

        // Luke enters manufacturing cost $500, tariff $75, fee deduction $0
        // Net Margin = 1000 - 500 - 75 = $425.00
        // Rep Cut (40%) = $425.00 * 0.40 = $170.00
        $response = $this->actingAs($admin)->post("/admin/order/{$order->id}/financials", [
            'manufacturing_cost'          => 500.00,
            'tariff_amount'               => 75.00,
            'fee_deduction'               => 0.00,
            'fundraising_amount'          => 0.00,
            'applied_rep_commission_rate' => 40.0,
            'commission_status'           => 'cleared',
            'financial_notes'             => 'Overseas import tariff applied',
        ]);

        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals(425.00, (float)$order->net_margin);
        $this->assertEquals(170.00, (float)$order->rep_commission_earned);
        $this->assertEquals('cleared', $order->commission_status);
        $this->assertEquals(40.0, (float)$order->applied_rep_commission_rate);
    }

    /**
     * Test admin can view sales partner profile with assigned clients and ledger.
     */
    public function test_admin_can_view_sales_partner_profile()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rep = User::factory()->create([
            'first_name'      => 'Marcus',
            'last_name'       => 'Robinson',
            'role'            => 'sales_rep',
            'commission_rate' => 50.0,
            'referral_slug'   => 'marcus',
        ]);

        $coach = User::factory()->create([
            'role'         => 'coach',
            'sales_rep_id' => $rep->id,
            'organization' => 'Westlake High',
        ]);

        $response = $this->actingAs($admin)->get("/admin/sales-reps/{$rep->id}");

        $response->assertOk();
        $response->assertSee('Marcus Robinson');
        $response->assertSee('Westlake High');
        $response->assertSee('Partner Profile');
    }

    /**
     * Test rep only receives orders placed on or after client assignment date.
     */
    public function test_rep_only_receives_orders_placed_on_or_after_assignment_date()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rep = User::factory()->create([
            'role'            => 'sales_rep',
            'commission_rate' => 30.0,
            'created_at'      => now(),
        ]);

        $coach = User::factory()->create([
            'role'                  => 'coach',
            'sales_rep_id'          => $rep->id,
            'sales_rep_assigned_at' => now(),
        ]);

        // Historical order placed BEFORE coach was assigned to this rep
        $oldOrder = ParentOrder::create([
            'user_id'            => $coach->id,
            'athlete_first_name' => 'John',
            'athlete_last_name'  => 'Doe',
            'subtotal'           => 1000.00,
            'total_paid'         => 1000.00,
            'status'             => 'Processing',
            'order_type'         => 'direct',
            'is_archived'        => true,
            'items_json'         => [],
            'sales_rep_id'       => null,
        ]);
        $oldOrder->created_at = now()->subDays(10);
        $oldOrder->save(['timestamps' => false]);

        // New order placed AFTER coach was assigned to this rep
        $newOrder = ParentOrder::create([
            'user_id'            => $coach->id,
            'athlete_first_name' => 'Jane',
            'athlete_last_name'  => 'Doe',
            'subtotal'           => 500.00,
            'total_paid'         => 500.00,
            'status'             => 'Processing',
            'order_type'         => 'direct',
            'is_archived'        => false,
            'items_json'         => [],
            'sales_rep_id'       => $rep->id,
        ]);
        $newOrder->created_at = now()->addHour();
        $newOrder->save(['timestamps' => false]);

        $scopedOrders = ParentOrder::getOrdersForSalesRep($rep);

        $this->assertFalse($scopedOrders->contains('id', $oldOrder->id), 'Old historical order should not be scoped to rep');
        $this->assertTrue($scopedOrders->contains('id', $newOrder->id), 'New order after assignment should be scoped to rep');
    }

    /**
     * Test reassigning coach to new rep transfers active orders and recalculates commission rate.
     */
    public function test_reassigning_coach_transfers_active_orders_and_recalculates_rate()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $oldRep = User::factory()->create(['role' => 'sales_rep', 'commission_rate' => 85.0]);
        $newRep = User::factory()->create(['role' => 'sales_rep', 'commission_rate' => 30.0]);

        $coach = User::factory()->create([
            'role'                  => 'coach',
            'organization'          => 'Track Stars',
            'phone'                 => '1234567890',
            'sport'                 => 'Track',
            'status'                => 'active',
            'sales_rep_id'          => $oldRep->id,
            'sales_rep_assigned_at' => now()->subDay(),
        ]);

        $order = ParentOrder::create([
            'user_id'                     => $coach->id,
            'athlete_first_name'          => 'Tim',
            'athlete_last_name'           => 'Duncan',
            'subtotal'                    => 1000.00,
            'total_paid'                  => 1000.00,
            'manufacturing_cost'          => 500.00,
            'net_margin'                  => 500.00,
            'applied_rep_commission_rate' => 85.0,
            'rep_commission_earned'       => 425.00,
            'sales_rep_id'                => $oldRep->id,
            'commission_status'           => 'pending',
            'status'                      => 'Processing',
            'order_type'                  => 'direct',
            'items_json'                  => [],
        ]);

        $response = $this->actingAs($admin)->post("/admin/coach/{$coach->id}/update", [
            'first_name'   => $coach->first_name,
            'last_name'    => $coach->last_name,
            'email'        => $coach->email,
            'organization' => $coach->organization,
            'phone'        => $coach->phone,
            'sport'        => $coach->sport,
            'status'       => $coach->status,
            'sales_rep_id' => $newRep->id,
        ]);

        $response->assertRedirect();

        $coach->refresh();
        $this->assertEquals($newRep->id, $coach->sales_rep_id);

        $order->refresh();
        $this->assertEquals($newRep->id, $order->sales_rep_id);
        $this->assertEquals(30.0, (float)$order->applied_rep_commission_rate);
        $this->assertEquals(150.00, (float)$order->rep_commission_earned);
    }

    /**
     * Test direct batch explicitly assigned to rep reflects on profile even if created before assignment date.
     */
    public function test_batch_explicitly_assigned_to_rep_reflects_on_rep_profile()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rep = User::factory()->create([
            'role'            => 'sales_rep',
            'commission_rate' => 85.0,
            'created_at'      => now(),
        ]);

        $coach = User::factory()->create([
            'role'                  => 'coach',
            'sales_rep_id'          => $rep->id,
            'sales_rep_assigned_at' => now(),
        ]);

        // A direct batch order created 3 days ago, but explicitly assigned to rep
        $batchId = 'batch-' . uniqid();
        $batchOrder = ParentOrder::create([
            'batch_id'                    => $batchId,
            'user_id'                     => $coach->id,
            'athlete_first_name'          => 'Speed',
            'athlete_last_name'           => 'Racer',
            'subtotal'                    => 1000.00,
            'total_paid'                  => 1000.00,
            'manufacturing_cost'          => 150.00,
            'net_margin'                  => 850.00,
            'applied_rep_commission_rate' => 85.0,
            'rep_commission_earned'       => 722.50,
            'sales_rep_id'                => $rep->id,
            'commission_status'           => 'cleared',
            'status'                      => 'Processing',
            'order_type'                  => 'direct',
            'is_archived'                 => true,
            'items_json'                  => [],
        ]);
        $batchOrder->created_at = now()->subDays(3);
        $batchOrder->save(['timestamps' => false]);

        $scopedOrders = ParentOrder::getOrdersForSalesRep($rep);
        $this->assertTrue($scopedOrders->contains('id', $batchOrder->id), 'Explicitly assigned batch should be included for rep');

        $response = $this->actingAs($admin)->get("/admin/sales-reps/{$rep->id}");
        $response->assertStatus(200);
        $response->assertSee('722.50');
    }
}

