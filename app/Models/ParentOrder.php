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
        'sales_rep_id',
        'batch_id',
        'order_type',
        'is_archived',
        'manufacturing_cost',
        'tariff_amount',
        'fee_deduction',
        'fundraising_amount',
        'net_margin',
        'applied_rep_commission_rate',
        'rep_commission_earned',
        'commission_status',
        'paid_to_rep_at',
        'financial_notes',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->sales_rep_id)) {
                if (!empty($order->user_id)) {
                    $coach = \App\Models\User::find($order->user_id);
                    if ($coach?->sales_rep_id) {
                        $order->sales_rep_id = $coach->sales_rep_id;
                    }
                }
                if (empty($order->sales_rep_id) && !empty($order->team_store_id)) {
                    $store = \App\Models\TeamStore::find($order->team_store_id);
                    if ($store?->sales_rep_id) {
                        $order->sales_rep_id = $store->sales_rep_id;
                    } elseif ($store?->user?->sales_rep_id) {
                        $order->sales_rep_id = $store->user->sales_rep_id;
                    }
                }
            }
        });
    }

    public function isDirectOrder(): bool
    {
        return ($this->order_type ?? '') === 'direct' || is_null($this->team_store_id);
    }

    public function isMasterOrder(): bool
    {
        return !$this->isDirectOrder();
    }

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
        'manufacturing_cost' => 'decimal:2',
        'tariff_amount'      => 'decimal:2',
        'fee_deduction'      => 'decimal:2',
        'fundraising_amount' => 'decimal:2',
        'net_margin'         => 'decimal:2',
        'applied_rep_commission_rate' => 'decimal:2',
        'rep_commission_earned'       => 'decimal:2',
        'paid_to_rep_at'     => 'datetime',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function getEffectiveQualifyingSales($store = null): float
    {
        if ((float) $this->subtotal > 0) {
            return (float) $this->subtotal;
        }
        if ((float) $this->total_retail_price > 0) {
            return (float) $this->total_retail_price;
        }
        $calculated = $this->getCalculatedSubtotal($store);
        if ($calculated > 0) {
            return $calculated;
        }
        if ((float) $this->total_paid > 0) {
            return max(0.0, (float) $this->total_paid - (float) $this->tax_amount - (float) $this->fee_amount);
        }
        return 0.0;
    }

    public function getEffectiveManufacturingCost($store = null): float
    {
        if ((float) $this->manufacturing_cost > 0) {
            return (float) $this->manufacturing_cost;
        }
        $store = $store ?: $this->teamStore;
        $items = is_array($this->items_json) ? $this->items_json : [];
        $totalWholesale = 0.0;
        foreach ($items as $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $prices = self::getItemPrices($item, $store);
            $totalWholesale += ($prices['wholesale_price'] * $qty);
        }
        return (float) $totalWholesale;
    }

    /**
     * Compute Net Margin and Rep Commission Payout
     * Formula:
     * Qualifying Sales = Subtotal / Total Paid (excluding taxes)
     * Net Margin = max(0, Qualifying Sales - (Manufacturing Cost + Tariff + Fee Deductions + Fundraising))
     * Rep Payout = Net Margin * (Rep Commission Rate / 100)
     */
    public function calculateAndApplyCommission(?float $rateOverride = null): void
    {
        $sales = $this->getEffectiveQualifyingSales();
        $mfg = $this->getEffectiveManufacturingCost();
        $tariff = (float) $this->tariff_amount;
        $fee = (float) $this->fee_deduction;
        $fundraising = (float) $this->fundraising_amount;

        if ($this->subtotal <= 0 && $sales > 0) {
            $this->subtotal = round($sales, 2);
        }
        if ($this->total_retail_price <= 0 && $sales > 0) {
            $this->total_retail_price = round($sales, 2);
        }
        if ($this->manufacturing_cost <= 0 && $mfg > 0) {
            $this->manufacturing_cost = round($mfg, 2);
        }

        $netMargin = max(0.0, $sales - ($mfg + $tariff + $fee + $fundraising));
        $this->net_margin = round($netMargin, 2);

        $rate = $rateOverride;
        if ($rate === null) {
            if ($this->salesRep) {
                $rate = $this->salesRep->getEffectiveCommissionRate();
            } elseif ($this->applied_rep_commission_rate !== null) {
                $rate = (float) $this->applied_rep_commission_rate;
            } else {
                $rate = 50.00;
            }
        }

        $this->applied_rep_commission_rate = round($rate, 2);
        $this->rep_commission_earned = round($netMargin * ($rate / 100.0), 2);
    }

    /**
     * Get all qualifying orders belonging strictly to a sales rep's book of business.
     * Enforces the rule that reps only receive orders placed on or after the client assignment date,
     * while preserving active batches explicitly assigned to the rep.
     */
    public static function getOrdersForSalesRep(User $rep, ?\Illuminate\Support\Collection $clients = null)
    {
        if ($clients === null) {
            $clients = User::where('sales_rep_id', $rep->id)->with(['teamStores'])->get();
        }
        $clientIds = $clients->pluck('id')->toArray();
        $clientsById = $clients->keyBy('id');

        $storeIds = TeamStore::where('sales_rep_id', $rep->id)
            ->orWhereIn('user_id', $clientIds)
            ->pluck('id')
            ->toArray();

        $candidates = static::where(function($q) use ($rep, $clientIds, $storeIds) {
                $q->where('sales_rep_id', $rep->id);
                if (!empty($clientIds)) {
                    $q->orWhereIn('user_id', $clientIds);
                }
                if (!empty($storeIds)) {
                    $q->orWhereIn('team_store_id', $storeIds);
                }
            })
            ->where('status', '!=', 'Draft')
            ->with(['teamStore', 'user', 'teamStore.items'])
            ->latest()
            ->get();

        return $candidates->filter(function($ord) use ($rep, $clientsById) {
            $orderDate = $ord->created_at ?: $ord->paid_at;

            $client = null;
            if ($ord->user_id && isset($clientsById[$ord->user_id])) {
                $client = $clientsById[$ord->user_id];
            } elseif ($ord->teamStore && isset($clientsById[$ord->teamStore->user_id])) {
                $client = $clientsById[$ord->teamStore->user_id];
            }

            if ($client) {
                $assignedAt = $client->sales_rep_assigned_at;

                // 1. Order placed on or after client was assigned to this rep
                if ($assignedAt && $orderDate && $orderDate->gte($assignedAt)) {
                    return true;
                }

                // 2. Fallback if assigned_at was not set: only include if on or after rep creation
                if (!$assignedAt && $orderDate && $orderDate->gte($rep->created_at->startOfDay())) {
                    return true;
                }

                // 3. Batch explicitly assigned to this sales rep
                if (!empty($ord->batch_id) && $ord->sales_rep_id == $rep->id && $ord->status !== 'Cancelled') {
                    return true;
                }

                // Historical activity prior to assignment belongs exclusively to Super Admin lifetime archive
                return false;
            }

            // Not an assigned client: must be directly assigned to this sales rep
            return ($ord->sales_rep_id == $rep->id);
        })->values();
    }

    /**
     * Deprecated no-op: Order attribution is strictly governed by coach assignment date
     * and explicit admin batch assignments.
     */
    public static function restoreMistakenOrdersForSalesRep(User $rep): void
    {
        // No-op
    }

    protected static function initDesignsCache()
    {
        if (self::$designsCache === null) {
            self::$designsCache = \App\Models\DesignCatalog::select('id', 'name', 'wholesale_price')->get();
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
                $retail = (float) $storeItem->retail_price;
                $wholesale = (float) $storeItem->wholesale_price;
                if ($retail > 0 || $wholesale > 0) {
                    return [
                        'retail_price'    => $retail > 0 ? $retail : $wholesale,
                        'wholesale_price' => $wholesale > 0 ? $wholesale : $retail,
                    ];
                }
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

        $ordersCollection = collect($orders);
        $seenReplicatedSessions = [];

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

                $sessionId = $order->stripe_session_id;
                $batchId = $order->batch_id;
                $sessionKey = $sessionId ?: ($order->isDirectOrder() && $batchId ? 'batch_' . $batchId : null);

                if ($sessionKey) {
                    $sharedOrders = $ordersCollection->filter(function($o) use ($sessionId, $batchId) {
                        return $sessionId 
                            ? ($o->stripe_session_id === $sessionId) 
                            : ($o->batch_id === $batchId && $o->isDirectOrder());
                    });

                    if ($sharedOrders->count() > 1) {
                        $firstPaid = (float) $sharedOrders->first()->total_paid;
                        $allIdenticalPaid = $sharedOrders->every(fn($o) => abs((float)$o->total_paid - $firstPaid) < 0.01);
                        $sessionSubtotal = (float) $sharedOrders->sum(fn($o) => (float)($o->subtotal ?? 0));

                        // If all orders in the shared batch/session have identical total_paid >= sessionSubtotal,
                        // it indicates the batch-level totals were replicated on each order row.
                        if ($allIdenticalPaid && $firstPaid >= ($sessionSubtotal - 0.01) && $firstPaid > 0) {
                            if (!in_array($sessionKey, $seenReplicatedSessions)) {
                                $seenReplicatedSessions[] = $sessionKey;
                                $orderTax = (float) ($order->tax_amount ?? 0);
                                $orderFees = (float) ($order->fee_amount ?? 0);
                                $orderOnlinePaid = (float) ($order->total_paid ?? 0);
                            } else {
                                $orderTax = 0.0;
                                $orderFees = 0.0;
                                $orderOnlinePaid = 0.0;
                            }
                        } else {
                            $orderTax = (float) ($order->tax_amount ?? 0);
                            $orderFees = (float) ($order->fee_amount ?? 0);
                            $orderOnlinePaid = (float) ($order->total_paid ?? 0);
                        }
                    } else {
                        $orderTax = (float) ($order->tax_amount ?? 0);
                        $orderFees = (float) ($order->fee_amount ?? 0);
                        $orderOnlinePaid = (float) ($order->total_paid ?? 0);
                    }
                } else {
                    $orderTax = (float) ($order->tax_amount ?? 0);
                    $orderFees = (float) ($order->fee_amount ?? 0);
                    $orderOnlinePaid = (float) ($order->total_paid ?? 0);
                }
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

    /**
     * Group orders into batches (Finalized Master Store or Direct Orders)
     * for Sales Partner commission review.
     */
    public static function groupOrdersIntoBatches($orders, $rep = null)
    {
        $ordersCollection = collect($orders);
        if ($ordersCollection->isEmpty()) {
            return collect();
        }

        $grouped = $ordersCollection->groupBy(function ($order) {
            if (!empty($order->batch_id)) {
                return 'batch_' . $order->batch_id;
            }
            if (!empty($order->team_store_id)) {
                return 'store_' . $order->team_store_id;
            }
            return 'order_' . $order->id;
        });

        $batches = collect();

        foreach ($grouped as $key => $batchOrders) {
            $first = $batchOrders->first();
            $store = $first->teamStore;
            $coach = $first->user ?: $store?->user;
            $isDirect = $first->isDirectOrder();

            $batchFinancials = self::calculateBatchFinancials($batchOrders, $store);

            // Gross sales: include the entire amount collected (Online Payment Stripe total in green if paid online, or total sales)
            $onlinePaid = (float)($batchFinancials['total_online_paid'] ?? 0);
            $totalSales = (float)($batchFinancials['total_sales'] ?? $batchOrders->sum(fn($o) => (float)$o->getEffectiveQualifyingSales($store)));
            $gross = $onlinePaid > 0 ? $onlinePaid : $totalSales;

            // Manu. Cost: Total Manufacturing Cost from batch summary (or effective manufacturing cost)
            $manuCost = (float) $batchOrders->sum(fn($o) => (float)$o->manufacturing_cost);
            if ($manuCost <= 0) {
                $manuCost = (float) $batchOrders->sum(fn($o) => (float)$o->getEffectiveManufacturingCost($store));
            }

            // Taxes: 7.5% customer sales tax directly from batch summary
            $taxes = (float)($batchFinancials['total_tax'] ?? 0);

            // Fees: Combine "Batch Tariff Amount" and "Extra Fee Deduction" from batch summary
            $tariffs = (float) $batchOrders->sum(fn($o) => (float)$o->tariff_amount);
            $feeDeduction = (float) $batchOrders->sum(fn($o) => (float)$o->fee_deduction);
            $fees = $tariffs + $feeDeduction;

            // Net Margin: Combine Manu. Cost + Taxes + Fees to get total expense, then subtract from Gross Sales
            $totalExpense = $manuCost + $taxes + $fees;
            $netMargin = max(0.0, $gross - $totalExpense);

            $splitRate = (float)($first->applied_rep_commission_rate ?? ($rep ? $rep->getEffectiveCommissionRate() : 50.0));
            $commission = round($netMargin * ($splitRate / 100.0), 2);

            if ($store) {
                $storeName = $store->name;
                $slug = $store->slug;
            } elseif (!empty($first->batch_id)) {
                $storeName = 'Direct Batch #' . substr($first->batch_id, 0, 8);
                $slug = null;
            } else {
                $storeName = 'Direct Order #' . $first->id;
                $slug = null;
            }

            $coachName = $coach?->name ?? 'Coach';
            $organization = $coach?->organization ?? $store?->name ?? 'Athletic Program';

            $paymentMode = 'Cash';
            if ($store && $store->payment_mode) {
                $paymentMode = match($store->payment_mode) {
                    'online' => 'Stripe',
                    'check'  => 'Check',
                    default  => 'Cash',
                };
            } elseif ($batchOrders->contains(fn($o) => !empty($o->stripe_session_id) || !empty($o->stripe_payment_intent_id))) {
                $paymentMode = 'Stripe';
            }

            $allPaid = $batchOrders->every(fn($o) => $o->commission_status === 'paid');
            $hasCleared = $batchOrders->contains(fn($o) => $o->commission_status === 'cleared');
            $status = $allPaid ? 'paid' : ($hasCleared ? 'cleared' : 'pending');

            $batches->push((object)[
                'key'              => $key,
                'batch_id'         => $first->batch_id,
                'store_id'         => $first->team_store_id,
                'store'            => $store,
                'store_name'       => $storeName,
                'slug'             => $slug,
                'coach_name'       => $coachName,
                'organization'     => $organization,
                'is_direct'        => $isDirect,
                'order_type'       => $isDirect ? 'FINALIZED DIRECT ORDERS' : 'FINALIZED MASTER ORDERS',
                'order_type_badge' => $isDirect ? 'direct' : 'master',
                'payment_method'   => $paymentMode,
                'order_count'      => $batchOrders->count(),
                'gross'            => $gross,
                'manu_cost'        => $manuCost,
                'base_cost'        => $manuCost,
                'taxes'            => $taxes,
                'fees'             => $fees,
                'taxes_fees'       => $fees,
                'net_margin'       => $netMargin,
                'margin'           => $netMargin,
                'commission'       => $commission,
                'rep_cut'          => $commission,
                'split_rate'       => $splitRate,
                'status'           => $status,
                'latest_date'      => $batchOrders->max('created_at'),
                'orders'           => $batchOrders,
            ]);
        }

        return $batches->sortByDesc('latest_date')->values();
    }
}
