<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamStore;
use App\Models\StoreItem;
use Illuminate\Support\Str;

class CoachController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $store = $user->teamStore()->with(['items', 'parentOrders'])->first();

        return view('coach.dashboard', compact('user', 'store'));
    }

    public function createStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        
        $store = TeamStore::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(), // Ensure uniqueness
            'status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new \App\Notifications\StoreCreated($store)
        );

        return redirect()->route('coach.dashboard')->with('success', 'Store creation requested! Pending admin approval.');
    }

    public function addStoreItem(Request $request, TeamStore $store)
    {
        // Ensure the coach owns this store
        if ($store->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'image_url' => 'nullable|url',
        ]);

        $store->items()->create([
            'name' => $request->name,
            'type' => $request->type,
            'image_url' => $request->image_url,
        ]);

        return redirect()->route('coach.dashboard')->with('success', 'Item attached to store.');
    }

    public function removeStoreItem(Request $request, StoreItem $item)
    {
        $store = $item->teamStore;
        if ($store->user_id !== $request->user()->id) {
            abort(403);
        }

        $item->delete();
        return redirect()->route('coach.dashboard')->with('success', 'Item removed.');
    }

    public function updateDeadline(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'deadline' => 'required|date',
        ]);

        $store->update(['order_deadline' => $request->deadline]);
        return redirect()->route('coach.dashboard')->with('success', 'Deadline updated.');
    }

    public function submitMasterOrder(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) {
            abort(403);
        }

        $store->update(['status' => 'submitted_to_admin']);
        return redirect()->route('coach.dashboard')->with('success', 'Master order submitted to The Commission Apparel!');
    }
}
