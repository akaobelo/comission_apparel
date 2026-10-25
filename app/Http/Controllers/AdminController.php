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
use App\Models\NewsArticle;
use App\Models\SiteSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    // ─── DASHBOARD ───────────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        // Fast partial response for collections accordion AJAX
        if ($request->input('ajax_section') === 'collections' || $request->header('X-Ajax-Section') === 'collections') {
            $collectionSearch = $request->input('collection_search');
            $collectionsQuery = \App\Models\DesignCollection::with(['designs' => function($q) {
                $q->with('coaches')->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
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

            $availableSports = config('sports.categories') ?? [];
            $allCollections = \App\Models\DesignCollection::select('id', 'name')->orderBy('name', 'asc')->get();
            $allCoaches = User::where('role', 'coach')
                ->select('id', 'first_name', 'last_name', 'organization')
                ->with('teamStore:id,user_id,name')
                ->get()
                ->sortBy(function($user) {
                    $club = $user->teamStore?->name ?? $user->organization ?? trim($user->first_name . ' ' . $user->last_name);
                    return strtolower(trim($club ?: 'zzz'));
                }, SORT_NATURAL | SORT_FLAG_CASE)->values();

            return view('admin.partials.existing_collections_list', compact(
                'designCollections',
                'availableSports',
                'allCollections',
                'allCoaches'
            ));
        }

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

        // Finalized store batches paginated by batch_id
        $finalizedStoreBatchQuery = ParentOrder::whereNotNull('team_store_id')
            ->where('order_type', '!=', 'direct')
            ->whereNotNull('batch_id')
            ->where('status', '!=', 'Pending')
            ->where('is_archived', false)
            ->select('batch_id', \Illuminate\Support\Facades\DB::raw('MAX(COALESCE(paid_at, updated_at, created_at)) as latest_batch_date'), \Illuminate\Support\Facades\DB::raw('MAX(id) as latest_order_id'))
            ->groupBy('batch_id');

        $finalizedStoreBatchIds = $finalizedStoreBatchQuery->orderByDesc('latest_batch_date')
            ->orderByDesc('latest_order_id')
            ->paginate(10, ['*'], 'finalized_store_page')
            ->withQueryString();

        $finalizedStoreBatches = ParentOrder::whereIn('batch_id', $finalizedStoreBatchIds->pluck('batch_id'))
            ->with(['user', 'teamStore', 'teamStore.items'])
            ->get()
            ->groupBy('batch_id');

        $storeBatchIdOrder = array_flip($finalizedStoreBatchIds->pluck('batch_id')->toArray());
        $finalizedStoreBatches = $finalizedStoreBatches->sortBy(function ($orders, $batchId) use ($storeBatchIdOrder) {
                return $storeBatchIdOrder[$batchId] ?? 999999;
            })
            ->map(function ($orders) {
                $store = $orders->first()->teamStore;
                $financials = \App\Models\ParentOrder::calculateBatchFinancials($orders, $store);
                return [
                    'orders' => $orders,
                    'financials' => $financials,
                ];
            });

        $finalizedStoreBatchesPaginator = $finalizedStoreBatchIds;

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
                $q->whereNull('batch_id')
                  ->where('is_archived', false)
                  ->where('status', '!=', 'Pending Payment');
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

        // Finalized direct orders paginated by batch_id
        $finalizedDirectBatchQuery = ParentOrder::where(function($q) {
                $q->where('order_type', 'direct')
                  ->orWhereNull('team_store_id');
            })
            ->whereNotNull('batch_id')
            ->whereNotIn('status', ['Pending', 'Draft'])
            ->where('is_archived', false)
            ->select('batch_id', \Illuminate\Support\Facades\DB::raw('MAX(COALESCE(paid_at, updated_at, created_at)) as latest_batch_date'), \Illuminate\Support\Facades\DB::raw('MAX(id) as latest_order_id'))
            ->groupBy('batch_id');

        $finalizedDirectBatchIds = $finalizedDirectBatchQuery->orderByDesc('latest_batch_date')
            ->orderByDesc('latest_order_id')
            ->paginate(10, ['*'], 'finalized_direct_page')
            ->withQueryString();

        $finalizedDirectOrderBatches = ParentOrder::whereIn('batch_id', $finalizedDirectBatchIds->pluck('batch_id'))
            ->with(['user', 'teamStore'])
            ->get()
            ->groupBy('batch_id');

        $directBatchIdOrder = array_flip($finalizedDirectBatchIds->pluck('batch_id')->toArray());
        $finalizedDirectOrderBatches = $finalizedDirectOrderBatches->sortBy(function ($orders, $batchId) use ($directBatchIdOrder) {
                return $directBatchIdOrder[$batchId] ?? 999999;
            })
            ->map(function ($orders) {
                $store = $orders->first()?->teamStore;
                $financials = \App\Models\ParentOrder::calculateBatchFinancials($orders, $store);
                return [
                    'orders' => $orders,
                    'financials' => $financials,
                ];
            });

        $finalizedDirectBatchesPaginator = $finalizedDirectBatchIds;

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
        $archivedStores = $archivedStoresQuery->latest('updated_at')->paginate(10, ['*'], 'archive_store_page')->withQueryString();

        // Archived batches
        $archivedBatchQuery = ParentOrder::where('is_archived', true)
            ->whereNotNull('batch_id')
            ->select('batch_id', \Illuminate\Support\Facades\DB::raw('MAX(updated_at) as latest_archive_date'))
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

        $archivedBatchIds = $archivedBatchQuery->orderByDesc('latest_archive_date')->paginate(10, ['*'], 'archive_batch_page')->withQueryString();

        $archivedOrderBatches = ParentOrder::whereIn('batch_id', $archivedBatchIds->pluck('batch_id'))
            ->with(['user', 'teamStore'])
            ->latest('updated_at')
            ->get()
            ->groupBy('batch_id')
            ->sortByDesc(function ($orders) {
                return $orders->max('updated_at');
            })
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
        }])->orderBy('name', 'asc')->get();

        $allCoaches = User::where('role', 'coach')
            ->select('id', 'first_name', 'last_name', 'organization')
            ->with('teamStore:id,user_id,name')
            ->get()
            ->sortBy(function($user) {
                $club = $user->teamStore?->name ?? $user->organization ?? trim($user->first_name . ' ' . $user->last_name);
                return strtolower(trim($club ?: 'zzz'));
            }, SORT_NATURAL | SORT_FLAG_CASE)->values();

        $availableSports = config('sports.categories');

        // Paginate collections (10 per page) and eager load designs & coaches for those collections only
        $collectionSearch = $request->input('collection_search');
        $collectionsQuery = \App\Models\DesignCollection::with(['designs' => function($q) {
            $q->with('coaches')->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
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

        // Complete list of collections for selection dropdowns
        $allCollections = \App\Models\DesignCollection::select('id', 'name')->orderBy('name', 'asc')->get();

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
        
        $globalSalesSummary = \Illuminate\Support\Facades\Cache::remember('admin_global_sales_summary', 300, function() use ($storeIds, $storesMap) {
            $summary = [
                'orders_count' => 0,
                'total_sales' => 0,
                'total_wholesale' => 0,
                'net_proceeds' => 0,
                'total_items_sold' => 0,
            ];
            
            // Chunk orders to process them in smaller memory batches, selecting only needed columns
            \App\Models\ParentOrder::whereIn('status', ['In Production', 'Shipped', 'Delivered', 'Processing', 'Completed'])
                ->select('id', 'items_json', 'team_store_id')
                ->chunk(150, function($orders) use (&$summary, $storesMap) {
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
                        
                        $summary['total_sales'] += $orderTotal;
                        $summary['total_wholesale'] += $orderWholesaleTotal;
                        $summary['total_items_sold'] += $orderItemsCount;
                        $summary['orders_count']++;
                    }
                });
            
            $summary['net_proceeds'] = $summary['total_sales'] - $summary['total_wholesale'];
            return $summary;
        });

        $newsArticles = NewsArticle::orderBy('sort_order', 'asc')->orderBy('published_at', 'desc')->get();
        $landingSettings = SiteSetting::all()->pluck('value', 'key');
        
        $salesReps = User::whereIn('role', ['sales_rep', 'partner', 'agent'])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'archived');
            })
            ->withCount(['assignedClients', 'salesRepTeamStores', 'salesRepOrders'])
            ->with(['salesRepOrders'])
            ->get();

        $archivedSalesReps = User::whereIn('role', ['sales_rep', 'partner', 'agent'])
            ->where('status', 'archived')
            ->withCount(['assignedClients', 'salesRepTeamStores', 'salesRepOrders'])
            ->with(['salesRepOrders'])
            ->get();

        return view('admin.dashboard', compact(
            'coaches', 'pendingStores', 'finalizedStoreBatches', 'finalizedStoreBatchesPaginator',
            'unassignedDesigns', 'allCollections', 'productionStores', 'quoteRequests', 'quoteRequestsTotal', 'newQuoteRequestsCount', 'landingCollections', 'allStores', 'allCoaches',
            'availableSports', 'designCollections', 'passwordResetLogs', 'testimonials', 'sizingCharts', 'heroSettings', 'campaignStores', 'archivedStores', 'finalizedDirectOrderBatches', 'finalizedDirectBatchesPaginator', 'archivedOrderBatches', 'globalSalesSummary', 'archivedBatchesPaginator',
            'salesAgents', 'newsArticles', 'landingSettings', 'salesReps', 'archivedSalesReps'
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
        $salesReps = User::whereIn('role', ['sales_rep', 'partner', 'agent'])->get();
        return view('admin.coach_edit', compact('user', 'designCatalog', 'assignedDesignIds', 'salesReps'));
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
            'sales_rep_id' => ['nullable', 'exists:users,id'],
            'is_tax_exempt'=> ['nullable', 'boolean'],
        ]);

        $validated['is_tax_exempt'] = $request->boolean('is_tax_exempt');

        $oldRepId = $user->sales_rep_id;

        if ($request->filled('sales_rep_id')) {
            $rep = User::find($request->sales_rep_id);
            if ($rep) {
                $validated['sales_rep_id'] = $rep->id;
                $validated['sales_rep'] = $rep->name;
            }
        } elseif ($request->filled('sales_rep')) {
            $repName = trim($request->sales_rep);
            $rep = User::where('referral_slug', $repName)
                ->orWhere('id', $repName)
                ->orWhere(function($sub) use ($repName) {
                    $parts = explode(' ', $repName, 2);
                    if (count($parts) === 2) {
                        $sub->where('first_name', $parts[0])->where('last_name', $parts[1]);
                    } else {
                        $sub->where('first_name', $repName)->orWhere('last_name', $repName);
                    }
                })->first();
            $validated['sales_rep_id'] = $rep?->id;
        } else {
            $validated['sales_rep_id'] = null;
            $validated['sales_rep'] = null;
        }

        // When a coach is unassigned or transferred to another rep, permanently preserve
        // all historical orders under the original sales rep so earned commissions are preserved.
        $newRepId = $validated['sales_rep_id'] ?? null;
        if ($oldRepId && $oldRepId != $newRepId) {
            $oldRep = User::find($oldRepId);
            if ($oldRep) {
                $coachStoreIds = TeamStore::where('user_id', $user->id)->pluck('id')->toArray();
                ParentOrder::where(function($q) use ($user, $coachStoreIds) {
                        $q->where('user_id', $user->id)
                          ->orWhereIn('team_store_id', $coachStoreIds);
                    })
                    ->where(function($q) use ($oldRep) {
                        $q->whereNull('sales_rep_id')
                          ->orWhere('sales_rep_id', $oldRep->id);
                    })
                    ->each(function($ord) use ($oldRep) {
                        $ord->sales_rep_id = $oldRep->id;
                        if ($ord->applied_rep_commission_rate === null || (float)$ord->net_margin == 0) {
                            $ord->calculateAndApplyCommission($oldRep->getEffectiveCommissionRate());
                        }
                        $ord->save();
                    });

                TeamStore::where('user_id', $user->id)
                    ->where('sales_rep_id', $oldRep->id)
                    ->update(['sales_rep_id' => null]);
            }
        }

        $user->update($validated);

        if ($request->filled('return_to')) {
            return redirect($request->input('return_to'))
                ->with('success', "Coach {$user->name}'s profile has been updated.");
        }

        return redirect()->route('admin.dashboard', ['tab' => 'coaches'])
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

    // ─── SALES REP & PARTNER MANAGEMENT ──────────────────────────────────────────

    public function createSalesRepUser(Request $request)
    {
        $validated = $request->validate([
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'partner_category' => ['required', 'string', 'in:independent_sales,authorized_business,established_business'],
            'commission_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
            'referral_slug'    => ['nullable', 'string', 'alpha_dash', 'max:50', 'unique:users,referral_slug'],
            'profile_image'    => ['nullable', 'image', 'max:10240'],
        ]);

        $logoPath = null;
        if ($request->hasFile('profile_image')) {
            $logoPath = \App\Services\ImageOptimizer::optimize($request->file('profile_image'), 'partner_avatars');
        }

        $slug = $validated['referral_slug'] ?: \Illuminate\Support\Str::slug($validated['first_name'] . '-' . $validated['last_name']);
        // Ensure uniqueness if auto-generated
        $originalSlug = $slug;
        $counter = 1;
        while (User::where('referral_slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $rep = User::create([
            'first_name'       => $validated['first_name'],
            'last_name'        => $validated['last_name'],
            'email'            => strtolower($validated['email']),
            'password'         => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'phone'            => $validated['phone'] ?? '',
            'organization'     => 'Sales Partner',
            'sport'            => 'General',
            'role'             => 'sales_rep',
            'status'           => 'active',
            'partner_category' => $validated['partner_category'],
            'commission_rate'  => $validated['commission_rate'],
            'referral_slug'    => $slug,
            'logo_path'        => $logoPath,
        ]);

        return redirect()->back()->with('success', "Sales Partner {$rep->name} successfully registered at {$rep->commission_rate}% commission rate.");
    }

    public function showSalesRepUser(Request $request, User $user)
    {
        if ($user->role === 'coach') {
            return redirect()->route('admin.dashboard', ['tab' => 'coaches'])
                ->with('info', "User {$user->name} is a coach.");
        }

        if (!$user->isSalesRep()) {
            return redirect()->route('admin.dashboard', ['tab' => 'coaches'])
                ->with('error', "User {$user->name} is not registered as a sales partner.");
        }

        $clientIds = User::where('sales_rep_id', $user->id)->pluck('id')->toArray();
        $storeIds = TeamStore::where('sales_rep_id', $user->id)
            ->orWhereIn('user_id', $clientIds)
            ->pluck('id')
            ->toArray();

        $clients = User::where('sales_rep_id', $user->id)
            ->with(['teamStores'])
            ->withCount(['teamStores', 'parentOrders'])
            ->latest()
            ->get();

        $stores = TeamStore::where('sales_rep_id', $user->id)
            ->orWhereIn('user_id', $clientIds)
            ->with(['user'])
            ->withCount('parentOrders')
            ->latest()
            ->get();

        $orders = ParentOrder::where(function($q) use ($user, $clientIds, $storeIds) {
                $q->where('sales_rep_id', $user->id)
                  ->orWhereIn('user_id', $clientIds)
                  ->orWhereIn('team_store_id', $storeIds);
            })
            ->where('status', '!=', 'Draft')
            ->with(['teamStore', 'user', 'teamStore.items'])
            ->latest()
            ->get();

        // Automatically stamp rep attribution and sync financials
        $repRate = $user->getEffectiveCommissionRate();
        foreach ($orders as $ord) {
            $dirty = false;
            if (!$ord->sales_rep_id) {
                $ord->sales_rep_id = $user->id;
                $dirty = true;
            }
            if ($ord->applied_rep_commission_rate === null || (float)$ord->net_margin == 0 || (float)$ord->subtotal == 0 || (float)$ord->manufacturing_cost == 0) {
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

        $totalQualifyingSales = $orders->sum(fn($o) => (float)$o->getEffectiveQualifyingSales());
        $totalManufacturingCost = $orders->sum(fn($o) => (float)$o->getEffectiveManufacturingCost());
        $totalTariffs = $orders->sum(fn($o) => (float)$o->tariff_amount);
        $totalFees = $orders->sum(fn($o) => (float)$o->fee_deduction);
        $totalNetMargin = $orders->sum(fn($o) => (float)$o->net_margin);

        $pendingCommission = $orders->where('commission_status', 'pending')->sum(fn($o) => (float)$o->rep_commission_earned);
        $clearedCommission = $orders->where('commission_status', 'cleared')->sum(fn($o) => (float)$o->rep_commission_earned);
        $paidCommission = $orders->where('commission_status', 'paid')->sum(fn($o) => (float)$o->rep_commission_earned);
        $lifetimeCommissionEarned = $orders->sum(fn($o) => (float)$o->rep_commission_earned);

        $referralUrl = $user->referral_url;

        // Separate active vs archived orders
        // An order is archived if:
        // 1. $o->is_archived is true
        // 2. OR its team store is archived
        // 3. OR the coach/store has been unassigned from this sales rep
        $isOrderArchived = function ($o) use ($clientIds, $storeIds) {
            if ($o->is_archived) {
                return true;
            }
            if ($o->teamStore && $o->teamStore->is_archived) {
                return true;
            }
            $hasAssignedCoach = $o->user_id && in_array($o->user_id, $clientIds);
            $hasAssignedStore = $o->team_store_id && in_array($o->team_store_id, $storeIds);
            if (!$hasAssignedCoach && !$hasAssignedStore) {
                return true;
            }
            return false;
        };

        $archivedOrders = $orders->filter($isOrderArchived)->values();
        $activeOrders = $orders->reject($isOrderArchived)->values();

        $orderBatches = \App\Models\ParentOrder::groupOrdersIntoBatches($activeOrders, $user);
        $archivedOrderBatches = \App\Models\ParentOrder::groupOrdersIntoBatches($archivedOrders, $user);
        $orders = $activeOrders;

        return view('admin.sales_rep_show', compact(
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
            'referralUrl'
        ));
    }

    public function archiveOrder(ParentOrder $order)
    {
        $order->update(['is_archived' => true]);
        return back()->with('success', "Order #{$order->id} archived successfully.");
    }

    public function unarchiveOrder(ParentOrder $order)
    {
        $order->update(['is_archived' => false]);
        return back()->with('success', "Order #{$order->id} restored successfully.");
    }

    public function archiveOrderBatch($batchId)
    {
        ParentOrder::where('batch_id', $batchId)->update(['is_archived' => true]);
        return back()->with('success', 'Order batch archived successfully.');
    }

    public function unarchiveOrderBatch($batchId)
    {
        ParentOrder::where('batch_id', $batchId)->update(['is_archived' => false]);
        return back()->with('success', 'Order batch restored successfully.');
    }

    public function updateSalesRepUser(Request $request, User $user)
    {
        if (!$user->isSalesRep()) abort(404);

        $validated = $request->validate([
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'            => ['nullable', 'string', 'max:20'],
            'partner_category' => ['required', 'string', 'in:independent_sales,authorized_business,established_business'],
            'commission_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
            'referral_slug'    => ['nullable', 'string', 'alpha_dash', 'max:50', 'unique:users,referral_slug,' . $user->id],
            'status'           => ['required', 'in:active,declined,archived'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_image'    => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('profile_image')) {
            $path = \App\Services\ImageOptimizer::optimize($request->file('profile_image'), 'partner_avatars');
            $validated['logo_path'] = $path;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back()->with('success', "Sales Partner {$user->name} updated successfully.");
    }

    public function archiveSalesRepUser(User $user)
    {
        if (!$user->isSalesRep()) abort(404);
        $user->update(['status' => 'archived']);
        return redirect()->back()->with('success', "Sales Partner {$user->name} has been archived.");
    }

    public function unarchiveSalesRepUser(User $user)
    {
        if (!$user->isSalesRep()) abort(404);
        $user->update(['status' => 'active']);
        return redirect()->back()->with('success', "Sales Partner {$user->name} has been restored to active status.");
    }

    public function updateSalesRepAvatar(Request $request, User $user)
    {
        if (!$user->isSalesRep()) abort(404);

        $request->validate([
            'profile_image' => ['required', 'image', 'max:15360'],
        ]);

        $path = \App\Services\ImageOptimizer::optimize($request->file('profile_image'), 'partner_avatars');
        $user->update(['logo_path' => $path]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'avatar_url' => $user->profile_image_url,
                'message' => 'Profile photo cropped and updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Profile photo updated successfully.');
    }

    public function unassignCoachFromSalesRep(Request $request, User $user, User $coach)
    {
        if (!$user->isSalesRep()) {
            abort(404);
        }
        if ($coach->role !== 'coach') {
            abort(404);
        }

        // 1. Ensure all historical orders belonging to this coach or their team stores are permanently stamped with this sales rep and commission calculations
        $repRate = $user->getEffectiveCommissionRate();
        $coachStoreIds = TeamStore::where('user_id', $coach->id)->pluck('id')->toArray();

        $ordersToPreserve = ParentOrder::where(function($q) use ($coach, $coachStoreIds) {
                $q->where('user_id', $coach->id)
                  ->orWhereIn('team_store_id', $coachStoreIds);
            })
            ->where(function($q) use ($user) {
                $q->whereNull('sales_rep_id')
                  ->orWhere('sales_rep_id', $user->id);
            })
            ->get();

        foreach ($ordersToPreserve as $ord) {
            $dirty = false;
            if ($ord->sales_rep_id != $user->id) {
                $ord->sales_rep_id = $user->id;
                $dirty = true;
            }
            if ($ord->applied_rep_commission_rate === null || (float)$ord->net_margin == 0 || (float)$ord->subtotal == 0 || (float)$ord->manufacturing_cost == 0) {
                $ord->calculateAndApplyCommission($repRate);
                $dirty = true;
            }
            if ($dirty) {
                $ord->save();
            }
        }

        // 2. Unassign the coach from this sales rep
        $coach->sales_rep_id = null;
        $coach->sales_rep = null;
        $coach->save();

        // 3. Clear sales_rep_id on the coach's team stores so future orders do not auto-attribute
        TeamStore::where('user_id', $coach->id)
            ->where('sales_rep_id', $user->id)
            ->update(['sales_rep_id' => null]);

        return redirect()->route('admin.sales-rep.show', ['user' => $user, 'tab' => 'clients'])
            ->with('success', "Coach {$coach->name} ({$coach->organization}) has been unassigned. All historical orders and commission statements remain permanently preserved for {$user->name}.");
    }

    public function updateOrderFinancials(Request $request, ParentOrder $order)
    {
        $validated = $request->validate([
            'manufacturing_cost'          => ['nullable', 'numeric', 'min:0'],
            'tariff_amount'               => ['nullable', 'numeric', 'min:0'],
            'fee_deduction'               => ['nullable', 'numeric', 'min:0'],
            'fundraising_amount'          => ['nullable', 'numeric', 'min:0'],
            'sales_rep_id'                => ['nullable', 'exists:users,id'],
            'applied_rep_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_status'           => ['required', 'in:pending,cleared,paid'],
            'financial_notes'             => ['nullable', 'string'],
        ]);

        $order->manufacturing_cost = (float) ($validated['manufacturing_cost'] ?? 0);
        $order->tariff_amount = (float) ($validated['tariff_amount'] ?? 0);
        $order->fee_deduction = (float) ($validated['fee_deduction'] ?? 0);
        $order->fundraising_amount = (float) ($validated['fundraising_amount'] ?? 0);
        $order->commission_status = $validated['commission_status'];
        $order->financial_notes = $validated['financial_notes'] ?? null;

        if ($request->filled('sales_rep_id')) {
            $order->sales_rep_id = $request->sales_rep_id;
        }

        if ($order->commission_status === 'paid' && !$order->paid_to_rep_at) {
            $order->paid_to_rep_at = now();
        }

        $rateOverride = $validated['applied_rep_commission_rate'] !== null ? (float)$validated['applied_rep_commission_rate'] : null;
        $order->calculateAndApplyCommission($rateOverride);
        $order->save();

        return redirect()->back()->with('success', "Order #{$order->id} financials, tariffs, and rep commission updated.");
    }

    public function updateBatchFinancials(Request $request, $batchId)
    {
        $validated = $request->validate([
            'manufacturing_cost'          => ['nullable', 'numeric', 'min:0'],
            'tariff_amount'               => ['nullable', 'numeric', 'min:0'],
            'fee_deduction'               => ['nullable', 'numeric', 'min:0'],
            'sales_rep_id'                => ['nullable', 'exists:users,id'],
            'applied_rep_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_status'           => ['required', 'in:pending,cleared,paid'],
            'financial_notes'             => ['nullable', 'string'],
        ]);

        $orders = ParentOrder::where('batch_id', $batchId)->where('status', '!=', 'Draft')->get();
        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No orders found in this batch.');
        }

        $count = $orders->count();
        $mfgPerOrder = (float) ($validated['manufacturing_cost'] ?? 0) / $count;
        $tariffPerOrder = (float) ($validated['tariff_amount'] ?? 0) / $count;
        $feePerOrder = (float) ($validated['fee_deduction'] ?? 0) / $count;

        foreach ($orders as $order) {
            $order->manufacturing_cost = round($mfgPerOrder, 2);
            $order->tariff_amount = round($tariffPerOrder, 2);
            $order->fee_deduction = round($feePerOrder, 2);
            $order->commission_status = $validated['commission_status'];
            $order->financial_notes = $validated['financial_notes'] ?? null;
            if ($request->filled('sales_rep_id')) {
                $order->sales_rep_id = $request->sales_rep_id;
            }
            if ($order->commission_status === 'paid' && !$order->paid_to_rep_at) {
                $order->paid_to_rep_at = now();
            }
            $rateOverride = $validated['applied_rep_commission_rate'] !== null ? (float)$validated['applied_rep_commission_rate'] : null;
            $order->calculateAndApplyCommission($rateOverride);
            $order->save();
        }

        return redirect()->back()->with('success', "Batch {$batchId} updated with tariffs and recalculated rep commissions across {$count} orders.");
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

    public function bulkDeleteDesigns(Request $request)
    {
        $request->validate([
            'design_ids'   => ['required', 'array'],
            'design_ids.*' => ['required', 'exists:design_catalog,id'],
        ]);

        $designs = DesignCatalog::whereIn('id', $request->design_ids)->get();
        $count = 0;

        foreach ($designs as $design) {
            // Delete associated images
            if (!empty($design->image_paths) && is_array($design->image_paths)) {
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

            // Detach from coaches pivot table
            $design->coaches()->detach();

            $design->delete();
            $count++;
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "{$count} " . \Illuminate\Support\Str::plural('design', $count) . " successfully deleted from catalog.");
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

        return redirect()->back()
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
            $q->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Collection sort order updated.']);
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
            'logo_path'    => $user->logo_path,
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
            $q->where('is_archived', false)->where('status', '!=', 'Pending Payment');
        }]);
        $financials = \App\Models\ParentOrder::calculateBatchFinancials($store->parentOrders, $store);
        $allDesigns = \App\Models\DesignCatalog::orderBy('sort_order', 'asc')->get();
        return view('admin.store_edit', compact('store', 'financials', 'allDesigns'));
    }

    public function updateStore(Request $request, TeamStore $store)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'sport'        => ['nullable', 'string', 'max:100'],
            'description'  => ['nullable', 'string'],
            'package_type' => ['nullable', 'in:package_a,package_b,package_c,individual'],
            'order_deadline' => ['nullable', 'date'],
            'status'       => ['required', 'in:pending,approved,submitted_to_admin,declined'],
            'pricing_approved' => ['boolean'],
            'is_tax_exempt'    => ['boolean'],
            'payment_mode'     => ['required', 'in:in_house,online,check'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['pricing_approved'] = $request->boolean('pricing_approved');
        $validated['is_tax_exempt'] = $request->boolean('is_tax_exempt');

        $store->update($validated);

        // Synchronize unpaid orders with the store's tax exemption and payment mode
        $isTaxExempt = $store->isTaxExempt();
        $taxRate = $isTaxExempt ? 0.00 : (float) config('services.stripe.tax_rate', 0.075);
        $feePercent = (float) config('services.stripe.fee_percent', 0.029);
        $feeFixed = (float) config('services.stripe.fee_fixed', 0.30);

        foreach ($store->parentOrders()->where(function($q) {
            $q->whereNull('payment_status')
              ->orWhereIn('payment_status', ['pending', 'not_applicable']);
        })->get() as $unpaidOrder) {
            $subtotal = $unpaidOrder->getCalculatedSubtotal($store);
            $taxAmount = $isTaxExempt ? 0.00 : round($subtotal * $taxRate, 2);
            if ($subtotal > 0 && ($store->isOnlinePayment() || $unpaidOrder->isOnlineOrder())) {
                $preFee = $subtotal + $taxAmount;
                $grandTotal = round(($preFee + $feeFixed) / (1 - $feePercent), 2);
                $feeAmount = round($grandTotal - $preFee, 2);
            } else {
                $feeAmount = 0.00;
                $grandTotal = $subtotal + $taxAmount;
            }

            $unpaidOrder->update([
                'subtotal'       => $subtotal,
                'tax_amount'     => $taxAmount,
                'fee_amount'     => $feeAmount,
                'total_paid'     => $grandTotal,
                'payment_status' => $validated['payment_mode'] === 'online' ? 'pending' : 'not_applicable',
            ]);
        }

        $redirectParams = ['store' => $store];
        if ($request->filled('return_to')) {
            $redirectParams['return_to'] = $request->input('return_to');
        }

        return redirect()->route('admin.store.edit', $redirectParams)
            ->with('success', "Store \"{$store->name}\" has been updated.");
    }

    public function updateStorePaymentMode(Request $request, TeamStore $store)
    {
        $validated = $request->validate([
            'payment_mode' => ['required', 'in:in_house,online,check'],
        ]);

        $store->update(['payment_mode' => $validated['payment_mode']]);

        if ($validated['payment_mode'] === 'online') {
            $store->parentOrders()->where(function($q) {
                $q->whereNull('payment_status')
                  ->orWhere('payment_status', 'not_applicable');
            })->update(['payment_status' => 'pending']);
        } else {
            $store->parentOrders()->where('payment_status', 'pending')
                  ->update([
                      'payment_status' => 'not_applicable',
                      'status'         => 'Submitted',
                  ]);
        }

        $label = match($validated['payment_mode']) {
            'online' => 'Online Credit Card Payment',
            'check'  => 'Pay by Check (Waived)',
            default  => 'Cash Collection (In-House)',
        };

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Store \"{$store->name}\" payment mode updated to: {$label}.",
                'payment_mode' => $validated['payment_mode'],
                'label' => $label,
            ]);
        }

        return back()->with('success', "Store \"{$store->name}\" payment mode updated to: {$label}.");
    }

    public function bulkUpdateStorePaymentMode(Request $request)
    {
        $validated = $request->validate([
            'store_ids' => ['required', 'array'],
            'store_ids.*' => ['required', 'exists:team_stores,id'],
            'payment_mode' => ['required', 'in:in_house,online,check'],
        ]);

        $mode = $validated['payment_mode'];
        $stores = TeamStore::whereIn('id', $validated['store_ids'])->get();

        foreach ($stores as $store) {
            $store->update(['payment_mode' => $mode]);

            if ($mode === 'online') {
                $store->parentOrders()->where(function($q) {
                    $q->whereNull('payment_status')
                      ->orWhere('payment_status', 'not_applicable');
                })->update(['payment_status' => 'pending']);
            } else {
                $store->parentOrders()->where('payment_status', 'pending')
                      ->update([
                          'payment_status' => 'not_applicable',
                          'status'         => 'Submitted',
                      ]);
            }
        }

        $label = match($mode) {
            'online' => 'Online Credit Card Payment (Stripe)',
            'check'  => 'Pay by Check (Waived)',
            default  => 'Cash Collection (In-House)',
        };

        $count = $stores->count();

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'count' => $count,
                'payment_mode' => $mode,
                'label' => $label,
                'message' => "Successfully updated {$count} store(s) to {$label}.",
            ]);
        }

        return back()->with('success', "Successfully updated {$count} store(s) to {$label}.");
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
                $updateData = [];

                if (array_key_exists('name', $data) && $data['name'] !== null && trim($data['name']) !== '') {
                    $updateData['name'] = trim($data['name']);
                }

                if (array_key_exists('wholesale_price', $data) && $data['wholesale_price'] !== null && $data['wholesale_price'] !== '') {
                    $updateData['wholesale_price'] = (float) $data['wholesale_price'];
                }

                if (array_key_exists('retail_price', $data) && $data['retail_price'] !== null && $data['retail_price'] !== '') {
                    $updateData['retail_price'] = (float) $data['retail_price'];
                }

                if (array_key_exists('sort_order', $data) && $data['sort_order'] !== null && $data['sort_order'] !== '') {
                    $updateData['sort_order'] = (int) $data['sort_order'];
                }

                if (!empty($updateData)) {
                    $storeItem->update($updateData);
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Store items updated.']);
        }

        return redirect()->route('admin.store.edit', $store)
            ->with('success', 'Store item pricing updated.');
    }

    public function updateStoreItemsSort(Request $request, TeamStore $store)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->items as $itemId => $data) {
            $storeItem = $store->items()->find($itemId);
            if ($storeItem && isset($data['sort_order'])) {
                $storeItem->update([
                    'sort_order' => (int) $data['sort_order'],
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Store items order saved.']);
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

        $redirectParams = ['store' => $store];
        if ($request->filled('return_to')) {
            $redirectParams['return_to'] = $request->input('return_to');
        }

        return redirect()->route('admin.store.edit', $redirectParams)
            ->with('success', 'Store cover image updated successfully.');
    }

    public function updateStoreLogo(Request $request, TeamStore $store)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($request->hasFile('logo')) {
            if ($store->logo_path) {
                $pathToRemove = str_replace('/storage/', '', $store->logo_path);
                $isReferencedElsewhere = TeamStore::where('id', '!=', $store->id)
                    ->where('logo_path', $store->logo_path)
                    ->exists() || ($store->user && $store->user->logo_path === $store->logo_path);

                if (!$isReferencedElsewhere) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
                }
            }
            $path = \App\Services\ImageOptimizer::optimize($request->file('logo'), 'organization_logos');
            $store->update(['logo_path' => $path]);
        }

        $redirectParams = ['store' => $store];
        if ($request->filled('return_to')) {
            $redirectParams['return_to'] = $request->input('return_to');
        }

        return redirect()->route('admin.store.edit', $redirectParams)
            ->with('success', 'Store logo updated successfully.');
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
            ->with(['user', 'teamStore'])
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('admin.dashboard')->with('error', 'Direct Order Batch not found or empty.');
        }

        $store = $orders->first()?->teamStore;
        $financials = \App\Models\ParentOrder::calculateBatchFinancials($orders, $store);
        $firstOrder = $orders->first();
        $coach = $firstOrder?->user;
        $coachStores = $coach ? $coach->teamStores : collect();
        $salesReps = User::whereIn('role', ['sales_rep', 'partner', 'agent'])->get();

        return view('admin.direct_batch_show', compact('batchId', 'orders', 'financials', 'firstOrder', 'coachStores', 'store', 'salesReps'));
    }

    public function updateDirectBatchStore(Request $request, $batchId)
    {
        $request->validate([
            'team_store_id' => 'required|exists:team_stores,id',
        ]);

        ParentOrder::where('batch_id', $batchId)->update([
            'team_store_id' => $request->team_store_id,
        ]);

        $store = TeamStore::find($request->team_store_id);

        return back()->with('success', 'Batch profile/store has been updated to "' . ($store?->name ?? 'selected store') . '".');
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
            ->where('order_type', '!=', 'direct')
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
        $order->load(['teamStore.user', 'salesRep']);
        $sizeChart = DesignCatalog::sizeChart();
        $salesReps = User::whereIn('role', ['sales_rep', 'partner', 'agent'])->get();
        return view('admin.order_edit', compact('order', 'sizeChart', 'salesReps'));
    }

    public function updateOrder(Request $request, ParentOrder $order)
    {
        $request->validate([
            'athlete_first_name'  => ['required', 'string', 'max:255'],
            'athlete_last_name'   => ['required', 'string', 'max:255'],
            'edit_pin'            => ['nullable', 'string', 'digits:4'],
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
            $entry = [
                'id'           => $item['id'] ?? $idx,
                'name'         => $item['name'] ?? 'Unknown',
                'types'        => $item['types'] ?? [],
                'sizes'        => $item['sizes'] ?? [],
                'qty'          => $item['qty'] ?? 1,
                'gender'       => $item['gender'] ?? $order->gender ?? 'Mens / Boys',
            ];
            if (!empty($item['components']) && is_array($item['components'])) {
                $entry['components'] = $item['components'];
            }
            $itemsJson[] = $entry;
        }

        $updateData = [
            'athlete_first_name'  => $request->athlete_first_name,
            'athlete_last_name'   => $request->athlete_last_name,
            'edit_pin'            => $request->filled('edit_pin') ? trim($request->edit_pin) : $order->edit_pin,
            'gender'              => $request->gender,
            'jersey_name'         => $request->jersey_name,
            'jersey_number'       => $request->jersey_number,
            'backpack_name'       => $request->backpack_name,
            'special_notes'       => $request->special_notes,
            'items_json'          => $itemsJson,
            'is_edited'           => true,
            'edited_by'           => 'admin',
        ];

        if ($order->team_store_id && !$order->isPaid()) {
            $store = $order->teamStore;
            $newSubtotal = 0;
            foreach ($itemsJson as $it) {
                $qty = (int) ($it['qty'] ?? 1);
                $prices = \App\Models\ParentOrder::getItemPrices($it, $store);
                $newSubtotal += ($prices['retail_price'] * $qty);
            }
            $isTaxExempt = (bool) $store->isTaxExempt();
            $taxRate = $isTaxExempt ? 0.00 : (float) config('services.stripe.tax_rate', 0.075);
            $newTax = round($newSubtotal * $taxRate, 2);

            if ($store->isOnlinePayment()) {
                $feePercent = (float) config('services.stripe.fee_percent', 0.029);
                $feeFixed = (float) config('services.stripe.fee_fixed', 0.30);
                $preFee = $newSubtotal + $newTax;
                $newTotal = round(($preFee + $feeFixed) / (1 - $feePercent), 2);
                $newFee = round($newTotal - $preFee, 2);
            } else {
                $newFee = 0.00;
                $newTotal = round($newSubtotal + $newTax, 2);
            }

            $updateData['subtotal'] = $newSubtotal;
            $updateData['tax_amount'] = $newTax;
            $updateData['fee_amount'] = $newFee;
            $updateData['total_paid'] = $newTotal;
            $updateData['total_retail_price'] = $newSubtotal;
        }

        $order->update($updateData);

        return redirect()->route('admin.order.edit', $order)
            ->with('success', 'Order updated successfully.');
    }

    public function deleteOrder(ParentOrder $order)
    {
        $isDirect = $order->isDirectOrder();
        $store = $order->teamStore;
        $batchId = $order->batch_id;
        $fullName = trim($order->athlete_first_name . ' ' . $order->athlete_last_name);
        $order->delete();
        
        if ($isDirect && $batchId) {
            return redirect()->route('admin.direct-batch.show', $batchId)
                ->with('success', "Order for {$fullName} has been deleted.");
        }

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
            $q->where('is_archived', false)->where('status', '!=', 'Pending Payment');
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

        $isExempt = (bool) $store->isTaxExempt();
        $taxRate = $isExempt ? 0.00 : 0.075;
        $taxRateStr = $isExempt ? '0% (Exempt)' : '7.5%';

        $columns = [
            'Store Name', 'Order ID', 'Submission Date', 'Payment Mode', 'Payment Status',
            'Athlete First Name', 'Athlete Last Name', 'Email', 'Phone', 'Shipping Address', 'Gender', 
            'Jersey Name', 'Jersey Number', 'Backpack Name',
            'Item Name', 'Item Type(s)', 'Size(s)', 'Quantity', 'Item Price', 'Total Row Price',
            'Tax Rate', 'Row Tax', 'Total Row With Tax',
            'Athlete Subtotal', 'Athlete Tax', 'Athlete Total to Collect',
            'Manufacture Price', 'Total Manufacture Price', 'Special Notes', 'Edited?'
        ];

        $callback = function() use ($store, $orders, $columns, $isExempt, $taxRate, $taxRateStr) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                if (is_array($order->items_json)) {
                    if ($order->isPaid() && (float) $order->subtotal > 0) {
                        $athleteSubtotal = (float) $order->subtotal;
                        $athleteTax = $isExempt ? 0.00 : (float) ($order->tax_amount > 0 ? $order->tax_amount : round($athleteSubtotal * $taxRate, 2));
                        $athleteTotalToCollect = $order->total_paid > 0 ? (float) $order->total_paid : round($athleteSubtotal + $athleteTax, 2);
                    } else {
                        $athleteSubtotal = (float) $order->getCalculatedSubtotal($store);
                        $athleteTax = $isExempt ? 0.00 : round($athleteSubtotal * $taxRate, 2);
                        if ($store->isOnlinePayment() && !$order->isPaid()) {
                            $feePercent = (float) config('services.stripe.fee_percent', 0.029);
                            $feeFixed = (float) config('services.stripe.fee_fixed', 0.30);
                            $preFee = $athleteSubtotal + $athleteTax;
                            $athleteTotalToCollect = round(($preFee + $feeFixed) / (1 - $feePercent), 2);
                        } else {
                            $athleteTotalToCollect = round($athleteSubtotal + $athleteTax, 2);
                        }
                    }

                    foreach ($order->items_json as $item) {
                        $typesStr = isset($item['types']) ? implode(', ', $item['types']) : ($item['type'] ?? 'N/A');
                        
                        $sizesArr = [];
                        if (isset($item['components']) && is_array($item['components'])) {
                            foreach ($item['components'] as $comp) {
                                if (isset($comp['sizes']) && is_array($comp['sizes'])) {
                                    foreach ($comp['sizes'] as $t => $s) {
                                        $sizesArr[] = "{$comp['name']} ($t): $s";
                                    }
                                } elseif (!empty($comp['size'])) {
                                    $sizesArr[] = "{$comp['name']}: {$comp['size']}";
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
                        $rowTax = $isExempt ? 0.00 : round($totalRowPrice * $taxRate, 2);
                        $totalRowWithTax = round($totalRowPrice + $rowTax, 2);
                        $mfgPrice = $prices['wholesale_price'];
                        $totalMfgPrice = $mfgPrice * $qty;

                        fputcsv($file, [
                            $store->name,
                            $order->id,
                            $order->created_at->format('Y-m-d'),
                            $store->payment_mode ?? 'in_house',
                            $order->payment_status ?? 'not_applicable',
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
                            $taxRateStr,
                            number_format($rowTax, 2, '.', ''),
                            number_format($totalRowWithTax, 2, '.', ''),
                            number_format($athleteSubtotal, 2, '.', ''),
                            number_format($athleteTax, 2, '.', ''),
                            number_format($athleteTotalToCollect, 2, '.', ''),
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

    public function finalizeStoreBatch(Request $request, TeamStore $store)
    {
        $unbatchedOrders = $store->parentOrders()
            ->whereNull('batch_id')
            ->where('is_archived', false);

        $count = $unbatchedOrders->count();

        if ($count === 0) {
            return back()->with('error', "No open or unbatched orders to finalize for store \"{$store->name}\".");
        }

        $batchId = (string) \Illuminate\Support\Str::uuid();

        $updateData = [
            'status'   => 'Submitted to Admin',
            'batch_id' => $batchId,
        ];

        if ($store->isCheckPayment() || ($store->payment_mode ?? 'in_house') === 'in_house') {
            $updateData['payment_status'] = 'not_applicable';
        }

        $unbatchedOrders->update($updateData);

        if ($store->status === 'approved') {
            $store->update(['status' => 'submitted_to_admin']);
        }

        return back()->with('success', "Master order of {$count} athlete order(s) successfully finalized and submitted to production for \"{$store->name}\".");
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
            'Payment Status', 'First Name', 'Last Name', 'Email', 'Phone', 'Shipping Address', 'Gender', 
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
                                } elseif (!empty($comp['size'])) {
                                    $sizesArr[] = "{$comp['name']}: {$comp['size']}";
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
                            $order->payment_status ?? 'not_applicable',
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

        $columns = ['Item Name', 'Gender / Cut', 'Type', 'Size', 'Unit Price', 'Total Quantity', 'Total Price', 'Manufacture Price', 'Total Manufacture Price'];
        
        $aggregated = [];

        foreach ($orders as $order) {
            if (is_array($order->items_json)) {
                foreach ($order->items_json as $item) {
                    $itemQty = max(1, (int)($item['qty'] ?? 1));
                    $gender = $item['gender'] ?? $order->gender ?? 'Not Specified';

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
                                    $key = "{$name}|{$gender}|{$type}|{$size}|{$itemPrice}|{$mfgPrice}";
                                    $aggregated[$key] = ($aggregated[$key] ?? 0) + $compQty;
                                }
                            } elseif (!empty($comp['size'])) {
                                $type = isset($comp['types']) ? implode(', ', $comp['types']) : ($comp['type'] ?? 'N/A');
                                $size = $comp['size'];
                                $key = "{$name}|{$gender}|{$type}|{$size}|{$itemPrice}|{$mfgPrice}";
                                $aggregated[$key] = ($aggregated[$key] ?? 0) + $compQty;
                            } else {
                                $type = isset($comp['types']) ? implode(', ', $comp['types']) : ($comp['type'] ?? 'N/A');
                                $key = "{$name}|{$gender}|{$type}|N/A|{$itemPrice}|{$mfgPrice}";
                                $aggregated[$key] = ($aggregated[$key] ?? 0) + $compQty;
                            }
                        }
                    } else {
                        $name = $item['name'] ?? 'Unknown Item';
                        
                        if (isset($item['sizes']) && is_array($item['sizes'])) {
                            foreach ($item['sizes'] as $type => $size) {
                                $key = "{$name}|{$gender}|{$type}|{$size}|{$itemPrice}|{$mfgPrice}";
                                $aggregated[$key] = ($aggregated[$key] ?? 0) + $itemQty;
                            }
                        } elseif (!empty($item['size'])) {
                            $type = isset($item['types']) ? implode(', ', $item['types']) : ($item['type'] ?? 'N/A');
                            $size = $item['size'];
                            $key = "{$name}|{$gender}|{$type}|{$size}|{$itemPrice}|{$mfgPrice}";
                            $aggregated[$key] = ($aggregated[$key] ?? 0) + $itemQty;
                        } else {
                            $type = isset($item['types']) ? implode(', ', $item['types']) : ($item['type'] ?? 'N/A');
                            $key = "{$name}|{$gender}|{$type}|N/A|{$itemPrice}|{$mfgPrice}";
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
                $unitPrice = (float)($parts[4] ?? 0);
                $totalPrice = $unitPrice * $qty;
                $mfgPrice = (float)($parts[5] ?? 0);
                $totalMfgPrice = $mfgPrice * $qty;
                fputcsv($file, [$parts[0], $parts[1], $parts[2], $parts[3], number_format($unitPrice, 2, '.', ''), $qty, number_format($totalPrice, 2, '.', ''), number_format($mfgPrice, 2, '.', ''), number_format($totalMfgPrice, 2, '.', '')]);
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

    public function deleteQuote(\App\Models\QuoteRequest $quoteRequest)
    {
        $quoteRequest->delete();
        return redirect()->back()->with('success', 'Quote inquiry deleted successfully.');
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

    // ─── DYNAMIC LANDING PAGE SETTINGS ──────────────────────────────────────────

    public function updateLandingSettings(Request $request)
    {
        // 1. Process all text and select inputs (excluding CSRF and internal keys)
        $excludedKeys = ['_token', '_method'];
        foreach ($request->except($excludedKeys) as $key => $value) {
            if (!$request->hasFile($key) && is_string($value)) {
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        // 2. Process all uploaded file inputs dynamically
        foreach ($request->allFiles() as $fileKey => $file) {
            if ($file->isValid()) {
                $path = $file->store('landing', 'public');
                SiteSetting::updateOrCreate(['key' => $fileKey], ['value' => '/storage/' . $path]);
            }
        }

        return redirect()->route('admin.dashboard', ['tab' => 'landing_settings'])
            ->with('success', 'Landing page settings updated successfully.');
    }

    // ─── NEWS & MEDIA MANAGEMENT ────────────────────────────────────────────────

    public function createNewsArticle(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'author'      => 'nullable|string|max:150',
            'summary'     => 'nullable|string|max:1000',
            'content'     => 'nullable|string',
            'video_url'   => 'nullable|url|max:500',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'gallery.*'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'cta_text'    => 'nullable|string|max:100',
            'cta_url'     => 'nullable|string|max:255',
            'is_featured' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = '/storage/' . $request->file('cover_image')->store('news', 'public');
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = '/storage/' . $file->store('news/gallery', 'public');
            }
        }

        $article = NewsArticle::create([
            'title'          => $validated['title'],
            'slug'           => Str::slug($validated['title']) . '-' . strtolower(Str::random(5)),
            'category'       => $validated['category'],
            'author'         => $validated['author'] ?? 'The Commission Editorial',
            'summary'        => $validated['summary'] ?? null,
            'content'        => $validated['content'] ?? null,
            'cover_image'    => $coverImagePath,
            'video_url'      => $validated['video_url'] ?? null,
            'gallery_images' => $galleryPaths,
            'cta_text'       => $validated['cta_text'] ?? 'Request A Custom Quote',
            'cta_url'        => $validated['cta_url'] ?? '/quote',
            'published_at'   => now(),
            'is_featured'    => $request->boolean('is_featured'),
            'is_active'      => true,
            'sort_order'     => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'news_articles'])
            ->with('success', 'News article created and published successfully!');
    }

    public function updateNewsArticle(Request $request, NewsArticle $article)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'author'      => 'nullable|string|max:150',
            'summary'     => 'nullable|string|max:1000',
            'content'     => 'nullable|string',
            'video_url'   => 'nullable|url|max:500',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'gallery.*'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'cta_text'    => 'nullable|string|max:100',
            'cta_url'     => 'nullable|string|max:255',
            'is_featured' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = '/storage/' . $request->file('cover_image')->store('news', 'public');
        }

        if ($request->hasFile('gallery')) {
            $gallery = $article->gallery_images ?? [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = '/storage/' . $file->store('news/gallery', 'public');
            }
            $validated['gallery_images'] = $gallery;
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $article->is_active;

        $article->update($validated);

        return redirect()->route('admin.dashboard', ['tab' => 'news_articles'])
            ->with('success', 'News article updated successfully.');
    }

    public function deleteNewsArticle(NewsArticle $article)
    {
        $article->delete();
        return redirect()->route('admin.dashboard', ['tab' => 'news_articles'])
            ->with('success', 'News article deleted.');
    }
}
