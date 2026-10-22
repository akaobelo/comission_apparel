<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\StoreItem;
use App\Models\DesignCatalog;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Automatically restore wholesale_price and retail_price on store_items
     * that were set to null or zero during drag-and-drop sort operations.
     */
    public function up(): void
    {
        $items = StoreItem::with('designCatalog', 'components')
            ->where(function ($query) {
                $query->whereNull('wholesale_price')
                    ->orWhere('wholesale_price', '<=', 0)
                    ->orWhereNull('retail_price')
                    ->orWhere('retail_price', '<=', 0);
            })
            ->get();

        foreach ($items as $item) {
            $catalog = $item->designCatalog;
            if (!$catalog && !empty($item->name)) {
                $catalog = DesignCatalog::where('name', $item->name)->first();
            }

            $newWholesale = $item->wholesale_price;
            $newRetail = $item->retail_price;

            if ($catalog && (float) $catalog->wholesale_price > 0) {
                if ($newWholesale === null || (float) $newWholesale <= 0) {
                    $newWholesale = (float) $catalog->wholesale_price;
                }
                if ($newRetail === null || (float) $newRetail <= 0) {
                    $newRetail = (float) $catalog->wholesale_price;
                }
            }

            if (($newWholesale === null || (float) $newWholesale <= 0) && $item->components->isNotEmpty()) {
                $componentWholesaleSum = $item->components->sum(function ($comp) {
                    return (float) ($comp->wholesale_price ?? $comp->designCatalog?->wholesale_price ?? 0);
                });
                $componentRetailSum = $item->components->sum(function ($comp) {
                    return (float) ($comp->retail_price ?? $comp->wholesale_price ?? $comp->designCatalog?->wholesale_price ?? 0);
                });

                if ($componentWholesaleSum > 0) {
                    $newWholesale = $componentWholesaleSum;
                }
                if ($componentRetailSum > 0) {
                    $newRetail = $componentRetailSum;
                }
            }

            if (($newRetail === null || (float) $newRetail <= 0) && (float) $newWholesale > 0) {
                $newRetail = $newWholesale;
            }

            if ((float) $newWholesale > 0 || (float) $newRetail > 0) {
                $item->update([
                    'wholesale_price' => $newWholesale,
                    'retail_price'    => $newRetail,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration required
    }
};
