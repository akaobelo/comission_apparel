<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamStore;
use App\Models\ParentOrder;

class StoreController extends Controller
{
    public function show($slug)
    {
        $store = TeamStore::where('slug', $slug)->with(['items', 'parentOrders', 'user'])->firstOrFail();

        // If the store isn't approved/active yet, or has been submitted to admin, we might want to restrict ordering.
        // For now, we allow viewing if it exists, but we can block the form if 'submitted_to_admin'
        
        return view('store.show', compact('store'));
    }

    public function submitOrder(Request $request, $slug)
    {
        $store = TeamStore::where('slug', $slug)->firstOrFail();

        if ($store->status === 'submitted_to_admin') {
            return back()->with('error', 'This store is no longer accepting orders. The master order has been finalized.');
        }

        $request->validate([
            'athlete_name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:50',
            'special_notes' => 'nullable|string',
            'items' => 'required|array', // The items selected
        ]);

        // Reformat the incoming items array (which could be key-value pairs from the form) into a clean JSON array
        $itemsJson = [];
        foreach ($request->items as $itemId => $details) {
            if (isset($details['selected']) && $details['selected'] == '1') {
                $itemsJson[] = [
                    'id' => $itemId,
                    'name' => $details['name'],
                    'size' => $details['size'] ?? 'N/A',
                    'qty' => $details['qty'] ?? 1,
                ];
            }
        }

        if (empty($itemsJson)) {
            return back()->with('error', 'You must select at least one item to submit an order.');
        }

        ParentOrder::create([
            'team_store_id' => $store->id,
            'athlete_name' => $request->athlete_name,
            'gender' => $request->gender,
            'special_notes' => $request->special_notes,
            'items_json' => $itemsJson,
            'status' => 'Submitted',
        ]);

        $store->user->notify(new \App\Notifications\ParentOrderPlaced($request->athlete_name, $store->name));

        return back()->with('success', 'Order successfully submitted for ' . $request->athlete_name . '!');
    }
}
