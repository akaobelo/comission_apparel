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
                        <input type="text" readonly value="{{ $referralUrl }}" class="w-full text-xs bg-white border border-slate-200 rounded px-3 py-2 text-slate-700 font-mono focus:outline-none select-all">
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

    <!-- Financial Key Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Qualifying Sales</p>
            <div class="text-2xl font-black text-slate-900">${{ number_format($totalQualifyingSales, 2) }}</div>
            <p class="text-[10px] text-slate-400 mt-1">Gross order revenue</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Taxes & Fees</p>
            <div class="text-2xl font-black text-slate-700">${{ number_format($totalManufacturingCost + $totalTariffs + $totalFees, 2) }}</div>
            <p class="text-[10px] text-slate-400 mt-1">Deducted costs & fees</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Net Margin</p>
            <div class="text-2xl font-black text-slate-900">${{ number_format($totalNetMargin, 2) }}</div>
            <p class="text-[10px] text-slate-400 mt-1">Commissionable pool</p>
        </div>
        <div class="bg-white border border-amber-200 bg-amber-50/40 rounded-xl p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800 mb-1">Pending Payout</p>
            <div class="text-2xl font-black text-amber-900">${{ number_format($pendingCommission, 2) }}</div>
            <p class="text-[10px] text-amber-700 mt-1">In active production</p>
        </div>
        <div class="bg-white border border-blue-200 bg-blue-50/40 rounded-xl p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-800 mb-1">Cleared Payout</p>
            <div class="text-2xl font-black text-blue-900">${{ number_format($clearedCommission, 2) }}</div>
            <p class="text-[10px] text-blue-700 mt-1">Ready for monthly deposit</p>
        </div>
        <div class="bg-white border border-emerald-200 bg-emerald-50/40 rounded-xl p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 mb-1">Lifetime Paid</p>
            <div class="text-2xl font-black text-emerald-900">${{ number_format($paidCommission, 2) }}</div>
            <p class="text-[10px] text-emerald-700 mt-1">Disbursed via QuickBooks</p>
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
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Assigned Athletic Programs & Coaches</h3>
                <span class="text-xs font-bold text-slate-500">{{ $clients->count() }} Accounts Active</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Organization / School</th>
                            <th class="px-6 py-3.5">Primary Coach Contact</th>
                            <th class="px-6 py-3.5">Sport</th>
                            <th class="px-6 py-3.5 text-center">Stores</th>
                            <th class="px-6 py-3.5 text-center">Orders</th>
                            <th class="px-6 py-3.5">Onboarded</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($clients as $c)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $c->organization }}</div>
                                    @if($c->is_tax_exempt)
                                        <span class="inline-block mt-0.5 text-[10px] font-black uppercase px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">501(c)(3) Exempt</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ $c->name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $c->email }} · {{ $c->phone }}</div>
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
                                    <p class="text-xs text-slate-400">Share your recruitment link or click "+ Register New Client" above to onboard your first athletic program.</p>
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
                            <th class="px-6 py-3.5">Coach / Program</th>
                            <th class="px-6 py-3.5 text-center">Orders Placed</th>
                            <th class="px-6 py-3.5">Deadline</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Production Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stores as $s)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="/store/{{ $s->slug }}" target="_blank" class="font-bold text-slate-900 hover:text-primary transition-colors flex items-center gap-1.5">
                                        {{ $s->name }}
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                    <div class="text-xs text-slate-400">Sport: {{ $s->sport }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $s->user?->organization }}</div>
                                    <div class="text-xs text-slate-500">{{ $s->user?->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-black text-slate-900">{{ $s->parent_orders_count }}</td>
                                <td class="px-6 py-4 text-xs font-mono">
                                    {{ $s->order_deadline ? $s->order_deadline->format('M d, Y') : 'Open Ended' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full border
                                        {{ $s->status === 'approved' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                                        {{ $s->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('rep.store.export', $s) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded text-xs font-bold uppercase tracking-wider transition-colors shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Master CSV
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
    <div x-show="activeTab === 'ledger'" class="space-y-6" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Itemized Commission Ledger</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Order-by-order calculation reflecting manufacturing, tariffs, fees, and your assigned rate.</p>
                </div>
                <a href="{{ route('rep.ledger.export') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-black uppercase tracking-wider rounded-lg transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Download Statement (CSV)
                </a>
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
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-4 font-mono text-xs text-slate-900">
                                    #{{ $ord->id }}
                                    <div class="text-[10px] text-slate-400">{{ $ord->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-800 text-xs">{{ $ord->user?->organization ?? $ord->user?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $ord->teamStore?->name ?? 'Direct Order Batch' }}</div>
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-bold text-slate-900">${{ number_format($gross, 2) }}</td>
                                <td class="px-4 py-4 text-right font-mono text-slate-600">-${{ number_format((float)$ord->manufacturing_cost, 2) }}</td>
                                <td class="px-4 py-4 text-right font-mono text-slate-500">-${{ number_format($deductions, 2) }}</td>
                                <td class="px-4 py-4 text-right font-mono font-bold text-slate-900">${{ number_format((float)$ord->net_margin, 2) }}</td>
                                <td class="px-4 py-4 text-center font-mono font-bold text-primary text-xs">{{ number_format($appliedRate, 1) }}%</td>
                                <td class="px-4 py-4 text-right font-mono font-black text-emerald-600 text-base">${{ number_format((float)$ord->rep_commission_earned, 2) }}</td>
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
