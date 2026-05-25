<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeamStore;
use App\Services\TwilioService;
use Carbon\Carbon;

class SendStoreClosingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS reminders to parents who have not ordered 3 days and 1 day before store closure.';

    /**
     * Execute the console command.
     */
    public function handle(TwilioService $twilio)
    {
        $this->info("Checking for store closing reminders...");

        $now = Carbon::now();
        
        // Find stores that are closing in exactly 3 days or 1 day
        $stores = TeamStore::where('status', 'approved')
            ->whereNotNull('order_deadline')
            ->get();

        $count = 0;

        foreach ($stores as $store) {
            $daysLeft = $now->diffInDays($store->order_deadline, false);
            
            // Allow exact match or if they're overdue but we haven't closed yet
            if ($daysLeft === 3 || $daysLeft === 1) {
                $this->info("Sending reminders for store: {$store->name} ({$daysLeft} days left)");
                $message = $twilio->generateReminderMessage($store, $daysLeft);
                
                // Get all parents in roster who haven't ordered yet
                $orderedPhones = $store->parentOrders()->whereNotNull('parent_phone')->pluck('parent_phone')->toArray();
                $orderedEmails = $store->parentOrders()->whereNotNull('parent_email')->pluck('parent_email')->toArray();
                
                // Fetch roster for this store
                $roster = $store->rosters;
                
                foreach ($roster as $parent) {
                    // Check if they already ordered by phone or email
                    $hasOrdered = false;
                    if ($parent->parent_phone && in_array($parent->parent_phone, $orderedPhones)) {
                        $hasOrdered = true;
                    }
                    if ($parent->parent_email && in_array($parent->parent_email, $orderedEmails)) {
                        $hasOrdered = true;
                    }
                    
                    if (!$hasOrdered && !empty($parent->parent_phone)) {
                        $this->info("  -> Sending to {$parent->parent_phone}");
                        $twilio->sendSms($parent->parent_phone, $message);
                        $count++;
                    }
                }
            }
        }

        $this->info("Finished sending {$count} reminders.");
    }
}
