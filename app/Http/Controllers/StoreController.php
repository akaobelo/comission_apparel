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
            ->where('status', 'approved')
            ->where('pricing_approved', true);

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

        $stores = $query->latest()->paginate(12)->withQueryString();

        return view('store.search', compact('stores'));
    }

    public function show($slug)
    {
        $store = TeamStore::where('slug', $slug)
            ->with(['items' => function($q) {
                $q->orderBy('sort_order', 'asc');
            }, 'items.designCatalog', 'parentOrders', 'user'])
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
            'gender'              => 'required|string|max:50',
            'jersey_name'         => 'nullable|string|max:255',
            'jersey_number'       => 'nullable|string|max:10',
            'backpack_name'       => 'nullable|string|max:255',
            'special_notes'       => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
        ]);

        // Build the rich items JSON — each item has per-piece sizing
        $itemsJson = [];

        foreach ($request->items as $itemId => $details) {
            if (!isset($details['selected']) || $details['selected'] != '1') {
                continue;
            }

            $storeItem = $store->items()->find($itemId);
            if (!$storeItem) continue;

            $qty = max(1, intval($details['qty'] ?? 1));

            $types = $storeItem->types ?? [$storeItem->type];
            $entry = [
                'id'           => $itemId,
                'name'         => $storeItem->name,
                'types'        => $types,
                'qty'          => $qty,
                'sizes'        => [],
            ];

            // Handle sizes for each sized type
            $sizedTypes = DesignCatalog::sizedTypes();
            foreach ($types as $t) {
                if (in_array($t, $sizedTypes)) {
                    $entry['sizes'][$t] = $details['sizes'][$t] ?? null;
                }
            }

            $itemsJson[] = $entry;
        }

        if (empty($itemsJson)) {
            return back()->with('error', 'Please select at least one item before submitting.');
        }

        ParentOrder::create([
            'team_store_id'       => $store->id,
            'athlete_first_name'  => trim($request->athlete_first_name),
            'athlete_last_name'   => trim($request->athlete_last_name),
            'gender'              => trim($request->gender),
            'jersey_name'         => $request->jersey_name,
            'jersey_number'       => $request->jersey_number,
            'backpack_name'       => $request->backpack_name,
            'special_notes'       => $request->special_notes,
            'items_json'          => $itemsJson,
            'status'              => 'Submitted',
            'total_retail_price'  => 0, // No longer tracked
        ]);

        $fullName = trim($request->athlete_first_name . ' ' . $request->athlete_last_name);

        $store->user->notify(
            new \App\Notifications\ParentOrderPlaced($fullName, $store->name)
        );

        return back()->with('success', 'Order successfully submitted for ' . $fullName . '! Your coach will be notified.');
    }
}
