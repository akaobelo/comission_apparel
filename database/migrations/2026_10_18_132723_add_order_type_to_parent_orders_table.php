<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('parent_orders', 'order_type')) {
            Schema::table('parent_orders', function (Blueprint $table) {
                $table->string('order_type')->default('store')->index()->after('batch_id');
            });
        }

        // Mark existing unlinked orders as direct orders
        DB::table('parent_orders')
            ->whereNull('team_store_id')
            ->update(['order_type' => 'direct']);

        // Attempt heuristic matching for existing direct orders to link them to their coach's matching store
        try {
            $directOrders = \App\Models\ParentOrder::whereNull('team_store_id')
                ->whereNotNull('user_id')
                ->get();

            foreach ($directOrders as $order) {
                $user = \App\Models\User::find($order->user_id);
                if (!$user) continue;
                $stores = $user->teamStores;
                if ($stores->isEmpty()) continue;

                if ($stores->count() === 1) {
                    $order->update(['team_store_id' => $stores->first()->id]);
                    continue;
                }

                $matchedStoreId = null;
                $itemsText = json_encode($order->items_json);

                foreach ($stores as $store) {
                    // Check acronym (e.g. Meridian Christian Academy -> MCA)
                    $words = preg_split('/\s+/', trim($store->name));
                    $acronym = '';
                    foreach ($words as $w) {
                        if (strlen($w) > 0) $acronym .= strtoupper($w[0]);
                    }

                    if (strlen($acronym) >= 2 && stripos($itemsText, $acronym) !== false) {
                        $matchedStoreId = $store->id;
                        break;
                    }

                    // Check significant words in store name
                    foreach ($words as $w) {
                        if (strlen($w) >= 4 && stripos($itemsText, $w) !== false) {
                            $matchedStoreId = $store->id;
                            break 2;
                        }
                    }
                }

                if ($matchedStoreId) {
                    $order->update(['team_store_id' => $matchedStoreId]);
                }
            }
        } catch (\Throwable $e) {
            // Log but don't fail migration
            \Illuminate\Support\Facades\Log::warning('Direct order heuristic backfill note: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('parent_orders', 'order_type')) {
            Schema::table('parent_orders', function (Blueprint $table) {
                $table->dropColumn('order_type');
            });
        }
    }
};
