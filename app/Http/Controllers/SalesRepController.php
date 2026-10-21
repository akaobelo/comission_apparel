<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeamStore;
use App\Models\ParentOrder;
use App\Models\DesignCatalog;
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
            ->withCount(['teamStores', 'parentOrders'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Assigned Team Stores
        $stores = TeamStore::where('sales_rep_id', $user->id)
            ->orWhereIn('user_id', $clientIds)
            ->with(['user'])
            ->withCount(['parentOrders'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Orders attributed to this sales rep
        $orders = ParentOrder::where(function($q) use ($user, $clientIds, $storeIds) {
                $q->where('sales_rep_id', $user->id)
                  ->orWhereIn('user_id', $clientIds)
                  ->orWhereIn('team_store_id', $storeIds);
            })
            ->where('status', '!=', 'Draft')
            ->with(['teamStore', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Automatically stamp rep attribution and compute commission for any unstamped order in scope
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

        // Financial Metrics
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
        $orderBatches = \App\Models\ParentOrder::groupOrdersIntoBatches($orders, $user);

        return view('rep.dashboard', compact(
            'user',
            'clients',
            'stores',
            'orders',
            'orderBatches',
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
        [$clientIds, $storeIds] = $this->getRepScope($rep);

        $orders = ParentOrder::where(function($q) use ($rep, $clientIds, $storeIds) {
                $q->where('sales_rep_id', $rep->id)
                  ->orWhereIn('user_id', $clientIds)
                  ->orWhereIn('team_store_id', $storeIds);
            })
            ->where('status', '!=', 'Draft')
            ->with(['teamStore', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

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
}
