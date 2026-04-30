<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeamStore;
use App\Models\ParentOrder;
use App\Models\DesignCatalog;
use App\Models\LandingCollection;
use App\Models\QuoteRequest;
use App\Models\StoreItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    // ─── DASHBOARD ───────────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $query = User::where('role', 'coach');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        $coaches = $query->latest()->paginate(20)->withQueryString();

        // Stores pending admin approval
        $pendingStores = TeamStore::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        // Finalized master orders (stores submitted to admin) — aggregate view
        $finalizedStores = TeamStore::where('status', 'submitted_to_admin')
            ->with(['user', 'parentOrders.teamStore'])
            ->latest()
            ->get();

        // Design catalog
        $designCatalog = DesignCatalog::latest()->get();

        // Production orders (in production status)
        $productionStores = TeamStore::where('status', 'approved')
            ->with(['user', 'parentOrders'])
            ->latest()
            ->get();

        $quoteRequests = Schema::hasTable('quote_requests')
            ? QuoteRequest::latest()->get()
            : collect();

        $landingCollections = LandingCollection::orderBy('sort_order', 'asc')->get();

        $allStores = TeamStore::with('user')->orderBy('name')->get();

        $availableSports = DesignCatalog::whereNotNull('sport')
            ->where('sport', '!=', '')
            ->distinct()
            ->orderBy('sport')
            ->pluck('sport');

        return view('admin.dashboard', compact(
            'coaches', 'pendingStores', 'finalizedStores',
            'designCatalog', 'productionStores', 'quoteRequests', 'landingCollections', 'allStores',
            'availableSports'
        ));
    }

    // ─── COACH MANAGEMENT ────────────────────────────────────────────────────────

    public function editCoach(User $user)
    {
        if ($user->role !== 'coach') abort(404);
        $designCatalog = DesignCatalog::latest()->get();
        $assignedDesignIds = $user->designCatalog()->pluck('design_catalog_id')->toArray();
        return view('admin.coach_edit', compact('user', 'designCatalog', 'assignedDesignIds'));
    }

    public function updateCoach(Request $request, User $user)
    {
        if ($user->role !== 'coach') abort(404);

        $validated = $request->validate([
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email,' . $user->id],
            'organization' => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:20'],
            'sport'        => ['required', 'string', 'max:100'],
            'status'       => ['required', 'in:active,declined'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.dashboard')
            ->with('success', "Coach {$user->name}'s profile has been updated.");
    }

    public function resetCoachPassword(Request $request, User $user)
    {
        if ($user->role !== 'coach') abort(404);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('admin.coach.edit', $user)
            ->with('success', "Password reset successfully for Coach {$user->name}.");
    }

    public function deleteCoach(User $user)
    {
        if ($user->role !== 'coach') abort(404);
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.dashboard')
            ->with('success', "Coach {$name} has been removed from the system.");
    }

    // ─── DESIGN CATALOG MANAGEMENT ───────────────────────────────────────────────

    public function createDesign(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'sport'            => ['nullable', 'string', 'max:100'],
            'types'            => ['required', 'array', 'min:1'],
            'types.*'          => ['string', 'in:uniform_top,uniform_bottom,warmup_top,warmup_bottom,backpack,arm_sleeve,accessory'],
            'category'         => ['required', 'string', 'max:255'],
            'images'           => ['nullable', 'array', 'max:100'],
            'images.*'         => ['image', 'max:10240'], // max 10MB per image
            'has_name_field'   => ['boolean'],
            'has_number_field' => ['boolean'],
            'notes'            => ['nullable', 'string'],
            'wholesale_price'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['has_name_field']   = $request->boolean('has_name_field');
        $validated['has_number_field'] = $request->boolean('has_number_field');

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('designs', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
        }
        $validated['image_paths'] = $imagePaths;

        // Make sure type and image_url are set to null since we are migrating to JSON
        $validated['type'] = null;
        $validated['image_url'] = null;

        DesignCatalog::create($validated);

        return redirect()->route('admin.dashboard')
            ->with('success', "Design \"{$validated['name']}\" added to catalog.");
    }

    public function deleteDesign(DesignCatalog $design)
    {
        $name = $design->name;
        $design->delete();
        return redirect()->route('admin.dashboard')
            ->with('success', "Design \"{$name}\" removed from catalog.");
    }

    public function updateDesign(Request $request, DesignCatalog $design)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'sport'            => ['nullable', 'string', 'max:100'],
            'types'            => ['required', 'array', 'min:1'],
            'types.*'          => ['string', 'in:uniform_top,uniform_bottom,warmup_top,warmup_bottom,backpack,arm_sleeve,accessory'],
            'category'         => ['required', 'string', 'max:255'],
            'images'           => ['nullable', 'array', 'max:100'],
            'images.*'         => ['image', 'max:10240'],
            'has_name_field'   => ['boolean'],
            'has_number_field' => ['boolean'],
            'notes'            => ['nullable', 'string'],
            'wholesale_price'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['has_name_field'] = $request->boolean('has_name_field');
        $validated['has_number_field'] = $request->boolean('has_number_field');

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('designs', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
            $validated['image_paths'] = $imagePaths;
            $validated['image_url'] = null;
        }

        $validated['type'] = null;

        $design->update($validated);

        // Keep existing store items in sync when base design details change.
        StoreItem::where('design_catalog_id', $design->id)->update([
            'name' => $validated['name'],
            'types' => $validated['types'],
            'wholesale_price' => $validated['wholesale_price'] ?? null,
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', "Design \"{$design->name}\" updated successfully.");
    }

    public function assignDesign(Request $request, User $coach)
    {
        $request->validate([
            'design_catalog_id' => ['required', 'exists:design_catalog,id'],
        ]);

        $coach->designCatalog()->syncWithoutDetaching([$request->design_catalog_id]);

        return redirect()->route('admin.coach.edit', $coach)
            ->with('success', 'Design assigned to coach successfully.');
    }

    public function removeDesign(Request $request, User $coach, DesignCatalog $design)
    {
        $coach->designCatalog()->detach($design->id);
        return redirect()->route('admin.coach.edit', $coach)
            ->with('success', 'Design removed from coach.');
    }

    public function assignToStore(Request $request, DesignCatalog $design)
    {
        $request->validate([
            'team_store_id' => 'required|exists:team_stores,id'
        ]);

        $store = TeamStore::findOrFail($request->team_store_id);

        if ($store->items()->where('design_catalog_id', $design->id)->exists()) {
            return back()->with('error', "{$design->name} is already assigned to {$store->name}");
        }

        StoreItem::create([
            'team_store_id' => $store->id,
            'design_catalog_id' => $design->id,
            'name' => $design->name,
            'type' => $design->type, // Legacy
            'types' => $design->types,
            'image_url' => $design->image_url,
            'image_paths' => $design->image_paths,
            'wholesale_price' => $design->wholesale_price,
            'retail_price' => 0, // Coach needs to set retail price before store goes live
            'status' => 'pending_pricing',
        ]);

        return back()->with('success', "{$design->name} was successfully added to {$store->name}!");
    }

    // ─── STORE MANAGEMENT ────────────────────────────────────────────────────────

    public function approveStore(TeamStore $store)
    {
        $store->update(['status' => 'approved']);
        $store->user->notify(new \App\Notifications\StoreApproved($store));
        return redirect()->route('admin.dashboard')
            ->with('success', "Store \"{$store->name}\" has been activated.");
    }

    public function declineStore(TeamStore $store)
    {
        $store->update(['status' => 'declined']);
        return redirect()->route('admin.dashboard')
            ->with('success', "Store \"{$store->name}\" has been declined.");
    }

    public function editStore(TeamStore $store)
    {
        $store->load(['user', 'items', 'parentOrders']);
        return view('admin.store_edit', compact('store'));
    }

    public function updateStore(Request $request, TeamStore $store)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'package_type' => ['nullable', 'in:package_a,package_b,package_c,individual'],
            'order_deadline' => ['nullable', 'date'],
            'status'       => ['required', 'in:pending,approved,submitted_to_admin,declined'],
            'pricing_approved' => ['boolean']
        ]);

        $validated['pricing_approved'] = $request->boolean('pricing_approved');

        $store->update($validated);

        return redirect()->route('admin.store.edit', $store)
            ->with('success', "Store \"{$store->name}\" has been updated.");
    }

    public function updateStorePricing(Request $request, TeamStore $store)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.retail_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($request->items as $itemId => $prices) {
            $storeItem = $store->items()->find($itemId);
            if ($storeItem) {
                $storeItem->update([
                    'wholesale_price' => $prices['wholesale_price'] ?? null,
                    'retail_price' => $prices['retail_price'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Store item pricing updated.');
    }

    // ─── ORDER MANAGEMENT ────────────────────────────────────────────────────────

    public function editOrder(ParentOrder $order)
    {
        $order->load('teamStore.user');
        $sizeChart = DesignCatalog::sizeChart();
        return view('admin.order_edit', compact('order', 'sizeChart'));
    }

    public function updateOrder(Request $request, ParentOrder $order)
    {
        $request->validate([
            'athlete_name'  => ['required', 'string', 'max:255'],
            'special_notes' => ['nullable', 'string'],
            'items'         => ['required', 'array'],
        ]);

        // Rebuild items JSON from form data
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
            'edited_by'     => 'admin',
        ]);

        return redirect()->route('admin.order.edit', $order)
            ->with('success', 'Order updated successfully.');
    }

    // ─── CSV EXPORT ──────────────────────────────────────────────────────────────

    public function exportOrderCSV(TeamStore $store)
    {
        $orders = $store->parentOrders;

        $filename = "{$store->slug}-master-order.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Athlete Name', 'Gender', 'Item', 'Type', 'Size', 'Qty', 'Name on Item', 'Number', 'Special Notes', 'Edited?'];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                if (is_array($order->items_json)) {
                    foreach ($order->items_json as $item) {
                        fputcsv($file, [
                            $order->athlete_name,
                            $order->gender ?? 'Not Specified',
                            $item['name'] ?? 'Unknown Item',
                            $item['type'] ?? 'N/A',
                            $item['size'] ?? 'N/A',
                            $item['qty'] ?? 1,
                            $item['name_on_item'] ?? '',
                            $item['number'] ?? '',
                            $order->special_notes ?? '',
                            $order->is_edited ? 'Yes' : 'No',
                        ]);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── LANDING COLLECTIONS ──────────────────────────────────────────────────────

    public function createCollection(Request $request)
    {
        $validated = $request->validate([
            'tab_name'    => ['required', 'string', 'max:255'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'sort_order'  => ['required', 'integer'],
            'image'       => ['required', 'image', 'max:10240'], // Increased to 10MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('collections', 'public');
            $imagePath = '/storage/' . $path;
        }

        LandingCollection::create([
            'tab_name'    => $validated['tab_name'],
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'sort_order'  => $validated['sort_order'],
            'image_path'  => $imagePath,
            'is_active'   => true,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Landing collection added successfully.');
    }

    public function deleteCollection(LandingCollection $collection)
    {
        $collection->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Landing collection removed.');
    }
}
