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
        $store = $user->teamStore()->with(['items' => function($q) {
            $q->orderBy('sort_order', 'asc');
        }, 'items.designCatalog'])->first();

        // Get unbatched orders for the active store roster
        if ($store) {
            $store->setRelation('parentOrders', $store->parentOrders()->whereNull('batch_id')->get());
        }

        // Designs assigned to this coach (for item builder)
        $assignedDesigns = $user->designCatalog()->latest()->get();

        // All batched orders placed by the coach (Direct Orders AND Store Orders)
        $batchedOrders = $user->parentOrders()->whereNotNull('batch_id')->where('is_archived', false)->latest()->get();
        $archivedBatchedOrders = $user->parentOrders()->whereNotNull('batch_id')->where('is_archived', true)->latest()->get();
        
        // Draft Direct Orders
        $directOrders = $user->parentOrders()->whereNull('team_store_id')->where('status', 'Draft')->where('is_archived', false)->latest()->get();

        // Group batched orders by batch
        $directOrderBatches = $batchedOrders->groupBy('batch_id');
        $archivedOrderBatches = $archivedBatchedOrders->groupBy('batch_id');

        // Organize assigned designs by category for package selection
        $packageDesigns = [
            'package_a' => $assignedDesigns->where('category', 'package_a'),
            'package_b' => $assignedDesigns->where('category', 'package_b'),
            'package_c' => $assignedDesigns->where('category', 'package_c'),
            'individual' => $assignedDesigns->where('category', 'individual'),
        ];

        // Global Design Catalog for picking
        $globalCatalog = DesignCatalog::latest()->get();

        $salesSummary = [
            'orders_count' => 0,
            'total_sales' => 0,
            'total_items_sold' => 0,
            'average_order_value' => 0,
            'order_rows' => [],
        ];

        if ($store) {
            $priceByItemId = $store->items->keyBy('id');
            $orderRows = [];
            $totalSales = 0;
            $totalWholesale = 0;
            $totalItemsSold = 0;

            foreach ($store->parentOrders as $order) {
                $orderTotal = 0;
                $orderWholesaleTotal = 0;
                $orderItemsCount = 0;
                $items = is_array($order->items_json) ? $order->items_json : [];

                foreach ($items as $orderedItem) {
                    $itemId = isset($orderedItem['id']) ? (int) $orderedItem['id'] : null;
                    $qty = max(1, (int) ($orderedItem['qty'] ?? 1));
                    $orderItemsCount += $qty;

                    $storeItem = $itemId ? $priceByItemId->get($itemId) : null;
                    $retailPrice = $storeItem ? (float) $storeItem->retail_price : 0;
                    $wholesalePrice = $storeItem ? (float) $storeItem->wholesale_price : 0;
                    
                    $orderTotal += ($retailPrice * $qty);
                    $orderWholesaleTotal += ($wholesalePrice * $qty);
                }

                $totalSales += $orderTotal;
                $totalWholesale += $orderWholesaleTotal;
                $totalItemsSold += $orderItemsCount;

                $orderRows[] = [
                    'athlete_name' => $order->athlete_name,
                    'items_count' => $orderItemsCount,
                    'order_total' => $orderTotal,
                    'submitted_at' => $order->created_at,
                ];
            }

            $ordersCount = count($orderRows);
            $salesSummary = [
                'orders_count' => $ordersCount,
                'total_sales' => $totalSales,
                'total_wholesale' => $totalWholesale,
                'net_proceeds' => $totalSales - $totalWholesale,
                'total_items_sold' => $totalItemsSold,
                'average_order_value' => $ordersCount > 0 ? $totalSales / $ordersCount : 0,
                'order_rows' => $orderRows,
            ];
        }

        return view('coach.dashboard', compact('user', 'store', 'assignedDesigns', 'packageDesigns', 'globalCatalog', 'salesSummary', 'directOrders', 'directOrderBatches', 'archivedOrderBatches'));
    }


    public function createStore(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'package_type' => 'nullable|in:package_a,package_b,package_c,individual',
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
            'design_catalog_id' => ['required', 'exists:design_catalog,id'],
        ]);

        $design = DesignCatalog::findOrFail($request->design_catalog_id);

        // Check if design is assigned to this coach
        if (!$request->user()->designCatalog()->where('design_catalog.id', $design->id)->exists()) {
            return redirect()->route('coach.dashboard')
                ->with('error', 'You do not have permission to add this design. Please contact The Commission Apparel to request permission.');
        }

        // Check it isn't already in the store
        if ($store->items()->where('design_catalog_id', $design->id)->exists()) {
            return redirect()->route('coach.dashboard')
                ->with('error', 'That design is already added to your store.');
        }

        $maxSort = $store->items()->max('sort_order') ?? 0;

        $store->items()->create([
            'design_catalog_id' => $design->id,
            'name'              => $design->name,
            'type'              => null, // Deprecated
            'types'             => $design->types,
            'image_url'         => null, // Deprecated
            'image_paths'       => $design->image_paths,
            'wholesale_price'   => $design->wholesale_price,
            'retail_price'      => $design->wholesale_price,
            'sort_order'        => $maxSort + 1,
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

    public function updateItemMarkup(Request $request, StoreItem $item)
    {
        $store = $item->teamStore;
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'retail_price' => 'required|numeric|min:' . $item->wholesale_price,
            'sort_order'   => 'nullable|integer',
        ]);

        $item->update([
            'retail_price' => $request->retail_price,
            'sort_order'   => $request->sort_order ?? $item->sort_order,
        ]);

        return redirect()->route('coach.dashboard')
            ->with('success', 'Store price updated successfully.');
    }

    public function updateBulkItemMarkup(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'items' => 'required|array',
            'items.*.retail_price' => 'required|numeric',
            'items.*.sort_order' => 'nullable|integer',
        ]);

        foreach ($request->items as $itemId => $data) {
            $item = $store->items()->find($itemId);
            if ($item) {
                if ($data['retail_price'] >= $item->wholesale_price) {
                    $item->update([
                        'retail_price' => $data['retail_price'],
                        'sort_order'   => $data['sort_order'] ?? $item->sort_order,
                    ]);
                }
            }
        }

        return redirect()->route('coach.dashboard')
            ->with('success', 'All store prices updated successfully.');
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

        $unbatchedOrders = $store->parentOrders()->whereNull('batch_id');

        if ($unbatchedOrders->count() === 0) {
            return redirect()->route('coach.dashboard')
                ->with('error', 'No orders have been submitted yet. Cannot finalize an empty roster.');
        }

        $batchId = (string) Str::uuid();

        $unbatchedOrders->update([
            'status' => 'Submitted to Admin',
            'batch_id' => $batchId
        ]);

        $store->update(['status' => 'submitted_to_admin']);
        
        \Illuminate\Support\Facades\Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new \App\Notifications\MasterOrderSubmitted($store)
        );

        return redirect()->route('coach.dashboard')
            ->with('success', 'Master order submitted to The Commission Apparel for production!');
    }

    public function exportOrderCSV(TeamStore $store)
    {
        if ($store->user_id !== request()->user()->id) abort(403);

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
            'Athlete First Name', 'Athlete Last Name', 'Gender', 
            'Jersey Name', 'Jersey Number', 'Backpack Name',
            'Item Name', 'Item Type(s)', 'Size(s)', 'Quantity', 'Item Price', 'Total Row Price'
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

    public function editOrder(Request $request, ParentOrder $order)
    {
        if ($order->team_store_id) {
            $store = $order->teamStore;
            if ($store->user_id !== $request->user()->id) abort(403);

            $sizeChart = DesignCatalog::sizeChart();
            
            $availableItems = $store->items()->with('designCatalog')->get()->map(function($item) {
                $types = $item->types ?? [$item->type];
                $sizedTypes = array_intersect($types, DesignCatalog::sizedTypes());
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'types' => $types,
                    'sizedTypes' => array_values($sizedTypes)
                ];
            });

            return view('coach.order_edit', compact('order', 'store', 'sizeChart', 'availableItems'));
        } else {
            // Direct Order
            if ($order->user_id !== $request->user()->id) abort(403);

            $sizeChart = DesignCatalog::sizeChart();
            
            $availableItems = $request->user()->designCatalog()->get()->map(function($item) {
                $types = $item->types ?? [$item->type];
                $sizedTypes = array_intersect($types, DesignCatalog::sizedTypes());
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'types' => $types,
                    'sizedTypes' => array_values($sizedTypes)
                ];
            });

            $store = null;
            return view('coach.order_edit', compact('order', 'store', 'sizeChart', 'availableItems'));
        }
    }

    public function updateOrder(Request $request, ParentOrder $order)
    {
        if ($order->team_store_id) {
            $store = $order->teamStore;
            if ($store->user_id !== $request->user()->id) abort(403);
        } else {
            if ($order->user_id !== $request->user()->id) abort(403);
        }

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
            'edited_by'           => 'coach',
        ]);

        $fullName = trim($request->athlete_first_name . ' ' . $request->athlete_last_name);

        if (!$order->team_store_id) {
            return redirect()->route('coach.dashboard')
                ->with('success', "Order for {$fullName} has been updated.")
                ->with('activeCoachTab', 'create_order');
        }

        return redirect()->route('coach.dashboard')
            ->with('success', "Order for {$fullName} has been updated.");
    }

    public function deleteOrder(Request $request, ParentOrder $order)
    {
        if ($order->team_store_id) {
            $store = $order->teamStore;
            if ($store->user_id !== $request->user()->id) abort(403);
            
            $fullName = trim($order->athlete_first_name . ' ' . $order->athlete_last_name);
            $order->delete();
            return redirect()->route('coach.dashboard')->with('success', "Order for {$fullName} has been deleted.");
        } else {
            if ($order->user_id !== $request->user()->id) abort(403);
            
            $fullName = trim($order->athlete_first_name . ' ' . $order->athlete_last_name);
            $order->delete();
            return redirect()->route('coach.dashboard')->with('success', "Order for {$fullName} has been deleted.")->with('activeCoachTab', 'create_order');
        }
    }

    public function approvePricing(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $store->update(['pricing_approved' => true]);

        return redirect()->route('coach.dashboard')
            ->with('success', 'Pricing approved! The public storefront is now live with the approved pricing.');
    }

    public function reopenStore(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        // Unlock the store. Previous orders have already been batched.
        $store->update(['status' => 'approved']);

        return redirect()->route('coach.dashboard')
            ->with('success', 'Store has been re-opened for new orders! The active roster is now clear for the new batch.');
    }
    public function updateCoverImage(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'cover_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($store->cover_image_path) {
                $pathToRemove = str_replace('/storage/', '', $store->cover_image_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }

            // Store new image
            $path = $request->file('cover_image')->store('covers', 'public');
            $store->update(['cover_image_path' => $path]);
        }

        return redirect()->route('coach.dashboard')
            ->with('success', 'Store cover image updated successfully.');
    }

    public function updateProfileLogo(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($user->logo_path) {
                $pathToRemove = str_replace('/storage/', '', $user->logo_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }

            // Store new logo
            $path = $request->file('logo')->store('organization_logos', 'public');
            $user->update(['logo_path' => $path]);
        }

        return redirect()->route('coach.dashboard')
            ->with('success', 'Organization logo updated successfully.');
    }

    public function submitDirectOrder(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'order_type'          => 'required|in:person,item',
            'athlete_first_name'  => 'nullable|string|max:255',
            'athlete_last_name'   => 'nullable|string|max:255',
            'gender'              => 'required|string|max:50',
            'jersey_name'         => 'nullable|string|max:255',
            'jersey_number'       => 'nullable|string|max:10',
            'backpack_name'       => 'nullable|string|max:255',
            'items'               => 'required|array|min:1',
        ]);

        $itemsJson = [];

        foreach ($request->items as $designId => $details) {
            if (!isset($details['selected']) || $details['selected'] != '1') {
                continue;
            }

            $design = $user->designCatalog()->find($designId);
            if (!$design) continue;

            $qty = max(1, intval($details['qty'] ?? 1));

            $types = $design->types ?? [];
            $sizedTypes = DesignCatalog::sizedTypes();
            $hasSizes = count(array_intersect($types, $sizedTypes)) > 0;

            if ($hasSizes && isset($details['sizes']) && is_array($details['sizes'])) {
                foreach ($details['sizes'] as $size => $qty) {
                    $qty = intval($qty);
                    if ($qty > 0) {
                        $entry = [
                            'id'           => $designId,
                            'name'         => $design->name,
                            'types'        => $types,
                            'qty'          => $qty,
                            'sizes'        => [],
                        ];
                        // Apply this size to all sized types in the item
                        foreach ($types as $t) {
                            if (in_array($t, $sizedTypes)) {
                                $entry['sizes'][$t] = $size;
                            }
                        }
                        $itemsJson[] = $entry;
                    }
                }
            } else if (!$hasSizes) {
                // Non-sized item
                $qty = max(1, intval($details['qty'] ?? 1));
                $entry = [
                    'id'           => $designId,
                    'name'         => $design->name,
                    'types'        => $types,
                    'qty'          => $qty,
                    'sizes'        => [],
                ];
                $itemsJson[] = $entry;
            }
        }

        if (empty($itemsJson)) {
            return back()->with('error', 'Please select at least one item before submitting.');
        }

        // If order by item (bulk), use "Bulk Order" as name
        $firstName = $request->order_type === 'item' ? 'Bulk' : trim($request->athlete_first_name);
        $lastName = $request->order_type === 'item' ? 'Order' : trim($request->athlete_last_name);

        ParentOrder::create([
            'user_id'             => $user->id,
            'team_store_id'       => null,
            'athlete_first_name'  => $firstName ?: 'Direct',
            'athlete_last_name'   => $lastName ?: 'Order',
            'gender'              => trim($request->gender),
            'jersey_name'         => $request->jersey_name,
            'jersey_number'       => $request->jersey_number,
            'backpack_name'       => $request->backpack_name,
            'items_json'          => $itemsJson,
            'status'              => 'Draft',
            'total_retail_price'  => 0,
        ]);

        return redirect()->route('coach.dashboard')->with('success', 'Order line added to your draft.')->with('activeCoachTab', 'create_order');
    }

    public function finalizeDirectOrders(Request $request)
    {
        $user = $request->user();
        
        $draftOrders = $user->parentOrders()
            ->whereNull('team_store_id')
            ->where('status', 'Draft')
            ->get();

        if ($draftOrders->isEmpty()) {
            return redirect()->route('coach.dashboard')->with('error', 'You have no draft orders to submit.');
        }

        $batchId = (string) Str::uuid();

        foreach ($draftOrders as $order) {
            $order->update([
                'status' => 'Submitted to Admin',
                'batch_id' => $batchId,
            ]);
        }

        // Notify Admin
        \Illuminate\Support\Facades\Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new \App\Notifications\MasterOrderSubmitted((object) ['name' => $user->organization . ' Direct Order', 'user' => $user])
        );

        return redirect()->route('coach.dashboard')->with('success', 'Your direct orders have been submitted to The Commission Apparel!')->with('activeCoachTab', 'create_order');
    }

    public function exportDirectOrderBatch(Request $request, $batchId)
    {
        $user = $request->user();
        $orders = $user->parentOrders()->where('batch_id', $batchId)->get();

        if ($orders->isEmpty()) abort(404);

        $filename = "direct-order-{$batchId}.csv";
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
            'Item', 'Types', 'Sizes', 'Qty'
        ];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                if (is_array($order->items_json)) {
                    foreach ($order->items_json as $item) {
                        $typesStr = isset($item['types']) ? implode(', ', $item['types']) : 'N/A';
                        
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
                        $sizesStr = !empty($sizesArr) ? implode(' | ', $sizesArr) : 'N/A';

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
                        ]);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function archiveDirectOrderBatch(Request $request, $batchId)
    {
        $user = $request->user();
        if ($user->role !== 'coach') abort(403);

        ParentOrder::where('user_id', $user->id)
            ->where('batch_id', $batchId)
            ->update(['is_archived' => true]);

        return back()->with('success', 'Batch has been archived successfully.');
    }

    public function addRosterPaste(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'emails' => 'required|string',
        ]);

        $inputs = explode(',', $request->emails);
        $added = 0;

        foreach ($inputs as $input) {
            $input = trim($input);
            $email = null;
            $phone = null;
            
            if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
                $email = $input;
            } else {
                // Remove non-numeric chars to check if it's a valid phone number (at least 10 digits)
                $numeric = preg_replace('/[^0-9]/', '', $input);
                if (strlen($numeric) >= 10) {
                    $phone = $input;
                }
            }

            if ($email || $phone) {
                // Check if already exists
                $exists = $store->rosters()->where(function ($query) use ($email, $phone) {
                    if ($email) $query->orWhere('parent_email', $email);
                    if ($phone) $query->orWhere('parent_phone', $phone);
                })->exists();

                if (!$exists) {
                    $store->rosters()->create([
                        'parent_email' => $email,
                        'parent_phone' => $phone,
                    ]);
                    $added++;
                }
            }
        }

        return back()->with('success', "{$added} parents added to the roster.");
    }

    public function addRosterUpload(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $request->validate([
            'roster_csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('roster_csv');
        $handle = fopen($file->path(), "r");
        
        $header = fgetcsv($handle);
        if (!$header) {
            return back()->with('error', 'CSV file is empty or invalid.');
        }

        // Find email, phone, and name columns
        $emailIdx = -1;
        $phoneIdx = -1;
        $nameIdx = -1;

        foreach ($header as $idx => $col) {
            $colName = strtolower(trim($col));
            if (str_contains($colName, 'email')) {
                $emailIdx = $idx;
            } elseif (str_contains($colName, 'phone') || str_contains($colName, 'mobile') || str_contains($colName, 'sms')) {
                $phoneIdx = $idx;
            } elseif (str_contains($colName, 'name') || str_contains($colName, 'athlete')) {
                $nameIdx = $idx;
            }
        }

        if ($emailIdx === -1 && $phoneIdx === -1) {
            return back()->with('error', 'Could not find an "Email" or "Phone" column in the CSV.');
        }

        $added = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $email = ($emailIdx !== -1 && isset($row[$emailIdx])) ? trim($row[$emailIdx]) : null;
            $phone = ($phoneIdx !== -1 && isset($row[$phoneIdx])) ? trim($row[$phoneIdx]) : null;
            $name = ($nameIdx !== -1 && isset($row[$nameIdx])) ? trim($row[$nameIdx]) : null;

            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = null;
            }
            if ($phone) {
                $numeric = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($numeric) < 10) {
                    $phone = null;
                }
            }

            if ($email || $phone) {
                $exists = $store->rosters()->where(function ($query) use ($email, $phone) {
                    if ($email) $query->orWhere('parent_email', $email);
                    if ($phone) $query->orWhere('parent_phone', $phone);
                })->exists();

                if (!$exists) {
                    $store->rosters()->create([
                        'parent_email' => $email,
                        'parent_phone' => $phone,
                        'athlete_name' => $name,
                    ]);
                    $added++;
                }
            }
        }

        fclose($handle);

        return back()->with('success', "{$added} parents added to the roster from CSV.");
    }
    public function sendReminderBlast(Request $request, TeamStore $store)
    {
        if ($store->user_id !== $request->user()->id) abort(403);

        $emailsSent = 0;
        $smsSent = 0;

        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioFrom = env('TWILIO_PHONE_NUMBER');
        
        $twilioClient = null;
        if ($twilioSid && $twilioToken && $twilioFrom) {
            $twilioClient = new \Twilio\Rest\Client($twilioSid, $twilioToken);
        }

        foreach ($store->rosters as $roster) {
            if (!$roster->has_ordered) {
                // Send Email
                if ($roster->parent_email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($roster->parent_email)
                            ->send(new \App\Mail\StoreOrderReminder($store));
                        $emailsSent++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send blast email to {$roster->parent_email}: " . $e->getMessage());
                    }
                }

                // Send SMS
                if ($roster->parent_phone && $twilioClient) {
                    try {
                        $message = "REMINDER: Ordering deadline for {$store->name} is approaching on {$store->order_deadline->format('M d, Y')}. Place your order here: " . route('store.show', $store->slug);
                        $twilioClient->messages->create(
                            $roster->parent_phone,
                            [
                                'from' => $twilioFrom,
                                'body' => $message
                            ]
                        );
                        $smsSent++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send blast SMS to {$roster->parent_phone}: " . $e->getMessage());
                    }
                }
            }
        }

        return back()->with('success', "Reminder blast sent! {$emailsSent} emails and {$smsSent} text messages were dispatched successfully.");
    }
}

