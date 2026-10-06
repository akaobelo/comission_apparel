<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamStore;
use App\Models\ParentOrder;
use App\Models\DesignCatalog;

class StoreController extends Controller
{
    public function search(Request $request)
    {
        $query = TeamStore::query()
            ->with('user')
            ->whereIn('status', ['approved', 'submitted_to_admin'])
            ->where('pricing_approved', true)
            ->where('is_archived', false);

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('organization', 'like', "%{$keyword}%")
                            ->orWhere('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere('sport', 'like', "%{$keyword}%");
                    });
            });
        }

        $stores = $query->orderBy('sort_order', 'asc')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('store.search', compact('stores'));
    }

    public function show($slug)
    {
        $store = TeamStore::where('slug', $slug)
            ->with(['items' => function($q) {
                $q->orderBy('sort_order', 'asc');
            }, 'items.designCatalog', 'parentOrders' => function($q) {
                $q->whereNull('batch_id')->where('is_archived', false);
            }, 'user'])
            ->firstOrFail();

        $sizeChart = DesignCatalog::sizeChart();

        return view('store.show', compact('store', 'sizeChart'));
    }

    public function submitOrder(Request $request, $slug)
    {
        $store = TeamStore::where('slug', $slug)->firstOrFail();

        if ($store->status === 'submitted_to_admin') {
            return back()->with('error', 'This store is no longer accepting orders. The master order has been finalized.');
        }

        if ($store->status !== 'approved') {
            return back()->with('error', 'This store is not yet open for orders.');
        }

        if (!$store->pricing_approved) {
            return back()->with('error', 'Pricing is currently under review by the coach.');
        }

        $request->validate([
            'athlete_first_name'  => 'required|string|max:255',
            'athlete_last_name'   => 'required|string|max:255',
            'parent_email'        => 'required|email|max:255',
            'parent_phone'        => 'required|string|max:50',
            'gender'              => 'required|string|max:50',
            'shipping_address'    => 'required|string|max:1000',
            'jersey_name'         => 'nullable|string|max:255',
            'jersey_number'       => 'nullable|string|max:10',
            'backpack_name'       => 'nullable|string|max:255',
            'special_notes'       => 'nullable|string|max:1000',
            'parent_phone'        => 'nullable|string|max:50',
            'items'               => 'required|array|min:1',
        ]);

        // Build the rich items JSON — supporting per-unit sizing and legacy format
        $itemsJson = [];

        foreach ($request->items as $itemId => $details) {
            if (!isset($details['selected']) || $details['selected'] != '1') {
                continue;
            }

            $storeItem = $store->items()->find($itemId);
            if (!$storeItem) continue;

            $qty = max(1, intval($details['qty'] ?? 1));
            $types = $storeItem->types ?? [$storeItem->type];

            // Collect per-unit data if submitted
            $units = [];
            if (!empty($details['units']) && is_array($details['units'])) {
                for ($u = 0; $u < $qty; $u++) {
                    if (isset($details['units'][$u])) {
                        $units[] = $details['units'][$u];
                    }
                }
            }

            // Fallback for single-size legacy submission or non-sized items
            if (empty($units)) {
                $units[] = [
                    'sizes'      => $details['sizes'] ?? [],
                    'components' => $details['components'] ?? [],
                    'qty'        => $qty,
                ];
            }

            // Group identical units by their size configuration so they have proper qty
            $groupedUnits = [];
            foreach ($units as $unit) {
                $unitCount = isset($unit['qty']) ? intval($unit['qty']) : 1;
                $unitKey = serialize([
                    'sizes'      => $unit['sizes'] ?? [],
                    'components' => $unit['components'] ?? [],
                ]);

                if (!isset($groupedUnits[$unitKey])) {
                    $groupedUnits[$unitKey] = [
                        'unit' => $unit,
                        'qty'  => 0,
                    ];
                }
                $groupedUnits[$unitKey]['qty'] += $unitCount;
            }

            foreach ($groupedUnits as $group) {
                $unitData = $group['unit'];
                $unitQty  = $group['qty'];

                $unitPrice = (float) ($storeItem->retail_price ?? 0);
                $entry = [
                    'id'          => $itemId,
                    'name'        => $storeItem->name,
                    'types'       => $types,
                    'qty'         => $unitQty,
                    'unit_price'  => $unitPrice,
                    'line_total'  => round($unitPrice * $unitQty, 2),
                    'sizes'       => [],
                ];

                if ($storeItem->isPackage() && isset($unitData['components'])) {
                    $componentsData = [];
                    foreach ($storeItem->components as $component) {
                        if (isset($unitData['components'][$component->id]['sizes'])) {
                            $compTypes = $component->types ?? [$component->type];
                            $compSizedTypes = array_intersect($compTypes, DesignCatalog::sizedTypes());

                            $compSizes = [];
                            foreach ($compSizedTypes as $t) {
                                $compSizes[$t] = $unitData['components'][$component->id]['sizes'][$t] ?? null;
                            }

                            $componentsData[] = [
                                'id'    => $component->id,
                                'name'  => $component->name,
                                'sizes' => $compSizes,
                            ];
                        }
                    }
                    $entry['components'] = $componentsData;
                } else {
                    $sizedTypes = DesignCatalog::sizedTypes();
                    foreach ($types as $t) {
                        if (in_array($t, $sizedTypes)) {
                            $entry['sizes'][$t] = $unitData['sizes'][$t] ?? null;
                        }
                    }
                }

                $itemsJson[] = $entry;
            }
        }

        if (empty($itemsJson)) {
            return back()->with('error', 'Please select at least one item before submitting.');
        }

        $subtotal = collect($itemsJson)->sum('line_total');
        $fullName = trim($request->athlete_first_name . ' ' . $request->athlete_last_name);

        $isTaxExempt = (bool) ($store->isTaxExempt());
        $taxRate = $isTaxExempt ? 0.00 : (float) config('services.stripe.tax_rate', 0.075);
        $taxAmount = round($subtotal * $taxRate, 2);

        $feePercent = (float) config('services.stripe.fee_percent', 0.029);
        $feeFixed = (float) config('services.stripe.fee_fixed', 0.30);
        $preFeeTotal = $subtotal + $taxAmount;
        $grandTotal = round(($preFeeTotal + $feeFixed) / (1 - $feePercent), 2);
        $feeAmount = round($grandTotal - $preFeeTotal, 2);

        $parentOrder = ParentOrder::create([
            'team_store_id'       => $store->id,
            'athlete_first_name'  => trim($request->athlete_first_name),
            'athlete_last_name'   => trim($request->athlete_last_name),
            'parent_email'        => trim($request->parent_email),
            'parent_phone'        => trim($request->parent_phone),
            'gender'              => trim($request->gender),
            'jersey_name'         => $request->jersey_name,
            'jersey_number'       => $request->jersey_number,
            'backpack_name'       => $request->backpack_name,
            'shipping_address'    => trim($request->shipping_address),
            'special_notes'       => $request->special_notes,
            'items_json'          => $itemsJson,
            'status'              => 'Submitted',
            'payment_status'      => $store->isOnlinePayment() ? 'pending' : 'not_applicable',
            'subtotal'            => $subtotal,
            'tax_amount'          => $taxAmount,
            'fee_amount'          => $feeAmount,
            'shipping_amount'     => 0.00,
            'shipping_method'     => 'coach_batch',
            'total_paid'          => $grandTotal,
            'total_retail_price'  => $subtotal,
        ]);

        // If parent email or phone exists in roster, mark as ordered
        $store->rosters()->where(function($query) use ($request) {
            $query->where('parent_email', trim($request->parent_email))
                  ->orWhere('parent_phone', preg_replace('/[^0-9]/', '', (string)$request->parent_phone));
        })->update(['has_ordered' => true]);

        $store->user->notify(
            new \App\Notifications\ParentOrderPlaced($fullName, $store->name)
        );

        return back()->with('success', 'Order successfully submitted for ' . $fullName . '! Your coach will review all team orders after the store deadline.');
    }

    public function payOrder(Request $request, $slug, $orderId)
    {
        $store = TeamStore::where('slug', $slug)->firstOrFail();
        $order = ParentOrder::where('id', $orderId)->where('team_store_id', $store->id)->firstOrFail();

        if ($order->isPaid()) {
            return back()->with('info', 'This order has already been paid.');
        }

        if ($store->isInHousePayment()) {
            return back()->with('error', 'This store is currently set to Cash Collection. Please pay your coach directly.');
        }

        $itemsJson = $order->items_json ?? [];
        if (empty($itemsJson)) {
            return back()->with('error', 'No order items found to pay for.');
        }

        $fullName = $order->athlete_name;
        $isTaxExempt = (bool) ($store->isTaxExempt() || $order->user?->is_tax_exempt);
        $taxRate = $isTaxExempt ? 0.00 : (float) config('services.stripe.tax_rate', 0.075);

        // Always refresh item prices from current store pricing for unpaid orders
        $subtotal = 0.0;
        $refreshedItemsJson = [];
        foreach ($itemsJson as $itemEntry) {
            $itemPrices = \App\Models\ParentOrder::getItemPrices($itemEntry, $store);
            $unitPrice = (float) $itemPrices['retail_price'];
            $qty = max(1, intval($itemEntry['qty'] ?? 1));
            $itemEntry['unit_price'] = $unitPrice;
            $itemEntry['line_total'] = round($unitPrice * $qty, 2);
            $subtotal += $itemEntry['line_total'];
            $refreshedItemsJson[] = $itemEntry;
        }

        $taxAmount = $isTaxExempt ? 0.00 : round($subtotal * $taxRate, 2);

        if ($subtotal > 0) {
            $feePercent = (float) config('services.stripe.fee_percent', 0.029);
            $feeFixed = (float) config('services.stripe.fee_fixed', 0.30);
            $preFeeTotal = $subtotal + $taxAmount;
            $grandTotal = round(($preFeeTotal + $feeFixed) / (1 - $feePercent), 2);
            $feeAmount = round($grandTotal - $preFeeTotal, 2);
            $totalPaid = $grandTotal;
        } else {
            $feeAmount = 0.00;
            $grandTotal = 0.00;
            $totalPaid = 0.00;
        }

        $order->update([
            'items_json' => $refreshedItemsJson,
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'fee_amount' => $feeAmount,
            'total_paid' => $totalPaid,
        ]);

        $stripeSecret = config('services.stripe.secret');

        if ($stripeSecret) {
            try {
                $stripe = new \Stripe\StripeClient($stripeSecret);

                $lineItems = [];
                foreach ($refreshedItemsJson as $itemEntry) {
                    $unitPrice = (float) ($itemEntry['unit_price'] ?? 0);
                    $unitCents = max(50, intval(round($unitPrice * 100)));
                    $lineItems[] = [
                        'price_data' => [
                            'currency'     => 'usd',
                            'unit_amount'  => $unitCents,
                            'product_data' => [
                                'name'        => $itemEntry['name'] ?? 'Team Apparel Item',
                                'description' => 'Athlete: ' . $fullName . ' (' . $store->name . ')',
                            ],
                        ],
                        'quantity'   => max(1, intval($itemEntry['qty'] ?? 1)),
                    ];
                }

                if ($taxAmount > 0) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency'     => 'usd',
                            'unit_amount'  => intval(round($taxAmount * 100)),
                            'product_data' => [
                                'name'        => 'Sales Tax (7.5%)',
                                'description' => 'Mandatory sales tax per transaction',
                            ],
                        ],
                        'quantity'   => 1,
                    ];
                }

                if ($feeAmount > 0) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency'     => 'usd',
                            'unit_amount'  => intval(round($feeAmount * 100)),
                            'product_data' => [
                                'name'        => 'Card Processing Fee',
                                'description' => 'Credit card processing fee',
                            ],
                        ],
                        'quantity'   => 1,
                    ];
                }

                $parentEmail = trim((string) $order->parent_email);

                $sessionParams = [
                    'payment_method_types' => ['card'],
                    'line_items'           => $lineItems,
                    'mode'                 => 'payment',
                    'client_reference_id'  => (string) $order->id,
                    'metadata'             => [
                        'order_id'     => $order->id,
                        'store_id'     => $store->id,
                        'athlete_name' => $fullName,
                        'parent_email' => $parentEmail,
                    ],
                    'success_url'          => route('store.checkout.success', ['slug' => $store->slug, 'order' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'           => route('store.checkout.cancel', ['slug' => $store->slug, 'order' => $order->id]),
                ];

                // Note: We deliberately do NOT prefill 'customer_email' here.
                // When customer_email is set to the athlete/parent's email, Stripe Link automatically triggers
                // SMS verification to the parent's phone, which locks out third-party payers (like grandparents
                // in another state, coaches, or sponsors) who don't have access to that phone.
                // Omitting it lets the payer enter their own email for authentication and receipt delivery.

                $session = $stripe->checkout->sessions->create($sessionParams);

                $order->update(['stripe_session_id' => $session->id]);

                return redirect($session->url);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Stripe Checkout creation failed: ' . $e->getMessage());
                return back()->with('error', 'Payment gateway error: ' . $e->getMessage());
            }
        } else {
            // Local dev simulation when Stripe secret key is not populated
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'Submitted',
                'paid_at'        => now(),
            ]);

            return redirect()->route('store.checkout.success', ['slug' => $store->slug, 'order' => $order->id])
                ->with('success', 'Order payment confirmed (Stripe Test Simulation)!');
        }
    }

    public function checkoutSuccess(Request $request, $slug, $orderId)
    {
        $store = TeamStore::where('slug', $slug)->firstOrFail();
        $order = ParentOrder::where('id', $orderId)->where('team_store_id', $store->id)->firstOrFail();

        $sessionId = $request->query('session_id');
        $stripeSecret = config('services.stripe.secret');

        if ($sessionId && $stripeSecret && !$order->isPaid()) {
            try {
                $stripe = new \Stripe\StripeClient($stripeSecret);
                $session = $stripe->checkout->sessions->retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    $updateFields = [
                        'payment_status'          => 'paid',
                        'status'                  => 'Submitted',
                        'stripe_payment_intent_id'=> $session->payment_intent,
                        'paid_at'                 => now(),
                    ];

                    $checkoutEmail = $session->customer_details->email ?? null;
                    if (empty($order->parent_email) && !empty($checkoutEmail)) {
                        $updateFields['parent_email'] = $checkoutEmail;
                    }

                    $order->update($updateFields);

                    $parentEmail = trim($order->parent_email);
                    $parentPhone = preg_replace('/[^0-9]/', '', (string)$order->parent_phone);
                    $store->rosters()->where(function($query) use ($parentEmail, $parentPhone) {
                        if ($parentEmail) $query->where('parent_email', $parentEmail);
                        if ($parentPhone) $query->orWhere('parent_phone', $parentPhone);
                    })->update(['has_ordered' => true]);

                    $fullName = $order->athlete_name;
                    $store->user->notify(new \App\Notifications\ParentOrderPlaced($fullName, $store->name));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error verifying Stripe session on return: ' . $e->getMessage());
            }
        }

        return view('store.receipt', compact('store', 'order'));
    }

    public function checkoutCancel(Request $request, $slug, $orderId)
    {
        $store = TeamStore::where('slug', $slug)->firstOrFail();
        return redirect()->route('store.show', $store->slug)
            ->with('error', 'Online payment was cancelled. You can complete payment at any time by clicking your order at the bottom of the page.');
    }

    public function orderReceipt($slug, $orderId)
    {
        $store = TeamStore::where('slug', $slug)->firstOrFail();
        $order = ParentOrder::where('id', $orderId)->where('team_store_id', $store->id)->firstOrFail();

        return view('store.receipt', compact('store', 'order'));
    }
}
