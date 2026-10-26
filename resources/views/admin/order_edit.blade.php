@extends('layouts.app')
@section('title', 'Edit Order | Admin')
@section('content')
<div class="max-w-3xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    @php
        $returnUrl = request('return_to');
        if (!$returnUrl) {
            $referer = request()->headers->get('referer');
            if ($referer && str_contains($referer, 'sales-rep')) {
                $returnUrl = $referer;
            }
        }
        $cancelUrl = $returnUrl ?: (!$order->isDirectOrder() && $order->teamStore ? route('admin.store.edit', $order->teamStore) : ($order->batch_id ? route('admin.direct-batch.show', $order->batch_id) : route('admin.dashboard', ['tab' => 'orders'])));
        $backLabel = ($returnUrl && str_contains($returnUrl, 'sales-rep')) ? 'Back to Sales Partner' : (!$order->isDirectOrder() && $order->teamStore ? 'Back to Store' : 'Back to Batch');
    @endphp
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all shadow-sm hover:shadow cursor-pointer">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Admin Control Center</span>
        </a>
    </div>

    <div class="flex items-center gap-4 mb-8">
        <a href="{{ $cancelUrl }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1.5 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ $backLabel }}
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Editing: {{ $order->athlete_first_name }} {{ $order->athlete_last_name }}'s Order</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Order Editor</h2>
            @if(!$order->isDirectOrder() && $order->teamStore)
                <p class="text-sm text-slate-500 mt-1">Store: {{ $order->teamStore->name }} · Submitted {{ $order->created_at->format('M d, Y') }}</p>
            @else
                <p class="text-sm text-slate-500 mt-1">Direct Order Batch: {{ $order->batch_id }} @if($order->teamStore) · {{ $order->teamStore->name }} @endif · Submitted {{ $order->created_at->format('M d, Y') }}</p>
            @endif
            @if($order->is_edited)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-orange-100 border border-orange-200 text-orange-700 text-xs font-bold rounded">
                    Previously edited by {{ $order->edited_by }}
                </div>
            @endif
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-300 text-amber-900 rounded-lg text-xs font-black uppercase tracking-wider shadow-sm">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Parent 4-Digit PIN:</span>
                    <strong class="font-mono text-sm tracking-widest text-slate-900">{{ $order->edit_pin ?: 'None Set' }}</strong>
                </span>
                <span class="text-xs text-slate-500 font-medium">Coaches or parents can use this PIN to adjust sizing before store closure.</span>
            </div>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.order.update', $order) }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-3 gap-5 border-b border-slate-200 pb-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">First Name</label>
                        <input type="text" name="athlete_first_name" value="{{ old('athlete_first_name', $order->athlete_first_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Last Name</label>
                        <input type="text" name="athlete_last_name" value="{{ old('athlete_last_name', $order->athlete_last_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Gender</label>
                        <input type="text" name="gender" value="{{ old('gender', $order->gender) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Parent 4-Digit PIN</label>
                        <input type="text" name="edit_pin" value="{{ old('edit_pin', $order->edit_pin) }}" maxlength="4" pattern="\d{4}" placeholder="e.g. 1234" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 font-mono tracking-widest font-black focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                        <p class="text-[10px] text-slate-400 mt-1">Parents use this PIN on the storefront to edit sizing.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Jersey Name</label>
                        <input type="text" name="jersey_name" value="{{ old('jersey_name', $order->jersey_name) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Jersey Number</label>
                        <input type="text" name="jersey_number" value="{{ old('jersey_number', $order->jersey_number) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Backpack Name</label>
                        <input type="text" name="backpack_name" value="{{ old('backpack_name', $order->backpack_name) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>

                    <div class="col-span-2 md:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Special Notes</label>
                        <input type="text" name="special_notes" value="{{ old('special_notes', $order->special_notes) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-700 mb-4 pb-2 border-b border-slate-200">Order Items</h3>
                    <div class="space-y-4">
                        @foreach(is_array($order->items_json) ? $order->items_json : [] as $idx => $item)
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <div class="flex items-start justify-between gap-4 flex-wrap">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $item['name'] ?? 'Unknown Item' }}</div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-primary mt-0.5">{{ str_replace('_', ' ', $item['type'] ?? '') }}</div>
                                </div>
                                <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item['id'] ?? $idx }}">
                                <input type="hidden" name="items[{{ $idx }}][name]" value="{{ $item['name'] ?? '' }}">
                                @if(isset($item['types']) && is_array($item['types']))
                                    @foreach($item['types'] as $tIdx => $type)
                                        <input type="hidden" name="items[{{ $idx }}][types][{{ $tIdx }}]" value="{{ $type }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="items[{{ $idx }}][type]" value="{{ $item['type'] ?? '' }}">
                                @endif
                                @if(!empty($item['components']) && is_array($item['components']))
                                    @foreach($item['components'] as $cIdx => $comp)
                                        <input type="hidden" name="items[{{ $idx }}][components][{{ $cIdx }}][name]" value="{{ $comp['name'] ?? '' }}">
                                        <input type="hidden" name="items[{{ $idx }}][components][{{ $cIdx }}][size]" value="{{ $comp['size'] ?? '' }}">
                                        <input type="hidden" name="items[{{ $idx }}][components][{{ $cIdx }}][type]" value="{{ $comp['type'] ?? '' }}">
                                    @endforeach
                                @endif
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $sizes = $item['sizes'] ?? [];
                                        if (empty($sizes) && isset($item['size'])) {
                                            $sizes = ['default' => $item['size']];
                                        }
                                    @endphp
                                    @foreach($sizes as $sizeType => $sizeVal)
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">{{ $sizeType === 'default' ? 'Size' : str_replace('_', ' ', $sizeType) }}</label>
                                            <select name="items[{{ $idx }}][sizes][{{ $sizeType }}]" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                                @foreach($sizeChart as $size)
                                                    <option value="{{ $size }}" {{ $sizeVal === $size ? 'selected' : '' }}>{{ $size }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Gender / Cut</label>
                                        @php
                                            $itemGender = $item['gender'] ?? $order->gender ?? 'Mens / Boys';
                                        @endphp
                                        <select name="items[{{ $idx }}][gender]" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm min-w-[120px]">
                                            <option value="Mens / Boys" {{ in_array($itemGender, ['Mens / Boys', 'Male']) ? 'selected' : '' }}>Men's / Boy's</option>
                                            <option value="Womens / Girls" {{ in_array($itemGender, ['Womens / Girls', 'Female']) ? 'selected' : '' }}>Women's / Girl's</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Qty</label>
                                        <input type="number" name="items[{{ $idx }}][qty]" value="{{ old("items.{$idx}.qty", $item['qty'] ?? 1) }}" min="1" max="10" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm w-16">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn btn-primary py-3 px-8 text-sm uppercase tracking-wider">Save Changes</button>
                    <a href="{{ $cancelUrl }}" class="btn btn-outline py-3 px-6 text-sm uppercase tracking-wider">Cancel</a>
                </div>
            </form>

            <!-- Super Admin Financials, Tariffs & Rep Commission Controls -->
            <div class="mt-10 pt-8 border-t-2 border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Financials, Tariffs & Partner Commission Management
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Enter actual manufacturing costs, tariffs, and fee deductions. The system recalculates net margin and rep commission payout.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider border {{ $order->commission_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($order->commission_status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                        Status: {{ strtoupper($order->commission_status ?? 'pending') }}
                    </span>
                </div>

                <form action="{{ route('admin.order.financials.update', $order) }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Manufacturing Base ($)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-slate-500 font-bold text-xs font-sans">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="manufacturing_cost" value="{{ old('manufacturing_cost', $order->manufacturing_cost) }}" placeholder="0.00" class="w-full bg-white border border-slate-300 rounded-lg pl-7 pr-3 py-2 text-sm font-sans font-bold text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <span class="text-[10px] text-slate-400">Wholesale cost</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Tariff Amount ($)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-slate-500 font-bold text-xs font-sans">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="tariff_amount" value="{{ old('tariff_amount', $order->tariff_amount) }}" placeholder="0.00" class="w-full bg-white border border-slate-300 rounded-lg pl-7 pr-3 py-2 text-sm font-sans font-bold text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <span class="text-[10px] text-slate-400">Import / freight tariff</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Processing / Fees ($)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-slate-500 font-bold text-xs font-sans">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="fee_deduction" value="{{ old('fee_deduction', $order->fee_deduction) }}" placeholder="0.00" class="w-full bg-white border border-slate-300 rounded-lg pl-7 pr-3 py-2 text-sm font-sans font-bold text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <span class="text-[10px] text-slate-400">Merchant / extra fees</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Fundraising ($)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-slate-500 font-bold text-xs font-sans">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="fundraising_amount" value="{{ old('fundraising_amount', $order->fundraising_amount) }}" placeholder="0.00" class="w-full bg-white border border-slate-300 rounded-lg pl-7 pr-3 py-2 text-sm font-sans font-bold text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <span class="text-[10px] text-slate-400">Coach kickback</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-slate-200">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Attributed Sales Partner</label>
                            <select name="sales_rep_id" onchange="onOrderRepChange(this)" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                <option value="" data-rate="0">-- No Partner Assigned --</option>
                                @foreach($salesReps as $rep)
                                    <option value="{{ $rep->id }}" data-rate="{{ number_format($rep->getEffectiveCommissionRate(), 1, '.', '') }}" {{ (old('sales_rep_id', $order->sales_rep_id) == $rep->id) ? 'selected' : '' }}>
                                        {{ $rep->name }} ({{ number_format($rep->getEffectiveCommissionRate(), 1) }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Commission Split (%)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <input type="number" step="0.1" min="0" max="100" id="order_commission_rate" name="applied_rep_commission_rate" value="{{ old('applied_rep_commission_rate', $order->applied_rep_commission_rate ?? $order->salesRep?->getEffectiveCommissionRate() ?? 50.00) }}" class="w-full bg-white border border-slate-300 rounded-lg pr-7 pl-3 py-2 text-sm font-sans font-bold text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                    <span class="text-slate-400 font-bold text-xs font-sans">%</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400">Defaults to partner tier %</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Commission Status</label>
                            <select name="commission_status" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                <option value="pending" {{ old('commission_status', $order->commission_status) === 'pending' ? 'selected' : '' }}>Pending (In Production)</option>
                                <option value="cleared" {{ old('commission_status', $order->commission_status) === 'cleared' ? 'selected' : '' }}>Cleared (Delivered & Settled)</option>
                                <option value="paid" {{ old('commission_status', $order->commission_status) === 'paid' ? 'selected' : '' }}>Paid (Disbursed)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-600 mb-1">Financial Notes / Tariff Details</label>
                        <input type="text" name="financial_notes" value="{{ old('financial_notes', $order->financial_notes) }}" placeholder="e.g. Freight surcharge on overseas shipment batch #104" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm font-sans text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>

                    <!-- Calculated Summary Banner -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between flex-wrap gap-4 text-xs font-sans">
                        <div>
                            <span class="text-slate-500 font-semibold">Retail Total:</span>
                            <strong class="text-slate-900 font-bold text-sm font-sans">${{ number_format((float)($order->subtotal > 0 ? $order->subtotal : $order->total_retail_price), 2) }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold">Current Net Margin:</span>
                            <strong class="text-slate-900 font-bold text-sm font-sans">${{ number_format((float)$order->net_margin, 2) }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold">Rep Payout:</span>
                            <strong class="text-emerald-600 font-black text-base font-sans">${{ number_format((float)$order->rep_commission_earned, 2) }}</strong>
                        </div>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-colors shadow-sm ml-auto">
                            Update Financials & Tariffs
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-200 flex justify-end">
                <form action="{{ route('admin.order.delete', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to completely delete this order? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 hover:text-red-700 font-bold uppercase tracking-wider rounded-lg text-xs transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function onOrderRepChange(selectEl) {
    const selected = selectEl.options[selectEl.selectedIndex];
    const rate = selected ? selected.getAttribute('data-rate') : null;
    const rateInput = document.getElementById('order_commission_rate');
    if (rate !== null && rate !== '' && rateInput) {
        rateInput.value = rate;
    }
}
</script>
@endsection
