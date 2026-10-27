<?php

namespace Tests\Feature;

use App\Mail\CoachRegisteredNotification;
use App\Mail\OrderSubmittedNotification;
use App\Mail\StoreRequestedNotification;
use App\Models\ParentOrder;
use App\Models\TeamStore;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailNotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_assigned_rep_receive_master_order_submission()
    {
        Mail::fake();

        $rep = User::factory()->create([
            'role'       => 'sales_rep',
            'email'      => 'agent.smith@test.com',
            'first_name' => 'Agent',
            'last_name'  => 'Smith',
        ]);

        $coach = User::factory()->create([
            'role'         => 'coach',
            'email'        => 'coach.carter@test.com',
            'sales_rep_id' => $rep->id,
            'organization' => 'Richmond Oilers',
        ]);

        $store = TeamStore::create([
            'user_id'      => $coach->id,
            'sales_rep_id' => $rep->id,
            'name'         => 'Oilers Basketball 2026',
            'slug'         => 'oilers-basketball-2026',
            'status'       => 'approved',
            'payment_mode' => 'in_house',
        ]);

        $batchId = (string) Str::uuid();

        ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'batch_id'           => $batchId,
            'status'             => 'Submitted to Admin',
            'subtotal'           => 500.00,
            'total_paid'         => 500.00,
            'athlete_first_name' => 'Timo',
            'athlete_last_name'  => 'Cruz',
            'items_json'         => [['name' => 'Jersey', 'qty' => 1, 'retail_price' => 500]],
        ]);

        EmailNotificationService::notifyMasterOrderSubmitted($store, $batchId);

        // Verify Super Admins received notification
        Mail::assertSent(OrderSubmittedNotification::class, function ($mail) {
            return $mail->hasTo('Luke@speedcapital.com') 
                && !$mail->hasTo('Info@TheCommissionApparel.com')
                && $mail->recipientType === 'admin';
        });

        // Verify Assigned Sales Rep received notification
        Mail::assertSent(OrderSubmittedNotification::class, function ($mail) use ($rep) {
            return $mail->hasTo($rep->email) && $mail->recipientType === 'sales_rep';
        });

        // Verify email rendered HTML contains key details
        $mailable = new OrderSubmittedNotification([
            'type_label'        => 'Master Order Batch',
            'batch_or_order_id' => substr($batchId, 0, 8),
            'organization'      => 'Richmond Oilers',
            'store_name'        => 'Oilers Basketball 2026',
            'coach_name'        => 'Coach Carter',
            'coach_email'       => 'coach.carter@test.com',
            'total_revenue'     => 500.00,
            'order_count'       => 1,
            'payment_mode'      => 'In-House Collection',
        ], 'admin');

        $html = $mailable->render();
        $this->assertStringContainsString('Richmond Oilers', $html);
        $this->assertStringContainsString('500.00', $html);
    }

    public function test_unassigned_order_notifies_super_admin_only()
    {
        Mail::fake();

        $rep = User::factory()->create([
            'role'  => 'sales_rep',
            'email' => 'unrelated.agent@test.com',
        ]);

        $coach = User::factory()->create([
            'role'         => 'coach',
            'sales_rep_id' => null,
            'organization' => 'Solo Eagles',
        ]);

        $store = TeamStore::create([
            'user_id'      => $coach->id,
            'sales_rep_id' => null,
            'name'         => 'Eagles Track 2026',
            'slug'         => 'eagles-track-2026',
            'status'       => 'approved',
            'payment_mode' => 'in_house',
        ]);

        $batchId = (string) Str::uuid();

        ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'batch_id'           => $batchId,
            'status'             => 'Submitted to Admin',
            'subtotal'           => 350.00,
            'athlete_first_name' => 'Speedy',
            'athlete_last_name'  => 'Gonzales',
            'items_json'         => [['name' => 'Track Jacket', 'qty' => 1, 'retail_price' => 350]],
        ]);

        EmailNotificationService::notifyMasterOrderSubmitted($store, $batchId);

        // Super Admin received
        Mail::assertSent(OrderSubmittedNotification::class, function ($mail) {
            return $mail->hasTo('Luke@speedcapital.com') && $mail->recipientType === 'admin';
        });

        // Unrelated sales rep did NOT receive
        Mail::assertNotSent(OrderSubmittedNotification::class, function ($mail) use ($rep) {
            return $mail->hasTo($rep->email);
        });
    }

    public function test_coach_registration_notifies_super_admin_and_referred_rep()
    {
        Mail::fake();

        $rep = User::factory()->create([
            'role'          => 'sales_rep',
            'email'         => 'pat.partner@test.com',
            'referral_slug' => 'pat-partner',
        ]);

        $response = $this->post('/register', [
            'first_name'            => 'John',
            'last_name'             => 'Wooden',
            'organization'          => 'UCLA Bruins',
            'email'                 => 'wooden@ucla.edu',
            'email_confirmation'    => 'wooden@ucla.edu',
            'phone'                 => '555-0199',
            'sport'                 => 'Basketball',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'sales_rep'             => 'pat-partner',
        ]);

        $response->assertRedirect('/coach/dashboard');

        // Super Admins received registration alert
        Mail::assertSent(CoachRegisteredNotification::class, function ($mail) {
            return $mail->hasTo('Luke@speedcapital.com') 
                && !$mail->hasTo('Info@TheCommissionApparel.com')
                && $mail->recipientType === 'admin';
        });

        // Referred Rep received registration alert
        Mail::assertSent(CoachRegisteredNotification::class, function ($mail) use ($rep) {
            return $mail->hasTo($rep->email) && $mail->recipientType === 'sales_rep';
        });
    }

    public function test_coach_registration_without_rep_notifies_admin_only()
    {
        Mail::fake();

        $rep = User::factory()->create([
            'role'  => 'sales_rep',
            'email' => 'other.rep@test.com',
        ]);

        $response = $this->post('/register', [
            'first_name'            => 'Phil',
            'last_name'             => 'Jackson',
            'organization'          => 'Chicago Bulls',
            'email'                 => 'phil@bulls.com',
            'email_confirmation'    => 'phil@bulls.com',
            'phone'                 => '555-0188',
            'sport'                 => 'Basketball',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'sales_rep'             => '',
        ]);

        $response->assertRedirect('/coach/dashboard');

        // Admin got it
        Mail::assertSent(CoachRegisteredNotification::class, function ($mail) {
            return $mail->hasTo('Luke@speedcapital.com') && $mail->recipientType === 'admin';
        });

        // Other rep did NOT get it
        Mail::assertNotSent(CoachRegisteredNotification::class, function ($mail) use ($rep) {
            return $mail->hasTo($rep->email);
        });
    }

    public function test_store_creation_notifies_admin_and_assigned_rep()
    {
        Mail::fake();

        $rep = User::factory()->create([
            'role'  => 'sales_rep',
            'email' => 'sam.rep@test.com',
        ]);

        $coach = User::factory()->create([
            'role'         => 'coach',
            'sales_rep_id' => $rep->id,
            'organization' => 'Lincoln High',
        ]);

        $this->actingAs($coach);

        $response = $this->post('/coach/store', [
            'name'         => 'Lincoln Patriots Track',
            'sport'        => 'Track & Field',
            'package_type' => 'individual',
            'payment_mode' => 'in_house',
        ]);

        $response->assertRedirect();

        // Admin received
        Mail::assertSent(StoreRequestedNotification::class, function ($mail) {
            return $mail->hasTo('Luke@speedcapital.com') 
                && !$mail->hasTo('Info@TheCommissionApparel.com')
                && $mail->recipientType === 'admin';
        });

        // Assigned rep received
        Mail::assertSent(StoreRequestedNotification::class, function ($mail) use ($rep) {
            return $mail->hasTo($rep->email) && $mail->recipientType === 'sales_rep';
        });
    }

    public function test_parent_order_submission_notifies_admin_and_assigned_rep()
    {
        Mail::fake();

        $rep = User::factory()->create([
            'role'  => 'sales_rep',
            'email' => 'jenny.rep@test.com',
        ]);

        $coach = User::factory()->create([
            'role'         => 'coach',
            'sales_rep_id' => $rep->id,
            'organization' => 'Oakland Tech',
        ]);

        $store = TeamStore::create([
            'user_id'      => $coach->id,
            'sales_rep_id' => $rep->id,
            'name'         => 'Oakland Tech Volleyball',
            'slug'         => 'oakland-tech-volleyball',
            'status'       => 'approved',
            'payment_mode' => 'in_house',
        ]);

        $order = ParentOrder::create([
            'team_store_id'      => $store->id,
            'user_id'            => $coach->id,
            'sales_rep_id'       => $rep->id,
            'athlete_first_name' => 'Maya',
            'athlete_last_name'  => 'Moore',
            'parent_name'        => 'Kathryn Moore',
            'parent_email'       => 'kmoore@example.com',
            'parent_phone'       => '555-4433',
            'status'             => 'Submitted',
            'subtotal'           => 120.00,
            'total_paid'         => 120.00,
            'items_json'         => [['name' => 'Volleyball Jersey', 'qty' => 1, 'retail_price' => 120]],
        ]);

        EmailNotificationService::notifyParentOrderSubmitted($order);

        // Verify Super Admins received
        Mail::assertSent(OrderSubmittedNotification::class, function ($mail) {
            return $mail->hasTo('Luke@speedcapital.com') 
                && !$mail->hasTo('Info@TheCommissionApparel.com')
                && $mail->recipientType === 'admin';
        });

        // Verify Assigned Sales Rep received
        Mail::assertSent(OrderSubmittedNotification::class, function ($mail) use ($rep) {
            return $mail->hasTo($rep->email) && $mail->recipientType === 'sales_rep';
        });
    }
}
