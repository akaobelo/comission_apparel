<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamStore;
use App\Models\ParentOrder;
use App\Models\DesignCatalog;

class StoreController extends Controller
{
    public function show($slug)
    {
        $store = TeamStore::where('slug', $slug)
            ->with(['items.designCatalog', 'parentOrders', 'user'])
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
            'athlete_name'  => 'required|string|max:255',
            'special_notes' => 'nullable|string|max:1000',
            'items'         => 'required|array|min:1',
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

            // Backpack/Personalized have name field
            if (in_array('backpack', $types) || ($storeItem->designCatalog && $storeItem->designCatalog->has_name_field)) {
                $entry['name_on_item'] = substr(trim($details['name_on_item'] ?? ''), 0, 50);
            }

            // Handle sizes for each sized type
            $sizedTypes = DesignCatalog::sizedTypes();
            foreach ($types as $t) {
                if (in_array($t, $sizedTypes)) {
                    $entry['sizes'][$t] = $details['sizes'][$t] ?? null;
                }
            }

            // Optional player number
            if (!empty($details['number'])) {
                $entry['number'] = substr(trim($details['number']), 0, 3);
            }

            $itemsJson[] = $entry;
        }

        if (empty($itemsJson)) {
            return back()->with('error', 'Please select at least one item before submitting.');
        }

        ParentOrder::create([
            'team_store_id'      => $store->id,
            'athlete_name'       => trim($request->athlete_name),
            'gender'             => $request->gender ?? null,
            'special_notes'      => $request->special_notes,
            'items_json'         => $itemsJson,
            'status'             => 'Submitted',
            'total_retail_price' => 0, // No longer tracked
        ]);

        $store->user->notify(
            new \App\Notifications\ParentOrderPlaced($request->athlete_name, $store->name)
        );

        return back()->with('success', 'Order successfully submitted for ' . $request->athlete_name . '! Your coach will be notified.');
    }
}
