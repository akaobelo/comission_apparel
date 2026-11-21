<?php

namespace App\Console\Commands;

use App\Models\ParentOrder;
use App\Models\TeamStore;
use App\Services\StripePaymentSyncService;
use Illuminate\Console\Command;

class SyncStripeOrders extends Command
{
    protected $signature = 'stripe:sync-orders
                            {--store= : TeamStore ID or slug to sync}
                            {--batch= : Batch ID to sync}
                            {--all : Sync all non-archived unpaid orders with a Stripe session or payment intent}';

    protected $description = 'Reconcile unpaid/pending ParentOrder records directly against Stripe Checkout Sessions';

    public function handle(StripePaymentSyncService $syncService): int
    {
        if (empty(config('services.stripe.secret'))) {
            $this->error('STRIPE_SECRET is not configured.');
            return self::FAILURE;
        }

        $storeOpt = $this->option('store');
        $batchOpt = $this->option('batch');
        $allOpt = $this->option('all');

        if (!$storeOpt && !$batchOpt && !$allOpt) {
            $this->error('Please specify --store=<id|slug>, --batch=<batch_id>, or --all.');
            return self::INVALID;
        }

        if ($storeOpt) {
            $store = is_numeric($storeOpt)
                ? TeamStore::find($storeOpt)
                : TeamStore::where('slug', $storeOpt)->first();

            if (!$store) {
                $this->error("TeamStore '{$storeOpt}' not found.");
                return self::FAILURE;
            }

            $this->info("Syncing unpaid Stripe orders for store: {$store->name} (#{$store->id})...");
            $summary = $syncService->syncStorePendingOrders($store);
        } elseif ($batchOpt) {
            $this->info("Syncing unpaid Stripe orders for batch: {$batchOpt}...");
            $summary = $syncService->syncBatchPendingOrders($batchOpt);
        } else {
            $orders = ParentOrder::where('is_archived', false)
                ->where(function ($q) {
                    $q->whereNull('payment_status')
                      ->orWhere('payment_status', '!=', 'paid');
                })
                ->where(function ($q) {
                    $q->whereNotNull('stripe_session_id')
                      ->orWhereNotNull('stripe_payment_intent_id');
                })
                ->get();

            $this->info("Found {$orders->count()} unpaid order(s) with Stripe references. Checking Stripe...");
            $summary = $syncService->syncOrders($orders);
        }

        $this->table(
            ['Checked', 'Marked Paid', 'Still Unpaid', 'Errors'],
            [[$summary['checked'], $summary['marked_paid'], $summary['still_unpaid'], $summary['errors']]]
        );

        if (!empty($summary['updated_order_ids'])) {
            $this->info('Updated Order IDs: ' . implode(', ', $summary['updated_order_ids']));
        }

        return self::SUCCESS;
    }
}
