<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\TeamStore;
use App\Models\ParentOrder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find GB All-Stars and any past stores whose deadline expired months ago with unbatched orders
        $stores = TeamStore::where('slug', 'like', '%gb-allstars%')
            ->orWhere(function ($query) {
                $query->whereNotNull('order_deadline')
                      ->where('order_deadline', '<', now()->subDays(30));
            })
            ->get();

        foreach ($stores as $store) {
            $unbatched = ParentOrder::where('team_store_id', $store->id)
                ->whereNull('batch_id')
                ->where('is_archived', false);

            if ($unbatched->count() > 0) {
                $batchId = (string) Str::uuid();
                $unbatched->update([
                    'batch_id' => $batchId,
                    'status' => 'Submitted to Admin',
                    'payment_status' => 'not_applicable',
                ]);

                if ($store->status === 'approved') {
                    $store->update(['status' => 'submitted_to_admin']);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data alignment
    }
};
