@extends('layouts.app')
@section('title', 'Review Direct Order Batch | Admin')
@section('content')
<div class="max-w-7xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    @php
        $returnUrl = request('return_to');
        if (!$returnUrl) {
            $referer = request()->headers->get('referer');
            if ($referer && str_contains($referer, 'sales-rep')) {
                $returnUrl = $referer;
            }
        }
        if (!$returnUrl) {
            $batchRep = $firstOrder?->user?->assignedSalesRep ?: $firstOrder?->salesRep;
            if ($batchRep) {
                $returnUrl = route('admin.sales-rep.show', ['user' => $batchRep, 'tab' => 'orders', 'batch' => 'batch_' . $batchId]);
            }
        }
        $backUrl = $returnUrl ?: route('admin.dashboard', ['tab' => 'orders']);
        $backLabel = ($returnUrl && str_contains($returnUrl, 'sales-rep')) ? 'Back to Sales Partner' : 'Back to Dashboard';
    @endphp
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ $backUrl }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ $backLabel }}
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold uppercase tracking-wide">Direct Order Batch: {{ $batchId }} @if($store) — <span class="text-primary font-black">{{ $store->name }}</span> @endif</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-bold">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3 space-y-6">
            <!-- Orders List -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Placed Orders</h3>
                        @if($orders->isNotEmpty())
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Timeline Tracker:</span>
                                <form action="{{ route('admin.batch.status.update', $batchId) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <select name="status" class="bg-white border border-slate-300 rounded px-2 py-1 text-[11px] font-bold text-slate-700 focus:border-primary focus:outline-none shadow-sm" onchange="this.form.submit()">
                                        <option value="Submitted to Admin" {{ $firstOrder->status === 'Submitted to Admin' ? 'selected' : '' }}>Submitted to Admin</option>
                                        <option value="Processing" {{ $firstOrder->status === 'Processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="In Production" {{ $firstOrder->status === 'In Production' ? 'selected' : '' }}>In Production</option>
                                        <option value="Shipped" {{ $firstOrder->status === 'Shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="Delivered" {{ $firstOrder->status === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="Completed" {{ $firstOrder->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-xs font-bold text-slate-500">{{ $orders->count() }} Orders</div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.batch.export', $batchId) }}" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 text-[11px] font-bold uppercase tracking-wide rounded hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                Roster CSV
                            </a>
                            <a href="{{ route('admin.batch.export-aggregate', $batchId) }}" class="px-3 py-1.5 bg-slate-900 border border-slate-900 text-white text-[11px] font-bold uppercase tracking-wide rounded hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                                Aggregate CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <div class="p-6 hover:bg-slate-50 transition-colors group">
                            <div class="flex flex-wrap md:flex-nowrap justify-between gap-4">
                                <div class="w-full md:w-auto">
                                    <div class="font-bold text-slate-900">{{ $order->athlete_first_name }} {{ $order->athlete_last_name }}</div>
                                    <div class="text-xs text-slate-500 mt-1 flex gap-3">
                                        @if($order->gender)<span class="bg-slate-100 px-2 py-0.5 rounded text-slate-600 font-medium">{{ $order->gender }}</span>@endif
                                    </div>
                                    @if($order->is_edited)
                                        <div class="mt-2 text-[10px] font-bold uppercase tracking-wider text-orange-600 bg-orange-50 px-2 py-1 rounded inline-block">
                                            Edited by {{ $order->edited_by }}
                                        </div>
                                    @endif
                                </div>

                                <div class="w-full md:w-auto space-y-2 flex-grow">
                                    @foreach(is_array($order->items_json) ? $order->items_json : [] as $item)
                                        <div class="text-sm flex justify-between bg-white border border-slate-100 p-2 rounded items-center">
                                            <div>
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-slate-700 font-medium truncate max-w-[200px]">{{ $item['name'] ?? 'Item' }}</span>
                                                    @if(!empty($item['gender']))
                                                        <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                                            {{ $item['gender'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if(!empty($item['components']) && is_array($item['components']))
                                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                                        @foreach($item['components'] as $comp)
                                                            <span>{{ $comp['name'] ?? '' }}: <strong>{{ $comp['size'] ?? '' }}</strong></span>@if(!$loop->last), @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-slate-500 text-right whitespace-nowrap">
                                                @if(!empty($item['sizes']))
                                                    @foreach($item['sizes'] as $sizeType => $size)
                                                        <span class="mr-2 border-r border-slate-200 pr-2 last:border-0 last:mr-0 last:pr-0">
                                                            {{ $sizeType === 'default' ? '' : str_replace('_', ' ', $sizeType).':' }} <strong>{{ $size }}</strong>
                                                        </span>
                                                    @endforeach
                                                @elseif(!empty($item['size']))
                                                    <strong>{{ $item['size'] }}</strong>
                                                @endif
                                                <span class="ml-2 bg-slate-100 px-2 py-0.5 rounded font-bold text-slate-700">Qty: {{ $item['qty'] ?? 1 }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($order->special_notes)
                                        <div class="text-xs text-slate-600 bg-yellow-50 p-2 rounded border border-yellow-100 mt-2">
                                            <strong class="text-yellow-800">Note:</strong> {{ $order->special_notes }}
                                        </div>
                                    @endif
                                </div>

                                <div class="w-full md:w-auto flex md:flex-col gap-2 items-end justify-start">
                                    <a href="{{ route('admin.order.edit', $order) }}" class="btn btn-outline py-2 px-4 text-xs">Edit</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            No active orders in this batch.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:w-1/3">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden sticky top-32">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Batch Summary</h3>
                </div>
                @php
                    $isOnlinePayment = ($firstOrder->payment_status === 'paid' || $firstOrder->stripe_session_id || ($financials['total_online_paid'] ?? 0) > 0);
                    $isCheckPayment = ($firstOrder->payment_status === 'not_applicable');
                    $isTaxExempt = (bool) ($firstOrder->user?->is_tax_exempt);
                @endphp
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Coach</span><span class="font-bold text-slate-900 text-right">{{ $firstOrder->user->first_name ?? 'Unknown' }} {{ $firstOrder->user->last_name ?? '' }}<br><span class="text-xs font-normal text-slate-500">{{ $firstOrder->user->email ?? $firstOrder->user->organization ?? '' }}</span></span></div>
                    <div class="flex justify-between text-sm items-center">
                        <span class="text-slate-500">Profile / Store</span>
                        <div class="text-right">
                            @if(isset($coachStores) && $coachStores->isNotEmpty())
                                <form action="{{ route('admin.direct-batch.store.update', $batchId) }}" method="POST">
                                    @csrf
                                    <select name="team_store_id" onchange="if(confirm('Reassign this batch to ' + this.options[this.selectedIndex].text + '?')) { this.form.submit(); } else { this.value = '{{ $store?->id }}'; }" class="text-xs font-bold text-slate-800 bg-slate-50 border border-slate-300 rounded px-2 py-1 focus:outline-none focus:border-primary max-w-[210px] truncate shadow-sm">
                                        @foreach($coachStores as $cs)
                                            <option value="{{ $cs->id }}" {{ ($store && $store->id === $cs->id) ? 'selected' : '' }}>
                                                {{ $cs->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="font-bold text-slate-900">{{ $store?->name ?? $firstOrder->user?->organization ?? '—' }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Submitted</span><span class="font-bold text-slate-900">{{ $firstOrder->created_at?->format('M d, Y') ?? '—' }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Payment Mode</span>
                        <span class="font-bold text-slate-900 uppercase text-xs">{{ $isOnlinePayment ? 'Online (Stripe)' : ($isCheckPayment ? 'Pay by Check' : 'In-House') }}</span>
                    </div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Athletes Ordered</span><span class="font-bold text-slate-900">{{ $orders->count() }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Total Items</span><span class="font-bold text-slate-900">{{ $financials['total_items_sold'] ?? 0 }}</span></div>
                    <div class="flex justify-between text-sm items-center">
                        <span class="text-slate-500">Payment</span>
                        @if($firstOrder->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-green-700 bg-green-50 px-2.5 py-0.5 rounded-full border border-green-200">
                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Paid Online
                            </span>
                        @elseif($isCheckPayment)
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">
                                Pay by Check
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                Pending
                            </span>
                        @endif
                    </div>
                    
                    <div class="pt-3 mt-3 border-t border-slate-200 space-y-2.5">
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Total Sales</span><span class="font-bold text-slate-900">${{ number_format($financials['total_sales'] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Average order</span><span class="font-bold text-slate-900">${{ number_format($financials['average_order_value'] ?? 0, 2) }}</span></div>
                        @if($isOnlinePayment)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Sales Tax {{ $isTaxExempt ? '(501(c)(3) Waived)' : '(7.5%)' }}</span>
                                <span class="font-bold {{ $isTaxExempt ? 'text-blue-700' : 'text-slate-700' }}">${{ number_format($financials['total_tax'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm"><span class="text-slate-500">Processing Fee (Stripe)</span><span class="font-bold text-slate-600">${{ number_format($financials['total_fees'] ?? 0, 2) }}</span></div>
                            <div class="flex justify-between text-sm pt-1 border-t border-slate-100"><span class="font-bold text-emerald-600">Online Payment (Stripe)</span><span class="font-bold text-emerald-600">${{ number_format($financials['total_online_paid'] ?? 0, 2) }}</span></div>
                        @endif
                        <div class="flex justify-between text-sm pt-1 border-t border-slate-100"><span class="font-bold text-secondary">Due to TCA</span><span class="font-bold text-secondary">${{ number_format($financials['total_wholesale'] ?? 0, 2) }}</span></div>
                        @if(($financials['coach_profit_owed'] ?? 0) > 0)
                            <div class="flex justify-between text-sm pt-1.5 border-t border-slate-100"><span class="text-indigo-900 font-bold">Store Profit Owed</span><span class="font-black text-indigo-600">${{ number_format($financials['coach_profit_owed'], 2) }}</span></div>
                        @endif
                    </div>
                </div>

                <!-- Batch Tariffs & Partner Commission Allocation -->
                <div class="mt-6 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                            Tariffs & Partner Split
                        </h3>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $orders->count() }} Orders in Batch</span>
                    </div>

                    @php
                        $batchMfg = $orders->sum(fn($o) => (float)$o->manufacturing_cost);
                        $hasCustomMfg = $orders->contains(fn($o) => (float)$o->manufacturing_cost > 0);
                        $batchTariff = $orders->sum(fn($o) => (float)$o->tariff_amount);
                        $batchFees = $orders->sum(fn($o) => (float)$o->fee_deduction);
                        $assignedRepId = ($firstOrder->user?->sales_rep_id) ?: ($firstOrder->sales_rep_id ?? null);
                        $currentRep = $assignedRepId ? $salesReps->firstWhere('id', $assignedRepId) : null;
                        $batchRepEarned = $orders->sum(fn($o) => (float)$o->rep_commission_earned);
                        $batchNetMargin = $orders->sum(fn($o) => (float)$o->net_margin);
                    @endphp

                    <form action="{{ route('admin.direct-batch.financials.update', $batchId) }}" method="POST" class="p-5 space-y-3.5">
                        @csrf
                        @if($returnUrl)
                            <input type="hidden" name="return_to" value="{{ $returnUrl }}">
                        @endif
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Total Manufacturing Cost ($)</label>
                            <input type="number" step="0.01" min="0" name="manufacturing_cost" value="{{ old('manufacturing_cost', $hasCustomMfg ? $batchMfg : '') }}" placeholder="Enter factory production base cost" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            <span class="text-[10px] text-slate-400">Actual factory cost to produce items (deducted from gross to find net profit)</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Batch Tariff Amount ($)</label>
                            <input type="number" step="0.01" min="0" name="tariff_amount" value="{{ old('tariff_amount', $batchTariff) }}" placeholder="e.g. 75.00" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            <span class="text-[10px] text-slate-400">Import / freight tariff allocated to batch</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Extra Fee Deductions ($)</label>
                            <input type="number" step="0.01" min="0" name="fee_deduction" value="{{ old('fee_deduction', $batchFees) }}" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Assigned Sales Partner</label>
                            <select name="sales_rep_id" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                <option value="">-- No Partner Assigned --</option>
                                @foreach($salesReps as $rep)
                                    <option value="{{ $rep->id }}" {{ ($assignedRepId == $rep->id) ? 'selected' : '' }}>
                                        {{ $rep->name }} ({{ number_format($rep->getEffectiveCommissionRate(), 1) }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Split %</label>
                                <input type="number" step="0.1" min="0" max="100" name="applied_rep_commission_rate" value="{{ old('applied_rep_commission_rate', $firstOrder->applied_rep_commission_rate ?? $currentRep?->getEffectiveCommissionRate() ?? 50.00) }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Status</label>
                                <select name="commission_status" class="w-full bg-white border border-slate-300 rounded px-2 py-1.5 text-xs font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="pending" {{ $firstOrder->commission_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="cleared" {{ $firstOrder->commission_status === 'cleared' ? 'selected' : '' }}>Cleared</option>
                                    <option value="paid" {{ $firstOrder->commission_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Notes</label>
                            <input type="text" name="financial_notes" value="{{ old('financial_notes', $firstOrder->financial_notes) }}" placeholder="e.g. Schedule A tariff deduction applied" class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        </div>

                        <div class="p-3 bg-slate-50 border border-slate-200 rounded space-y-1.5 text-xs font-mono">
                            <div class="flex justify-between items-center text-slate-500">
                                <span>Batch Net Margin:</span>
                                <span class="font-bold text-slate-900">${{ number_format($batchNetMargin, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-500 border-t border-slate-200 pt-1.5">
                                <span>Rep Payout Total:</span>
                                <span class="font-black text-emerald-600 text-sm">${{ number_format($batchRepEarned, 2) }}</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded text-xs font-black uppercase tracking-wider transition-colors shadow-sm">
                            Apply Tariffs & Recalculate Split
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
