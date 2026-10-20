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
            'password'         => 'Password123!',
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
}

