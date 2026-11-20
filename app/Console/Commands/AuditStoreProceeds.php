<?php

namespace App\Console\Commands;

use App\Models\ParentOrder;
use App\Models\TeamStore;
use Illuminate\Console\Command;

class AuditStoreProceeds extends Command
{
    protected $signature = 'stores:audit-proceeds {names?* : Store name search terms (e.g. "Enloe" "Independence" "Track Masterz")} {--all : Audit all stores with orders} {--include-archived : Include archived orders in the audit} {--unpaid : List every unpaid / non-Stripe order with athlete name and totals}';

    protected $description = 'Read-only financial audit of store sales, wholesale (Due to TCA), recorded manufacturing_cost, and Store Profit Owed';

    public function handle(): int
    {
        $names = $this->argument('names');
        $includeArchived = (bool) $this->option('include-archived');

        $query = TeamStore::with(['items', 'parentOrders' => function ($q) use ($includeArchived) {
            if (!$includeArchived) {
                $q->where('is_archived', false);
            }
            $q->where('status', '!=', 'Pending Payment')->orderBy('id');
        }]);

        if (!empty($names)) {
            $query->where(function ($q) use ($names) {
                foreach ($names as $term) {
                    $q->orWhere('name', 'like', '%' . trim($term) . '%');
                }
            });
        } elseif (!$this->option('all')) {
            $this->error('Provide one or more store names (e.g. php artisan stores:audit-proceeds "Enloe" "Independence" "Track Masterz") or pass --all.');
            return self::FAILURE;
        }

        $stores = $query->orderBy('id')->get();

        if ($stores->isEmpty()) {
            $this->warn('No matching stores found.');
            return self::SUCCESS;
        }

        foreach ($stores as $store) {
            $orders = $store->parentOrders;
            if ($this->option('all') && $orders->isEmpty()) {
                continue;
            }

            $this->newLine();
            $this->info(str_repeat('=', 80));
            $this->info("STORE #{$store->id}: {$store->name}");
            $this->line(sprintf(
                'Payment Mode: %s | Tax Exempt: %s | Status: %s | Orders: %d',
                strtoupper((string) $store->payment_mode),
                $store->isTaxExempt() ? 'YES' : 'NO',
                $store->status,
                $orders->count()
            ));

            // 1. Current store item pricing
            $itemRows = $store->items->map(function ($si) {
                $w = (float) $si->wholesale_price;
                $r = (float) $si->retail_price;
                return [
                    $si->id,
                    $si->name,
                    '$' . number_format($w, 2),
                    '$' . number_format($r, 2),
                    '$' . number_format($r - $w, 2),
                ];
            })->toArray();

            if (!empty($itemRows)) {
                $this->table(['Item ID', 'Store Item Name', 'Wholesale', 'Retail', 'Markup'], $itemRows);
            }

            // 2. Per-order breakdown & discrepancy detection
            $sumSubtotal = 0.0;
            $sumItemWholesale = 0.0;
            $sumRecordedMfg = 0.0;
            $sumTax = 0.0;
            $sumFee = 0.0;
            $sumRawPaid = 0.0;
            $sumItemsQty = 0;
            $flags = [];

            foreach ($orders as $order) {
                $items = is_array($order->items_json) ? $order->items_json : [];
                $orderItemWholesale = 0.0;

                foreach ($items as $it) {
                    $qty = max(1, (int) ($it['qty'] ?? 1));
                    $sumItemsQty += $qty;
                    $prices = ParentOrder::getItemPrices($it, $store);
                    $orderItemWholesale += ($prices['wholesale_price'] * $qty);

                    $itId = isset($it['id']) ? (int) $it['id'] : null;
                    $itName = $it['name'] ?? '(unnamed)';
                    $matchedInStore = $store->items->contains(fn($si) => ($itId && $si->id === $itId) || strcasecmp(trim($si->name), trim($itName)) === 0);
                    if (!$matchedInStore) {
                        $flags[] = "Order #{$order->id} ({$order->athlete_name}): item \"{$itName}\" (id={$itId}) is NOT in current store_items (resolved wholesale=\$" . number_format($prices['wholesale_price'], 2) . ").";
                    }
                }

                $sub = (float) ($order->subtotal > 0 ? $order->subtotal : $order->total_retail_price);
                $mfg = (float) ($order->manufacturing_cost ?? 0);
                $tax = (float) ($order->tax_amount ?? 0);
                $fee = (float) ($order->fee_amount ?? 0);
                $rawPaid = (float) $order->getRawOriginal('total_paid');

                $sumSubtotal += $sub;
                $sumItemWholesale += $orderItemWholesale;
                $sumRecordedMfg += $mfg;
                if ($order->isPaid()) {
                    $sumTax += $tax;
                    $sumFee += $fee;
                    $sumRawPaid += $rawPaid;

                    $expectedPaid = round($sub + $tax + $fee - (float) ($order->deposit_credit_applied ?? 0), 2);
                    if ($rawPaid > 0 && abs($rawPaid - $expectedPaid) > 0.05) {
                        $flags[] = sprintf(
                            'Order #%d (%s): raw total_paid ($%.2f) != subtotal+tax+fee ($%.2f)',
                            $order->id,
                            $order->athlete_name,
                            $rawPaid,
                            $expectedPaid
                        );
                    }
                }

                if ($mfg > 0 && abs($mfg - $orderItemWholesale) > 0.05) {
                    $flags[] = sprintf(
                        'Order #%d (%s): recorded manufacturing_cost ($%.2f) != current item wholesale ($%.2f) [diff: %+.2f]',
                        $order->id,
                        $order->athlete_name,
                        $mfg,
                        $orderItemWholesale,
                        $mfg - $orderItemWholesale
                    );
                }
            }

            $fin = ParentOrder::calculateBatchFinancials($orders, $store);

            $this->table(
                ['Metric', 'Amount'],
                [
                    ['Total Items Ordered', (string) $sumItemsQty],
                    ['Total Retail Sales (sum of order subtotals)', '$' . number_format($sumSubtotal, 2)],
                    ['Current Store-Items Wholesale (recalculated)', '$' . number_format($sumItemWholesale, 2)],
                    ['Recorded manufacturing_cost (sum on orders)', '$' . number_format($sumRecordedMfg, 2)],
                    ['System Total Sales (calculateBatchFinancials)', '$' . number_format($fin['total_sales'], 2)],
                    ['System Due to TCA (calculateBatchFinancials)', '$' . number_format($fin['total_wholesale'], 2)],
                    ['System Store Profit Owed (Proceeds)', '$' . number_format($fin['coach_profit_owed'], 2)],
                    ['Item-Based Proceeds (Sales - Item Wholesale)', '$' . number_format(max(0, $sumSubtotal - $sumItemWholesale), 2)],
                    ['Sales Tax Collected', '$' . number_format($fin['total_tax'], 2)],
                    ['Stripe Processing Fees', '$' . number_format($fin['total_fees'], 2)],
                    ['Online Payment (Stripe) — System', '$' . number_format($fin['total_online_paid'], 2)],
                    ['Online Payment (Stripe) — Raw DB sum', '$' . number_format($sumRawPaid, 2)],
                ]
            );

            if (empty($flags)) {
                $this->info('STATUS: CLEAN — No discrepancies detected between orders, store items, and proceeds.');
            } else {
                $this->warn('FLAGS DETECTED (' . count($flags) . '):');
                foreach ($flags as $flag) {
                    $this->line('  - ' . $flag);
                }
            }

            if ($this->option('unpaid')) {
                $unpaidRows = [];
                $unpaidSubTotal = 0.0;
                $unpaidWhTotal = 0.0;

                foreach ($orders as $order) {
                    if ($order->isPaid()) {
                        continue;
                    }
                    $items = is_array($order->items_json) ? $order->items_json : [];
                    $itemSummary = collect($items)->map(fn($it) => (($it['qty'] ?? 1) . 'x ' . ($it['name'] ?? 'Item')))->implode(', ');
                    $orderWh = 0.0;
                    foreach ($items as $it) {
                        $qty = max(1, (int) ($it['qty'] ?? 1));
                        $orderWh += (ParentOrder::getItemPrices($it, $store)['wholesale_price'] * $qty);
                    }
                    $sub = (float) ($order->subtotal > 0 ? $order->subtotal : $order->total_retail_price);
                    $unpaidSubTotal += $sub;
                    $unpaidWhTotal += $orderWh;

                    $unpaidRows[] = [
                        '#' . $order->id,
                        trim($order->athlete_name),
                        $order->parent_email ?? '—',
                        $order->status,
                        $order->payment_status ?? '—',
                        '$' . number_format($sub, 2),
                        '$' . number_format($orderWh, 2),
                        $order->created_at?->format('M d, Y') ?? '—',
                        $itemSummary,
                    ];
                }

                if (empty($unpaidRows)) {
                    $this->info('UNPAID ORDERS: None — all orders in this store are marked paid.');
                } else {
                    $this->warn(sprintf(
                        'UNPAID / NON-STRIPE ORDERS (%d) — Retail: $%.2f | Wholesale: $%.2f:',
                        count($unpaidRows),
                        $unpaidSubTotal,
                        $unpaidWhTotal
                    ));
                    $this->table(
                        ['Order', 'Athlete', 'Parent Email', 'Status', 'Payment', 'Retail', 'Wholesale', 'Date', 'Items'],
                        $unpaidRows
                    );
                }
            }
        }

        return self::SUCCESS;
    }
}
