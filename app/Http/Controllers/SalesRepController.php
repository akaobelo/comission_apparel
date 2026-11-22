<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeamStore;
use App\Models\ParentOrder;
use App\Models\DesignCatalog;
use App\Models\DesignCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalesRepController extends Controller
{
    /**
     * Get IDs of clients and stores assigned to this sales representative.
     */
    protected function getRepScope(User $rep)
    {
        $clientIds = User::where('sales_rep_id', $rep->id)->pluck('id')->toArray();
        $storeIds = TeamStore::where('sales_rep_id', $rep->id)
            ->orWhereIn('user_id', $clientIds)
            ->pluck('id')
            ->toArray();

        return [$clientIds, $storeIds];
    }

    /**
     * Sales Representative Portal Dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        [$clientIds, $storeIds] = $this->getRepScope($user);

        // Assigned Clients (Coaches / Schools)
        $clients = User::where('sales_rep_id', $user->id)
            ->with(['teamStores'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Assigned Team Stores
        $stores = TeamStore::where('sales_rep_id', $user->id)
            ->orWhereIn('user_id', $clientIds)
            ->with(['user'])
            ->withCount(['parentOrders'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Orders strictly attributed to this sales rep's book of business
        // (Enforces: only client orders created on or after sales_rep_assigned_at belong to this rep)
        $orders = ParentOrder::getOrdersForSalesRep($user, $clients);

        // Assign accurate rep orders count per client
        foreach ($clients as $client) {
            $client->rep_orders_count = $orders->where('user_id', $client->id)->count();
        }

        // Automatically compute commission for any unstamped order or missing calculations
        $repRate = $user->getEffectiveCommissionRate();
        foreach ($orders as $ord) {
            $dirty = false;
            if (empty($ord->sales_rep_id)) {
                $ord->sales_rep_id = $user->id;
                $dirty = true;
            }
            if ($ord->applied_rep_commission_rate === null || (float)$ord->subtotal == 0) {
                $ord->calculateAndApplyCommission($repRate);
                $dirty = true;
            }
            if ($dirty) {
                $ord->save();
            }
        }

        // Date Filtering
        $dateFilter = $request->query('date_filter', 'all');
        $now = \Carbon\Carbon::now();
        $startDate = match ($dateFilter) {
            'today'           => $now->copy()->startOfDay(),
            'this_week'       => $now->copy()->startOfWeek(),
            'this_month'      => $now->copy()->startOfMonth(),
            'this_quarter'    => $now->copy()->firstOfQuarter()->startOfDay(),
            'last_six_months' => $now->copy()->subMonths(6)->startOfDay(),
            'this_year'       => $now->copy()->startOfYear(),
            default           => null,
        };

        if ($startDate) {
            $orders = $orders->filter(function($o) use ($startDate) {
                $orderDate = $o->created_at ?: $o->paid_at;
                return $orderDate && $orderDate->gte($startDate);
            });
        }

        // Active vs Archived split:
        // An order is archived if it is explicitly archived, its team store is archived,
        // or its coach/client is no longer assigned to this sales rep.
        $isOrderArchived = function($o) use ($clientIds, $storeIds, $user) {
            if (!empty($o->is_archived)) {
                return true;
            }
            if ($o->teamStore && !empty($o->teamStore->is_archived)) {
                return true;
            }
            $hasAssignedCoach = $o->user_id && in_array($o->user_id, $clientIds);
            $hasAssignedStore = $o->team_store_id && in_array($o->team_store_id, $storeIds);
            $isExplicitRepOrder = ($o->sales_rep_id == $user->id);
            if (!$hasAssignedCoach && !$hasAssignedStore && !$isExplicitRepOrder) {
                return true;
            }
            return false;
        };

        $activeOrders = $orders->filter(fn($o) => !$isOrderArchived($o))->values();
        $archivedOrders = $orders->filter(fn($o) => $isOrderArchived($o))->values();

        // Financial Metrics (Pending & Cleared payout strictly reflect active book of business)
        $totalQualifyingSales = $activeOrders->sum(fn($o) => (float)$o->getEffectiveQualifyingSales());
        $totalManufacturingCost = $activeOrders->sum(fn($o) => (float)$o->getEffectiveManufacturingCost());
        $totalTariffs = $activeOrders->sum(fn($o) => (float)$o->tariff_amount);
        $totalFees = $activeOrders->sum(fn($o) => (float)$o->fee_deduction);
        $totalNetMargin = $activeOrders->sum(fn($o) => (float)$o->net_margin);

        $pendingCommission = $activeOrders->where('commission_status', 'pending')->sum(fn($o) => (float)$o->rep_commission_earned);
        $clearedCommission = $activeOrders->where('commission_status', 'cleared')->sum(fn($o) => (float)$o->rep_commission_earned);
        $paidCommission = $orders->where('commission_status', 'paid')->sum(fn($o) => (float)$o->rep_commission_earned);
        $lifetimeCommissionEarned = $orders->sum(fn($o) => (float)$o->rep_commission_earned);

        $referralUrl = $user->referral_url;

        $orderBatches = \App\Models\ParentOrder::groupOrdersIntoBatches($activeOrders, $user);
        $archivedOrderBatches = \App\Models\ParentOrder::groupOrdersIntoBatches($archivedOrders, $user);
        $orders = $activeOrders;

        $fullName = trim("{$user->first_name} {$user->last_name}");
        $assignedInquiries = \App\Models\QuoteRequest::where('sales_rep_id', $user->id)
            ->when(!empty($fullName), function($q) use ($fullName) {
                $q->orWhere('sales_rep', $fullName);
            })
            ->latest()
            ->get();

        // ─── Design Catalog & Collections Support ─────────────────────────────
        $availableSports = config('sports.categories') ?? [];

        $collectionSearch = $request->input('collection_search');
        $collectionsQuery = DesignCollection::with(['designs' => function($q) {
            $q->with(['coaches:id,first_name,last_name,organization'])->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
        }]);

        if (!empty($collectionSearch)) {
            $collectionsQuery->where(function($q) use ($collectionSearch) {
                $q->where('name', 'like', "%{$collectionSearch}%")
                  ->orWhere('sports', 'like', "%{$collectionSearch}%")
                  ->orWhereHas('designs', function($dq) use ($collectionSearch) {
                      $dq->where('name', 'like', "%{$collectionSearch}%");
                  });
            });
        }

        $designCollections = $collectionsQuery->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'collection_page')
            ->withQueryString();

        $allCoaches = $clients;

        // AJAX pagination / search for existing collections
        if ($request->ajax() && $request->input('ajax_section') === 'collections') {
            $allCollections = DesignCollection::select('id', 'name')->orderBy('name', 'asc')->get();

            return view('admin.partials.existing_collections_list', compact(
                'designCollections',
                'availableSports',
                'allCollections',
                'allCoaches'
            ));
        }

        // Complete list of collections for selection dropdowns
        $allCollections = DesignCollection::select('id', 'name')
            ->with(['designs' => function($q) {
                $q->select('id', 'design_collection_id', 'name', 'types', 'type', 'sort_order')
                  ->orderBy('sort_order', 'asc')
                  ->orderBy('id', 'asc');
            }])
            ->orderBy('name', 'asc')
            ->get();

        $unassignedSearch = $request->input('unassigned_search');
        $unassignedQuery = DesignCatalog::whereNull('design_collection_id')
            ->with(['designCollection:id,name', 'coaches:id,first_name,last_name,organization']);

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

        $typeOptions = [
            'accessory' => 'Accessories',
            'arm_sleeve' => 'Arm Sleeves',
            'arm_sleeve_2' => 'Arm Sleeves #2',
            'arm_sleeve_3' => 'Arm Sleeves #3',
            'arm_sleeve_4' => 'Arm Sleeves #4',
            'backpack' => 'Backpacks',
            'headwear' => 'Headwear',
            'hoodie' => 'Hoodies & Pullovers',
            'jacket' => 'Jackets',
            'leggings' => 'Leggings/Tights',
            'pants' => 'Pants',
            'polo' => 'Polos',
            'shirt_short' => 'Shirts (short sleeve)',
            'shirt_long' => 'Shirts (long sleeve)',
            'shorts' => 'Shorts',
            'socks' => 'Socks',
            'uniform_top' => 'Uniform (top)',
            'uniform_bottom' => 'Uniform (bottom)',
            'uniform_set' => 'Uniform Set (top/bottom)',
            'uniform_2_top' => 'Uniform #2 (top)',
            'uniform_2_bottom' => 'Uniform #2 (bottom)',
            'uniform_set_2' => 'Uniform #2 (Set)',
            'uniform_3_top' => 'Uniform #3 (top)',
            'uniform_3_bottom' => 'Uniform #3 (bottom)',
            'uniform_set_3' => 'Uniform #3 (Set)',
            'uniform_4_top' => 'Uniform #4 (top)',
            'uniform_4_bottom' => 'Uniform #4 (bottom)',
            'uniform_set_4' => 'Uniform #4 (Set)',
            'warmup_top' => 'Warm-up (top)',
            'warmup_bottom' => 'Warm-up (bottom)',
            'warmup_set' => 'Warm-up (top/bottom)',
            'warmup_2_top' => 'Warm-up #2 (top)',
            'warmup_2_bottom' => 'Warm-up #2 (bottom)',
            'warmup_set_2' => 'Warm-up #2 (set)',
            'uniform_package_gold' => 'Uniform Package (Gold)',
            'uniform_package_silver' => 'Uniform Package (Silver)',
            'uniform_package_bronze' => 'Uniform Package (Bronze)',
            'uniform_package_custom' => 'Uniform Package (Custom)',
        ];

        return view('rep.dashboard', compact(
            'user',
            'clients',
            'stores',
            'orders',
            'archivedOrders',
            'orderBatches',
            'archivedOrderBatches',
            'dateFilter',
            'totalQualifyingSales',
            'totalManufacturingCost',
            'totalTariffs',
            'totalFees',
            'totalNetMargin',
            'pendingCommission',
            'clearedCommission',
            'paidCommission',
            'lifetimeCommissionEarned',
            'referralUrl',
            'assignedInquiries',
            'availableSports',
            'designCollections',
            'allCollections',
            'unassignedDesigns',
            'allCoaches',
            'typeOptions'
        ));
    }

    /**
     * Store a new Client / Coach created directly by this Rep.
     */
    public function createClient(Request $request)
    {
        $rep = $request->user();

        $validated = $request->validate([
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:20'],
            'sport'        => ['required', 'string', 'max:100'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'     => ['required', 'string', 'min:8'],
            'logo'         => ['nullable', 'image', 'max:10240'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = \App\Services\ImageOptimizer::optimize($request->file('logo'), 'organization_logos');
        }

        $client = User::create([
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => strtolower($validated['email']),
            'password'     => Hash::make($validated['password']),
            'organization' => $validated['organization'],
            'phone'        => $validated['phone'],
            'sport'        => $validated['sport'],
            'logo_path'    => $logoPath,
            'role'         => 'coach',
            'status'       => 'active',
            'sales_rep'    => $rep->name,
            'sales_rep_id' => $rep->id,
            'sales_rep_assigned_at' => now(),
        ]);

        return redirect()->route('rep.dashboard')
            ->with('success', "Client account for {$client->name} ({$client->organization}) successfully created and assigned to your book of business.");
    }

    /**
     * Export Roster / Order CSV for a store belonging to this rep.
     */
    public function exportStoreCSV(Request $request, TeamStore $store)
    {
        $rep = $request->user();
        [$clientIds, $storeIds] = $this->getRepScope($rep);

        if (!in_array($store->id, $storeIds) && !$rep->isAdmin()) {
            abort(403, 'Unauthorized access to store.');
        }

        $orders = $store->parentOrders()->where('status', '!=', 'Draft')->get();
        $filename = 'Store_Roster_' . Str::slug($store->name) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Order ID', 'Athlete First Name', 'Athlete Last Name', 'Gender',
                'Jersey Name', 'Jersey Number', 'Backpack Name', 'Item Name',
                'Cut / Type', 'Size', 'Qty', 'Parent Email', 'Parent Phone', 'Status', 'Date'
            ]);

            foreach ($orders as $order) {
                $items = is_array($order->items_json) ? $order->items_json : [];
                foreach ($items as $item) {
                    $sizes = $item['sizes'] ?? [];
                    $sizeStr = !empty($sizes) ? implode(', ', array_map(fn($k, $v) => "{$k}: {$v}", array_keys($sizes), $sizes)) : ($item['size'] ?? '');
                    
                    fputcsv($file, [
                        $order->id,
                        $order->athlete_first_name,
                        $order->athlete_last_name,
                        $order->gender,
                        $order->jersey_name,
                        $order->jersey_number,
                        $order->backpack_name,
                        $item['name'] ?? 'Item',
                        $item['gender'] ?? $order->gender,
                        $sizeStr,
                        $item['qty'] ?? 1,
                        $order->parent_email,
                        $order->parent_phone,
                        $order->status,
                        $order->created_at->format('Y-m-d H:i')
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export full Financial Ledger Statement CSV for this Rep.
     */
    public function exportLedgerCSV(Request $request)
    {
        $rep = $request->user();
        $orders = ParentOrder::getOrdersForSalesRep($rep);

        $filename = 'Commission_Statement_' . Str::slug($rep->name) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders, $rep) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Order ID', 'Date', 'Organization / Client', 'Store Name',
                'Retail Gross ($)', 'Manufacturing Base ($)', 'Tariffs ($)',
                'Fees & Deductions ($)', 'Net Margin ($)', 'Rep Tier Rate (%)',
                'Commission Earned ($)', 'Payout Status', 'Notes'
            ]);

            foreach ($orders as $order) {
                $gross = (float)($order->subtotal > 0 ? $order->subtotal : $order->total_retail_price);
                fputcsv($file, [
                    $order->id,
                    $order->created_at->format('Y-m-d'),
                    $order->user?->organization ?? $order->user?->name ?? '—',
                    $order->teamStore?->name ?? 'Direct Order',
                    number_format($gross, 2),
                    number_format((float)$order->manufacturing_cost, 2),
                    number_format((float)$order->tariff_amount, 2),
                    number_format((float)($order->fee_deduction + $order->fundraising_amount), 2),
                    number_format((float)$order->net_margin, 2),
                    number_format((float)($order->applied_rep_commission_rate ?? $rep->getEffectiveCommissionRate()), 1) . '%',
                    number_format((float)$order->rep_commission_earned, 2),
                    strtoupper($order->commission_status ?? 'pending'),
                    $order->financial_notes ?? ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mark an assigned quote inquiry as addressed.
     */
    public function markInquiryAddressed(Request $request, \App\Models\QuoteRequest $quoteRequest)
    {
        $user = $request->user();
        $fullName = trim("{$user->first_name} {$user->last_name}");

        if ($quoteRequest->sales_rep_id !== $user->id && $quoteRequest->sales_rep !== $fullName) {
            abort(403, 'Unauthorized inquiry.');
        }

        $quoteRequest->update([
            'status'       => 'addressed',
            'addressed_by' => $fullName ?: $user->email,
            'addressed_at' => now(),
        ]);

        return back()->with('success', 'Lead inquiry marked as addressed.');
    }

    /**
     * Revert an assigned quote inquiry back to new.
     */
    public function unmarkInquiryAddressed(Request $request, \App\Models\QuoteRequest $quoteRequest)
    {
        $user = $request->user();
        $fullName = trim("{$user->first_name} {$user->last_name}");

        if ($quoteRequest->sales_rep_id !== $user->id && $quoteRequest->sales_rep !== $fullName) {
            abort(403, 'Unauthorized inquiry.');
        }

        $quoteRequest->update([
            'status'       => 'new',
            'addressed_by' => null,
            'addressed_at' => null,
        ]);

        return back()->with('success', 'Lead inquiry reverted to New.');
    }
}
