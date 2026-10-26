<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ParentOrder;
use App\Models\TeamStore;

class SyncSalesRepAssignmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-sales-rep-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes and repairs sales partner client assignments, clears mistakenly attributed historical orders from reps, and syncs active direct batches.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sales partner assignment and order sync...');

        // 1. Ensure sales_rep_assigned_at is set for all assigned clients
        $assignedCoaches = User::whereNotNull('sales_rep_id')->get();
        foreach ($assignedCoaches as $coach) {
            if (!$coach->sales_rep_assigned_at) {
                $coach->sales_rep_assigned_at = $coach->updated_at ?: $coach->created_at ?: now();
                $coach->save();
                $this->info("Set sales_rep_assigned_at for coach {$coach->name} ({$coach->id}) to {$coach->sales_rep_assigned_at}");
            }
        }

        // 2. Identify sales reps
        $earl = User::where('role', 'sales_rep')
            ->where(function($q) {
                $q->where('first_name', 'like', '%Earl%')
                  ->orWhere('name', 'like', '%Earl%');
            })->first();

        $ernie = User::where('role', 'sales_rep')
            ->where(function($q) {
                $q->where('first_name', 'like', '%Ernie%')
                  ->orWhere('name', 'like', '%Ernie%');
            })->first();

        $yohanda = User::where('role', 'sales_rep')
            ->where(function($q) {
                $q->where('first_name', 'like', '%Yohanda%')
                  ->orWhere('name', 'like', '%Yohanda%');
            })->first();

        // 3. Heal Eli West / UnityDriven batch -> Yohanda Robinson
        $eliWest = User::where('name', 'like', '%Eli West%')
            ->orWhere('organization', 'like', '%Unity%')
            ->first();

        if ($eliWest && $yohanda) {
            $eliWest->sales_rep_id = $yohanda->id;
            $eliWest->sales_rep = $yohanda->name;
            if (!$eliWest->sales_rep_assigned_at) {
                $eliWest->sales_rep_assigned_at = now();
            }
            $eliWest->save();

            TeamStore::where('user_id', $eliWest->id)->update(['sales_rep_id' => $yohanda->id]);

            // Sync Eli's active / unpaid orders to Yohanda
            $yohandaRate = $yohanda->getEffectiveCommissionRate();
            $eliOrders = ParentOrder::where('user_id', $eliWest->id)
                ->where('commission_status', '!=', 'paid')
                ->get();

            foreach ($eliOrders as $ord) {
                $ord->sales_rep_id = $yohanda->id;
                $ord->calculateAndApplyCommission($yohandaRate);
                $ord->save();
            }
            $this->info("Aligned Eli West / UnityDriven active orders to Yohanda Robinson @ {$yohandaRate}%");
        }

        // 4. Heal Akeem Oliver / Police Training College batch -> Ernie Barr
        $akeem = User::where('name', 'like', '%Akeem Oliver%')
            ->orWhere('organization', 'like', '%Police Training%')
            ->first();

        if ($akeem && $ernie) {
            $akeem->sales_rep_id = $ernie->id;
            $akeem->sales_rep = $ernie->name;
            if (!$akeem->sales_rep_assigned_at) {
                $akeem->sales_rep_assigned_at = now();
            }
            $akeem->save();

            TeamStore::where('user_id', $akeem->id)->update(['sales_rep_id' => $ernie->id]);

            $ernieRate = $ernie->getEffectiveCommissionRate();
            $akeemOrders = ParentOrder::where('user_id', $akeem->id)
                ->where('commission_status', '!=', 'paid')
                ->get();

            foreach ($akeemOrders as $ord) {
                $ord->sales_rep_id = $ernie->id;
                $ord->calculateAndApplyCommission($ernieRate);
                $ord->save();
            }
            $this->info("Aligned Akeem Oliver / Police Training College active orders to Ernie Barr @ {$ernieRate}%");
        }

        // Also check batch 1c76e444-7ef5-4089-94c5-62c3e30f4a3e specifically
        $batch = ParentOrder::where('batch_id', '1c76e444-7ef5-4089-94c5-62c3e30f4a3e')->get();
        if ($batch->isNotEmpty() && $ernie) {
            $ernieRate = $ernie->getEffectiveCommissionRate();
            foreach ($batch as $ord) {
                $ord->sales_rep_id = $ernie->id;
                $ord->calculateAndApplyCommission($ernieRate);
                $ord->save();
            }
            $this->info("Batch 1c76e444-7ef5-4089-94c5-62c3e30f4a3e assigned to Ernie Barr @ {$ernieRate}%");
        }

        // 5. Clean up Order 1025 / William Bazemore / M&B Track Club from Yohanda
        $order1025 = ParentOrder::find(1025);
        if ($order1025 && $yohanda && $order1025->sales_rep_id == $yohanda->id) {
            $order1025->sales_rep_id = null;
            $order1025->save();
            $this->info("Order #1025 unlinked from Yohanda Robinson (historical order prior to assignment)");
        }

        // 6. Generic sync for all coaches and active batches:
        // Ensure that any active (unpaid) batch matches the coach's assigned sales rep
        $activeBatches = ParentOrder::whereNotNull('batch_id')
            ->where('status', '!=', 'Draft')
            ->where('commission_status', '!=', 'paid')
            ->get()
            ->groupBy('batch_id');

        foreach ($activeBatches as $batchId => $bOrders) {
            $first = $bOrders->first();
            $coach = $first?->user;
            if ($coach && $coach->sales_rep_id) {
                $rep = User::find($coach->sales_rep_id);
                if ($rep) {
                    $repRate = $rep->getEffectiveCommissionRate();
                    foreach ($bOrders as $ord) {
                        if ($ord->sales_rep_id !== $rep->id || (float)$ord->applied_rep_commission_rate !== (float)$repRate) {
                            $ord->sales_rep_id = $rep->id;
                            $ord->calculateAndApplyCommission($repRate);
                            $ord->save();
                        }
                    }
                }
            }
        }

        // 7. Clear mistakenly attributed historical orders from reps
        // If an order was placed BEFORE the client was assigned to the rep, and is NOT an active direct batch:
        // Clear sales_rep_id so it doesn't inflate the rep's Qualifying Sales or past batches.
        foreach ($assignedCoaches as $coach) {
            if (!$coach->sales_rep_assigned_at) continue;

            $pastOrders = ParentOrder::where('user_id', $coach->id)
                ->where('created_at', '<', $coach->sales_rep_assigned_at)
                ->where('sales_rep_id', $coach->sales_rep_id)
                ->where(function($q) {
                    $q->where('is_archived', true)
                      ->orWhereNull('batch_id')
                      ->orWhere('commission_status', 'paid');
                })
                ->get();

            foreach ($pastOrders as $pOrd) {
                $pOrd->sales_rep_id = null;
                $pOrd->save();
                $this->info("Cleared mistakenly stamped sales_rep_id on historical Order #{$pOrd->id} for coach {$coach->name}");
            }
        }

        // 8. Unlink mistakenly stamped orders from Earl Smith:
        // Any order stamped with Earl's ID where the coach is NOT assigned to Earl,
        // or where the coach is assigned to another sales partner (e.g. Yohanda or Ernie):
        if ($earl) {
            $earlCoachIds = User::where('sales_rep_id', $earl->id)->pluck('id')->toArray();

            $leakedOrders = ParentOrder::where('sales_rep_id', $earl->id)
                ->where(function($q) use ($earlCoachIds) {
                    $q->whereNotIn('user_id', $earlCoachIds)
                      ->orWhereNull('user_id');
                })
                ->get();

            foreach ($leakedOrders as $lOrd) {
                $actualCoach = $lOrd->user ?? ($lOrd->teamStore ? $lOrd->teamStore->user : null);

                // If coach belongs to another sales rep (e.g. Yohanda or Ernie) and this is an active batch, align it
                if ($actualCoach && $actualCoach->sales_rep_id && $actualCoach->sales_rep_id != $earl->id && !empty($lOrd->batch_id) && $lOrd->commission_status !== 'paid') {
                    $actualRep = User::find($actualCoach->sales_rep_id);
                    if ($actualRep) {
                        $lOrd->sales_rep_id = $actualRep->id;
                        $lOrd->calculateAndApplyCommission($actualRep->getEffectiveCommissionRate());
                        $lOrd->save();
                        $this->info("Realigned Order #{$lOrd->id} from Earl to {$actualRep->name}");
                        continue;
                    }
                }

                // Otherwise, this is historical past activity that belongs exclusively to Super Admin lifetime archive
                $lOrd->sales_rep_id = null;
                $lOrd->save();
                $this->info("Cleared mistakenly stamped Earl sales_rep_id on Order #{$lOrd->id}");
            }
        }

        $this->info('Sales partner assignment sync completed successfully!');
        return Command::SUCCESS;
    }
}
