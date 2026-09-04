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
        'status',
        'is_edited',
        'edited_by',
        'total_retail_price',
        'user_id',
        'batch_id',
        'is_archived',
    ];

    public function getAthleteNameAttribute()
    {
        return $this->athlete_first_name . ' ' . $this->athlete_last_name;
    }

    protected $casts = [
        'items_json' => 'array',
        'is_edited'  => 'boolean',
        'total_retail_price' => 'decimal:2',
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

        foreach ($orders as $order) {
            $orderTotal = 0;
            $orderWholesaleTotal = 0;
            $orderItemsCount = 0;
            $items = is_array($order->items_json) ? $order->items_json : [];

            foreach ($items as $orderedItem) {
                $qty = max(1, (int) ($orderedItem['qty'] ?? 1));
                $orderItemsCount += $qty;

                $prices = self::getItemPrices($orderedItem, $store);
                $retailPrice = $prices['retail_price'];
                $wholesalePrice = $prices['wholesale_price'];
                
                $orderTotal += ($retailPrice * $qty);
                $orderWholesaleTotal += ($wholesalePrice * $qty);
            }

            $totalSales += $orderTotal;
            $totalWholesale += $orderWholesaleTotal;
            $totalItemsSold += $orderItemsCount;
        }

        $ordersCount = count($orders);

        return [
            'orders_count' => $ordersCount,
            'total_sales' => $totalSales,
            'total_wholesale' => $totalWholesale,
            'net_proceeds' => $totalSales - $totalWholesale,
            'total_items_sold' => $totalItemsSold,
            'average_order_value' => $ordersCount > 0 ? $totalSales / $ordersCount : 0,
        ];
    }
}
