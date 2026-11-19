<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill total_paid for paid online parent orders that were left at $0.00
     * after submitOrder stopped pre-populating total_paid prior to Stripe confirmation.
     */
    public function up(): void
    {
        DB::table('parent_orders')
            ->where('payment_status', 'paid')
            ->where(function ($q) {
                $q->whereNull('total_paid')->orWhere('total_paid', '<=', 0);
            })
            ->where(function ($q) {
                $q->whereNull('deposit_credit_applied')->orWhere('deposit_credit_applied', '<=', 0);
            })
            ->where('subtotal', '>', 0)
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    $computed = round(
                        (float) ($order->subtotal ?? 0)
                        + (float) ($order->tax_amount ?? 0)
                        + (float) ($order->fee_amount ?? 0),
                        2
                    );

                    if ($computed > 0) {
                        DB::table('parent_orders')
                            ->where('id', $order->id)
                            ->update(['total_paid' => $computed]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Data backfill migration — no destructive rollback.
    }
};
