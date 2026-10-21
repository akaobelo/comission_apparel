@extends('layouts.app')
@section('title', 'Sales Partner Portal | The Commission Apparel')
@section('content')
<div class="max-w-[1500px] mx-auto px-6 pb-16 pt-28 lg:pt-36" x-data="{
    activeTab: 'clients',
    addClientModal: false,
    copied: false,
    copyLink() {
        navigator.clipboard.writeText('{{ $referralUrl }}');
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    }
}">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-green-500 hover:text-green-800 text-sm font-black">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-red-500 hover:text-red-800 text-sm font-black">&times;</button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-8 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                @if($user->profile_image_url)
                    <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover shadow-md flex-shrink-0 border border-slate-200">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-2xl tracking-tight shadow-md flex-shrink-0">
                        {{ strtoupper(substr($user->first_name ?: 'S', 0, 1) . substr($user->last_name ?: 'P', 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black tracking-wider uppercase bg-primary/10 text-primary border border-primary/20">
                            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                            Authorized Sales Partner
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-black tracking-wider uppercase bg-slate-100 text-slate-700 border border-slate-200">
                            Tier Rate: <strong class="text-slate-900">{{ number_format($user->getEffectiveCommissionRate(), 1) }}%</strong> Net Margin Split
                        </span>
                        @if($user->partner_category)
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                {{ $user->partner_category_label }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-black text-slate-900 uppercase tracking-tight mt-2">
                        Welcome back, {{ $user->name }}
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Manage your assigned clients, monitor team store fulfillment, and track itemized commission statements.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch gap-3 lg:min-w-[460px]">
                <!-- Referral Link Widget -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex-grow">
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5">
                        Your Personal Coach Recruitment Link
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $referralUrl }}" class="w-full text-xs bg-white border border-slate-200 rounded px-3 py-2 text-slate-700 font-medium focus:outline-none select-all">
                        <button type="button" @click="copyLink()" class="px-4 py-2 bg-slate-900 text-white rounded text-xs font-bold uppercase tracking-wider hover:bg-slate-800 transition-all flex items-center gap-1.5 whitespace-nowrap shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5">
                        Any coach registering via this link is automatically locked to your book of business.
                    </p>
                </div>

                <!-- Sign Out Form -->
                <form action="{{ route('logout') }}" method="POST" class="flex-shrink-0 flex items-stretch">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-3 sm:py-0 bg-slate-50 hover:bg-red-50 border border-slate-200 hover:border-red-200 text-slate-600 hover:text-red-600 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex sm:flex-col items-center justify-center gap-1.5 shadow-sm" title="Sign Out of Partner Portal">
                        <svg class="w-4 h-4 text-slate-400 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Date Filter Bar -->
    <div class="mb-6 bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-xs font-black uppercase tracking-wider text-slate-700">Filter Orders by Date:</span>
            @if(($dateFilter ?? 'all') !== 'all')
                <span class="inline-flex px-2 py-0.5 text-[10px] font-black rounded-full bg-primary/10 text-primary uppercase">
                    Active Filter
                </span>
            @endif
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
            @php
                $filters = [
                    'all'             => 'All Time',
                    'today'           => 'Today',
                    'this_week'       => 'This Week',
                    'this_month'      => 'This Month',
                    'this_quarter'    => 'This Quarter',
                    'last_six_months' => 'Last 6 Months',
                    'this_year'       => 'This Year',
                ];
                $activeFilter = $dateFilter ?? 'all';
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['date_filter' => $key]) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $activeFilter === $key ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Financial Key Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5 font-sans">Qualifying Sales</p>
            <div class="text-2xl font-bold tracking-tight text-slate-900 font-sans">${{ number_format($totalQualifyingSales, 2) }}</div>
            <p class="text-[11px] text-slate-400 mt-1 font-sans">Gross order revenue</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5 font-sans">Taxes & Fees</p>
            <div class="text-2xl font-bold tracking-tight text-slate-700 font-sans">${{ number_format($totalTariffs + $totalFees, 2) }}</div>
            <p class="text-[11px] text-slate-400 mt-1 font-sans">Deducted fees (Base Mfg: ${{ number_format($totalManufacturingCost, 2) }})</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5 font-sans">Net Margin</p>
            <div class="text-2xl font-bold tracking-tight text-slate-900 font-sans">${{ number_format($totalNetMargin, 2) }}</div>
            <p class="text-[11px] text-slate-400 mt-1 font-sans">Commissionable pool</p>
        </div>
        <div class="bg-white border border-amber-200 bg-amber-50/40 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-800 mb-1.5 font-sans">Pending Payout</p>
            <div class="text-2xl font-bold tracking-tight text-amber-600 font-sans">${{ number_format($pendingCommission, 2) }}</div>
            <p class="text-[11px] text-amber-600/80 mt-1 font-sans">In active production</p>
        </div>
        <div class="bg-white border border-blue-200 bg-blue-50/40 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-800 mb-1.5 font-sans">Cleared Payout</p>
            <div class="text-2xl font-bold tracking-tight text-blue-600 font-sans">${{ number_format($clearedCommission, 2) }}</div>
            <p class="text-[11px] text-blue-600/80 mt-1 font-sans">Ready for monthly deposit</p>
        </div>
        <div class="bg-white border border-emerald-200 bg-emerald-50/40 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800 mb-1.5 font-sans">Lifetime Paid</p>
            <div class="text-2xl font-bold tracking-tight text-emerald-600 font-sans">${{ number_format($paidCommission, 2) }}</div>
            <p class="text-[11px] text-emerald-600/80 mt-1 font-sans">Disbursed via QuickBooks</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center justify-between border-b border-slate-200 mb-8 overflow-x-auto gap-4">
        <div class="flex items-center gap-8">
            <button type="button" @click="activeTab = 'clients'" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeTab === 'clients' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-900'">
                My Clients ({{ $clients->count() }})
            </button>
            <button type="button" @click="activeTab = 'stores'" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeTab === 'stores' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-900'">
                Team Stores & Pipeline ({{ $stores->count() }})
            </button>
            <button type="button" @click="activeTab = 'ledger'" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeTab === 'ledger' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-900'">
                Financial Ledger & Statements ({{ $orders->count() }})
            </button>
        </div>

        <div class="pb-3">
            <button type="button" @click="addClientModal = true" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                + Register New Client
            </button>
        </div>
    </div>

    <!-- TAB 1: CLIENTS LIST -->
    <div x-show="activeTab === 'clients'" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Assigned Clients & Organizations</h3>
                <span class="text-xs font-bold text-slate-500">{{ $clients->count() }} Accounts Active</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Organization / School</th>
                            <th class="px-6 py-3.5">Primary Contact</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5 text-center">Stores</th>
                            <th class="px-6 py-3.5 text-center">Orders</th>
                            <th class="px-6 py-3.5">Onboarded</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($clients as $c)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    @php
                                        $primaryStore = $c->teamStores->first();
                                    @endphp
                                    @if($primaryStore)
                                        <a href="{{ route('admin.store.edit', ['store' => $primaryStore, 'return_to' => url()->full()]) }}" class="font-bold text-slate-900 hover:text-primary hover:underline transition-colors inline-block" title="Edit Store: {{ $primaryStore->name }}">
                                            {{ $c->organization }}
                                        </a>
                                    @else
                                        <div class="font-bold text-slate-900">{{ $c->organization }}</div>
                                    @endif
                                    @if($c->is_tax_exempt)
                                        <span class="inline-block mt-0.5 text-[10px] font-black uppercase px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">501(c)(3) Exempt</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.coach.edit', ['user' => $c, 'return_to' => url()->full()]) }}" class="font-medium text-slate-900 hover:text-primary hover:underline transition-colors inline-block" title="Edit Coach">
                                        {{ $c->name }}
                                    </a>
                                    <div class="text-xs text-slate-500">{{ $c->email }} · {{ $c->phone }}</div>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-700">{{ $c->sport }}</td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $c->team_stores_count }}</td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $c->parent_orders_count }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">{{ $c->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <p class="font-bold text-base text-slate-700 mb-1">No clients assigned yet</p>
                                    <p class="text-xs text-slate-400">Share your recruitment link or click "+ Register New Client" above to onboard your first client organization.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: TEAM STORES & PRODUCTION -->
    <div x-show="activeTab === 'stores'" class="space-y-6" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Client Team Stores & Production Pipeline</h3>
                <span class="text-xs font-bold text-slate-500">{{ $stores->count() }} Stores</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Store Name</th>
                            <th class="px-6 py-3.5">Organization / Admin</th>
                            <th class="px-6 py-3.5 text-center">Orders Placed</th>
                            <th class="px-6 py-3.5">Deadline</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Store Productivity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stores as $s)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $storeLogo = $s->logo_url;
                                        @endphp
                                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex-shrink-0 flex items-center justify-center overflow-hidden shadow-2xs">
                                            @if($storeLogo)
                                                <img src="{{ $storeLogo }}" alt="{{ $s->name }}" class="w-full h-full object-contain p-1" loading="lazy">
                                            @else
                                                <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-500 font-black text-xs uppercase tracking-wider select-none">
                                                    {{ strtoupper(substr($s->name, 0, 2)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $s->slug ? route('store.show', $s->slug) : '#' }}" target="_blank" class="font-bold text-slate-900 hover:text-primary transition-colors inline-flex items-center gap-1.5 group">
                                                <span>{{ $s->name }}</span>
                                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-primary transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                            <div class="text-xs text-slate-400">Category: {{ $s->sport ?: ($s->user?->sport ?: 'Athletics') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $s->user?->organization }}</div>
                                    <div class="text-xs text-slate-500">{{ $s->user?->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-black text-slate-900">{{ $s->parent_orders_count }}</td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                    {{ $s->order_deadline ? $s->order_deadline->format('M d, Y') : 'Open Ended' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full border
                                        {{ $s->status === 'approved' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                                        {{ $s->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $s->slug ? route('store.show', $s->slug) : '#' }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded text-xs font-bold uppercase tracking-wider transition-colors shadow-sm">
                                        <span>View Store</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No active stores created by your clients yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: COMMISSION LEDGER & STATEMENTS -->
    <div x-show="activeTab === 'ledger'" class="space-y-6" style="display: none;" x-data="{
        viewMode: 'batches',
        selectedBatchKey: null,
        perPage: 15,
        orderPage: 1,
        batchPage: 1,
        batchItemPages: {},

        get totalOrders() {
            return {{ $orders->count() }};
        },
        get totalOrderPages() {
            return Math.ceil(this.totalOrders / this.perPage) || 1;
        },
        isOrderVisible(idx) {
            const start = (this.orderPage - 1) * this.perPage;
            return idx >= start && idx < (start + this.perPage);
        },
        getOrderPageNumbers() {
            const total = this.totalOrderPages;
            const current = this.orderPage;
            const delta = 2;
            const range = [];
            for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                range.push(i);
            }
            if (current - delta > 2) range.unshift('...');
            if (current + delta < total - 1) range.push('...');
            range.unshift(1);
            if (total > 1) range.push(total);
            return range;
        },

        get totalBatches() {
            return {{ $orderBatches->count() }};
        },
        get totalBatchPages() {
            return Math.ceil(this.totalBatches / this.perPage) || 1;
        },
        isBatchVisible(idx) {
            const start = (this.batchPage - 1) * this.perPage;
            return idx >= start && idx < (start + this.perPage);
        },
        getBatchPageNumbers() {
            const total = this.totalBatchPages;
            const current = this.batchPage;
            const delta = 2;
            const range = [];
            for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                range.push(i);
            }
            if (current - delta > 2) range.unshift('...');
            if (current + delta < total - 1) range.push('...');
            range.unshift(1);
            if (total > 1) range.push(total);
            return range;
        },

        getBatchItemPage(key) {
            return this.batchItemPages[key] || 1;
        },
        setBatchItemPage(key, page) {
            this.batchItemPages[key] = page;
        },
        isBatchItemVisible(key, idx) {
            const page = this.getBatchItemPage(key);
            const start = (page - 1) * this.perPage;
            return idx >= start && idx < (start + this.perPage);
        },
        getBatchItemPageNumbers(key, count) {
            const total = Math.ceil(count / this.perPage) || 1;
            const current = this.getBatchItemPage(key);
            const delta = 2;
            const range = [];
            for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                range.push(i);
            }
            if (current - delta > 2) range.unshift('...');
            if (current + delta < total - 1) range.push('...');
            range.unshift(1);
            if (total > 1) range.push(total);
            return range;
        }
    }">
        {{-- STEP 1: GROUPED ORDER BATCHES / STORES --}}
        <div x-show="viewMode === 'batches' && !selectedBatchKey" class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">
                            Order Batches & Statements ({{ $orderBatches->count() }})
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Orders grouped by Master Store and Direct Order batches. Click any batch to view its itemized breakdown.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm text-xs font-bold">
                            <button type="button" @click="viewMode = 'batches'" class="px-3 py-1 rounded-md transition-colors bg-slate-900 text-white font-black uppercase tracking-wider">
                                Grouped ({{ $orderBatches->count() }})
                            </button>
                            <button type="button" @click="viewMode = 'all_orders'" class="px-3 py-1 rounded-md transition-colors text-slate-600 hover:text-slate-900 uppercase tracking-wider">
                                All ({{ $orders->count() }})
                            </button>
                        </div>
                        <a href="{{ route('rep.ledger.export') }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-black uppercase tracking-wider rounded-lg transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                            Export CSV
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3.5 text-center">Sort</th>
                                <th class="px-5 py-3.5">Store / Event Name</th>
                                <th class="px-5 py-3.5">Coach / Program</th>
                                <th class="px-4 py-3.5 text-center">Orders</th>
                                <th class="px-4 py-3.5">Order Type</th>
                                <th class="px-4 py-3.5">Payment Method</th>
                                <th class="px-4 py-3.5 text-right">Retail Gross</th>
                                <th class="px-4 py-3.5 text-right">Net Margin</th>
                                <th class="px-4 py-3.5 text-right">Your Payout</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orderBatches as $batch)
                                <tr x-show="isBatchVisible({{ $loop->index }})" @click="selectedBatchKey = '{{ $batch->key }}'" class="hover:bg-slate-50 transition-colors cursor-pointer group">
                                    <td class="px-4 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-sans">
                                            <span class="text-slate-300 font-bold text-sm leading-none select-none">≡</span>
                                            <span class="w-7 h-7 rounded-md bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 font-bold text-xs font-sans">
                                                {{ $loop->iteration }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 font-sans">
                                        <div class="font-bold text-slate-900 group-hover:text-primary transition-colors">
                                            {{ $batch->store_name }}
                                        </div>
                                        @if($batch->slug)
                                            <div class="text-[11px] text-slate-400 font-sans">Slug: {{ $batch->slug }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-sans">
                                        <div class="font-bold text-slate-800">{{ $batch->organization }}</div>
                                        <div class="text-xs text-slate-500">{{ $batch->coach_name }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center font-sans">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-800 border border-slate-200 font-sans">
                                            {{ $batch->order_count }} {{ \Illuminate\Support\Str::plural('order', $batch->order_count) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 font-sans">
                                        @if($batch->is_direct)
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded border bg-purple-50 text-purple-700 border-purple-200">
                                                FINALIZED DIRECT ORDERS
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded border bg-blue-50 text-blue-700 border-blue-200">
                                                FINALIZED MASTER ORDERS
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 font-sans">
                                        @if($batch->payment_method === 'Stripe')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                                <span>💳</span> Stripe
                                            </span>
                                        @elseif($batch->payment_method === 'Check')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-800 border border-blue-300">
                                                <span>📑</span> Check
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                                <span>💵</span> Cash
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right font-semibold text-slate-900">
                                        ${{ number_format($batch->gross, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right font-semibold text-slate-900">
                                        ${{ number_format($batch->net_margin, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right font-bold text-emerald-600 text-base">
                                        ${{ number_format($batch->commission, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-full border
                                            {{ $batch->status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($batch->status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                            {{ $batch->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right" @click.stop>
                                        <button type="button" @click="selectedBatchKey = '{{ $batch->key }}'" class="px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded text-xs font-black uppercase tracking-wider transition-all shadow-sm inline-flex items-center gap-1.5 cursor-pointer">
                                            <span>Breakdown</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-12 text-center text-slate-500">
                                        No commissionable orders recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for Grouped Batches --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 font-sans">
                        Showing <span class="font-bold text-slate-900" x-text="totalBatches > 0 ? (batchPage - 1) * perPage + 1 : 0"></span>
                        to <span class="font-bold text-slate-900" x-text="Math.min(batchPage * perPage, totalBatches)"></span>
                        of <span class="font-bold text-slate-900">{{ $orderBatches->count() }}</span> batches
                    </div>
                    <div class="flex items-center gap-1 font-sans" x-show="totalBatchPages > 1">
                        <button type="button" 
                                @click="if(batchPage > 1) { batchPage--; $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
                                :disabled="batchPage === 1"
                                class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-colors"
                                :class="batchPage === 1 ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100 bg-white cursor-pointer shadow-2xs'">
                            &larr; Prev
                        </button>
                        <template x-for="(p, i) in getBatchPageNumbers()" :key="i">
                            <span>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-xs text-slate-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" 
                                            @click="batchPage = p; $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' });"
                                            class="min-w-[32px] h-8 px-2 rounded-lg text-xs font-bold transition-all shadow-2xs cursor-pointer"
                                            :class="batchPage === p ? 'bg-slate-900 text-white font-black' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100'"
                                            x-text="p">
                                    </button>
                                </template>
                            </span>
                        </template>
                        <button type="button" 
                                @click="if(batchPage < totalBatchPages) { batchPage++; $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
                                :disabled="batchPage === totalBatchPages"
                                class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-colors"
                                :class="batchPage === totalBatchPages ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100 bg-white cursor-pointer shadow-2xs'">
                            Next &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2: ITEMIZED BREAKDOWN FOR SELECTED BATCH --}}
        @foreach($orderBatches as $batch)
            <div x-show="viewMode === 'batches' && selectedBatchKey === '{{ $batch->key }}'" class="space-y-6" style="display: none;">
                {{-- Back Navigation --}}
                <div class="flex items-center justify-between">
                    <button type="button" @click="selectedBatchKey = null" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all shadow-md hover:shadow-lg cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span>← Back to Order Batches</span>
                    </button>
                    @if($batch->slug)
                        <a href="{{ route('store.show', $batch->slug) }}" target="_blank" class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1.5">
                            View Storefront ↗
                        </a>
                    @endif
                </div>

                {{-- Batch Summary Card --}}
                <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 shadow-sm">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="text-lg font-black text-slate-900">{{ $batch->store_name }}</h3>
                                @if($batch->is_direct)
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded border bg-purple-50 text-purple-700 border-purple-200">
                                        FINALIZED DIRECT ORDERS
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded border bg-blue-50 text-blue-700 border-blue-200">
                                        FINALIZED MASTER ORDERS
                                    </span>
                                @endif
                                <span class="inline-flex px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-full border
                                    {{ $batch->status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($batch->status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                    {{ $batch->status }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-3 flex-wrap">
                                <span>Client: <strong class="text-slate-700">{{ $batch->coach_name }}</strong> ({{ $batch->organization }})</span>
                                <span>· Payment: <strong class="text-slate-700">{{ $batch->payment_method }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-5 font-sans text-center">
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Orders</span>
                            <span class="text-base font-bold text-slate-900">{{ $batch->order_count }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Retail Gross</span>
                            <span class="text-base font-bold text-slate-900">${{ number_format($batch->gross, 2) }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Mfg Wholesale</span>
                            <span class="text-base font-bold text-slate-700">${{ number_format($batch->base_cost, 2) }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Taxes & Fees</span>
                            <span class="text-base font-bold text-slate-700">${{ number_format($batch->taxes_fees, 2) }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Net Margin</span>
                            <span class="text-base font-black text-slate-900">${{ number_format($batch->net_margin, 2) }}</span>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-200">
                            <span class="block text-[10px] font-black uppercase tracking-wider text-emerald-800 font-sans">Your Payout</span>
                            <span class="text-base font-black text-emerald-700">${{ number_format($batch->commission, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Itemized Table for this Batch --}}
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h4 class="text-sm font-black uppercase tracking-wider text-slate-900">
                            Itemized Orders in Batch ({{ $batch->order_count }})
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5">Order</th>
                                    <th class="px-4 py-3.5">Client / Store</th>
                                    <th class="px-4 py-3.5 text-right">Retail Gross</th>
                                    <th class="px-4 py-3.5 text-right">Mfg Wholesale</th>
                                    <th class="px-4 py-3.5 text-right">Taxes & Fees</th>
                                    <th class="px-4 py-3.5 text-right">Net Margin</th>
                                    <th class="px-4 py-3.5 text-center">Split %</th>
                                    <th class="px-4 py-3.5 text-right">Your Payout</th>
                                    <th class="px-4 py-3.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($batch->orders as $ord)
                                    @php
                                        $gross = (float)($ord->subtotal > 0 ? $ord->subtotal : $ord->total_retail_price);
                                        $deductions = (float)($ord->tariff_amount + $ord->fee_deduction + $ord->fundraising_amount);
                                        $appliedRate = (float)($ord->applied_rep_commission_rate ?? $user->getEffectiveCommissionRate());
                                    @endphp
                                    <tr x-show="isBatchItemVisible('{{ $batch->key }}', {{ $loop->index }})" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-4 font-sans font-bold text-xs text-slate-900">
                                            #{{ $ord->id }}
                                            <div class="text-[10px] text-slate-400 font-sans">{{ $ord->created_at->format('M d, Y') }}</div>
                                            @if($ord->batch_id)
                                                <div class="text-[9px] text-slate-400 font-sans truncate max-w-[120px]">Batch: {{ substr($ord->batch_id, 0, 8) }}...</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="font-bold text-slate-800 text-xs">{{ $ord->user?->organization ?? $ord->user?->name ?? '—' }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $ord->teamStore?->name ?? 'Direct Order' }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-right font-semibold text-slate-900">${{ number_format($gross, 2) }}</td>
                                        <td class="px-4 py-4 text-right text-slate-600">-${{ number_format((float)$ord->manufacturing_cost, 2) }}</td>
                                        <td class="px-4 py-4 text-right text-slate-500">-${{ number_format($deductions, 2) }}</td>
                                        <td class="px-4 py-4 text-right font-semibold text-slate-900">${{ number_format((float)$ord->net_margin, 2) }}</td>
                                        <td class="px-4 py-4 text-center font-bold text-primary text-xs">{{ number_format($appliedRate, 1) }}%</td>
                                        <td class="px-4 py-4 text-right font-bold text-emerald-600 text-base">${{ number_format((float)$ord->rep_commission_earned, 2) }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full border
                                                {{ $ord->commission_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($ord->commission_status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                                {{ $ord->commission_status ?? 'pending' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination for Orders inside this Batch --}}
                    @if($batch->order_count > 15)
                        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="text-xs text-slate-500 font-sans">
                                Showing <span class="font-bold text-slate-900" x-text="(getBatchItemPage('{{ $batch->key }}') - 1) * perPage + 1"></span>
                                to <span class="font-bold text-slate-900" x-text="Math.min(getBatchItemPage('{{ $batch->key }}') * perPage, {{ $batch->order_count }})"></span>
                                of <span class="font-bold text-slate-900">{{ $batch->order_count }}</span> orders in batch
                            </div>
                            <div class="flex items-center gap-1 font-sans">
                                <button type="button" 
                                        @click="if(getBatchItemPage('{{ $batch->key }}') > 1) { setBatchItemPage('{{ $batch->key }}', getBatchItemPage('{{ $batch->key }}') - 1); $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
                                        :disabled="getBatchItemPage('{{ $batch->key }}') === 1"
                                        class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-colors"
                                        :class="getBatchItemPage('{{ $batch->key }}') === 1 ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100 bg-white cursor-pointer shadow-2xs'">
                                    &larr; Prev
                                </button>
                                <template x-for="(p, i) in getBatchItemPageNumbers('{{ $batch->key }}', {{ $batch->order_count }})" :key="i">
                                    <span>
                                        <template x-if="p === '...'">
                                            <span class="px-2 py-1 text-xs text-slate-400 select-none">...</span>
                                        </template>
                                        <template x-if="p !== '...'">
                                            <button type="button" 
                                                    @click="setBatchItemPage('{{ $batch->key }}', p); $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' });"
                                                    class="min-w-[32px] h-8 px-2 rounded-lg text-xs font-bold transition-all shadow-2xs cursor-pointer"
                                                    :class="getBatchItemPage('{{ $batch->key }}') === p ? 'bg-slate-900 text-white font-black' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100'"
                                                    x-text="p">
                                            </button>
                                        </template>
                                    </span>
                                </template>
                                <button type="button" 
                                        @click="if(getBatchItemPage('{{ $batch->key }}') < Math.ceil({{ $batch->order_count }} / perPage)) { setBatchItemPage('{{ $batch->key }}', getBatchItemPage('{{ $batch->key }}') + 1); $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
                                        :disabled="getBatchItemPage('{{ $batch->key }}') >= Math.ceil({{ $batch->order_count }} / perPage)"
                                        class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-colors"
                                        :class="getBatchItemPage('{{ $batch->key }}') >= Math.ceil({{ $batch->order_count }} / perPage) ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100 bg-white cursor-pointer shadow-2xs'">
                                    Next &rarr;
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- OPTIONAL ALL ORDERS FLAT TABLE --}}
        <div x-show="viewMode === 'all_orders'" class="space-y-6" style="display: none;">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">All Individual Orders ({{ $orders->count() }})</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Order-by-order calculation reflecting manufacturing, tariffs, fees, and your assigned rate.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm text-xs font-bold">
                            <button type="button" @click="viewMode = 'batches'" class="px-3 py-1 rounded-md transition-colors text-slate-600 hover:text-slate-900 uppercase tracking-wider">
                                Grouped ({{ $orderBatches->count() }})
                            </button>
                            <button type="button" @click="viewMode = 'all_orders'" class="px-3 py-1 rounded-md transition-colors bg-slate-900 text-white font-black uppercase tracking-wider">
                                All ({{ $orders->count() }})
                            </button>
                        </div>
                        <a href="{{ route('rep.ledger.export') }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-black uppercase tracking-wider rounded-lg transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                            Export CSV
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3.5">Order</th>
                                <th class="px-4 py-3.5">Client / Store</th>
                                <th class="px-4 py-3.5 text-right">Retail Gross</th>
                                <th class="px-4 py-3.5 text-right">Mfg Wholesale</th>
                                <th class="px-4 py-3.5 text-right">Taxes & Fees</th>
                                <th class="px-4 py-3.5 text-right">Net Margin</th>
                                <th class="px-4 py-3.5 text-center">Split %</th>
                                <th class="px-4 py-3.5 text-right">Your Payout</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orders as $ord)
                                @php
                                    $gross = (float)($ord->subtotal > 0 ? $ord->subtotal : $ord->total_retail_price);
                                    $deductions = (float)($ord->tariff_amount + $ord->fee_deduction + $ord->fundraising_amount);
                                    $appliedRate = (float)($ord->applied_rep_commission_rate ?? $user->getEffectiveCommissionRate());
                                @endphp
                                <tr x-show="isOrderVisible({{ $loop->index }})" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-4 font-sans font-bold text-xs text-slate-900">
                                        #{{ $ord->id }}
                                        <div class="text-[10px] text-slate-400">{{ $ord->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-slate-800 text-xs">{{ $ord->user?->organization ?? $ord->user?->name ?? '—' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $ord->teamStore?->name ?? 'Direct Order Batch' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-semibold text-slate-900">${{ number_format($gross, 2) }}</td>
                                    <td class="px-4 py-4 text-right text-slate-600">-${{ number_format((float)$ord->manufacturing_cost, 2) }}</td>
                                    <td class="px-4 py-4 text-right text-slate-500">-${{ number_format($deductions, 2) }}</td>
                                    <td class="px-4 py-4 text-right font-semibold text-slate-900">${{ number_format((float)$ord->net_margin, 2) }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-primary text-xs">{{ number_format($appliedRate, 1) }}%</td>
                                    <td class="px-4 py-4 text-right font-bold text-emerald-600 text-base">${{ number_format((float)$ord->rep_commission_earned, 2) }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full border
                                            {{ $ord->commission_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($ord->commission_status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                            {{ $ord->commission_status ?? 'pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-slate-500">
                                        No commissionable orders recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for All Individual Orders --}}
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 font-sans">
                        Showing <span class="font-bold text-slate-900" x-text="totalOrders > 0 ? (orderPage - 1) * perPage + 1 : 0"></span>
                        to <span class="font-bold text-slate-900" x-text="Math.min(orderPage * perPage, totalOrders)"></span>
                        of <span class="font-bold text-slate-900">{{ $orders->count() }}</span> orders
                    </div>
                    <div class="flex items-center gap-1 font-sans" x-show="totalOrderPages > 1">
                        <button type="button" 
                                @click="if(orderPage > 1) { orderPage--; $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
                                :disabled="orderPage === 1"
                                class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-colors"
                                :class="orderPage === 1 ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100 bg-white cursor-pointer shadow-2xs'">
                            &larr; Prev
                        </button>
                        <template x-for="(p, i) in getOrderPageNumbers()" :key="i">
                            <span>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-xs text-slate-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" 
                                            @click="orderPage = p; $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' });"
                                            class="min-w-[32px] h-8 px-2 rounded-lg text-xs font-bold transition-all shadow-2xs cursor-pointer"
                                            :class="orderPage === p ? 'bg-slate-900 text-white font-black' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100'"
                                            x-text="p">
                                    </button>
                                </template>
                            </span>
                        </template>
                        <button type="button" 
                                @click="if(orderPage < totalOrderPages) { orderPage++; $el.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
                                :disabled="orderPage === totalOrderPages"
                                class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-colors"
                                :class="orderPage === totalOrderPages ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100 bg-white cursor-pointer shadow-2xs'">
                            Next &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD CLIENT -->
    <div x-show="addClientModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-cloak>
        <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black uppercase tracking-tight text-slate-900">Register New Athletic Client</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Locks this coach and program directly to your sales account.</p>
                </div>
                <button type="button" @click="addClientModal = false" class="text-slate-400 hover:text-slate-700 text-2xl font-black">&times;</button>
            </div>
            <form action="{{ route('rep.client.create') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Coach First Name *</label>
                        <input type="text" name="first_name" required class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Coach Last Name *</label>
                        <input type="text" name="last_name" required class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">School / Organization Name *</label>
                    <input type="text" name="organization" required placeholder="e.g. Westlake High School" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Sport / Program *</label>
                        <select name="sport" required class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            <option value="">-- Select Sport / Program --</option>
                            @foreach(config('sports.categories') as $cat)
                                <option value="{{ $cat }}" {{ old('sport') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Phone *</label>
                        <input type="text" name="phone" required placeholder="e.g. (555) 000-0000" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Coach Email (Login Username) *</label>
                    <input type="email" name="email" required placeholder="coach@school.edu" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Initial Password *</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimum 8 characters" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Team Logo (Optional)</label>
                    <input type="file" name="logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="addClientModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-bold uppercase hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-xs font-black uppercase tracking-wider shadow-sm">Register Client</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
