<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StoreItem;
use App\Models\DesignCatalog;

class RestoreStoreItemPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:restore-pricing {--dry-run : Only show what would be updated without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore wholesale and retail prices on store items that were wiped to zero or null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No database changes will be saved.');
        }

        $items = StoreItem::with('designCatalog', 'teamStore', 'components')
            ->where(function ($query) {
                $query->whereNull('wholesale_price')
                    ->orWhere('wholesale_price', '<=', 0)
                    ->orWhereNull('retail_price')
                    ->orWhere('retail_price', '<=', 0);
            })
            ->get();

        if ($items->isEmpty()) {
            $this->info('No store items found with missing or zero pricing.');
            return 0;
        }

        $this->info("Found {$items->count()} store item(s) with missing or zero pricing.");
        $restoredCount = 0;

        foreach ($items as $item) {
            $storeName = $item->teamStore ? $item->teamStore->name : "Store #{$item->team_store_id}";
            $catalog = $item->designCatalog;

            // Name-based fallback if designCatalog relation is missing
            if (!$catalog && !empty($item->name)) {
                $catalog = DesignCatalog::where('name', $item->name)->first();
            }

            $newWholesale = $item->wholesale_price;
            $newRetail = $item->retail_price;

            // 1. Try to recover from design catalog
            if ($catalog && (float) $catalog->wholesale_price > 0) {
                if ($newWholesale === null || (float) $newWholesale <= 0) {
                    $newWholesale = (float) $catalog->wholesale_price;
                }
                if ($newRetail === null || (float) $newRetail <= 0) {
                    $newRetail = (float) $catalog->wholesale_price;
                }
            }

            // 2. If it's a package and still missing pricing, check if components have prices
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

            // 3. Fallback retail to wholesale if retail is still 0
            if (($newRetail === null || (float) $newRetail <= 0) && (float) $newWholesale > 0) {
                $newRetail = $newWholesale;
            }

            if ((float) $newWholesale > 0 || (float) $newRetail > 0) {
                $this->line("Item #{$item->id} '{$item->name}' [{$storeName}]: Wholesale => \${$newWholesale}, Retail => \${$newRetail}");
                if (!$isDryRun) {
                    $item->update([
                        'wholesale_price' => $newWholesale,
                        'retail_price'    => $newRetail,
                    ]);
                }
                $restoredCount++;
            } else {
                $this->error("Item #{$item->id} '{$item->name}' [{$storeName}]: Unable to determine price automatically (no linked catalog design found).");
            }
        }

        $this->info("Successfully restored pricing for {$restoredCount} store item(s).");
        return 0;
    }
}
