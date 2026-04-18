<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamStore;
use App\Models\StoreItem;
use App\Models\ParentOrder;
use App\Models\DesignCatalog;
use Illuminate\Support\Str;

class CoachController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Load store with items and orders
        $store = $user->teamStore()->with(['items.designCatalog', 'parentOrders'])->first();

        // Designs assigned to this coach (for item builder)
        $assignedDesigns = $user->designCatalog()->latest()->get();

        // Organize assigned designs by category for package selection
        $packageDesigns = [
            'package_a' => $assignedDesigns->where('category', 'package_a'),
            'package_b' => $assignedDesigns->where('category', 'package_b'),
            'package_c' => $assignedDesigns->where('category', 'package_c'),
            'individual' => $assignedDesigns->where('category', 'individual'),
        ];

        return view('coach.dashboard', compact('user', 'store', 'assignedDesigns', 'packageDesigns'));
    }

    public function createStore(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'package_type' => 'required|in:package_a,package_b,package_c,individual',
        ]);

        $user = $request->user();

        if ($user->teamStore()->exists()) {
            return redirect()->route('coach.dashboard')
                ->with('error', 'You already have a team store. Contact admin to create additional stores.');
        }

        $store = TeamStore::create([
            'user_id'      => $user->id,
            'name'         => $request->name,
            'description'  => $request->description,
            'slug'         => Str::slug($request->name) . '-' . strtolower(Str::random(6)),
            'package_type' => $request->package_type,
            'status'       => 'pending',
        ]);

        \Illuminate\Support\Facades\Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new \App\Notifications\StoreCreated($store)
        );

        return redirect()->route('coach.dashboard')
            ->with('success', 'Team store request submitted! Awaiting admin approval. You can set it up while you wait.');
    }

    public function addStoreItem(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'design_catalog_id' => [
                'required',
                'exists:design_catalog,id',
                function($attribute, $value, $fail) use ($request) {
                    // Ensure the design is actually assigned to this coach
                    $isAssigned = $request->user()->designCatalog()
                        ->where('design_catalog_id', $value)->exists();
                    if (!$isAssigned) {
                        $fail('This design is not assigned to your profile.');
                    }
                }
            ],
        ]);

        $design = DesignCatalog::findOrFail($request->design_catalog_id);

        // Check it isn't already in the store
        if ($store->items()->where('design_catalog_id', $design->id)->exists()) {
            return redirect()->route('coach.dashboard')
                ->with('error', 'That design is already added to your store.');
        }

        $store->items()->create([
            'design_catalog_id' => $design->id,
            'name'              => $design->name,
            'type'              => $design->type,
            'image_url'         => $design->image_url,
        ]);

        return redirect()->route('coach.dashboard')
            ->with('success', "\"{$design->name}\" added to your store.");
    }

    public function removeStoreItem(Request $request, StoreItem $item)
    {
        $store = $item->teamStore;
        if ($store->user_id !== $request->user()->id) abort(403);

        $item->delete();
        return redirect()->route('coach.dashboard')
            ->with('success', 'Item removed from store.');
    }

    public function updateDeadline(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'deadline' => 'required|date|after:today',
        ]);

        $store->update(['order_deadline' => $request->deadline]);
        return redirect()->route('coach.dashboard')
            ->with('success', 'Order deadline updated.');
    }

    public function submitMasterOrder(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        if ($store->parentOrders()->count() === 0) {
            return redirect()->route('coach.dashboard')
                ->with('error', 'No orders have been submitted yet. Cannot finalize an empty roster.');
        }

        $store->update(['status' => 'submitted_to_admin']);
        return redirect()->route('coach.dashboard')
            ->with('success', 'Master order submitted to The Commission Apparel for production!');
    }

    // Edit a parent's order (coach can correct mistakes)
    public function editOrder(Request $request, ParentOrder $order)
    {
        $store = $order->teamStore;
        if ($store->user_id !== $request->user()->id) abort(403);

        $sizeChart = DesignCatalog::sizeChart();
        return view('coach.order_edit', compact('order', 'store', 'sizeChart'));
    }

    public function updateOrder(Request $request, ParentOrder $order)
    {
        $store = $order->teamStore;
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'athlete_name'  => ['required', 'string', 'max:255'],
            'special_notes' => ['nullable', 'string'],
            'items'         => ['required', 'array'],
        ]);

        $itemsJson = [];
        foreach ($request->items as $idx => $item) {
            $itemsJson[] = [
                'id'           => $item['id'] ?? $idx,
                'name'         => $item['name'] ?? 'Unknown',
                'type'         => $item['type'] ?? 'accessory',
                'size'         => $item['size'] ?? null,
                'qty'          => $item['qty'] ?? 1,
                'name_on_item' => $item['name_on_item'] ?? null,
                'number'       => $item['number'] ?? null,
            ];
        }

        $order->update([
            'athlete_name'  => $request->athlete_name,
            'special_notes' => $request->special_notes,
            'items_json'    => $itemsJson,
            'is_edited'     => true,
            'edited_by'     => 'coach',
        ]);

        return redirect()->route('coach.dashboard')
            ->with('success', "Order for {$order->athlete_name} has been updated.");
    }

    public function approvePricing(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $store->update(['pricing_approved' => true]);

        return redirect()->route('coach.dashboard')
            ->with('success', 'Pricing approved! The public storefront is now live with the approved pricing.');
    }
}
