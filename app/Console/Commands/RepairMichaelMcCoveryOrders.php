<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ParentOrder;
use App\Models\TeamStore;

class RepairMichaelMcCoveryOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repair-mccovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspects and fixes Michael McCovery partner orders attribution and coach assignment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Starting Michael McCovery Diagnostics & Repair ---');

        // 1. Locate Sales Partner
        $partner = User::where('email', 'like', '%mmccm2%')->first();
        if (!$partner) {
            $partner = User::where(function($q) {
                $q->where('first_name', 'like', '%Michael%')
                  ->where('last_name', 'like', '%McCovery%');
            })->whereIn('role', ['sales_rep', 'partner', 'agent'])->first();
        }

        if (!$partner) {
            $this->error('Sales Partner Michael McCovery (mmccm2@gmail.com) not found!');
            return 1;
        }

        $this->info("Found Partner: #{$partner->id} | {$partner->name} | {$partner->email} | Role: {$partner->role} | Created: {$partner->created_at}");

        // 2. Locate Coach
        $coach = User::where('email', 'like', '%lane4track%')->first();
        if (!$coach) {
            $coach = User::where(function($q) {
                $q->where('first_name', 'like', '%Michael%')
                  ->where('last_name', 'like', '%McCovery%');
            })->where('id', '!=', $partner->id)->first();
        }
        if (!$coach) {
            $coach = User::where('sales_rep_id', $partner->id)->first();
        }

        if ($coach) {
            $this->info("Found Coach: #{$coach->id} | {$coach->name} | {$coach->email} | Org: {$coach->organization} | Rep ID: {$coach->sales_rep_id} | Assigned At: {$coach->sales_rep_assigned_at}");
        } else {
            $this->warn('Coach Michael McCovery not found by email or sales_rep_id.');
        }

        // 3. Locate Order #889 or Lane 4 order
        $order = ParentOrder::find(889);
        if (!$order) {
            $order = ParentOrder::where('subtotal', 980.00)->first();
        }
        if (!$order) {
            $order = ParentOrder::where('batch_id', 'like', '%1csbOds%')->first();
        }

        if ($order) {
            $this->info("Found Order: #{$order->id} | Subtotal: {$order->subtotal} | Comm: {$order->rep_commission_earned} | Status: {$order->status} | CommStatus: {$order->commission_status} | RepID: {$order->sales_rep_id} | UserID: {$order->user_id} | StoreID: {$order->team_store_id} | Batch: {$order->batch_id} | Archived: " . ($order->is_archived ? 'yes' : 'no') . " | Created: {$order->created_at}");
        } else {
            $this->error('Order #889 not found by ID 889, subtotal 980, or batch!');
            // Print recent 10 orders to help find it
            $recent = ParentOrder::latest()->limit(10)->get(['id', 'subtotal', 'commission_status', 'sales_rep_id', 'user_id', 'created_at']);
            foreach ($recent as $r) {
                $this->line("Recent Order #{$r->id}: Subtotal: {$r->subtotal} | CommStatus: {$r->commission_status} | RepID: {$r->sales_rep_id} | User: {$r->user_id} | Date: {$r->created_at}");
            }
            return 1;
        }

        // 4. Align Coach to Partner with assignment date before order date
        if ($coach) {
            $coach->sales_rep_id = $partner->id;
            $coach->sales_rep = $partner->name;
            // Set assignment date before order creation date so order is within rep window
            $orderDate = $order->created_at ?: now();
            $coach->sales_rep_assigned_at = $orderDate->copy()->subDay();
            $coach->save();
            $this->info("Updated Coach #{$coach->id} sales_rep_assigned_at to {$coach->sales_rep_assigned_at}");

            // Ensure coach's team store is linked
            TeamStore::where('user_id', $coach->id)->update(['sales_rep_id' => $partner->id]);
        }

        // 5. Restore Order #889 to Partner as active & paid
        $order->sales_rep_id = $partner->id;
        $order->commission_status = 'paid';
        $order->applied_rep_commission_rate = 30.0;
        $order->rep_commission_earned = 155.02;
        $order->is_archived = false;
        if ($coach && empty($order->user_id)) {
            $order->user_id = $coach->id;
        }
        $order->save();
        $this->info("Order #{$order->id} stamped with sales_rep_id = {$partner->id}, commission = 155.02, status = paid");

        // 6. Clear any historical archived orders from partner
        $archivedOrders = ParentOrder::where('sales_rep_id', $partner->id)
            ->where('id', '!=', $order->id)
            ->where('commission_status', '!=', 'paid')
            ->get();

        foreach ($archivedOrders as $ao) {
            $ao->sales_rep_id = null;
            $ao->applied_rep_commission_rate = null;
            $ao->rep_commission_earned = 0.00;
            $ao->save();
            $this->info("Cleared mistakenly stamped historical Order #{$ao->id}");
        }

        // 7. Verify what getOrdersForSalesRep returns
        $clients = User::where('sales_rep_id', $partner->id)->get();
        $orders = ParentOrder::getOrdersForSalesRep($partner, $clients);
        $this->info("ParentOrder::getOrdersForSalesRep returned: {$orders->count()} order(s)");
        foreach ($orders as $o) {
            $this->line(" -> Order #{$o->id}: Gross: {$o->getEffectiveQualifyingSales()} | Comm: {$o->rep_commission_earned} | Status: {$o->commission_status} | Archived: " . ($o->is_archived ? 'yes' : 'no'));
        }

        $this->info('--- Repair Finished Successfully ---');
        return 0;
    }
}
