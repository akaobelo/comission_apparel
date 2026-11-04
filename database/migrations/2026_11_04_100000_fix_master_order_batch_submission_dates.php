<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parent_orders') || !Schema::hasColumn('parent_orders', 'submitted_at')) {
            return;
        }

        // 1. For Direct Order batches where paid_at is newer than submitted_at (because submitted_at held draft time),
        // align submitted_at to the batch's actual payment/submission timestamp.
        $directBatches = DB::table('parent_orders')
            ->whereNotNull('batch_id')
            ->where('order_type', 'direct')
            ->select(
                'batch_id',
                DB::raw('MAX(paid_at) as max_paid_at'),
                DB::raw('MAX(submitted_at) as max_submitted_at'),
                DB::raw('MAX(created_at) as max_created_at')
            )
            ->groupBy('batch_id')
            ->get();

        foreach ($directBatches as $batch) {
            $trueSubmittedAt = $batch->max_paid_at && (!$batch->max_submitted_at || $batch->max_paid_at > $batch->max_submitted_at)
                ? $batch->max_paid_at
                : ($batch->max_submitted_at ?? $batch->max_paid_at ?? $batch->max_created_at);

            if ($trueSubmittedAt) {
                DB::table('parent_orders')
                    ->where('batch_id', $batch->batch_id)
                    ->update(['submitted_at' => $trueSubmittedAt]);
            }
        }

        // 2. For Master Order (Team Store) batches, determine the true Master Order submission timestamp:
        //    a) Check the database `notifications` table for the `MasterOrderSubmitted` notification for that store
        //    b) Otherwise, if the batch status is still 'Submitted to Admin', use MAX(updated_at) of the batch
        //    c) Otherwise, use the batch's MAX(COALESCE(submitted_at, paid_at, created_at)) so all rows in the batch share a single submission date
        $storeBatches = DB::table('parent_orders')
            ->whereNotNull('batch_id')
            ->whereNotNull('team_store_id')
            ->where(function ($q) {
                $q->whereNull('order_type')->orWhere('order_type', '!=', 'direct');
            })
            ->select(
                'batch_id',
                'team_store_id',
                DB::raw('MAX(status) as batch_status'),
                DB::raw('MAX(submitted_at) as max_submitted_at'),
                DB::raw('MAX(paid_at) as max_paid_at'),
                DB::raw('MAX(created_at) as max_created_at'),
                DB::raw('MAX(updated_at) as max_updated_at')
            )
            ->groupBy('batch_id', 'team_store_id')
            ->get();

        $hasNotificationsTable = Schema::hasTable('notifications');

        foreach ($storeBatches as $batch) {
            $store = DB::table('team_stores')->where('id', $batch->team_store_id)->first();
            $notificationSubmittedAt = null;

            if ($hasNotificationsTable && $store && !empty($store->name)) {
                $needle = "Store '{$store->name}' has submitted their master order for production.";
                $notificationSubmittedAt = DB::table('notifications')
                    ->where('type', 'App\\Notifications\\MasterOrderSubmitted')
                    ->where('data', 'like', '%' . addcslashes($needle, '%_\\') . '%')
                    ->max('created_at');
            }

            $fallbackSubmittedAt = $batch->max_submitted_at ?? $batch->max_paid_at ?? $batch->max_created_at;

            if ($notificationSubmittedAt && (!$fallbackSubmittedAt || $notificationSubmittedAt > $fallbackSubmittedAt)) {
                $trueSubmittedAt = $notificationSubmittedAt;
            } elseif ($batch->batch_status === 'Submitted to Admin' && $batch->max_updated_at && (!$fallbackSubmittedAt || $batch->max_updated_at > $fallbackSubmittedAt)) {
                $trueSubmittedAt = $batch->max_updated_at;
            } else {
                $trueSubmittedAt = $fallbackSubmittedAt;
            }

            if ($trueSubmittedAt) {
                DB::table('parent_orders')
                    ->where('batch_id', $batch->batch_id)
                    ->update(['submitted_at' => $trueSubmittedAt]);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive data backfill
    }
};
