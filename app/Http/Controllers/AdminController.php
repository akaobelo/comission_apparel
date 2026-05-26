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
use App\Models\PasswordResetLog;
use App\Models\Testimonial;
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

        // Finalized store batches (preserves history even if store is re-opened)
        $finalizedStoreBatches = ParentOrder::whereNotNull('team_store_id')
            ->whereNotNull('batch_id')
            ->where('status', '!=', 'Pending')
            ->where('is_archived', false)
            ->with(['user', 'teamStore', 'teamStore.items'])
            ->latest()
            ->get()
            ->groupBy('batch_id')
            ->map(function ($orders) {
                $store = $orders->first()->teamStore;
                $financials = \App\Models\ParentOrder::calculateBatchFinancials($orders, $store);
                return [
                    'orders' => $orders,
                    'financials' => $financials,
                ];
            });

        // Design catalog
        $designCatalog = DesignCatalog::orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        // Production orders (in production status)
        $productionStores = TeamStore::where('status', 'approved')
            ->where('is_archived', false)
            ->with(['user', 'parentOrders'])
            ->latest()
            ->get();

        // Archived stores
        $archivedStores = TeamStore::where('is_archived', true)
            ->with(['user', 'parentOrders'])
            ->latest()
            ->get();

        // Finalized direct orders (no team store)
        $finalizedDirectOrders = ParentOrder::whereNull('team_store_id')
            ->whereNotNull('batch_id')
            ->where('status', '!=', 'Pending')
            ->where('is_archived', false)
            ->with('user')
            ->latest()
            ->get();
        $finalizedDirectOrderBatches = $finalizedDirectOrders->groupBy('batch_id')->map(function ($orders) {
            $financials = \App\Models\ParentOrder::calculateBatchFinancials($orders, null);
            return [
                'orders' => $orders,
                'financials' => $financials,
            ];
        });

        $archivedOrderBatches = ParentOrder::where('is_archived', true)
            ->with(['user', 'teamStore'])
            ->latest()
            ->get()
            ->groupBy('batch_id')
            ->map(function ($orders) {
                $store = $orders->first()->teamStore;
                $financials = \App\Models\ParentOrder::calculateBatchFinancials($orders, $store);
                return [
                    'orders' => $orders,
                    'financials' => $financials,
                ];
            });

        $quoteRequestsQuery = \App\Models\QuoteRequest::query();

        if ($request->filled('quote_search')) {
            $qs = $request->input('quote_search');
            $quoteRequestsQuery->where(function ($q) use ($qs) {
                $q->where('organization_name', 'like', "%{$qs}%")
                  ->orWhere('first_name', 'like', "%{$qs}%")
                  ->orWhere('last_name', 'like', "%{$qs}%")
                  ->orWhere('email', 'like', "%{$qs}%");
            });
        }

        $quoteRequests = Schema::hasTable('quote_requests')
            ? $quoteRequestsQuery->latest()->paginate(10, ['*'], 'quote_page')->withQueryString()
            : collect();

        $quoteRequestsTotal = Schema::hasTable('quote_requests') ? \App\Models\QuoteRequest::count() : 0;
        $newQuoteRequestsCount = Schema::hasTable('quote_requests') ? \App\Models\QuoteRequest::where('status', 'new')->count() : 0;

        $landingCollections = LandingCollection::orderBy('sort_order', 'asc')->get();

        $allStores = TeamStore::with('user')->latest()->get();
        $allCoaches = User::where('role', 'coach')->orderBy('organization')->get();

        $availableSports = DesignCatalog::whereNotNull('sport')
            ->where('sport', '!=', '')
            ->distinct()
            ->pluck('sport')
            ->merge(
                LandingCollection::whereNotNull('tab_name')
                    ->where('tab_name', '!=', '')
                    ->distinct()
                    ->pluck('tab_name')
            )
            ->unique()
            ->sort()
            ->values();

        $designCollections = \App\Models\DesignCollection::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        $passwordResetLogs = PasswordResetLog::with('user')->latest()->get();

        $testimonials = Testimonial::orderBy('sort_order', 'asc')->get();

        $heroSettings = [
            'subtitle'   => \App\Models\SiteSetting::where('key', 'hero_subtitle')->value('value') ?? 'Premium armor tailored for programs that demand greatness. Built for the modern athlete, delivered with lightning speed.',
            'media_path' => \App\Models\SiteSetting::where('key', 'hero_media_path')->value('value'),
            'media_type' => \App\Models\SiteSetting::where('key', 'hero_media_type')->value('value') ?? 'image',
        ];

        $campaignStores = TeamStore::whereHas('user', function($q) {
            $q->where('role', 'admin');
        })->latest()->get();

        return view('admin.dashboard', compact(
            'coaches', 'pendingStores', 'finalizedStoreBatches',
            'designCatalog', 'productionStores', 'quoteRequests', 'quoteRequestsTotal', 'newQuoteRequestsCount', 'landingCollections', 'allStores', 'allCoaches',
            'availableSports', 'designCollections', 'passwordResetLogs', 'testimonials', 'heroSettings', 'campaignStores', 'archivedStores', 'finalizedDirectOrderBatches', 'archivedOrderBatches'
        ));
    }

    // ─── COACH MANAGEMENT ────────────────────────────────────────────────────────

    public function editCoach(User $user)
    {
        if ($user->role !== 'coach') abort(404);
        $designCatalog = DesignCatalog::orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
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
            'name'                 => ['required', 'string', 'max:255'],
            'design_collection_id' => ['nullable', 'exists:design_collections,id'],
            'sport'            => ['nullable', 'string', 'max:100'],
            'types'            => ['required', 'array', 'min:1'],
            'types.*'          => ['string', 'in:accessory,arm_sleeve,backpack,headwear,hoodie,jacket,leggings,pants,polo,shirt_short,shirt_long,shorts,socks,uniform_top,uniform_bottom,uniform_set,uniform_set_2,warmup_top,warmup_bottom,warmup_set,warmup_set_2,uniform_package_gold,uniform_package_silver,uniform_package_bronze,uniform_package_custom'],
            'category'         => ['required', 'string', 'max:255'],
            'images'           => ['nullable', 'array', 'max:100'],
            'images.*'         => ['image', 'max:10240'], // max 10MB per image
            'has_name_field'   => ['boolean'],
            'has_number_field' => ['boolean'],
            'notes'            => ['nullable', 'string'],
            'description'      => ['nullable', 'string'],
            'wholesale_price'  => ['nullable', 'numeric', 'min:0'],
            'sort_order'       => ['nullable', 'integer'],
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
        
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = DesignCatalog::max('sort_order') + 1;
        }

        DesignCatalog::create($validated);

        return redirect()->route('admin.dashboard')
            ->with('success', "Design \"{$validated['name']}\" added to catalog.");
    }

    public function deleteDesign(DesignCatalog $design)
    {
        $name = $design->name;

        // Delete associated images
        if (!empty($design->image_paths)) {
            foreach ($design->image_paths as $path) {
                $pathToRemove = str_replace('/storage/', '', $path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
        } elseif (!empty($design->image_url)) {
            $pathToRemove = str_replace('/storage/', '', $design->image_url);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
        }
        // Delete associated store items so they are removed from all coach stores
        \App\Models\StoreItem::where('design_catalog_id', $design->id)->delete();

        $design->delete();
        return redirect()->route('admin.dashboard')
            ->with('success', "Design \"{$name}\" removed from catalog.");
    }

    public function updateDesign(Request $request, DesignCatalog $design)
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'design_collection_id' => ['nullable', 'exists:design_collections,id'],
            'sport'            => ['nullable', 'string', 'max:100'],
            'types'            => ['required', 'array', 'min:1'],
            'types.*'          => ['string', 'in:accessory,arm_sleeve,backpack,headwear,hoodie,jacket,leggings,pants,polo,shirt_short,shirt_long,shorts,socks,uniform_top,uniform_bottom,uniform_set,uniform_set_2,warmup_top,warmup_bottom,warmup_set,warmup_set_2,uniform_package_gold,uniform_package_silver,uniform_package_bronze,uniform_package_custom'],
            'category'         => ['required', 'string', 'max:255'],
            'images'           => ['nullable', 'array', 'max:100'],
            'images.*'         => ['image', 'max:10240'],
            'has_name_field'   => ['boolean'],
            'has_number_field' => ['boolean'],
            'notes'            => ['nullable', 'string'],
            'description'      => ['nullable', 'string'],
            'wholesale_price'  => ['nullable', 'numeric', 'min:0'],
            'sort_order'       => ['nullable', 'integer'],
        ]);

        $validated['has_name_field'] = $request->boolean('has_name_field');
        $validated['has_number_field'] = $request->boolean('has_number_field');

        $existingPaths = is_array($design->image_paths) ? $design->image_paths : [];

        // If no image paths exist but a legacy image_url exists, migrate it
        if (empty($existingPaths) && !empty($design->image_url)) {
            $existingPaths[] = $design->image_url;
        }

        // Handle explicit reordering from frontend
        if ($request->has('existing_images') && is_array($request->existing_images)) {
            // Keep only paths that were originally part of the design to prevent spoofing
            $reordered = [];
            foreach ($request->existing_images as $path) {
                if (in_array($path, $existingPaths)) {
                    $reordered[] = $path;
                }
            }
            $existingPaths = $reordered;
        }

        // Handle image removals
        if ($request->has('remove_images') && is_array($request->remove_images)) {
            foreach ($request->remove_images as $pathToRemoveRaw) {
                if (($key = array_search($pathToRemoveRaw, $existingPaths)) !== false) {
                    $pathToRemove = str_replace('/storage/', '', $pathToRemoveRaw);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
                    unset($existingPaths[$key]);
                }
            }
            $existingPaths = array_values($existingPaths); // re-index
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('designs', 'public');
                $existingPaths[] = '/storage/' . $path;
            }
        }
        
        $validated['image_paths'] = array_values($existingPaths);
        $validated['image_url'] = null;
        $validated['type'] = null;

        $design->update($validated);

        // Keep existing store items in sync when base design details change.
        $syncData = [
            'name' => $validated['name'],
            'types' => $validated['types'],
            'wholesale_price' => $validated['wholesale_price'] ?? null,
        ];
        
        if (isset($validated['image_paths'])) {
            $syncData['image_paths'] = $validated['image_paths'];
            $syncData['image_url'] = null;
        }

        StoreItem::where('design_catalog_id', $design->id)->update($syncData);

        return redirect()->route('admin.dashboard')
            ->with('success', "Design \"{$design->name}\" updated.");
    }

    public function bulkSortDesigns(Request $request)
    {
        $request->validate([
            'designs' => ['required', 'array'],
            'designs.*.sort_order' => ['required', 'numeric'],
        ]);

        foreach ($request->designs as $designId => $data) {
            $design = \App\Models\DesignCatalog::find($designId);
            if ($design) {
                $design->update(['sort_order' => $data['sort_order']]);
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Design catalog sort orders updated.");
    }

    public function bulkAssignDesigns(Request $request)
    {
        $request->validate([
            'coach_id' => ['required', 'exists:users,id'],
            'design_ids' => ['required', 'array'],
            'design_ids.*' => ['required', 'exists:design_catalog,id'],
        ]);

        $coach = User::where('role', 'coach')->findOrFail($request->coach_id);

        $coach->designCatalog()->syncWithoutDetaching($request->design_ids);

        return redirect()->route('admin.dashboard')
            ->with('success', count($request->design_ids) . " designs successfully assigned to {$coach->first_name} {$coach->last_name}'s profile!");
    }

    public function bulkUpdateDesignCollections(Request $request)
    {
        $request->validate([
            'collections' => ['required', 'array'],
            'collections.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->collections as $collectionId => $data) {
            $collection = \App\Models\DesignCollection::find($collectionId);
            if ($collection) {
                $collection->update(['sort_order' => $data['sort_order']]);
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Design collections sort orders updated.");
    }

    // ─── DESIGN COLLECTION MANAGEMENT ───────────────────────────────────────────

    public function createDesignCollection(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'image'      => ['nullable', 'image', 'max:10240'],
            'sort_order' => ['nullable', 'integer'],
            'sports'     => ['nullable', 'array'],
            'sports.*'   => ['string'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('collections', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = \App\Models\DesignCollection::max('sort_order') + 1;
        }

        $validated['sports'] = $request->input('sports', []);

        \App\Models\DesignCollection::create($validated);

        return redirect()->route('admin.dashboard')
            ->with('success', "Collection \"{$validated['name']}\" added.");
    }

    public function updateDesignCollection(Request $request, \App\Models\DesignCollection $collection)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'image'      => ['nullable', 'image', 'max:10240'],
            'sort_order' => ['required', 'integer'],
            'sports'     => ['nullable', 'array'],
            'sports.*'   => ['string'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old
            if ($collection->image_path) {
                $pathToRemove = str_replace('/storage/', '', $collection->image_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
            $path = $request->file('image')->store('collections', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $validated['sports'] = $request->input('sports', []);

        $collection->update($validated);

        return redirect()->route('admin.dashboard')
            ->with('success', "Collection \"{$validated['name']}\" updated.");
    }

    public function deleteDesignCollection(\App\Models\DesignCollection $collection)
    {
        if ($collection->image_path) {
            $pathToRemove = str_replace('/storage/', '', $collection->image_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
        }

        // Associated designs will have their design_collection_id set to null automatically due to nullOnDelete constraint.
        $collection->delete();

        return redirect()->route('admin.dashboard')
            ->with('success', "Collection removed.");
    }

    public function manageCollection(\App\Models\DesignCollection $collection)
    {
        $collection->load(['designs' => function($q) {
            $q->orderBy('sort_order', 'desc')->orderBy('created_at', 'desc');
        }]);

        // Get designs that are NOT in this collection to show in the "Add Design" dropdown
        $availableDesigns = \App\Models\DesignCatalog::where('design_collection_id', '!=', $collection->id)
            ->orWhereNull('design_collection_id')
            ->orderBy('name')
            ->get();

        return view('admin.design_collection_manage', compact('collection', 'availableDesigns'));
    }

    public function addDesignToCollection(Request $request, \App\Models\DesignCollection $collection)
    {
        $request->validate([
            'design_catalog_id' => ['required', 'exists:design_catalog,id'],
        ]);

        $design = \App\Models\DesignCatalog::findOrFail($request->design_catalog_id);
        
        // Auto sort order logic
        $maxSort = $collection->designs()->max('sort_order') ?? 0;

        $design->update([
            'design_collection_id' => $collection->id,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->route('admin.design-collection.manage', $collection)
            ->with('success', "Design \"{$design->name}\" added to collection.");
    }

    public function removeDesignFromCollection(Request $request, \App\Models\DesignCollection $collection, \App\Models\DesignCatalog $design)
    {
        if ($design->design_collection_id == $collection->id) {
            $design->update(['design_collection_id' => null]);
            return redirect()->route('admin.design-collection.manage', $collection)
                ->with('success', "Design \"{$design->name}\" removed from collection.");
        }

        return redirect()->route('admin.design-collection.manage', $collection)
            ->with('error', "Design does not belong to this collection.");
    }

    public function updateCollectionDesignsSort(Request $request, \App\Models\DesignCollection $collection)
    {
        $request->validate([
            'designs' => ['required', 'array'],
            'designs.*.id' => ['required', 'exists:design_catalog,id'],
            'designs.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->designs as $designData) {
            $design = \App\Models\DesignCatalog::find($designData['id']);
            if ($design && $design->design_collection_id == $collection->id) {
                $design->update(['sort_order' => $designData['sort_order']]);
            }
        }

        return redirect()->route('admin.design-collection.manage', $collection)
            ->with('success', "Collection sort order updated.");
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

    public function assignToCoachProfile(Request $request, DesignCatalog $design)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id'
        ]);

        $coach = User::where('role', 'coach')->findOrFail($request->coach_id);

        // Ensure coach has access to the design
        if (!$coach->designCatalog()->where('design_catalog_id', $design->id)->exists()) {
            $coach->designCatalog()->attach($design->id);
            return back()->with('success', "{$design->name} was successfully assigned to {$coach->first_name} {$coach->last_name}'s profile!");
        }

        return back()->with('error', "{$design->name} is already assigned to {$coach->first_name} {$coach->last_name}.");
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

    public function createCampaignStore(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'order_deadline' => 'nullable|date',
        ]);

        $user = $request->user();

        $store = TeamStore::create([
            'user_id'      => $user->id,
            'name'         => $request->name,
            'description'  => $request->description,
            'slug'         => Str::slug($request->name) . '-' . strtolower(Str::random(6)),
            'package_type' => 'individual', // Admins usually sell individually for campaigns
            'status'       => 'approved', // Auto-approved since admin created it
            'pricing_approved' => true,   // Auto-approved
            'order_deadline' => $request->order_deadline,
        ]);

        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Campaign Store created successfully. You can now assign designs to it.');
    }

    public function archiveStore(TeamStore $store)
    {
        $store->update(['is_archived' => true]);
        return back()->with('success', "Store \"{$store->name}\" has been archived.");
    }

    public function unarchiveStore(TeamStore $store)
    {
        $store->update(['is_archived' => false]);
        return back()->with('success', "Store \"{$store->name}\" has been unarchived.");
    }

    public function deleteArchivedStore(TeamStore $store)
    {
        if (! $store->is_archived) {
            return back()->with('error', 'Only archived stores can be permanently deleted from the archive.');
        }

        $storeName = $store->name;
        $store->delete();

        return back()->with('success', "Archived store \"{$storeName}\" has been deleted.");
    }

    public function editStore(TeamStore $store)
    {
        $store->load(['user', 'items', 'parentOrders']);
        $financials = \App\Models\ParentOrder::calculateBatchFinancials($store->parentOrders, $store);
        $allDesigns = \App\Models\DesignCatalog::latest()->get();
        return view('admin.store_edit', compact('store', 'financials', 'allDesigns'));
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
            'items.*.sort_order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $itemId => $data) {
            $storeItem = $store->items()->find($itemId);
            if ($storeItem) {
                $storeItem->update([
                    'wholesale_price' => $data['wholesale_price'] ?? null,
                    'retail_price' => $data['retail_price'] ?? null,
                    'sort_order' => $data['sort_order'] ?? $storeItem->sort_order,
                ]);
            }
        }

        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Store item pricing updated.');
    }

    public function attachPackageComponent(Request $request, TeamStore $store, StoreItem $package)
    {
        $request->validate([
            'component_id' => 'required|exists:store_items,id'
        ]);

        if ($package->team_store_id !== $store->id) {
            abort(403, 'Package does not belong to this store.');
        }

        $component = StoreItem::findOrFail($request->component_id);
        if ($component->team_store_id !== $store->id) {
            abort(403, 'Component does not belong to this store.');
        }

        // Check if already attached
        if (!$package->components()->where('component_id', $component->id)->exists()) {
            $package->components()->attach($component->id);
        }

        return redirect()->route('admin.store.edit', $store)
            ->with('success', "Added component to package.");
    }

    public function detachPackageComponent(Request $request, TeamStore $store, StoreItem $package, StoreItem $component)
    {
        if ($package->team_store_id !== $store->id) {
            abort(403, 'Package does not belong to this store.');
        }

        $package->components()->detach($component->id);

        return redirect()->route('admin.store.edit', $store)
            ->with('success', "Removed component from package.");
    }

    public function updateCoverImage(Request $request, TeamStore $store)
    {
        $request->validate([
            'cover_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($request->hasFile('cover_image')) {
            if ($store->cover_image_path) {
                $pathToRemove = str_replace('/storage/', '', $store->cover_image_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $store->update(['cover_image_path' => $path]);
        }

        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Store cover image updated successfully.');
    }

    public function updateStoreLogo(Request $request, TeamStore $store)
    {
        $user = $store->user;

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($request->hasFile('logo')) {
            if ($user->logo_path) {
                $pathToRemove = str_replace('/storage/', '', $user->logo_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
            $path = $request->file('logo')->store('organization_logos', 'public');
            $user->update(['logo_path' => $path]);
        }

        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Organization logo updated successfully.');
    }

    public function addStoreItem(Request $request, TeamStore $store)
    {
        $request->validate([
            'design_catalog_id' => ['required', 'exists:design_catalog,id'],
        ]);

        $design = \App\Models\DesignCatalog::findOrFail($request->design_catalog_id);

        if ($store->items()->where('design_catalog_id', $design->id)->exists()) {
            return redirect()->route('admin.store.edit', $store)
                ->with('error', 'That design is already added to the store.');
        }

        $maxSort = $store->items()->max('sort_order') ?? 0;

        $store->items()->create([
            'design_catalog_id' => $design->id,
            'name'              => $design->name,
            'type'              => null,
            'types'             => $design->types,
            'image_url'         => null,
            'image_paths'       => $design->image_paths,
            'wholesale_price'   => $design->wholesale_price,
            'retail_price'      => $design->wholesale_price,
            'sort_order'        => $maxSort + 1,
        ]);

        return redirect()->route('admin.store.edit', $store)
            ->with('success', "\"{$design->name}\" added to the store.");
    }

    public function removeStoreItem(Request $request, \App\Models\StoreItem $item)
    {
        $store = $item->teamStore;
        $item->delete();
        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Item removed from store.');
    }

    // ─── DIRECT ORDER BATCH REVIEW ───────────────────────────────────────────────

    public function showDirectBatch($batchId)
    {
        $orders = ParentOrder::whereNull('team_store_id')
            ->where('batch_id', $batchId)
            ->where('is_archived', false)
            ->with('user')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('admin.dashboard')->with('error', 'Direct Order Batch not found or empty.');
        }

        $financials = \App\Models\ParentOrder::calculateBatchFinancials($orders, null);
        $firstOrder = $orders->first();

        return view('admin.direct_batch_show', compact('batchId', 'orders', 'financials', 'firstOrder'));
    }

    public function markDirectBatchAddressed($batchId)
    {
        ParentOrder::whereNull('team_store_id')
            ->where('batch_id', $batchId)
            ->update(['status' => 'Processing', 'is_archived' => true]);
            
        return back()->with('success', 'Direct Order Batch marked as addressed and archived.');
    }

    public function markStoreBatchAddressed($batchId)
    {
        ParentOrder::whereNotNull('team_store_id')
            ->where('batch_id', $batchId)
            ->update(['status' => 'Processing', 'is_archived' => true]);
            
        return back()->with('success', 'Master Order Batch marked as addressed and archived.');
    }

    public function deleteArchivedOrderBatch($batchId)
    {
        $orders = ParentOrder::where('batch_id', $batchId)
            ->where('is_archived', true)
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Archived order batch not found.');
        }

        ParentOrder::where('batch_id', $batchId)
            ->where('is_archived', true)
            ->delete();

        return back()->with('success', 'Archived order batch has been permanently deleted.');
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
            'athlete_first_name'  => ['required', 'string', 'max:255'],
            'athlete_last_name'   => ['required', 'string', 'max:255'],
            'gender'              => ['nullable', 'string', 'max:50'],
            'jersey_name'         => ['nullable', 'string', 'max:255'],
            'jersey_number'       => ['nullable', 'string', 'max:10'],
            'backpack_name'       => ['nullable', 'string', 'max:255'],
            'special_notes'       => ['nullable', 'string'],
            'items'               => ['required', 'array'],
        ]);

        // Rebuild items JSON from form data
        $itemsJson = [];
        foreach ($request->items as $idx => $item) {
            $itemsJson[] = [
                'id'           => $item['id'] ?? $idx,
                'name'         => $item['name'] ?? 'Unknown',
                'types'        => $item['types'] ?? [],
                'sizes'        => $item['sizes'] ?? [],
                'qty'          => $item['qty'] ?? 1,
            ];
        }

        $order->update([
            'athlete_first_name'  => $request->athlete_first_name,
            'athlete_last_name'   => $request->athlete_last_name,
            'gender'              => $request->gender,
            'jersey_name'         => $request->jersey_name,
            'jersey_number'       => $request->jersey_number,
            'backpack_name'       => $request->backpack_name,
            'special_notes'       => $request->special_notes,
            'items_json'          => $itemsJson,
            'is_edited'           => true,
            'edited_by'           => 'admin',
        ]);

        return redirect()->route('admin.order.edit', $order)
            ->with('success', 'Order updated successfully.');
    }

    public function deleteOrder(ParentOrder $order)
    {
        $store = $order->teamStore;
        $batchId = $order->batch_id;
        $fullName = trim($order->athlete_first_name . ' ' . $order->athlete_last_name);
        $order->delete();
        
        if ($store) {
            return redirect()->route('admin.store.edit', $store)
                ->with('success', "Order for {$fullName} has been deleted.");
        }
        
        if ($batchId) {
            return redirect()->route('admin.direct-batch.show', $batchId)
                ->with('success', "Order for {$fullName} has been deleted.");
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Order for {$fullName} has been deleted.");
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

        $columns = [
            'Athlete First Name', 'Athlete Last Name', 'Gender', 
            'Jersey Name', 'Jersey Number', 'Backpack Name',
            'Item', 'Types', 'Sizes', 'Qty', 'Special Notes', 'Edited?'
        ];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                if (is_array($order->items_json)) {
                    foreach ($order->items_json as $item) {
                        $typesStr = isset($item['types']) ? implode(', ', $item['types']) : ($item['type'] ?? 'N/A');
                        
                        $sizesArr = [];
                        if (isset($item['components']) && is_array($item['components'])) {
                            foreach ($item['components'] as $comp) {
                                if (isset($comp['sizes']) && is_array($comp['sizes'])) {
                                    foreach ($comp['sizes'] as $t => $s) {
                                        $sizesArr[] = "{$comp['name']} ($t): $s";
                                    }
                                }
                            }
                        } elseif (isset($item['sizes']) && is_array($item['sizes'])) {
                            foreach ($item['sizes'] as $t => $s) {
                                $sizesArr[] = "$t: $s";
                            }
                        }
                        $sizesStr = !empty($sizesArr) ? implode(' | ', $sizesArr) : ($item['size'] ?? 'N/A');

                        fputcsv($file, [
                            $order->athlete_first_name,
                            $order->athlete_last_name,
                            $order->gender ?? 'Not Specified',
                            $order->jersey_name ?? '',
                            $order->jersey_number ?? '',
                            $order->backpack_name ?? '',
                            $item['name'] ?? 'Unknown Item',
                            $typesStr,
                            $sizesStr,
                            $item['qty'] ?? 1,
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

    public function exportBatchCSV(Request $request, $batchId)
    {
        $orders = ParentOrder::where('batch_id', $batchId)->get();

        if ($orders->isEmpty()) abort(404);

        $filename = "batch-order-{$batchId}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'First Name', 'Last Name', 'Gender', 
            'Jersey Name', 'Jersey Number', 'Backpack Name',
            'Item', 'Types', 'Sizes', 'Qty', 'Special Notes', 'Edited?'
        ];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                if (is_array($order->items_json)) {
                    foreach ($order->items_json as $item) {
                        $typesStr = isset($item['types']) ? implode(', ', $item['types']) : ($item['type'] ?? 'N/A');
                        
                        $sizesArr = [];
                        if (isset($item['components']) && is_array($item['components'])) {
                            foreach ($item['components'] as $comp) {
                                if (isset($comp['sizes']) && is_array($comp['sizes'])) {
                                    foreach ($comp['sizes'] as $t => $s) {
                                        $sizesArr[] = "{$comp['name']} ($t): $s";
                                    }
                                }
                            }
                        } elseif (isset($item['sizes']) && is_array($item['sizes'])) {
                            foreach ($item['sizes'] as $t => $s) {
                                $sizesArr[] = "$t: $s";
                            }
                        }
                        $sizesStr = !empty($sizesArr) ? implode(' | ', $sizesArr) : ($item['size'] ?? 'N/A');

                        fputcsv($file, [
                            $order->athlete_first_name,
                            $order->athlete_last_name,
                            $order->gender ?? 'Not Specified',
                            $order->jersey_name ?? '',
                            $order->jersey_number ?? '',
                            $order->backpack_name ?? '',
                            $item['name'] ?? 'Unknown Item',
                            $typesStr,
                            $sizesStr,
                            $item['qty'] ?? 1,
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

    public function exportBatchAggregationCSV(Request $request, $batchId)
    {
        $orders = ParentOrder::where('batch_id', $batchId)->get();

        if ($orders->isEmpty()) abort(404);

        $filename = "batch-aggregate-{$batchId}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Item Name', 'Type', 'Size', 'Total Quantity'];
        
        $aggregated = [];

        foreach ($orders as $order) {
            if (is_array($order->items_json)) {
                foreach ($order->items_json as $item) {
                    $itemQty = max(1, (int)($item['qty'] ?? 1));

                    if (isset($item['components']) && is_array($item['components'])) {
                        foreach ($item['components'] as $comp) {
                            $compQty = $itemQty * max(1, (int)($comp['qty'] ?? 1));
                            $name = $comp['name'] ?? 'Unknown Component';
                            
                            if (isset($comp['sizes']) && is_array($comp['sizes'])) {
                                foreach ($comp['sizes'] as $type => $size) {
                                    $key = "{$name}|{$type}|{$size}";
                                    $aggregated[$key] = ($aggregated[$key] ?? 0) + $compQty;
                                }
                            } else {
                                $type = isset($comp['types']) ? implode(', ', $comp['types']) : ($comp['type'] ?? 'N/A');
                                $key = "{$name}|{$type}|N/A";
                                $aggregated[$key] = ($aggregated[$key] ?? 0) + $compQty;
                            }
                        }
                    } else {
                        $name = $item['name'] ?? 'Unknown Item';
                        
                        if (isset($item['sizes']) && is_array($item['sizes'])) {
                            foreach ($item['sizes'] as $type => $size) {
                                $key = "{$name}|{$type}|{$size}";
                                $aggregated[$key] = ($aggregated[$key] ?? 0) + $itemQty;
                            }
                        } else {
                            $type = isset($item['types']) ? implode(', ', $item['types']) : ($item['type'] ?? 'N/A');
                            $key = "{$name}|{$type}|N/A";
                            $aggregated[$key] = ($aggregated[$key] ?? 0) + $itemQty;
                        }
                    }
                }
            }
        }

        $callback = function() use ($aggregated, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($aggregated as $key => $qty) {
                $parts = explode('|', $key);
                fputcsv($file, [$parts[0], $parts[1], $parts[2], $qty]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function updateBatchStatus(Request $request, $batchId)
    {
        $request->validate([
            'status' => 'required|string|max:255',
        ]);

        ParentOrder::where('batch_id', $batchId)->update(['status' => $request->status]);

        return back()->with('success', 'Batch status updated to ' . $request->status . '.');
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

    public function updateCollection(Request $request, LandingCollection $collection)
    {
        $validated = $request->validate([
            'tab_name'    => ['required', 'string', 'max:255'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'sort_order'  => ['required', 'integer'],
            'image'       => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('image')) {
            if ($collection->image_path) {
                $pathToRemove = str_replace('/storage/', '', $collection->image_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
            $path = $request->file('image')->store('collections', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $collection->update($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Landing collection updated successfully.');
    }

    public function deleteCollection(LandingCollection $collection)
    {
        if ($collection->image_path) {
            $pathToRemove = str_replace('/storage/', '', $collection->image_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
        }
        $collection->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Landing collection removed.');
    }

    // ─── TESTIMONIALS ────────────────────────────────────────────────────────────

    public function createTestimonial(Request $request)
    {
        $validated = $request->validate([
            'client_name'  => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'sort_order'   => ['required', 'integer'],
            'image'        => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('testimonials', 'public');
            $imagePath = '/storage/' . $path;
        }

        Testimonial::create([
            'client_name'  => $validated['client_name'],
            'organization' => $validated['organization'],
            'content'      => $validated['content'],
            'sort_order'   => $validated['sort_order'],
            'image_path'   => $imagePath,
            'is_active'    => true,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Testimonial added successfully.');
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'client_name'  => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'sort_order'   => ['required', 'integer'],
            'image'        => ['nullable', 'image', 'max:5120'],
            'is_active'    => ['nullable', 'boolean']
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($testimonial->image_path) {
                $pathToRemove = str_replace('/storage/', '', $testimonial->image_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
            $path = $request->file('image')->store('testimonials', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $validated['is_active'] = $request->has('is_active');

        $testimonial->update($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Testimonial updated successfully.');
    }

    public function deleteTestimonial(Testimonial $testimonial)
    {
        if ($testimonial->image_path) {
            $pathToRemove = str_replace('/storage/', '', $testimonial->image_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
        }
        $testimonial->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Testimonial removed.');
    }

    // ─── HERO SETTINGS ────────────────────────────────────────────────────────────

    public function updateHeroSettings(Request $request)
    {
        $validated = $request->validate([
            'hero_subtitle' => ['required', 'string'],
            'hero_media'    => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi', 'max:20480'], // 20MB max
        ]);

        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'hero_subtitle'],
            ['value' => $validated['hero_subtitle']]
        );

        if ($request->hasFile('hero_media')) {
            $file = $request->file('hero_media');
            $mimeType = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());
            $videoExtensions = ['mp4', 'mov', 'avi', 'webm', 'ogg', 'mkv'];
            
            $isImage = str_starts_with($mimeType, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
            $isVideo = str_starts_with($mimeType, 'video/') || in_array($extension, $videoExtensions);

            if ($isImage || $isVideo) {
                // Delete old media if it exists
                $oldMediaPath = \App\Models\SiteSetting::where('key', 'hero_media_path')->value('value');
                if ($oldMediaPath) {
                    $pathToRemove = str_replace('/storage/', '', $oldMediaPath);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
                }

                $path = $file->store('hero', 'public');
                
                \App\Models\SiteSetting::updateOrCreate(
                    ['key' => 'hero_media_path'],
                    ['value' => '/storage/' . $path]
                );

                \App\Models\SiteSetting::updateOrCreate(
                    ['key' => 'hero_media_type'],
                    ['value' => $isVideo ? 'video' : 'image']
                );
            }
        }

        return redirect()->route('admin.dashboard')->with('success', 'Hero settings updated successfully.');
    }

    public function removeHeroMedia(Request $request)
    {
        $oldMediaPath = \App\Models\SiteSetting::where('key', 'hero_media_path')->value('value');
        if ($oldMediaPath) {
            $pathToRemove = str_replace('/storage/', '', $oldMediaPath);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            
            \App\Models\SiteSetting::where('key', 'hero_media_path')->delete();
            \App\Models\SiteSetting::where('key', 'hero_media_type')->delete();
        }

        return redirect()->route('admin.dashboard')->with('success', 'Hero media removed successfully.');
    }

    public function markQuoteAddressed(\App\Models\QuoteRequest $quoteRequest)
    {
        $quoteRequest->update(['status' => 'addressed']);
        return redirect()->back()->with('success', 'Quote inquiry marked as addressed.');
    }
}
