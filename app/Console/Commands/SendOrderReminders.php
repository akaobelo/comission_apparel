<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendOrderReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated email reminders to parents for approaching store deadlines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get stores that have a deadline in exactly 3 days or exactly 1 day.
        $stores = \App\Models\TeamStore::whereIn('status', ['approved', 'submitted_to_admin'])
            ->whereNotNull('order_deadline')
            ->where(function ($q) {
                $q->whereDate('order_deadline', now()->addDays(3)->toDateString())
                  ->orWhereDate('order_deadline', now()->addDays(1)->toDateString());
            })
            ->with('rosters')
            ->get();

        $emailsSent = 0;
        $smsSent = 0;

        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioFrom = env('TWILIO_PHONE_NUMBER');
        
        $twilioClient = null;
        if ($twilioSid && $twilioToken && $twilioFrom) {
            $twilioClient = new \Twilio\Rest\Client($twilioSid, $twilioToken);
        }

        foreach ($stores as $store) {
            foreach ($store->rosters as $roster) {
                if (!$roster->has_ordered) {
                    
                    // Send Email
                    if ($roster->parent_email) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($roster->parent_email)
                                ->send(new \App\Mail\StoreOrderReminder($store));
                            $emailsSent++;
                        } catch (\Exception $e) {
                            $this->error("Failed to send email to {$roster->parent_email}: " . $e->getMessage());
                        }
                    }

                    // Send SMS
                    if ($roster->parent_phone && $twilioClient) {
                        try {
                            $message = "REMINDER: Ordering deadline for {$store->name} is approaching on {$store->order_deadline->format('M d, Y')}. Place your order here: " . route('store.show', $store->slug);
                            $twilioClient->messages->create(
                                $roster->parent_phone,
                                [
                                    'from' => $twilioFrom,
                                    'body' => $message
                                ]
                            );
                            $smsSent++;
                        } catch (\Exception $e) {
                            $this->error("Failed to send SMS to {$roster->parent_phone}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        $this->info("Successfully sent {$emailsSent} emails and {$smsSent} SMS order reminders.");
    }
}
