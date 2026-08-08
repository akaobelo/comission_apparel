<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentOrder extends Model
{
    protected static $designsCache = null;

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

        // Fallback to DesignCatalog using localized cache to avoid N+1 query bottleneck
        $design = null;
        if (self::$designsCache === null) {
            self::$designsCache = \App\Models\DesignCatalog::all();
        }

        if ($itemId) {
            $design = self::$designsCache->firstWhere('id', $itemId);
        }

        if (!$design && $itemName) {
            $design = self::$designsCache->firstWhere('name', $itemName);
            if (!$design) {
                $normalized = str_replace(' ', '', strtolower($itemName));
                foreach (self::$designsCache as $d) {
                    if (str_replace(' ', '', strtolower($d->name)) === $normalized) {
                        $design = $d;
                        break;
                    }
                }
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
