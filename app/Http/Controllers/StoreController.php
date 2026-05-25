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

        $stores = $query->latest()->paginate(12)->withQueryString();

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
            'jersey_name'         => 'nullable|string|max:255',
            'jersey_number'       => 'nullable|string|max:10',
            'backpack_name'       => 'nullable|string|max:255',
            'special_notes'       => 'nullable|string|max:1000',
            'parent_phone'        => 'nullable|string|max:50',
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

            if ($storeItem->isPackage() && isset($details['components'])) {
                $componentsData = [];
                foreach ($storeItem->components as $component) {
                    if (isset($details['components'][$component->id]['sizes'])) {
                        $compTypes = $component->types ?? [$component->type];
                        $compSizedTypes = array_intersect($compTypes, DesignCatalog::sizedTypes());
                        
                        $compSizes = [];
                        foreach ($compSizedTypes as $t) {
                            $compSizes[$t] = $details['components'][$component->id]['sizes'][$t] ?? null;
                        }
                        
                        $componentsData[] = [
                            'id' => $component->id,
                            'name' => $component->name,
                            'sizes' => $compSizes
                        ];
                    }
                }
                $entry['components'] = $componentsData;
            } else {
                // Handle sizes for each sized type
                $sizedTypes = DesignCatalog::sizedTypes();
                foreach ($types as $t) {
                    if (in_array($t, $sizedTypes)) {
                        $entry['sizes'][$t] = $details['sizes'][$t] ?? null;
                    }
                }
            }

            $itemsJson[] = $entry;
        }

        if (empty($itemsJson)) {
            return back()->with('error', 'Please select at least one item before submitting.');
        }

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
            'special_notes'       => $request->special_notes,
            'items_json'          => $itemsJson,
            'status'              => 'Submitted',
            'total_retail_price'  => 0, // No longer tracked
        ]);

        // If parent email or phone exists in roster, mark as ordered
        $store->rosters()->where(function($query) use ($request) {
            $query->where('parent_email', trim($request->parent_email))
                  ->orWhere('parent_phone', preg_replace('/[^0-9]/', '', $request->parent_phone));
        })->update(['has_ordered' => true]);

        $fullName = trim($request->athlete_first_name . ' ' . $request->athlete_last_name);

        $store->user->notify(
            new \App\Notifications\ParentOrderPlaced($fullName, $store->name)
        );

        return back()->with('success', 'Order successfully submitted for ' . $fullName . '! Your coach will be notified.');
    }
}
