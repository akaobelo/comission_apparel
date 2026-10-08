<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentOrder extends Model
{
    protected static $designsCache = null;
    protected static $designsById = null;
    protected static $designsByName = null;
    protected static $designsByNormalized = null;

    protected $fillable = [
        'team_store_id',
        'athlete_first_name',
        'athlete_last_name',
        'parent_email',
        'parent_phone',
        'gender',
        'jersey_name',
        'jersey_number',
        'backpack_name',
        'shipping_address',
        'items_json',
        'special_notes',
        'edit_pin',
        'status',
        'is_edited',
        'edited_by',
        'total_retail_price',
        'payment_status',
        'subtotal',
        'tax_amount',
        'fee_amount',
        'shipping_amount',
        'shipping_method',
        'total_paid',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'user_id',
        'batch_id',
        'is_archived',
    ];

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isOnlineOrder(): bool
    {
        return in_array($this->payment_status, ['pending', 'paid', 'failed']);
    }

    public function getAthleteNameAttribute()
    {
        return $this->athlete_first_name . ' ' . $this->athlete_last_name;
    }

    public function getCalculatedSubtotal($store = null): float
    {
        if ($this->isPaid() && (float) $this->subtotal > 0) {
            return (float) $this->subtotal;
        }

        $store = $store ?: $this->teamStore;
        $items = is_array($this->items_json) ? $this->items_json : [];
        $total = 0.0;

        foreach ($items as $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $prices = self::getItemPrices($item, $store);
            $total += ($prices['retail_price'] * $qty);
        }

        if ($total > 0) {
            return (float) $total;
        }

        if ((float) $this->subtotal > 0) {
            return (float) $this->subtotal;
        }

        return (float) ($this->total_retail_price ?? 0);
    }

    protected $casts = [
        'items_json' => 'array',
        'is_edited'  => 'boolean',
        'total_retail_price' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'fee_amount'      => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_paid'      => 'decimal:2',
        'paid_at'         => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function initDesignsCache()
    {
        if (self::$designsCache === null) {
            self::$designsCache = \App\Models\DesignCatalog::all();
            self::$designsById = [];
            self::$designsByName = [];
            self::$designsByNormalized = [];

            foreach (self::$designsCache as $d) {
                self::$designsById[$d->id] = $d;
                if ($d->name) {
                    self::$designsByName[$d->name] = $d;
                    $norm = str_replace(' ', '', strtolower($d->name));
                    self::$designsByNormalized[$norm] = $d;
                }
            }
        }
    }

    public static function getItemPrices($item, $store = null)
    {
        $itemId = isset($item['id']) ? (int) $item['id'] : null;
        $itemName = $item['name'] ?? null;

        if ($store) {
            $storeItem = $itemId ? $store->items->firstWhere('id', $itemId) : null;
            if ($storeItem) {
                return [
                    'retail_price'    => (float) $storeItem->retail_price,
                    'wholesale_price' => (float) $storeItem->wholesale_price,
                ];
            }
        }

        // Fast O(1) hashmap lookup to avoid N*M loop bottleneck
        self::initDesignsCache();

        $design = null;
        if ($itemId && isset(self::$designsById[$itemId])) {
            $design = self::$designsById[$itemId];
        } elseif ($itemName) {
            if (isset(self::$designsByName[$itemName])) {
                $design = self::$designsByName[$itemName];
            } else {
                $norm = str_replace(' ', '', strtolower($itemName));
                $design = self::$designsByNormalized[$norm] ?? null;
            }
        }

        if ($design) {
            return [
                'retail_price'    => (float) $design->wholesale_price,
                'wholesale_price' => (float) $design->wholesale_price,
            ];
        }

        // Fallback: check if unit_price or price was stored directly on the item snapshot
        if (isset($item['unit_price']) || isset($item['price'])) {
            $savedPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
            return [
                'retail_price'    => $savedPrice,
                'wholesale_price' => 0.0,
            ];
        }

        return [
            'retail_price'    => 0.0,
            'wholesale_price' => 0.0,
        ];
    }

    public static function calculateBatchFinancials($orders, $store = null)
    {
        $totalSales = 0;
        $totalWholesale = 0;
        $totalItemsSold = 0;
        $totalTax = 0;
        $totalFees = 0;
        $totalOnlinePaid = 0;

        foreach ($orders as $order) {
            $orderTotal = 0;
            $orderWholesaleTotal = 0;
            $orderItemsCount = 0;
            $items = is_array($order->items_json) ? $order->items_json : [];

            if ($order->isPaid()) {
                // For PAID orders: use the locked historical financial snapshot
                $orderTotal = (float) ($order->subtotal ?? 0);
                foreach ($items as $orderedItem) {
                    $qty = max(1, (int) ($orderedItem['qty'] ?? 1));
                    $orderItemsCount += $qty;
                    $prices = self::getItemPrices($orderedItem, $store);
                    $orderWholesaleTotal += ($prices['wholesale_price'] * $qty);
                }
                $orderTax = (float) ($order->tax_amount ?? 0);
                $orderFees = (float) ($order->fee_amount ?? 0);
                $orderOnlinePaid = (float) ($order->total_paid ?? 0);
            } else {
                // For UNPAID / PENDING orders: dynamically recalculate from current store item prices
                foreach ($items as $orderedItem) {
                    $qty = max(1, (int) ($orderedItem['qty'] ?? 1));
                    $orderItemsCount += $qty;

                    $prices = self::getItemPrices($orderedItem, $store);
                    $retailPrice = (float) $prices['retail_price'];
                    $wholesalePrice = (float) $prices['wholesale_price'];

                    $orderTotal += ($retailPrice * $qty);
                    $orderWholesaleTotal += ($wholesalePrice * $qty);
                }

                $isTaxExempt = (bool) ($store?->isTaxExempt() || $order->user?->is_tax_exempt);
                $taxRate = $isTaxExempt ? 0.00 : (float) config('services.stripe.tax_rate', 0.075);
                $orderTax = $isTaxExempt ? 0.00 : round($orderTotal * $taxRate, 2);

                if ($orderTotal > 0 && ($store?->isOnlinePayment() || $order->isOnlineOrder())) {
                    $feePercent = (float) config('services.stripe.fee_percent', 0.029);
                    $feeFixed = (float) config('services.stripe.fee_fixed', 0.30);
                    $preFeeTotal = $orderTotal + $orderTax;
                    $orderOnlinePaid = round(($preFeeTotal + $feeFixed) / (1 - $feePercent), 2);
                    $orderFees = round($orderOnlinePaid - $preFeeTotal, 2);
                } else {
                    $orderFees = 0.0;
                    $orderOnlinePaid = 0.0;
                }
            }

            $totalSales += $orderTotal;
            $totalWholesale += $orderWholesaleTotal;
            $totalItemsSold += $orderItemsCount;
            $totalTax += $orderTax;
            $totalFees += $orderFees;
            $totalOnlinePaid += $orderOnlinePaid;
        }

        $ordersCount = count($orders);

        return [
            'orders_count'        => $ordersCount,
            'total_sales'         => $totalSales,
            'total_wholesale'     => $totalWholesale,
            'net_proceeds'        => $totalSales - $totalWholesale,
            'coach_profit_owed'   => max(0, $totalSales - $totalWholesale),
            'total_tax'           => $totalTax,
            'total_fees'          => $totalFees,
            'total_online_paid'   => $totalOnlinePaid,
            'total_items_sold'    => $totalItemsSold,
            'average_order_value' => $ordersCount > 0 ? $totalSales / $ordersCount : 0,
        ];
    }
}
