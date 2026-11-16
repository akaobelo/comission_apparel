<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRepAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function createQuoteRequest(array $overrides = []): QuoteRequest
    {
        return QuoteRequest::create(array_merge([
            'first_name'         => 'Chris',
            'last_name'          => 'Martinez',
            'position_title'     => 'Head Coach',
            'email'              => 'chris@example.com',
            'phone'              => '555-123-4567',
            'organization_name'  => 'Alamance Penguins XC',
            'apparel_category'   => 'Track and Field',
            'estimated_quantity' => 25,
            'status'             => 'new',
        ], $overrides));
    }

    public function test_admin_can_assign_sales_rep_to_quote_inquiry_singular_route()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $rep = User::factory()->create([
            'first_name' => 'Yohando',
            'last_name'  => 'Robinson',
            'role'       => 'sales_rep',
        ]);

        $quote = $this->createQuoteRequest();

        $response = $this->actingAs($admin)->postJson(route('admin.quote.assign-rep', $quote), [
            'sales_rep_id' => $rep->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success'      => true,
                'sales_rep_id' => $rep->id,
                'sales_rep'    => 'Yohando Robinson',
            ]);

        $quote->refresh();
        $this->assertEquals($rep->id, $quote->sales_rep_id);
        $this->assertEquals('Yohando Robinson', $quote->sales_rep);
    }

    public function test_admin_can_assign_sales_rep_to_quote_inquiry_plural_route()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $rep = User::factory()->create([
            'first_name' => 'Yohando',
            'last_name'  => 'Robinson',
            'role'       => 'sales_rep',
        ]);

        $quote = $this->createQuoteRequest();

        // Plural URL that was invoked in the dashboard JS
        $response = $this->actingAs($admin)->postJson("/admin/quotes/{$quote->id}/assign-rep", [
            'sales_rep_id' => $rep->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success'      => true,
                'sales_rep_id' => $rep->id,
                'sales_rep'    => 'Yohando Robinson',
            ]);

        $quote->refresh();
        $this->assertEquals($rep->id, $quote->sales_rep_id);
        $this->assertEquals('Yohando Robinson', $quote->sales_rep);
    }

    public function test_admin_can_unassign_sales_rep_from_quote_inquiry()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $rep = User::factory()->create([
            'first_name' => 'Yohando',
            'last_name'  => 'Robinson',
            'role'       => 'sales_rep',
        ]);

        $quote = $this->createQuoteRequest([
            'sales_rep_id' => $rep->id,
            'sales_rep'    => 'Yohando Robinson',
        ]);

        $response = $this->actingAs($admin)->postJson("/admin/quote/{$quote->id}/assign-rep", [
            'sales_rep_id' => null,
        ]);

        $response->assertOk()
            ->assertJson([
                'success'      => true,
                'sales_rep_id' => null,
                'sales_rep'    => null,
            ]);

        $quote->refresh();
        $this->assertNull($quote->sales_rep_id);
        $this->assertNull($quote->sales_rep);
    }

    public function test_non_admin_cannot_assign_quote_sales_rep()
    {
        $user = User::factory()->create([
            'role' => 'coach',
        ]);

        $quote = $this->createQuoteRequest();

        $response = $this->actingAs($user)->post("/admin/quote/{$quote->id}/assign-rep", [
            'sales_rep_id' => 1,
        ]);

        $response->assertRedirect('/login');
    }
}
