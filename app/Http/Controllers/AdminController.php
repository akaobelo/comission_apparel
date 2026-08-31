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
use App\Models\SizingChart;
use App\Models\SalesAgent;
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
                  ->orWhere('organization', 'like', "%{$search}%")
                  ->orWhereHas('teamStores', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $coaches = $query->latest()->paginate(20, ['*'], 'coach_page')->withQueryString();

        // Stores pending admin approval
        $pendingStoresQuery = TeamStore::where('status', 'pending')
            ->with('user');

        if ($request->filled('pending_store_search')) {
            $search = $request->pending_store_search;
            $pendingStoresQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('organization', 'like', "%{$search}%");
                  });
            });
        }

        $pendingStores = $pendingStoresQuery->latest()->get();

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

        // Unassigned designs paginated
        $unassignedSearch = $request->input('unassigned_search');
        $unassignedQuery = DesignCatalog::whereNull('design_collection_id')
            ->with(['designCollection', 'coaches']);

        if (!empty($unassignedSearch)) {
            $unassignedQuery->where(function($q) use ($unassignedSearch) {
                $q->where('name', 'like', "%{$unassignedSearch}%")
                  ->orWhere('sport', 'like', "%{$unassignedSearch}%")
                  ->orWhereHas('coaches', function($q2) use ($unassignedSearch) {
                      $q2->where('first_name', 'like', "%{$unassignedSearch}%")
                         ->orWhere('last_name', 'like', "%{$unassignedSearch}%")
                         ->orWhere('organization', 'like', "%{$unassignedSearch}%");
                  });
            });
        }

        $unassignedDesigns = $unassignedQuery->orderBy('sort_order', 'asc')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'unassigned_page')
            ->withQueryString();

        // Production orders (in production status)
        $activeStoresQuery = TeamStore::whereIn('status', ['approved', 'submitted_to_admin'])
            ->where('is_archived', false)
            ->with(['user', 'parentOrders' => function($q) {
                $q->where('is_archived', false);
            }]);

        if ($request->filled('active_store_search')) {
            $search = $request->active_store_search;
            $activeStoresQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('organization', 'like', "%{$search}%");
                  });
            });
        }
        $productionStores = $activeStoresQuery->orderBy('sort_order', 'asc')->orderByDesc('created_at')->orderByDesc('id')->get();

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

        // Archived stores
        $archivedStoresQuery = TeamStore::where('is_archived', true)
            ->with(['user', 'parentOrders']);

        if ($request->filled('archive_search')) {
            $search = $request->archive_search;
            $archivedStoresQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('organization', 'like', "%{$search}%");
                  });
            });
        }
        $archivedStores = $archivedStoresQuery->latest()->paginate(10, ['*'], 'archive_store_page')->withQueryString();

        // Archived batches
        $archivedBatchQuery = ParentOrder::where('is_archived', true)
            ->whereNotNull('batch_id')
            ->select('batch_id')
            ->groupBy('batch_id');

        if ($request->filled('archive_search')) {
            $search = $request->archive_search;
            // Since we're selecting distinct batch_id, we need to join or whereHas carefully.
            // ParentOrder has user_id and team_store_id
            $archivedBatchQuery->where(function($q) use ($search) {
                $q->where('batch_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('organization', 'like', "%{$search}%");
                  })
                  ->orWhereHas('teamStore', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $archivedBatchIds = $archivedBatchQuery->latest('batch_id')->paginate(10, ['*'], 'archive_batch_page')->withQueryString();

        $archivedOrderBatches = ParentOrder::whereIn('batch_id', $archivedBatchIds->pluck('batch_id'))
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

        // Pass the paginator to the view so we can render links
        $archivedBatchesPaginator = $archivedBatchIds;

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

        $landingCollections = LandingCollection::orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->get();

        // Optimize dropdown payloads: select only necessary columns to avoid loading heavy object trees
        $allStores = TeamStore::select('id', 'name', 'user_id')->with(['user' => function($q) {
            $q->select('id', 'first_name', 'last_name', 'organization');
        }])->latest()->get();

        $allCoaches = User::where('role', 'coach')
            ->select('id', 'first_name', 'last_name', 'organization')
            ->with('teamStore:id,user_id,name')
            ->get()
            ->sortBy(function($user) {
                $club = $user->teamStore?->name ?? $user->organization ?? '';
                return strtolower($club);
            });

        $availableSports = config('sports.categories');

        // Paginate collections and eager load designs & coaches for those collections only
        $designCollections = \App\Models\DesignCollection::with(['designs' => function($q) {
            $q->with('coaches')->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
        }])->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->paginate(10, ['*'], 'collection_page')->withQueryString();

        // Complete list of collections for selection dropdowns
        $allCollections = \App\Models\DesignCollection::select('id', 'name')->orderBy('sort_order', 'asc')->get();

        // Paginate password reset logs
        $passwordResetLogs = PasswordResetLog::with(['user' => function($q) {
            $q->select('id', 'first_name', 'last_name', 'email');
        }])->latest()->paginate(10, ['*'], 'password_log_page')->withQueryString();

        $testimonials = Testimonial::orderBy('sort_order', 'asc')->get();

        $sizingCharts = SizingChart::orderBy('sort_order', 'asc')->get();

        $salesAgents = SalesAgent::orderBy('sort_order', 'asc')->get();

        $heroSettings = [
            'subtitle'   => \App\Models\SiteSetting::where('key', 'hero_subtitle')->value('value') ?? 'Premium armor tailored for programs that demand greatness. Built for the modern athlete, delivered with lightning speed.',
            'media_path' => \App\Models\SiteSetting::where('key', 'hero_media_path')->value('value'),
            'media_type' => \App\Models\SiteSetting::where('key', 'hero_media_type')->value('value') ?? 'image',
        ];

        $campaignStores = TeamStore::whereHas('user', function($q) {
            $q->where('role', 'admin');
        })->latest()->get();

        // Optimize memory by plucking store IDs via distinct SQL query
        $storeIds = \App\Models\ParentOrder::whereIn('status', ['In Production', 'Shipped', 'Delivered', 'Processing', 'Completed'])
            ->whereNotNull('team_store_id')
            ->distinct()
            ->pluck('team_store_id');
            
        $storesMap = TeamStore::whereIn('id', $storeIds)->with('items')->get()->keyBy('id');
        
        $globalSalesSummary = [
            'orders_count' => 0,
            'total_sales' => 0,
            'total_wholesale' => 0,
            'net_proceeds' => 0,
            'total_items_sold' => 0,
        ];
        
        // Chunk orders to process them in smaller memory batches, selecting only needed columns
        \App\Models\ParentOrder::whereIn('status', ['In Production', 'Shipped', 'Delivered', 'Processing', 'Completed'])
            ->select('id', 'items_json', 'team_store_id')
            ->chunk(150, function($orders) use (&$globalSalesSummary, $storesMap) {
                foreach ($orders as $order) {
                    $store = $order->team_store_id ? $storesMap->get($order->team_store_id) : null;
                    
                    $orderTotal = 0;
                    $orderWholesaleTotal = 0;
                    $orderItemsCount = 0;
                    $items = is_array($order->items_json) ? $order->items_json : [];

                    foreach ($items as $orderedItem) {
                        $qty = max(1, (int) ($orderedItem['qty'] ?? 1));
                        $orderItemsCount += $qty;

                        $prices = \App\Models\ParentOrder::getItemPrices($orderedItem, $store);
                        $retailPrice = $prices['retail_price'];
                        $wholesalePrice = $prices['wholesale_price'];
                        
                        $orderTotal += ($retailPrice * $qty);
                        $orderWholesaleTotal += ($wholesalePrice * $qty);
                    }
                    
                    $globalSalesSummary['total_sales'] += $orderTotal;
                    $globalSalesSummary['total_wholesale'] += $orderWholesaleTotal;
                    $globalSalesSummary['total_items_sold'] += $orderItemsCount;
                    $globalSalesSummary['orders_count']++;
                }
            });
        
        $globalSalesSummary['net_proceeds'] = $globalSalesSummary['total_sales'] - $globalSalesSummary['total_wholesale'];

        return view('admin.dashboard', compact(
            'coaches', 'pendingStores', 'finalizedStoreBatches',
            'unassignedDesigns', 'allCollections', 'productionStores', 'quoteRequests', 'quoteRequestsTotal', 'newQuoteRequestsCount', 'landingCollections', 'allStores', 'allCoaches',
            'availableSports', 'designCollections', 'passwordResetLogs', 'testimonials', 'sizingCharts', 'heroSettings', 'campaignStores', 'archivedStores', 'finalizedDirectOrderBatches', 'archivedOrderBatches', 'globalSalesSummary', 'archivedBatchesPaginator',
            'salesAgents'
        ));
    }

    // ─── COACH MANAGEMENT ────────────────────────────────────────────────────────

    public function editCoach(User $user)
    {
        if ($user->role !== 'coach') abort(404);
        $designCatalog = DesignCatalog::orderBy('sort_order', 'asc')
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
            'sales_rep'    => ['nullable', 'string', 'max:255'],
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

    public function exportCoaches()
    {
        $coaches = User::where('role', 'coach')->get();
        $filename = "coaches_export_" . date('Y-m-d_H-i') . ".csv";
        $handle = fopen('php://output', 'w');
        
        ob_start();
        fputcsv($handle, ['ID', 'First Name', 'Last Name', 'Email', 'Organization', 'Sport', 'Status', 'Phone', 'Created At']);
        
        foreach ($coaches as $coach) {
            fputcsv($handle, [
                $coach->id,
                $coach->first_name,
                $coach->last_name,
                $coach->email,
                $coach->organization,
                $coach->sport,
                $coach->status,
                $coach->phone,
                $coach->created_at->format('Y-m-d H:i:s')
            ]);
        }
        fclose($handle);
        
        $csv = ob_get_clean();
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
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
                $path = \App\Services\ImageOptimizer::optimize($image, 'designs');
                $imagePaths[] = '/storage/' . $path;
            }
        }
        $validated['image_paths'] = $imagePaths;

        // Make sure type and image_url are set to null since we are migrating to JSON
        $validated['type'] = null;
        $validated['image_url'] = null;
        
        if (!isset($validated['sort_order'])) {
            if (isset($validated['design_collection_id']) && $validated['design_collection_id']) {
                $validated['sort_order'] = (DesignCatalog::where('design_collection_id', $validated['design_collection_id'])->max('sort_order') ?? 0) + 1;
            } else {
                $validated['sort_order'] = (DesignCatalog::whereNull('design_collection_id')->max('sort_order') ?? 0) + 1;
            }
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
                $path = \App\Services\ImageOptimizer::optimize($image, 'designs');
                $existingPaths[] = '/storage/' . $path;
            }
        }
        
        $validated['image_paths'] = array_values($existingPaths);
        $validated['image_url'] = null;
        $validated['type'] = null;

        // If the collection changed, automatically place it at the end of the new collection's sequence
        if (array_key_exists('design_collection_id', $validated) && $design->design_collection_id != $validated['design_collection_id']) {
            if ($validated['design_collection_id']) {
                $validated['sort_order'] = (DesignCatalog::where('design_collection_id', $validated['design_collection_id'])->max('sort_order') ?? 0) + 1;
            } else {
                $validated['sort_order'] = (DesignCatalog::whereNull('design_collection_id')->max('sort_order') ?? 0) + 1;
            }
        }

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
        \Log::info('Designs bulkSortDesigns requested data: ' . json_encode($request->all()));

        $request->validate([
            'designs' => ['required', 'array'],
            'designs.*.sort_order' => ['nullable', 'numeric'],
        ]);

        foreach ($request->designs as $designId => $data) {
            $design = \App\Models\DesignCatalog::find($designId);
            if ($design) {
                $sortOrder = isset($data['sort_order']) && $data['sort_order'] !== '' ? $data['sort_order'] : 0;
                $design->update(['sort_order' => $sortOrder]);
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

    public function bulkSortLandingCollections(Request $request)
    {
        $request->validate([
            'collections' => ['required', 'array'],
            'collections.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->collections as $collectionId => $data) {
            $collection = \App\Models\LandingCollection::find($collectionId);
            if ($collection) {
                $collection->update(['sort_order' => $data['sort_order']]);
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Landing collections sort orders updated.");
    }

    public function bulkSortTestimonials(Request $request)
    {
        $request->validate([
            'testimonials' => ['required', 'array'],
            'testimonials.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->testimonials as $testimonialId => $data) {
            $testimonial = \App\Models\Testimonial::find($testimonialId);
            if ($testimonial) {
                $testimonial->update(['sort_order' => $data['sort_order']]);
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Testimonials sort orders updated.");
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'collections');
            $validated['image_path'] = '/storage/' . $path;
        }

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = 0;
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'collections');
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
            $q->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
        }]);

        // Auto-heal sort orders if they are not sequential starting from 1
        $needsHeal = false;
        foreach ($collection->designs as $index => $design) {
            if ($design->sort_order !== $index + 1) {
                $needsHeal = true;
                break;
            }
        }
        
        if ($needsHeal) {
            foreach ($collection->designs as $index => $design) {
                $design->update(['sort_order' => $index + 1]);
                $design->sort_order = $index + 1;
            }
        }

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

    public function updateStoresSortOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:team_stores,id',
            'items.*.order' => 'nullable|integer',
        ]);

        foreach ($validated['items'] as $item) {
            $order = isset($item['order']) ? (int)$item['order'] : 0;
            TeamStore::where('id', $item['id'])->update(['sort_order' => $order]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Stores sort order updated successfully.');
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
        $store->load(['user', 'items', 'parentOrders' => function($q) {
            $q->where('is_archived', false);
        }]);
        $financials = \App\Models\ParentOrder::calculateBatchFinancials($store->parentOrders, $store);
        $allDesigns = \App\Models\DesignCatalog::orderBy('sort_order', 'asc')->get();
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
            'pricing_approved' => ['boolean'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
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
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.retail_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $itemId => $data) {
            $storeItem = $store->items()->find($itemId);
            if ($storeItem) {
                $storeItem->update([
                    'name' => $data['name'] ?? $storeItem->name,
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('cover_image'), 'covers');
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('logo'), 'organization_logos');
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

        $retailPrice = $request->filled('retail_price') ? max($request->retail_price, $design->wholesale_price) : $design->wholesale_price;

        $store->items()->create([
            'design_catalog_id' => $design->id,
            'name'              => $design->name,
            'type'              => null,
            'types'             => $design->types,
            'image_url'         => null,
            'image_paths'       => $design->image_paths,
            'wholesale_price'   => $design->wholesale_price,
            'retail_price'      => $retailPrice,
            'sort_order'        => $maxSort + 1,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "{$design->name} added to the store."]);
        }

        return redirect()->route('admin.store.edit', $store)
            ->with('success', "\"{$design->name}\" added to the store.");
    }

    public function removeStoreItem(Request $request, \App\Models\StoreItem $item)
    {
        $store = $item->teamStore;
        $item->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Item removed from store.']);
        }
        
        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Item removed from store.');
    }

    // ─── DIRECT ORDER BATCH REVIEW ───────────────────────────────────────────────

    public function showDirectBatch($batchId)
    {
        $orders = ParentOrder::where('batch_id', $batchId)
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
        ParentOrder::where('batch_id', $batchId)
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
        $store->load(['items', 'parentOrders' => function($q) {
            $q->where('is_archived', false);
        }]);
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
            'Store Name', 'Order ID', 'Submission Date', 
            'Athlete First Name', 'Athlete Last Name', 'Email', 'Phone', 'Shipping Address', 'Gender', 
            'Jersey Name', 'Jersey Number', 'Backpack Name',
            'Item Name', 'Item Type(s)', 'Size(s)', 'Quantity', 'Item Price', 'Total Row Price', 'Manufacture Price', 'Total Manufacture Price', 'Special Notes', 'Edited?'
        ];

        $callback = function() use ($store, $orders, $columns) {
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

                        $qty = $item['qty'] ?? 1;
                        $prices = \App\Models\ParentOrder::getItemPrices($item, $store);
                        $itemPrice = $prices['retail_price'];
                        $totalRowPrice = $itemPrice * $qty;
                        $mfgPrice = $prices['wholesale_price'];
                        $totalMfgPrice = $mfgPrice * $qty;

                        fputcsv($file, [
                            $store->name,
                            $order->id,
                            $order->created_at->format('Y-m-d'),
                            $order->athlete_first_name,
                            $order->athlete_last_name,
                            $order->parent_email ?? '',
                            $order->parent_phone ?? '',
                            $order->shipping_address ?? '',
                            $item['gender'] ?? $order->gender ?? 'Not Specified',
                            $order->jersey_name ?? '',
                            $order->jersey_number ?? '',
                            $order->backpack_name ?? '',
                            $item['name'] ?? 'Unknown Item',
                            $typesStr,
                            $sizesStr,
                            $qty,
                            number_format($itemPrice, 2, '.', ''),
                            number_format($totalRowPrice, 2, '.', ''),
                            number_format($mfgPrice, 2, '.', ''),
                            number_format($totalMfgPrice, 2, '.', ''),
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
        $orders = ParentOrder::with(['teamStore.items'])->where('batch_id', $batchId)->get();

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
            'First Name', 'Last Name', 'Email', 'Phone', 'Shipping Address', 'Gender', 
            'Jersey Name', 'Jersey Number', 'Backpack Name',
            'Item', 'Types', 'Sizes', 'Qty', 'Item Price', 'Total Price', 'Manufacture Price', 'Total Manufacture Price', 'Special Notes', 'Edited?'
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

                        $itemPrice = 0;
                        $mfgPrice = 0;
                        $prices = \App\Models\ParentOrder::getItemPrices($item, $order->teamStore);
                        $itemPrice = $prices['retail_price'];
                        $mfgPrice = $prices['wholesale_price'];
                        
                        $qty = $item['qty'] ?? 1;
                        $totalPrice = $itemPrice * $qty;
                        $totalMfgPrice = $mfgPrice * $qty;

                        fputcsv($file, [
                            $order->athlete_first_name,
                            $order->athlete_last_name,
                            $order->parent_email ?? '',
                            $order->parent_phone ?? '',
                            $order->shipping_address ?? '',
                            $item['gender'] ?? $order->gender ?? 'Not Specified',
                            $order->jersey_name ?? '',
                            $order->jersey_number ?? '',
                            $order->backpack_name ?? '',
                            $item['name'] ?? 'Unknown Item',
                            $typesStr,
                            $sizesStr,
                            $qty,
                            number_format($itemPrice, 2, '.', ''),
                            number_format($totalPrice, 2, '.', ''),
                            number_format($mfgPrice, 2, '.', ''),
                            number_format($totalMfgPrice, 2, '.', ''),
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
        $orders = ParentOrder::with(['teamStore.items'])->where('batch_id', $batchId)->get();

        if ($orders->isEmpty()) abort(404);

        $filename = "batch-aggregate-{$batchId}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Item Name', 'Type', 'Size', 'Unit Price', 'Total Quantity', 'Total Price', 'Manufacture Price', 'Total Manufacture Price'];
        
        $aggregated = [];

        foreach ($orders as $order) {
            if (is_array($order->items_json)) {
                foreach ($order->items_json as $item) {
                    $itemQty = max(1, (int)($item['qty'] ?? 1));

                    $itemPrice = 0;
                    $mfgPrice = 0;
                    $prices = \App\Models\ParentOrder::getItemPrices($item, $order->teamStore);
                    $itemPrice = $prices['retail_price'];
                    $mfgPrice = $prices['wholesale_price'];

                    if (isset($item['components']) && is_array($item['components'])) {
                        foreach ($item['components'] as $comp) {
                            $compQty = $itemQty * max(1, (int)($comp['qty'] ?? 1));
                            $name = $comp['name'] ?? 'Unknown Component';
                            
                            if (isset($comp['sizes']) && is_array($comp['sizes'])) {
                                foreach ($comp['sizes'] as $type => $size) {
                                    $key = "{$name}|{$type}|{$size}|{$itemPrice}|{$mfgPrice}";
                                    $aggregated[$key] = ($aggregated[$key] ?? 0) + $compQty;
                                }
                            } else {
                                $type = isset($comp['types']) ? implode(', ', $comp['types']) : ($comp['type'] ?? 'N/A');
                                $key = "{$name}|{$type}|N/A|{$itemPrice}|{$mfgPrice}";
                                $aggregated[$key] = ($aggregated[$key] ?? 0) + $compQty;
                            }
                        }
                    } else {
                        $name = $item['name'] ?? 'Unknown Item';
                        
                        if (isset($item['sizes']) && is_array($item['sizes'])) {
                            foreach ($item['sizes'] as $type => $size) {
                                $key = "{$name}|{$type}|{$size}|{$itemPrice}|{$mfgPrice}";
                                $aggregated[$key] = ($aggregated[$key] ?? 0) + $itemQty;
                            }
                        } else {
                            $type = isset($item['types']) ? implode(', ', $item['types']) : ($item['type'] ?? 'N/A');
                            $key = "{$name}|{$type}|N/A|{$itemPrice}|{$mfgPrice}";
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
                $unitPrice = (float)($parts[3] ?? 0);
                $totalPrice = $unitPrice * $qty;
                $mfgPrice = (float)($parts[4] ?? 0);
                $totalMfgPrice = $mfgPrice * $qty;
                fputcsv($file, [$parts[0], $parts[1], $parts[2], number_format($unitPrice, 2, '.', ''), $qty, number_format($totalPrice, 2, '.', ''), number_format($mfgPrice, 2, '.', ''), number_format($totalMfgPrice, 2, '.', '')]);
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'collections');
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'collections');
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

    // ─── SIZING CHARTS ────────────────────────────────────────────────────────────

    public function createSizingChart(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'sort_order'  => ['required', 'integer'],
            'images'      => ['required', 'array', 'min:1'],
            'images.*'    => ['image', 'max:10240'], // 10MB max
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = \App\Services\ImageOptimizer::optimize($image, 'sizing_charts');
                $imagePaths[] = '/storage/' . $path;
            }
        }

        SizingChart::create([
            'title'       => $validated['title'],
            'sort_order'  => $validated['sort_order'],
            'image_paths' => $imagePaths,
            'is_active'   => true,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Sizing chart added successfully.');
    }

    public function updateSizingChart(Request $request, SizingChart $chart)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'sort_order'  => ['required', 'integer'],
            'images'      => ['nullable', 'array'],
            'images.*'    => ['image', 'max:10240'],
        ]);

        // Get existing paths (supporting legacy image_path or image_paths)
        $existingPaths = $chart->image_paths ?? [];
        if (empty($existingPaths) && !empty($chart->image_path)) {
            $existingPaths[] = $chart->image_path;
        }

        // Handle explicit reordering from frontend
        if ($request->has('existing_images') && is_array($request->existing_images)) {
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

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = \App\Services\ImageOptimizer::optimize($image, 'sizing_charts');
                $existingPaths[] = '/storage/' . $path;
            }
        }

        $validated['image_paths'] = array_values($existingPaths);
        $validated['image_path']  = null;

        unset($validated['images']);
        $chart->update($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Sizing chart updated successfully.');
    }

    public function deleteSizingChart(SizingChart $chart)
    {
        if ($chart->image_paths) {
            foreach ($chart->image_paths as $path) {
                $pathToRemove = str_replace('/storage/', '', $path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
        } elseif ($chart->image_path) {
            $pathToRemove = str_replace('/storage/', '', $chart->image_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
        }
        $chart->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Sizing chart removed.');
    }

    public function updateSizingChartOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:sizing_charts,id',
            'items.*.order' => 'nullable|integer',
        ]);

        foreach ($request->items as $item) {
            $order = isset($item['order']) && $item['order'] !== '' ? (int)$item['order'] : 0;
            SizingChart::where('id', $item['id'])->update(['sort_order' => $order]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Sizing chart order updated successfully.');
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'testimonials');
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
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'testimonials');
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

                if ($isImage) {
                    $path = \App\Services\ImageOptimizer::optimize($file, 'hero');
                } else {
                    $path = $file->store('hero', 'public');
                }
                
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

    // ─── PUBLIC OUR TEAM ────────────────────────────────────────────────────────
    public function publicOurTeam(Request $request)
    {
        $query = SalesAgent::where('is_active', true);

        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('state', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%")
                    ->orWhere('bio', 'like', "%{$q}%");
            });
        }

        $agents = $query->orderBy('sort_order', 'asc')->get();

        // Separate US reps (has state) and International reps (no state, country is not 'USA' / 'US')
        $usAgents = $agents->filter(function($agent) {
            return !empty($agent->state) && in_array(strtoupper(trim($agent->country)), ['USA', 'US', '']);
        })->groupBy(function($agent) {
            return strtoupper(trim($agent->state));
        });

        $intlAgents = $agents->filter(function($agent) {
            $country = strtoupper(trim($agent->country));
            return !in_array($country, ['USA', 'US', '']) || empty($agent->state);
        })->groupBy(function($agent) {
            return strtoupper(trim($agent->country ?: 'USA'));
        });

        return view('our-team', compact('agents', 'usAgents', 'intlAgents'));
    }

    // ─── SALES AGENTS ADMIN CRUD ───────────────────────────────────────────────
    public function createSalesAgent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'sales-agents');
            $validated['image_path'] = '/storage/' . $path;
        }

        SalesAgent::create($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Sales agent added successfully.');
    }

    public function updateSalesAgent(Request $request, SalesAgent $agent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($agent->image_path) {
                $pathToRemove = str_replace('/storage/', '', $agent->image_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
            $path = \App\Services\ImageOptimizer::optimize($request->file('image'), 'sales-agents');
            $validated['image_path'] = '/storage/' . $path;
        }

        $agent->update($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Sales agent updated successfully.');
    }

    public function deleteSalesAgent(SalesAgent $agent)
    {
        $agent->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Sales agent removed.');
    }

    public function bulkSortSalesAgents(Request $request)
    {
        $request->validate([
            'agents' => ['required', 'array'],
            'agents.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->agents as $agentId => $data) {
            $agent = SalesAgent::find($agentId);
            if ($agent) {
                $agent->update(['sort_order' => $data['sort_order']]);
            }
        }

        return redirect()->route('admin.dashboard')->with('success', 'Sales agents sort orders updated.');
    }

    public function editProfile(Request $request)
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'current_password' => ['required', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.'])->withInput();
        }

        $user->email = $request->email;

        if ($request->filled('new_password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->route('admin.dashboard')->with('success', 'Credentials updated successfully.');
    }
}
