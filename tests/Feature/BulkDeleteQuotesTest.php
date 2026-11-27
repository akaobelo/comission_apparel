<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDeleteQuotesTest extends TestCase
{
    use RefreshDatabase;

    private function createQuote(string $orgName): QuoteRequest
    {
        return QuoteRequest::create([
            'first_name'         => 'Coach',
            'last_name'          => 'Test',
            'position_title'     => 'Head Coach',
            'organization_name'  => $orgName,
            'email'              => strtolower(str_replace(' ', '', $orgName)) . '@example.com',
            'phone'              => '5551234567',
            'apparel_category'   => 'Track & Field',
            'estimated_quantity' => 30,
            'package_type'       => 'full_program_bundle',
            'status'             => 'addressed',
        ]);
    }

    public function test_admin_can_bulk_delete_selected_quote_inquiries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $q1 = $this->createQuote('Wade Hampton Generals');
        $q2 = $this->createQuote('Sparkle City Star');
        $q3 = $this->createQuote('Next Level Track Club');

        $response = $this->actingAs($admin)
            ->from('/admin/dashboard?tab=quote_inquiries&quote_page=2')
            ->delete(route('admin.quotes.bulk-delete'), [
                'quote_ids' => [$q1->id, $q2->id],
            ]);

        $response->assertRedirect('/admin/dashboard?tab=quote_inquiries&quote_page=2');
        $response->assertSessionHas('success', '2 quote inquiries deleted successfully.');

        $this->assertDatabaseMissing('quote_requests', ['id' => $q1->id]);
        $this->assertDatabaseMissing('quote_requests', ['id' => $q2->id]);
        $this->assertDatabaseHas('quote_requests', ['id' => $q3->id]);
    }

    public function test_bulk_delete_requires_at_least_one_valid_quote_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->delete(route('admin.quotes.bulk-delete'), [
                'quote_ids' => [],
            ]);

        $response->assertSessionHasErrors('quote_ids');
    }

    public function test_non_admin_cannot_bulk_delete_quote_inquiries(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $q1 = $this->createQuote('Protected Lead');

        $response = $this->actingAs($coach)
            ->delete(route('admin.quotes.bulk-delete'), [
                'quote_ids' => [$q1->id],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quote_requests', ['id' => $q1->id]);
    }

    public function test_admin_dashboard_renders_bulk_delete_controls_on_quote_inquiries_tab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createQuote('Sample Lead Org');

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard', ['tab' => 'quote_inquiries']));

        $response->assertOk();
        $response->assertSee('Select All on Page (1)');
        $response->assertSee(route('admin.quotes.bulk-delete'), false);
    }
}
