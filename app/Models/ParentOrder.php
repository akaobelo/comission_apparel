<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentOrder extends Model
{
    protected $fillable = [
        'team_store_id',
        'athlete_first_name',
        'athlete_last_name',
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

    public static function calculateBatchFinancials($orders, $store = null)
    {
        $totalSales = 0;
        $totalWholesale = 0;
        $totalItemsSold = 0;

        // If it's a store batch, load prices from the store
        $priceByItemId = $store ? $store->items->keyBy('id') : collect();
        // If it's a direct order batch, we load prices from DesignCatalog
        $designCatalogById = !$store ? \App\Models\DesignCatalog::all()->keyBy('id') : collect();

        foreach ($orders as $order) {
            $orderTotal = 0;
            $orderWholesaleTotal = 0;
            $orderItemsCount = 0;
            $items = is_array($order->items_json) ? $order->items_json : [];

            foreach ($items as $orderedItem) {
                $itemId = isset($orderedItem['id']) ? (int) $orderedItem['id'] : null;
                $qty = max(1, (int) ($orderedItem['qty'] ?? 1));
                $orderItemsCount += $qty;

                if ($store) {
                    $storeItem = $itemId ? $priceByItemId->get($itemId) : null;
                    $retailPrice = $storeItem ? (float) $storeItem->retail_price : 0;
                    $wholesalePrice = $storeItem ? (float) $storeItem->wholesale_price : 0;
                } else {
                    $design = $itemId ? $designCatalogById->get($itemId) : null;
                    $wholesalePrice = $design ? (float) $design->wholesale_price : 0;
                    $retailPrice = $wholesalePrice; // No markup for direct orders
                }
                
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
