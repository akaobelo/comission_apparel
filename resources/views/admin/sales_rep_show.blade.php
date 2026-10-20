@extends('layouts.app')
@section('title', 'Sales Partner Profile: ' . $user->name . ' | Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 pt-32 lg:pt-40" x-data="{
    activeTab: 'overview',
    copied: false,
    copyLink() {
        navigator.clipboard.writeText('{{ $referralUrl }}');
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    }
}">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.dashboard', ['tab' => 'sales_reps']) }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Sales Partners
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold text-xs uppercase tracking-wider">Partner Profile</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Profile Header Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-8 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-2xl tracking-tight shadow-md flex-shrink-0">
                    {{ strtoupper(substr($user->first_name ?: 'S', 0, 1) . substr($user->last_name ?: 'P', 0, 1)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h1 class="text-2xl font-black uppercase tracking-tight text-slate-900">{{ $user->name }}</h1>
                        <span class="inline-flex px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full border
                            {{ $user->partner_category === 'established_business' ? 'bg-purple-50 text-purple-800 border-purple-200' : ($user->partner_category === 'authorized_business' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                            {{ $user->partner_category ? str_replace('_', ' ', $user->partner_category) : 'Sales Partner' }}
                        </span>
                        <span class="inline-flex px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full border {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200' }}">
                            {{ $user->status }}
                        </span>
                        <span class="inline-flex px-2.5 py-0.5 text-[10px] font-mono font-black uppercase tracking-wider rounded-full bg-slate-100 text-slate-800 border border-slate-200">
                            Tier: {{ number_format($user->getEffectiveCommissionRate(), 1) }}% Net Split
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-medium">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $user->email }}
                        </span>
                        @if($user->phone)
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $user->phone }}
                            </span>
                        @endif
                        <span>Partner Since {{ $user->created_at?->format('M d, Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Referral Link Widget -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 lg:min-w-[360px]">
                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">
                    Partner Coach Recruitment Link
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $referralUrl }}" class="w-full text-xs bg-white border border-slate-200 rounded px-3 py-1.5 text-slate-700 font-mono select-all focus:outline-none">
                    <button type="button" @click="copyLink()" class="px-3.5 py-1.5 bg-slate-900 text-white rounded text-xs font-bold uppercase tracking-wider hover:bg-slate-800 transition-all flex items-center gap-1 whitespace-nowrap shadow-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-text="copied ? 'Copied' : 'Copy'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Qualifying Sales</span>
            <span class="block text-xl font-black text-slate-900 font-mono">${{ number_format($totalQualifyingSales, 2) }}</span>
            <span class="text-[10px] text-slate-400">Gross order revenue</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Base & Tariffs</span>
            <span class="block text-xl font-black text-slate-900 font-mono">${{ number_format($totalManufacturingCost + $totalTariffs + $totalFees, 2) }}</span>
            <span class="text-[10px] text-slate-400">Deducted base costs</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Net Margin</span>
            <span class="block text-xl font-black text-slate-900 font-mono">${{ number_format($totalNetMargin, 2) }}</span>
            <span class="text-[10px] text-slate-400">Commissionable pool</span>
        </div>

        <div class="bg-white border border-amber-200/80 rounded-xl p-4 shadow-sm bg-gradient-to-br from-white to-amber-50/30">
            <span class="block text-[10px] font-black uppercase tracking-wider text-amber-700 mb-1">Pending Payout</span>
            <span class="block text-xl font-black text-amber-600 font-mono">${{ number_format($pendingCommission, 2) }}</span>
            <span class="text-[10px] text-amber-600/70">In active production</span>
        </div>

        <div class="bg-white border border-blue-200/80 rounded-xl p-4 shadow-sm bg-gradient-to-br from-white to-blue-50/30">
            <span class="block text-[10px] font-black uppercase tracking-wider text-blue-700 mb-1">Cleared Payout</span>
            <span class="block text-xl font-black text-blue-600 font-mono">${{ number_format($clearedCommission, 2) }}</span>
            <span class="text-[10px] text-blue-600/70">Ready for deposit</span>
        </div>

        <div class="bg-white border border-emerald-200/80 rounded-xl p-4 shadow-sm bg-gradient-to-br from-white to-emerald-50/30">
            <span class="block text-[10px] font-black uppercase tracking-wider text-emerald-700 mb-1">Lifetime Paid</span>
            <span class="block text-xl font-black text-emerald-600 font-mono">${{ number_format($paidCommission, 2) }}</span>
            <span class="text-[10px] text-emerald-600/70">Disbursed via QuickBooks</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-8 flex items-center gap-6 overflow-x-auto">
        <button type="button" @click="activeTab = 'overview'" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Partner Settings & Profile
        </button>
        <button type="button" @click="activeTab = 'clients'" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'clients' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Assigned Coaches & Clients ({{ $clients->count() }})
        </button>
        <button type="button" @click="activeTab = 'stores'" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'stores' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Team Stores & Pipeline ({{ $stores->count() }})
        </button>
        <button type="button" @click="activeTab = 'orders'" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'orders' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Commission Statements & Orders ({{ $orders->count() }})
        </button>
    </div>

    <!-- TAB 1: OVERVIEW & PROFILE SETTINGS -->
    <div x-show="activeTab === 'overview'" class="max-w-4xl space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Edit Partner Agreement & Account</h3>
                <p class="text-xs text-slate-500 mt-0.5">Configure commission splits, referral codes, and partner tiers.</p>
            </div>
            <form action="{{ route('admin.sales-rep.update', $user) }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email Address (Login Username) *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. (813) 000-0000" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Agreement Category Tier *</label>
                        <select name="partner_category" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            <option value="independent_sales" {{ old('partner_category', $user->partner_category) === 'independent_sales' ? 'selected' : '' }}>Independent Sales (30%)</option>
                            <option value="authorized_business" {{ old('partner_category', $user->partner_category) === 'authorized_business' ? 'selected' : '' }}>Authorized Business (40%)</option>
                            <option value="established_business" {{ old('partner_category', $user->partner_category) === 'established_business' ? 'selected' : '' }}>Approved Established (50%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Commission Split Rate (%) *</label>
                        <input type="number" step="0.1" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $user->commission_rate ?? 50.00) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-mono font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Account Status *</label>
                        <select name="status" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="declined" {{ old('status', $user->status) === 'declined' ? 'selected' : '' }}>Deactivated / Declined</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Personal Referral Slug</label>
                        <input type="text" name="referral_slug" value="{{ old('referral_slug', $user->referral_slug) }}" placeholder="e.g. arven" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        <span class="text-[11px] text-slate-400 mt-1 block">Used for commissionapparel.com/register?rep=[slug]</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Reset Password (Leave blank to keep current)</label>
                        <input type="password" name="password" minlength="8" placeholder="Minimum 8 characters" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                        Save Partner Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: ASSIGNED COACHES & CLIENTS -->
    <div x-show="activeTab === 'clients'" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Athletic Programs & Coaches ({{ $clients->count() }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">All clients linked directly to this representative's book of business.</p>
                </div>
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
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($clients as $client)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $client->organization }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $client->name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $client->email }} @if($client->phone) · {{ $client->phone }} @endif</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full bg-slate-100 text-slate-700">
                                        {{ $client->sport ?: 'Athletics' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $client->team_stores_count }}</td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $client->parent_orders_count }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">{{ $client->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.coach.edit', $client) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors">
                                        Manage Coach →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    No coaches or athletic programs linked to this partner yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: TEAM STORES & PIPELINE -->
    <div x-show="activeTab === 'stores'" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Partner Team Stores ({{ $stores->count() }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Team apparel stores and direct bulk batches managed by this partner.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Store / Event</th>
                            <th class="px-6 py-3.5">School / Coach</th>
                            <th class="px-6 py-3.5">Sport</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-center">Orders</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stores as $st)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $st->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">Deadline: {{ $st->order_deadline ? \Carbon\Carbon::parse($st->order_deadline)->format('M d, Y') : 'Rolling' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $st->user?->organization ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $st->user?->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-700">{{ $st->sport }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full border
                                        {{ $st->status === 'approved' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($st->status === 'pending' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                                        {{ $st->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-slate-900">{{ $st->parent_orders_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.store.edit', $st) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors">
                                            Manage Store →
                                        </a>
                                        @if($st->slug)
                                            <a href="{{ route('store.show', $st->slug) }}" target="_blank" class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-600 rounded text-xs font-bold uppercase tracking-wider border border-slate-200 transition-colors" title="View Public Store">
                                                ↗
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No team stores launched under this partner yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: COMMISSION STATEMENTS & ORDERS -->
    <div x-show="activeTab === 'orders'" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Commission Ledger & Statements ({{ $orders->count() }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Itemized breakdown showing sales gross, manufacturing deductions, net margin, and partner payout.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Order / Batch</th>
                            <th class="px-5 py-3.5">Client / Store</th>
                            <th class="px-5 py-3.5 text-right">Gross Sales</th>
                            <th class="px-5 py-3.5 text-right">Base Cost</th>
                            <th class="px-5 py-3.5 text-right">Tariffs & Fees</th>
                            <th class="px-5 py-3.5 text-right">Net Margin</th>
                            <th class="px-5 py-3.5 text-center">Split %</th>
                            <th class="px-5 py-3.5 text-right">Commission</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $ord)
                            @php
                                $gross = (float)($ord->subtotal > 0 ? $ord->subtotal : $ord->total_retail_price);
                                $base = (float)$ord->manufacturing_cost;
                                $tariffsFees = (float)($ord->tariff_amount + $ord->fee_deduction);
                                $margin = (float)$ord->net_margin;
                                $rate = (float)($ord->applied_rep_commission_rate ?? $user->getEffectiveCommissionRate());
                                $earned = (float)$ord->rep_commission_earned;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors font-mono text-xs">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900">#{{ $ord->id }}</div>
                                    <div class="text-[10px] text-slate-400 font-sans">{{ $ord->created_at?->format('M d, Y') ?? '—' }}</div>
                                    @if($ord->batch_id)
                                        <div class="text-[9px] text-slate-400 font-mono truncate max-w-[120px]">Batch: {{ substr($ord->batch_id, 0, 8) }}...</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 font-sans">
                                    <div class="font-bold text-slate-900">{{ $ord->teamStore?->name ?? $ord->user?->organization ?? 'Direct Order' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $ord->user?->name ?? 'Coach' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-slate-900">${{ number_format($gross, 2) }}</td>
                                <td class="px-5 py-3.5 text-right text-slate-600">${{ number_format($base, 2) }}</td>
                                <td class="px-5 py-3.5 text-right text-slate-600">${{ number_format($tariffsFees, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-bold text-slate-900">${{ number_format($margin, 2) }}</td>
                                <td class="px-5 py-3.5 text-center font-bold text-slate-700">{{ number_format($rate, 1) }}%</td>
                                <td class="px-5 py-3.5 text-right font-black text-emerald-600 text-sm">${{ number_format($earned, 2) }}</td>
                                <td class="px-5 py-3.5 text-center font-sans">
                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-full border
                                        {{ $ord->commission_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($ord->commission_status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                        {{ $ord->commission_status ?: 'pending' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-sans">
                                    @if($ord->isDirectOrder() && $ord->batch_id)
                                        <a href="{{ route('admin.direct-batch.show', $ord->batch_id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-[11px] font-bold uppercase tracking-wider border border-slate-300 transition-colors">
                                            Edit Batch →
                                        </a>
                                    @else
                                        <a href="{{ route('admin.order.edit', $ord) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-[11px] font-bold uppercase tracking-wider border border-slate-300 transition-colors">
                                            Edit Tariffs →
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-12 text-center text-slate-500 font-sans">
                                    No qualifying orders recorded for this sales partner yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
