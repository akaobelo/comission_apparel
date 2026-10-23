<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ParentOrder;

class RestoreHistoricalRepOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:restore-historical-reps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restores historical orders that were mistakenly attributed to newer sales reps upon coach assignment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting check for mistakenly attributed historical orders...');

        $reps = User::whereIn('role', ['sales_rep', 'partner', 'agent'])->get();

        if ($reps->count() <= 1) {
            $this->info('Only one or zero sales reps exist. No cross-rep historical conflicts found.');
            return 0;
        }

        $restoredCount = 0;

        foreach ($reps as $rep) {
            if (!$rep->created_at) {
                continue;
            }

            $mistakenOrders = ParentOrder::where('sales_rep_id', $rep->id)
                ->where('created_at', '<', $rep->created_at->copy()->subMinutes(10))
                ->get();

            if ($mistakenOrders->isEmpty()) {
                continue;
            }

            $originalRep = User::whereIn('role', ['sales_rep', 'partner', 'agent'])
                ->where('id', '!=', $rep->id)
                ->where(function($q) {
                    $q->where('name', 'like', '%Earl%')
                      ->orWhere('first_name', 'like', '%Earl%')
                      ->orWhere('email', 'like', '%earl%');
                })
                ->first()
                ?? User::whereIn('role', ['sales_rep', 'partner', 'agent'])
                    ->where('id', '!=', $rep->id)
                    ->orderBy('id', 'asc')
                    ->first();

            if (!$originalRep) {
                $this->warn("No original sales rep found to restore orders for rep {$rep->name} (ID: {$rep->id}).");
                continue;
            }

            $rate = $originalRep->getEffectiveCommissionRate();

            foreach ($mistakenOrders as $order) {
                $order->sales_rep_id = $originalRep->id;
                $order->calculateAndApplyCommission($rate);
                $order->save();
                $restoredCount++;
                $this->line("Restored Order #{$order->order_number} (ID: {$order->id}) -> Rep: {$originalRep->name} (ID: {$originalRep->id})");
            }
        }

        $this->info("Completed! Restored {$restoredCount} orders back to their original sales rep.");
        return 0;
    }
}
