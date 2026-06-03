@extends('layouts.app')
@section('title', 'Admin Portal | The Commission Apparel')
@section('content')
            @php
                $typeOptions = [
                    'accessory' => 'Accessories',
                    'arm_sleeve' => 'Arm Sleeves',
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
                    'uniform_set_2' => 'Uniform Set #2 (top/bottom)',
                    'warmup_top' => 'Warm-up (top)',
                    'warmup_bottom' => 'Warm-up (bottom)',
                    'warmup_set' => 'Warm-up (top/bottom)',
                    'warmup_set_2' => 'Warm-up Set #2 (top/bottom)',
                    'uniform_package_gold' => 'Uniform Package (Gold)',
                    'uniform_package_silver' => 'Uniform Package (Silver)',
                    'uniform_package_bronze' => 'Uniform Package (Bronze)',
                    'uniform_package_custom' => 'Uniform Package (Custom)',
                ];
            @endphp
<div class="max-w-[1600px] mx-auto px-6 pb-8" style="padding-top: clamp(2rem, 10vw, 7rem);">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-bold flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex flex-col gap-2">
            <div class="flex items-center gap-3 font-bold">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Please correct the following errors:</span>
            </div>
            <ul class="list-disc list-inside ml-8 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-red-100 border border-red-200 text-red-700 text-xs font-bold uppercase tracking-widest rounded-full mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> Super Admin
            </div>
            <h1 class="text-4xl font-black uppercase tracking-tight text-slate-900">Admin <span class="text-secondary">Control Center</span></h1>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline py-2 px-4 text-xs uppercase tracking-wider bg-secondary text-white">Log Out</button>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid gap-4 mb-10" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-primary">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Total Coaches</p>
            <div class="text-3xl font-black text-slate-900">{{ $coaches->total() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-orange-400">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pending Stores</p>
            <div class="text-3xl font-black text-orange-500">{{ $pendingStores->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-green-500">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Active Stores</p>
            <div class="text-3xl font-black text-green-600">{{ $productionStores->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-slate-400">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Finalized Orders</p>
            <div class="text-3xl font-black text-slate-900">{{ $finalizedStoreBatches->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-purple-500">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Finalized Direct Orders</p>
            <div class="text-3xl font-black text-purple-600">{{ $finalizedDirectOrderBatches->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-blue-500">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Quote Inquiries</p>
            <div class="text-3xl font-black text-blue-600">{{ $newQuoteRequestsCount }}</div>
        </div>
    </div>

    <div x-data="{
        activeAdminTab: 'stores',
        init() {
            const savedTab = localStorage.getItem('adminActiveTab');
            if (savedTab) {
                this.activeAdminTab = savedTab;
            }
        },
        setTab(tab) {
            this.activeAdminTab = tab;
            localStorage.setItem('adminActiveTab', tab);
        }
    }">
        {{-- Admin Navigation Tabs --}}
        <div class="flex overflow-x-auto pb-0 mb-8 border-b border-slate-200 gap-8 -mx-6 px-6 md:mx-0 md:px-0 scrollbar-hide">
            <button @click="setTab('stores')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'stores' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Stores & Orders</button>
            <button @click="setTab('coaches')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'coaches' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Coaches</button>
            <button @click="setTab('catalog')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'catalog' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Design Catalog</button>
            <button @click="setTab('landing')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'landing' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Landing Page Settings</button>
            <button @click="setTab('testimonials')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'testimonials' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Testimonials</button>
            <button @click="setTab('sizing_charts')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'sizing_charts' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Sizing Charts</button>
            <button @click="setTab('campaigns')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'campaigns' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Campaign Stores</button>
            <button @click="setTab('security')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'security' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Security Logs</button>
        </div>

        {{-- ═══ STORES & ORDERS TAB ═══ --}}
        <div x-show="activeAdminTab === 'stores'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                {{-- ═══ PENDING STORE APPROVALS ═══ --}}
            @if($pendingStores->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-orange-50 flex items-center justify-between">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pending Store Approvals
                        <span class="ml-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-500 text-white text-[10px] font-black">{{ $pendingStores->count() }}</span>
                    </h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($pendingStores as $store)
                    <div class="p-5 flex flex-col md:flex-row md:items-center gap-4 justify-between">
                        <div>
                            <div class="font-bold text-slate-900 uppercase">{{ $store->name }}</div>
                            <div class="text-sm text-slate-500 mt-0.5">
                                Coach: <span class="font-semibold text-slate-700">{{ $store->user->name }}</span> —
                                {{ $store->user->organization }} — Package: <span class="font-bold text-primary uppercase text-xs">{{ str_replace('_', ' ', $store->package_type ?? 'N/A') }}</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1">Requested {{ $store->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <form action="{{ route('admin.stores.approve', $store) }}" method="POST">
                                @csrf
                                <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase tracking-wide rounded-lg transition-colors">Approve</button>
                            </form>
                            <form action="{{ route('admin.stores.decline', $store) }}" method="POST">
                                @csrf
                                <button class="px-4 py-2 bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-bold uppercase tracking-wide rounded-lg transition-colors">Decline</button>
                            </form>
                            <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wide rounded-lg transition-colors">Edit</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

                <div x-data="{ expanded: false, init() { const k = 'admin_quote_inquiries'; this.expanded = localStorage.getItem(k) === 'true'; $watch('expanded', v => localStorage.setItem(k, v)) } }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div @click="expanded = !expanded" class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer hover:bg-slate-100 transition-colors">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Quote Inquiries</h2>
                            <p class="text-sm text-slate-500 mt-1">Public quote requests submitted from the website.</p>
                        </div>
                        <div class="flex items-center gap-4 text-slate-400">
                            <span class="text-sm font-bold">{{ $quoteRequestsTotal }} Inquiries</span>
                            <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-cloak>
                        <div class="p-4 border-b border-slate-100 bg-white">
                            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex gap-2 max-w-md">
                                <input type="text" name="quote_search" value="{{ request('quote_search') }}" placeholder="Search inquiries..." class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-black transition-colors">Search</button>
                                @if(request('quote_search'))
                                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors">Clear</a>
                                @endif
                            </form>
                        </div>
                        @if($quoteRequests->isEmpty())
                            <div class="p-8 text-center text-slate-400 text-sm">No quote inquiries found.</div>
                    @else
                        <div class="divide-y divide-slate-100 max-h-[32rem] overflow-y-auto">
                            @foreach($quoteRequests as $quoteRequest)
                                <div class="p-5">
                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                        <div>
                                            <div class="font-bold text-slate-900">
                                                {{ $quoteRequest->organization_name }}
                                                <span class="text-slate-500 font-medium">· {{ $quoteRequest->apparel_category }}</span>
                                            </div>
                                            <div class="text-sm text-slate-500 mt-1">
                                                {{ $quoteRequest->first_name }} {{ $quoteRequest->last_name }} · {{ $quoteRequest->position_title }}
                                            </div>
                                            <div class="text-sm text-slate-500">
                                                {{ $quoteRequest->email }} · {{ $quoteRequest->phone }}
                                            </div>
                                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Estimated Qty</div>
                                                    <div class="text-sm font-black text-slate-900">{{ $quoteRequest->estimated_quantity }}</div>
                                                </div>
                                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Package</div>
                                                    <div class="text-sm font-black text-primary uppercase">{{ str_replace('_', ' ', $quoteRequest->package_type) }}</div>
                                                </div>
                                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Target Date</div>
                                                    <div class="text-sm font-black text-slate-900">{{ $quoteRequest->target_delivery_date?->format('M d, Y') ?? 'Not provided' }}</div>
                                                </div>
                                            </div>
                                            @if($quoteRequest->design_vision)
                                                <p class="text-sm text-slate-600 mt-3">{{ $quoteRequest->design_vision }}</p>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400 flex-shrink-0 flex flex-col items-end gap-2">
                                            <span>{{ $quoteRequest->created_at->diffForHumans() }}</span>
                                            @if($quoteRequest->status === 'new' || !$quoteRequest->status)
                                            <form action="{{ route('admin.quote.mark-addressed', $quoteRequest) }}" method="POST" onsubmit="return confirm('Mark this quote as addressed?')">
                                                @csrf
                                                <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border border-green-200 rounded-md text-[10px] font-bold uppercase tracking-wider transition-colors shadow-sm mt-2">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Mark Addressed
                                                </button>
                                            </form>
                                            @else
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 text-slate-500 border border-slate-200 rounded-md text-[10px] font-bold uppercase tracking-wider mt-2 opacity-75 cursor-default">
                                                <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                Addressed
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($quoteRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $quoteRequests->hasPages())
                        <div class="p-4 border-t border-slate-100 bg-white">
                            {{ $quoteRequests->links() }}
                        </div>
                        @endif
                        @endif
                    </div>
                </div>

            </div>
            <div class="space-y-8">
                {{-- ═══ ACTIVE STORES IN PRODUCTION ═══ --}}
                <div x-data="{ expanded: false, init() { const k = 'admin_active_team_stores'; this.expanded = localStorage.getItem(k) === 'true'; $watch('expanded', v => localStorage.setItem(k, v)) } }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div @click="expanded = !expanded" class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer hover:bg-slate-100 transition-colors">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Active Team Stores</h2>
                        </div>
                        <div class="flex items-center gap-4 text-slate-400">
                            <span class="text-sm font-bold">{{ $productionStores->count() }} Stores</span>
                            <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse>
                        @if($productionStores->isEmpty())
                            <div class="p-8 text-center text-slate-400 text-sm">No active stores.</div>
                        @else
                            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                                @foreach($productionStores as $store)
                                <div class="p-4 flex items-center justify-between gap-4">
                                    <div>
                                        <div class="font-bold text-sm text-slate-900">{{ $store->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $store->user->name }} · {{ $store->parentOrders->count() }} orders</div>
                                        @if($store->order_deadline)
                                            <div class="text-[10px] font-bold text-{{ $store->order_deadline->isPast() ? 'red' : 'slate' }}-500 mt-0.5 uppercase tracking-wide">
                                                Deadline: {{ $store->order_deadline->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <form action="{{ route('admin.stores.archive', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this active store?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-500 text-xs font-bold rounded-lg hover:bg-slate-100 transition-colors">Archive</button>
                                        </form>
                                        <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1.5 bg-white border border-secondary text-secondary text-xs font-bold rounded-lg hover:bg-secondary hover:text-white transition-colors">Edit</a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ═══ FINALIZED MASTER ORDERS ═══ --}}
                <div x-data="{ expanded: false, init() { const k = 'admin_finalized_master_orders'; this.expanded = localStorage.getItem(k) === 'true'; $watch('expanded', v => localStorage.setItem(k, v)) } }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mt-8">
                    <div @click="expanded = !expanded" class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer hover:bg-slate-100 transition-colors">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Finalized Master Orders</h2>
                            <p class="text-sm text-slate-500 mt-1">Aggregate totals per team store that have reached end of registration.</p>
                        </div>
                        <div class="flex items-center gap-4 text-slate-400">
                            <span class="text-sm font-bold">{{ $finalizedStoreBatches->count() }} Batches</span>
                            <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse>
                        @if($finalizedStoreBatches->isEmpty())
                            <div class="p-12 text-center text-slate-400 text-sm">No finalized store orders yet.</div>
                        @else
                            <div class="divide-y divide-slate-100">
                                @foreach($finalizedStoreBatches as $batchId => $batchData)
                                @php
                                    $batchOrders = $batchData['orders'] ?? collect();
                                    $store = $batchOrders->first()?->teamStore;
                                    $coach = $store ? $store->user : null;
                                    $totalAthletes = $batchOrders->count();
                                    $totalItems = $batchOrders->sum(fn($o) => count(is_array($o->items_json) ? $o->items_json : []));
                                @endphp
                                <div class="p-5">
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                        <div>
                                            <div class="font-black text-slate-900 uppercase text-base">{{ $store ? $store->name : 'Unknown Store' }}</div>
                                            <div class="text-sm text-slate-500 mt-0.5">
                                                Coach: {{ $coach ? $coach->name : 'Unknown' }} — {{ $coach ? $coach->organization : '—' }}
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider rounded-md border border-slate-200">
                                                    Status: {{ $batchOrders->first()?->status ?? 'Submitted' }}
                                                </span>
                                                <div class="text-xs text-slate-400 uppercase tracking-wide font-bold">Batch Submitted: {{ $batchOrders->first()?->created_at?->format('M d, Y') ?? 'Unknown' }}</div>
                                            </div>
                                            <div class="mt-3 grid grid-cols-3 gap-4">
                                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                    <div class="text-2xl font-black text-primary">{{ $totalAthletes }}</div>
                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Athletes</div>
                                                </div>
                                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                    <div class="text-2xl font-black text-slate-900">{{ $totalItems }}</div>
                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Items</div>
                                                </div>
                                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                    <div class="text-xs font-bold text-slate-900">{{ $store && $store->order_deadline ? $store->order_deadline->format('M d') : '—' }}</div>
                                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Deadline</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-2 flex-shrink-0">
                                            @if($store)
                                                <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-[#a11825] transition-colors text-center">Review / Edit Store</a>
                                                <div class="flex gap-2">
                                                    <!-- We can reuse admin batch export for batches -->
                                                    <a href="{{ route('admin.batch.export', $batchId) }}" class="w-full px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-slate-50 transition-colors text-center">Roster CSV</a>
                                                    <a href="{{ route('admin.batch.export-aggregate', $batchId) }}" class="w-full px-4 py-2 bg-slate-900 text-white text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-slate-800 transition-colors text-center shadow-sm">Aggregate CSV</a>
                                                </div>
                                                <form action="{{ route('admin.store-batch.mark-addressed', $batchId) }}" method="POST" onsubmit="return confirm('Mark this batch as addressed to remove it from the new count?')" class="w-full">
                                                    @csrf
                                                    <button type="submit" class="w-full px-4 py-2 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border border-green-200 text-xs font-bold uppercase tracking-wide rounded-lg transition-colors text-center">Mark Addressed</button>
                                                </form>
                                                <form action="{{ route('admin.stores.archive', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this closed store?')" class="w-full">
                                                    @csrf
                                                    <button type="submit" class="w-full px-4 py-2 bg-white border border-slate-300 text-slate-500 text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-slate-100 transition-colors text-center">Archive Store</button>
                                                </form>
                                                <form action="{{ route('admin.batch.status.update', $batchId) }}" method="POST" class="mt-2 flex items-center justify-between bg-white border border-slate-200 rounded-lg p-2 gap-2 shadow-sm">
                                                    @csrf
                                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Production Status:</span>
                                                    <select name="status" onchange="this.form.submit()" class="text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded px-2 py-1 outline-none focus:border-primary focus:ring-1 focus:ring-primary w-full max-w-[130px]">
                                                        <option value="Submitted to Admin" @if(($batchOrders->first()?->status ?? '') == 'Submitted to Admin') selected @endif>Submitted to Admin</option>
                                                        <option value="Processing" @if(($batchOrders->first()?->status ?? '') == 'Processing') selected @endif>Processing</option>
                                                        <option value="Design Approved" @if(($batchOrders->first()?->status ?? '') == 'Design Approved') selected @endif>Design Approved</option>
                                                        <option value="In Production" @if(($batchOrders->first()?->status ?? '') == 'In Production') selected @endif>In Production</option>
                                                        <option value="Shipped" @if(($batchOrders->first()?->status ?? '') == 'Shipped') selected @endif>Shipped</option>
                                                        <option value="Delivered" @if(($batchOrders->first()?->status ?? '') == 'Delivered') selected @endif>Delivered</option>
                                                        <option value="Completed" @if(($batchOrders->first()?->status ?? '') == 'Completed') selected @endif>Completed</option>
                                                    </select>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ═══ FINALIZED DIRECT ORDERS ═══ --}}
                <div x-data="{ expanded: false, init() { const k = 'admin_finalized_direct_orders'; this.expanded = localStorage.getItem(k) === 'true'; $watch('expanded', v => localStorage.setItem(k, v)) } }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mt-8">
                    <div @click="expanded = !expanded" class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer hover:bg-slate-100 transition-colors">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Finalized Direct Orders</h2>
                            <p class="text-sm text-slate-500 mt-1">Direct order batches submitted by coaches (no storefront).</p>
                        </div>
                        <div class="flex items-center gap-4 text-slate-400">
                            <span class="text-sm font-bold">{{ $finalizedDirectOrderBatches->count() }} Batches</span>
                            <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse>
                        @if($finalizedDirectOrderBatches->isEmpty())
                            <div class="p-12 text-center text-slate-400 text-sm">No finalized direct orders.</div>
                        @else
                        <div class="divide-y divide-slate-100">
                            @foreach($finalizedDirectOrderBatches as $batchId => $batchData)
                            @php
                                $batchOrders = $batchData['orders'] ?? collect();
                                $totalItems = $batchOrders->sum(fn($o) => count(is_array($o->items_json) ? $o->items_json : []));
                                $coach = $batchOrders->first()?->user;
                            @endphp
                            <div class="p-5">
                                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                    <div>
                                        <div class="font-black text-slate-900 uppercase text-base">Direct Order Batch</div>
                                        <div class="text-sm text-slate-500 mt-0.5">
                                            Coach: {{ $coach?->name ?? 'Unknown' }} — {{ $coach?->organization ?? '—' }}
                                        </div>
                                        <div class="mt-1">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider rounded-md border border-slate-200">
                                                Status: {{ $batchOrders->first()?->status ?? 'Submitted' }}
                                            </span>
                                        </div>
                                        <div class="mt-3 grid grid-cols-3 gap-4">
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-2xl font-black text-primary">{{ $batchOrders->count() }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Orders</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-2xl font-black text-slate-900">{{ $totalItems }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Items</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-xs font-bold text-slate-900">{{ $batchOrders->first()?->created_at?->format('M d') ?? 'Unknown' }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Submitted</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 flex-shrink-0">
                                        <a href="{{ route('admin.direct-batch.show', $batchId) }}" class="px-4 py-2 bg-primary text-white text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-primary/90 transition-colors text-center">Review / Edit Order</a>
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.batch.export', $batchId) }}" class="w-full px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-slate-50 transition-colors text-center">Roster CSV</a>
                                            <a href="{{ route('admin.batch.export-aggregate', $batchId) }}" class="w-full px-4 py-2 bg-slate-900 text-white text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-slate-800 transition-colors text-center shadow-sm">Aggregate CSV</a>
                                        </div>
                                        <form action="{{ route('admin.direct-batch.mark-addressed', $batchId) }}" method="POST" onsubmit="return confirm('Mark this batch as addressed to remove it from the new count?')" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-2 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border border-green-200 text-xs font-bold uppercase tracking-wide rounded-lg transition-colors text-center">Mark Addressed</button>
                                        </form>
                                        <form action="{{ route('admin.batch.status.update', $batchId) }}" method="POST" class="mt-2 flex items-center justify-between bg-white border border-slate-200 rounded-lg p-2 gap-2 shadow-sm">
                                            @csrf
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Production Status:</span>
                                            <select name="status" onchange="this.form.submit()" class="text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded px-2 py-1 outline-none focus:border-primary focus:ring-1 focus:ring-primary w-full max-w-[130px]">
                                                <option value="Submitted to Admin" @if(($batchOrders->first()?->status ?? '') == 'Submitted to Admin') selected @endif>Submitted to Admin</option>
                                                <option value="Processing" @if(($batchOrders->first()?->status ?? '') == 'Processing') selected @endif>Processing</option>
                                                <option value="Design Approved" @if(($batchOrders->first()?->status ?? '') == 'Design Approved') selected @endif>Design Approved</option>
                                                <option value="In Production" @if(($batchOrders->first()?->status ?? '') == 'In Production') selected @endif>In Production</option>
                                                <option value="Shipped" @if(($batchOrders->first()?->status ?? '') == 'Shipped') selected @endif>Shipped</option>
                                                <option value="Delivered" @if(($batchOrders->first()?->status ?? '') == 'Delivered') selected @endif>Delivered</option>
                                                <option value="Completed" @if(($batchOrders->first()?->status ?? '') == 'Completed') selected @endif>Completed</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ═══ ARCHIVED STORES & ORDERS ═══ --}}
                <div x-data="{ expanded: false, init() { const k = 'admin_archived_stores'; this.expanded = localStorage.getItem(k) === 'true'; $watch('expanded', v => localStorage.setItem(k, v)) } }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mt-8">
                    <div class="p-6 border-b border-slate-200 bg-slate-100 flex items-center justify-between cursor-pointer hover:bg-slate-200 transition-colors" @click="expanded = !expanded">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-500">Archived Stores & Orders</h2>
                            <p class="text-sm text-slate-400 mt-1">Past orders that have been archived for production auditing.</p>
                        </div>
                        <div class="flex items-center gap-4 text-slate-400">
                            <span class="text-sm font-bold">{{ $archivedStores->count() }} Stores</span>
                            <span class="text-sm font-bold">{{ $archivedOrderBatches->count() }} Batches</span>
                            <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse class="divide-y divide-slate-100">
                        @if($archivedStores->isEmpty() && $archivedOrderBatches->isEmpty())
                            <div class="p-8 text-center text-slate-400 text-sm">No archived stores or orders.</div>
                        @else
                            @if($archivedStores->isNotEmpty())
                                <div class="p-4 border-b border-slate-100 bg-slate-50">
                                    <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Archived Stores</div>
                                </div>
                                @foreach($archivedStores as $store)
                                <div class="p-4 flex items-center justify-between gap-4 bg-slate-50 opacity-75 hover:opacity-100 transition-opacity">
                                    <div>
                                        <div class="font-bold text-sm text-slate-700">{{ $store->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $store->user->name }} · {{ $store->parentOrders->count() }} orders</div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <form action="{{ route('admin.stores.unarchive', $store) }}" method="POST" onsubmit="return confirm('Restore this store back to active production?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-white border border-slate-300 text-slate-500 text-xs font-bold rounded hover:bg-slate-200 transition-colors">Unarchive</button>
                                        </form>
                                        <form action="{{ route('admin.stores.delete', $store) }}" method="POST" onsubmit="return confirm('Delete this archived store and all related data? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-white border border-red-300 text-red-600 text-xs font-bold rounded hover:bg-red-50 transition-colors">Delete</button>
                                        </form>
                                        <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1 bg-white border border-slate-300 text-slate-500 text-xs font-bold rounded hover:bg-slate-200 transition-colors">View</a>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                            @if($archivedOrderBatches->isNotEmpty())
                                <div class="p-4 border-b border-slate-100 bg-slate-50">
                                    <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Archived Order Batches</div>
                                </div>
                                @foreach($archivedOrderBatches as $batchId => $batchData)
                                @php
                                    $orders = $batchData['orders'];
                                    $store = $orders->first()->teamStore;
                                    $coach = $orders->first()->user;
                                @endphp
                                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 opacity-75 hover:opacity-100 transition-opacity">
                                    <div>
                                        <div class="font-bold text-sm text-slate-700">{{ $store ? $store->name : 'Direct Order Batch' }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $coach?->name ?? 'Unknown Coach' }} · {{ $orders->count() }} orders
                                            @if($store) · Team Store @endif
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1">Batch ID: {{ $batchId }}</div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if($store)
                                            <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1 bg-white border border-slate-300 text-slate-500 text-xs font-bold rounded hover:bg-slate-200 transition-colors">View Store</a>
                                        @else
                                            <a href="{{ route('admin.direct-batch.show', $batchId) }}" class="px-3 py-1 bg-white border border-slate-300 text-slate-500 text-xs font-bold rounded hover:bg-slate-200 transition-colors">View Batch</a>
                                        @endif
                                        <form action="{{ route('admin.archived-orders.delete', $batchId) }}" method="POST" onsubmit="return confirm('Permanently delete this archived order batch? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-white border border-red-300 text-red-600 text-xs font-bold rounded hover:bg-red-50 transition-colors">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- ═══ COACHES TAB ═══ --}}
        <div x-show="activeAdminTab === 'coaches'" x-cloak>
            {{-- ═══ COACHES DATABASE ═══ --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Coaches Database</h2>
                            <a href="{{ route('admin.coaches.export') }}" class="px-3 py-1.5 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg hover:bg-slate-800 transition-colors shadow-sm whitespace-nowrap">Export CSV</a>
                        </div>
                        <form action="{{ route('admin.dashboard') }}" method="GET" class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                            <div class="relative flex-1 md:w-72">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or org..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none w-full shadow-sm">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-secondary text-white text-sm font-bold rounded-lg hover:bg-[#a11825] transition-colors shrink-0">Search</button>
                            @if(request('search'))
                                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-600 text-sm font-bold rounded-lg hover:bg-slate-50 transition-colors">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">ID</th>
                            <th class="px-5 py-3">First Name</th>
                            <th class="px-5 py-3">Last Name</th>
                            <th class="px-5 py-3">Organization</th>
                            <th class="px-5 py-3">Apparel Category</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Store</th>
                            <th class="px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($coaches as $coach)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 text-slate-500 font-mono text-xs">#{{ $coach->id }}</td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $coach->first_name }}</div>
                                <div class="text-xs text-slate-500">{{ $coach->email }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $coach->last_name }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $coach->organization ?? '—' }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $coach->sport ?? '—' }}</td>
                            <td class="px-5 py-4">
                                @if($coach->status === 'active')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700 border border-green-200">Active</span>
                                @elseif($coach->status === 'declined')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-700 border border-red-200">Declined</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">{{ ucfirst($coach->status) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                @if($coach->teamStore)
                                    <a href="{{ route('admin.store.edit', $coach->teamStore) }}" class="text-primary hover:underline text-xs font-bold uppercase">{{ $coach->teamStore->name }}</a>
                                @else
                                    <span class="text-slate-400 text-xs">No Store</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.coach.edit', $coach) }}" class="px-3 py-1.5 bg-white border border-secondary text-secondary text-xs font-bold rounded-lg hover:bg-secondary hover:text-white transition-colors">Edit</a>
                                    <form action="{{ route('admin.coach.delete', $coach) }}" method="POST" onsubmit="return confirm('Remove coach {{ addslashes($coach->name) }} from the system? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1.5 bg-white border border-red-300 text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($coaches->isEmpty())
                            <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400 text-sm">No coaches found{{ request('search') ? ' matching "' . request('search') . '"' : '' }}.</td></tr>
                        @endif
                    </tbody>
                </table>
                </div>
                @if($coaches->hasPages())
                    <div class="p-5 border-t border-slate-200">{{ $coaches->links() }}</div>
                @endif
            </div>

        </div>


        {{-- ═══ LANDING PAGE SETTINGS TAB ═══ --}}
        <div x-show="activeAdminTab === 'landing'" x-cloak class="max-w-4xl">

            {{-- ═══ HERO SECTION CONFIGURATION ═══ --}}
            <div x-data="{ expanded: false, init() { const k = 'admin_hero_settings'; this.expanded = localStorage.getItem(k) === 'true'; $watch('expanded', v => localStorage.setItem(k, v)) } }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-8">
                <div @click="expanded = !expanded" class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer hover:bg-slate-100 transition-colors">
                    <div>
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Hero Section Setup</h2>
                        <p class="text-xs text-slate-500 mt-1">Configure the main landing page text and background media (image or video).</p>
                    </div>
                    <div class="text-slate-400">
                        <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div x-show="expanded" x-collapse class="p-6">
                    <form id="remove-media-form" action="{{ url('/admin/hero-settings/remove-media') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <form action="{{ url('/admin/hero-settings') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Hero Subtitle</label>
                            <textarea name="hero_subtitle" required rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">{{ $heroSettings['subtitle'] }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Hero Media (Image or Video)</label>
                            @if($heroSettings['media_path'])
                                <div class="mb-3">
                                    <div class="rounded-lg overflow-hidden border border-slate-200 inline-block">
                                        @if($heroSettings['media_type'] === 'video')
                                            @php
                                                $ext = strtolower(pathinfo($heroSettings['media_path'], PATHINFO_EXTENSION));
                                                $mime = 'video/mp4';
                                                if ($ext === 'mov') $mime = 'video/quicktime';
                                                elseif ($ext === 'webm') $mime = 'video/webm';
                                                elseif ($ext === 'ogg') $mime = 'video/ogg';
                                            @endphp
                                            <video autoplay loop muted playsinline class="h-32 w-auto object-cover">
                                                <source src="{{ asset($heroSettings['media_path']) }}" type="{{ $mime }}">
                                            </video>
                                        @else
                                            <img src="{{ asset($heroSettings['media_path']) }}" class="h-32 w-auto object-cover">
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <button type="button" class="text-[10px] font-bold uppercase tracking-wider text-red-500 hover:text-red-700 underline" onclick="if(confirm('Are you sure you want to remove the media?')) document.getElementById('remove-media-form').submit();">Remove Media</button>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="hero_media" accept="image/*,video/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                            <p class="text-[10px] text-slate-400 mt-1.5 font-medium uppercase tracking-wider">Leave blank to keep current. Max 20MB. Videos will auto-play on mute.</p>
                        </div>
                        <button type="submit" class="py-2.5 px-6 bg-slate-900 hover:bg-slate-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors">
                            Save Hero Settings
                        </button>
                    </form>
                </div>
            </div>

            {{-- ═══ LANDING PAGE COLLECTIONS ═══ --}}
            <div x-data="{ expanded: true, init() { const k = 'admin_landing_collections'; const val = localStorage.getItem(k); this.expanded = val !== null ? val === 'true' : true; $watch('expanded', v => localStorage.setItem(k, v)) } }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center cursor-pointer hover:bg-slate-100 transition-colors" @click="expanded = !expanded">
                    <div>
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Landing Page Collections</h2>
                        <p class="text-xs text-slate-500 mt-1">Manage the showcase catalog cards on the public storefront.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <form id="bulk-landing-collections-sort-form" action="{{ route('admin.landing.bulk-sort') }}" method="POST" @click.stop>
                            @csrf
                        </form>
                        <button type="submit" form="bulk-landing-collections-sort-form" @click.stop class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
                        <div class="text-slate-400">
                            <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
                <div x-show="expanded" x-collapse>
                    <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <form action="{{ url('/admin/landing-collections') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div x-data="{
                                open: false,
                                search: '',
                                options: {{ json_encode(is_array($availableSports) ? array_values($availableSports) : $availableSports->values()->all()) }},
                                get filteredOptions() {
                                    if (this.search === '') return this.options;
                                    return this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                selectOption(val) {
                                    this.search = val;
                                    this.open = false;
                                }
                            }" class="relative z-20">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sport / Tab Name</label>
                                <div class="relative">
                                    <input type="text" name="tab_name" required x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Tackle Football" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 pr-10 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" autocomplete="off">

                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>

                                <ul x-show="open" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto py-1" style="display: none;">
                                    <template x-for="option in filteredOptions" :key="option">
                                        <li @click="selectOption(option)" class="px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer transition-colors font-medium" x-text="option"></li>
                                    </template>
                                    <li x-show="filteredOptions.length === 0" class="px-3 py-2 text-sm text-slate-500 italic">Press enter to use "<span x-text="search"></span>"</li>
                                </ul>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Organization Title</label>
                                <input type="text" name="title" required placeholder="e.g. Springfield High" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Description</label>
                            <textarea name="description" rows="2" required placeholder="Add a short description..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary shadow-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3 items-end">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Image Upload (4:3 fit)</label>
                                <input type="file" name="image" required accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" required value="0" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary shadow-sm">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                            Add Collection
                        </button>
                    </form>
                </div>

                {{-- Active Landing Collections --}}
                @if($landingCollections->isNotEmpty())
                <div class="max-h-[800px] overflow-y-auto divide-y divide-slate-100">
                    @foreach($landingCollections as $collection)
                    <div x-data="{ editModal: false }" class="flex flex-col">
                        <div class="flex items-center gap-4 px-4 py-3 hover:bg-slate-50">
                            @if($collection->image_path)
                                <img src="{{ $collection->image_path }}" class="w-16 h-12 object-cover rounded shadow-sm border border-slate-200">
                            @else
                                <div class="w-16 h-12 bg-slate-200 rounded flex items-center justify-center text-slate-400 text-xs shadow-sm border border-slate-200">No Img</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-slate-900 truncate">{{ $collection->title }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mt-0.5 flex items-center gap-2">
                                    {{ $collection->tab_name }} · Sort: 
                                    <input type="number" 
                                           name="collections[{{ $collection->id }}][sort_order]" 
                                           value="{{ $collection->sort_order }}" 
                                           form="bulk-landing-collections-sort-form"
                                           class="w-16 bg-white border border-slate-300 rounded-lg px-2 py-1 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="editModal = true" class="text-blue-500 hover:text-blue-700 p-1 transition-colors" title="Edit Collection">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form action="{{ url('/admin/landing-collections/' . $collection->id) }}" method="POST" onsubmit="return confirm('Remove this collection from the landing page?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 transition-colors p-1" title="Delete Collection">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                            <div @click.away="editModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-xl overflow-hidden animate-fade-in">
                                <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Edit Collection</h2>
                                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                                <div class="p-6">
                                    <form action="{{ url('/admin/landing-collections/' . $collection->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf @method('PUT')
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sport / Tab Name</label>
                                                <input type="text" name="tab_name" value="{{ $collection->tab_name }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Organization Title</label>
                                                <input type="text" name="title" value="{{ $collection->title }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Description</label>
                                            <textarea name="description" rows="2" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">{{ $collection->description }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 items-end">
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Replace Image (Optional)</label>
                                                <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                                <input type="number" name="sort_order" value="{{ $collection->sort_order }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                            </div>
                                        </div>
                                        <div class="pt-2 flex gap-3">
                                            <button type="submit" class="flex-1 py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">Save Changes</button>
                                            <button type="button" @click="editModal = false" class="px-6 py-2.5 border border-slate-300 text-slate-700 text-sm font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-6 text-center text-sm text-slate-400">No collections configured.</div>
                @endif
                </div>
            </div>
        </div>

        {{-- ═══ DESIGN CATALOG TAB ═══ --}}
        <div x-show="activeAdminTab === 'catalog'" x-cloak>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- ADD NEW COLLECTION -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ expandedAddCollection: false, init() { const k = 'admin_add_new_collection'; this.expandedAddCollection = localStorage.getItem(k) === 'true'; $watch('expandedAddCollection', v => localStorage.setItem(k, v)) } }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer" @click="expandedAddCollection = !expandedAddCollection">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Collection</h2>
                            <p class="text-xs text-slate-500 mt-1">Create collection entities with cover photos.</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedAddCollection ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="p-5 border-b border-slate-200" x-show="expandedAddCollection" x-cloak>
                        <form action="{{ route('admin.design-collection.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="w-full">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name</label>
                                    <input type="text" name="name" required placeholder="e.g. Tampa Xpress Track Club Collection" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                </div>
                                <div class="w-full">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" placeholder="0" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                </div>
                                <div class="w-full">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Cover Image</label>
                                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer">
                                </div>
                            </div>
                            <div class="w-full">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Categories (Sports)</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 bg-slate-50 p-3 rounded-lg border border-slate-200 max-h-32 overflow-y-auto">
                                    @foreach($availableSports as $sport)
                                        <label class="inline-flex items-center text-xs text-slate-700 font-medium cursor-pointer">
                                            <input type="checkbox" name="sports[]" value="{{ $sport }}" class="rounded border-slate-300 text-primary focus:ring-primary mr-2">
                                            {{ $sport }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-secondary hover:bg-[#a11825] text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors whitespace-nowrap shadow-sm">
                                    Add Collection
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ 
                    expanded: false,
                    init() {
                        const savedExpanded = localStorage.getItem('collectionsExpanded');
                        if (savedExpanded === 'true') {
                            this.expanded = true;
                        }
                        $watch('expanded', value => {
                            localStorage.setItem('collectionsExpanded', value);
                        });
                    }
                }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Update Existing Collections</h2>
                            <p class="text-xs text-slate-500 mt-1">Edit or remove existing collection entities and their cover images.</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="max-h-[900px] overflow-y-auto" x-show="expanded" x-cloak>
                        @if($designCollections->isEmpty())
                            <div class="p-8 text-center text-slate-500 font-bold uppercase tracking-widest text-sm">No collections created yet.</div>
                        @else
                            <form id="bulk-collections-sort-form" action="{{ route('admin.design-collection.bulk-update') }}" method="POST">
                                @csrf
                            </form>
                            <div class="p-3 bg-white border-b border-slate-200 flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjust sort orders below and click save</span>
                                <button type="submit" form="bulk-collections-sort-form" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
                            </div>

                            <div id="update-collections-sortable-list">
                            @foreach($designCollections as $collection)
                            <div x-data="{ showModal: false, showItems: false }" class="border-b border-slate-100 last:border-0 bg-white">
    <div class="flex items-center justify-between px-4 py-3 hover:bg-slate-50">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="cursor-move text-slate-300 hover:text-slate-500 transition-colors px-1" title="Drag to reorder">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </div>
                                    @if($collection->image_path)
                                        <div class="w-12 h-12 rounded-lg bg-slate-200 overflow-hidden flex-shrink-0">
                                            <img src="{{ $collection->image_path }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                                            <span class="text-[10px] text-slate-400 font-bold">NONE</span>
                                        </div>
                                    @endif
                                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pr-4">
                                        <div class="text-sm font-bold text-slate-900">{{ $collection->name }}</div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Sort Order:</span>
                                            <input type="number" 
                                                   name="collections[{{ $collection->id }}][sort_order]"
                                                   value="{{ $collection->sort_order }}" 
                                                   form="bulk-collections-sort-form"
                                                   class="sort-order-input-update-col w-16 bg-white border border-slate-300 rounded-lg px-2 py-1 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <button @click="showItems = !showItems" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-secondary flex items-center gap-1 transition-colors mr-2">
    <svg class="w-3 h-3 transform transition-transform" :class="showItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    View Items
</button>
<a href="{{ route('admin.design-collection.manage', $collection) }}" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-secondary flex items-center gap-1 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                        Manage Items
                                    </a>
                                    <button @click="showModal = true" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-primary flex items-center gap-1 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Edit
                                    </button>
                                </div>

                                <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
                                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showModal = false" x-transition.opacity></div>
                                    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                                        <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                                            <h3 class="font-black uppercase tracking-tight text-slate-900">Edit Collection</h3>
                                            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div class="p-5">
                                            <form action="{{ route('admin.design-collection.update', $collection) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name</label>
                                                    <input type="text" name="name" value="{{ $collection->name }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                                    <input type="number" name="sort_order" value="{{ $collection->sort_order }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Update Cover Image</label>
                                                    <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Categories (Sports)</label>
                                                    <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-lg border border-slate-200 max-h-32 overflow-y-auto">
                                                        @foreach($availableSports as $sport)
                                                            <label class="inline-flex items-center text-xs text-slate-700 font-medium cursor-pointer">
                                                                <input type="checkbox" name="sports[]" value="{{ $sport }}" 
                                                                       @if(is_array($collection->sports) && in_array($sport, $collection->sports)) checked @endif
                                                                       class="rounded border-slate-300 text-primary focus:ring-primary mr-2">
                                                                {{ $sport }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="pt-2">
                                                    <button type="submit" class="w-full py-2.5 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-700 transition-colors">Save Changes</button>
                                                </div>
                                            </form>
                                            <form action="{{ route('admin.design-collection.delete', $collection) }}" method="POST" class="mt-4 pt-4 border-t border-slate-100" onsubmit="return confirm('Delete this collection? Designs in this collection will NOT be deleted, but will lose their collection grouping.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full py-2.5 bg-white border border-red-200 text-red-600 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-red-50 transition-colors">Delete Collection</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            
</div>
@php
    $collectionItems = $designCatalog->where('design_collection_id', $collection->id)->values();
@endphp
<div class="border-t border-slate-100 bg-white" x-show="showItems" x-cloak x-data="{
    search: '',
    page: 1,
    perPage: 100,
    items: {{ json_encode($collectionItems->map(function($d) {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'collection_name' => $d->designCollection ? $d->designCollection->name : '',
            'coach_name' => $d->coaches->map(function($c) { return $c->first_name . ' ' . $c->last_name; })->implode(', ')
        ];
    })->values()) }},
    selectedDesigns: [],
    get filteredItems() {
        if (this.search === '') return this.items;
        return this.items.filter(i => {
            const name = (i.name || '').toLowerCase();
            const coach = (i.coach_name || '').toLowerCase();
            const query = this.search.toLowerCase();
            return name.includes(query) || coach.includes(query);
        });
    },
    get totalPages() {
        return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
    },
    get paginatedItemIds() {
        const start = (this.page - 1) * this.perPage;
        const end = start + this.perPage;
        return this.filteredItems.slice(start, end).map(i => i.id);
    }
}">
<div x-show="showItems" x-cloak>

                        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="text-sm font-bold text-slate-700">Collection Items</div>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <div class="relative flex-1 sm:flex-none">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input type="text" x-model="search" placeholder="Search designs..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm w-full md:w-64">
                                </div>
                                <button type="button" x-show="search !== ''" @click="search = ''" x-cloak class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">Clear</button>
                            </div>
                        </div>
                        @if($collectionItems->isNotEmpty())
                        <form id="bulk-sort-form-{{ $collection->id }}" action="{{ route('admin.design.bulk-sort') }}" method="POST">
                            @csrf
                        </form>
                        <div class="p-3 bg-white border-b border-slate-200 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjust sort orders below and click save</span>
                            <button type="submit" form="bulk-sort-form-{{ $collection->id }}" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
                        </div>
                        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="selectedDesigns = (selectedDesigns.length === filteredItems.length) ? [] : filteredItems.map(i => i.id)" 
                                        class="px-2.5 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">
                                    <span x-text="selectedDesigns.length === filteredItems.length ? 'Deselect All' : 'Select All'"></span>
                                </button>
                                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider" x-show="selectedDesigns.length > 0">
                                    <span x-text="selectedDesigns.length"></span> selected
                                </span>
                            </div>
                            
                            <form action="{{ route('admin.design.bulk-assign') }}" method="POST" class="flex items-center gap-1.5" @submit="if(selectedDesigns.length === 0) { event.preventDefault(); alert('Please select at least one design to assign.'); }">
                                @csrf
                                <template x-for="id in selectedDesigns" :key="id">
                                    <input type="hidden" name="design_ids[]" :value="id">
                                </template>
                                <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2.5 py-1.5 w-48 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="">Assign selected to...</option>
                                    @foreach($allCoaches as $c)
                                        <option value="{{ $c->id }}">{{ $c->organization ?? 'No Org' }} ({{ $c->first_name }} {{ $c->last_name }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-xs font-bold uppercase px-3 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors">Mass Assign</button>
                            </form>
                        </div>
                        <div id="update-catalog-sortable-list-{{ isset($collection) ? $collection->id : 'unassigned' }}" class="update-catalog-sortable-list max-h-[900px] overflow-y-auto" data-is-collection="{{ isset($collection) ? 'true' : 'false' }}">
                            @foreach($collectionItems as $design)
                        <div x-show="paginatedItemIds.includes({{ $design->id }})" x-cloak class="flex flex-col sm:flex-row sm:items-start justify-between px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 gap-4 bg-white">
                            <div class="flex items-start gap-3 flex-1 pr-4">
                                <div class="cursor-move text-slate-300 hover:text-slate-500 transition-colors px-1 mt-1" title="Drag to reorder">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </div>
                                <input type="checkbox" value="{{ $design->id }}" x-model="selectedDesigns" class="mt-1.5 rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                @php
                                    $imageSrc = null;
                                    if (!empty($design->image_paths)) {
                                        $imageSrc = $design->image_paths[0];
                                    } elseif ($design->image_url) {
                                        $imageSrc = $design->image_url;
                                    }
                                @endphp
                                @if($imageSrc)
                                    <div class="w-12 h-16 rounded overflow-hidden flex-shrink-0 border border-slate-200">
                                        <img src="{{ asset($imageSrc) }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-12 h-16 rounded bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">No Img</span>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <div class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-primary mt-0.5">
                                    {{ $design->sport ? $design->sport . ' · ' : '' }}{{ $design->type_label }} · {{ $design->category_label }}
                                </div>
                                <div class="mt-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Sort Order</label>
                                    <input type="number" 
                                           name="designs[{{ $design->id }}][sort_order]"
                                           value="{{ $design->sort_order }}" 
                                           form="bulk-sort-form-{{ $collection->id }}"
                                           class="sort-order-input-catalog-col w-16 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                </div>
                                <details class="mt-2">
                                    <summary class="text-[10px] font-bold uppercase tracking-wider text-slate-500 cursor-pointer hover:text-primary">Edit design</summary>
                                    <form action="{{ route('admin.design.update', $design) }}" method="POST" enctype="multipart/form-data" class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Name</label>
                                                <input type="text" name="name" value="{{ $design->name }}" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name</label>
                                                <div class="relative">
                                                    <select name="design_collection_id" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none appearance-none">
                                                        <option value="">No Collection</option>
                                                        @foreach($designCollections as $collection)
                                                            <option value="{{ $collection->id }}" {{ $design->design_collection_id == $collection->id ? 'selected' : '' }}>{{ $collection->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div x-data="{
                                                open: false,
                                                search: '{{ addslashes($design->sport) }}',
                                                options: {{ json_encode(is_array($availableSports) ? array_values($availableSports) : $availableSports->values()->all()) }},
                                                get filteredOptions() {
                                                    if (this.search === '') return this.options;
                                                    return this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase()));
                                                },
                                                selectOption(val) {
                                                    this.search = val;
                                                    this.open = false;
                                                }
                                            }" class="relative">
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Sport</label>
                                                <div class="relative">
                                                    <input type="text" name="sport" x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Football" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 pr-8 text-xs text-slate-900 focus:border-primary focus:outline-none" autocomplete="off">
                                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-2 text-slate-500 hover:text-slate-700 focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                </div>
                                                <div x-show="open && filteredOptions.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded shadow-lg max-h-40 overflow-y-auto">
                                                    <template x-for="opt in filteredOptions" :key="opt">
                                                        <div @click="selectOption(opt)" class="px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer font-medium" x-text="opt"></div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Category</label>
                                                <input list="edit_category_options_{{ $design->id }}" name="category" value="{{ $design->category }}" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                                <datalist id="edit_category_options_{{ $design->id }}">
                                                    <option value="package_a">Package A — Base</option>
                                                    <option value="package_b">Package B — Standard</option>
                                                    <option value="package_c">Package C — Full</option>
                                                    <option value="individual">Individual Item</option>
                                                </datalist>
                                            </div>
                                        </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Item Types</label>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                    @foreach($typeOptions as $val => $label)
                                                        <label class="flex items-center gap-1.5 text-[10px] text-slate-700">
                                                            <input
                                                                type="checkbox"
                                                                name="types[]"
                                                                value="{{ $val }}"
                                                                class="rounded border-slate-300 text-primary focus:ring-primary"
                                                                {{ in_array($val, $design->types ?? []) ? 'checked' : '' }}
                                                            >
                                                            <span>{{ $label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Product Description (Optional)</label>
                                                <textarea name="description" rows="3" placeholder="Outline the item(s) included, especially for packages..." class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">{{ $design->description }}</textarea>
                                            </div>
                                        @php
                                            $currentImages = is_array($design->image_paths) ? $design->image_paths : [];
                                            if (empty($currentImages) && !empty($design->image_url)) {
                                                $currentImages[] = $design->image_url;
                                            }
                                        @endphp
                                        @if(!empty($currentImages))
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Current Images (Use arrows to reorder. Click DEL to remove)</label>
                                                <div x-data="{
                                                    images: {{ json_encode($currentImages) }},
                                                    removed: [],
                                                    moveLeft(idx) {
                                                        if (idx > 0) {
                                                            let temp = this.images[idx];
                                                            this.images[idx] = this.images[idx - 1];
                                                            this.images[idx - 1] = temp;
                                                        }
                                                    },
                                                    moveRight(idx) {
                                                        if (idx < this.images.length - 1) {
                                                            let temp = this.images[idx];
                                                            this.images[idx] = this.images[idx + 1];
                                                            this.images[idx + 1] = temp;
                                                        }
                                                    },
                                                    removeImage(idx) {
                                                        this.removed.push(this.images[idx]);
                                                        this.images.splice(idx, 1);
                                                    }
                                                }" class="flex flex-wrap gap-2">
                                                    <template x-for="(imgPath, idx) in images" :key="imgPath">
                                                        <div class="relative group block w-16 h-16 bg-white rounded-md border border-slate-200 shadow-sm">
                                                            <input type="hidden" name="existing_images[]" :value="imgPath">
                                                            <img :src="imgPath" class="w-full h-full object-cover rounded-md">

                                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center rounded-md">
                                                                <div class="flex gap-1 mb-1">
                                                                    <button type="button" @click.prevent="moveLeft(idx)" x-show="idx > 0" class="p-1 bg-white hover:bg-slate-200 text-slate-900 rounded-sm" title="Move Left">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                                                                    </button>
                                                                    <button type="button" @click.prevent="moveRight(idx)" x-show="idx < images.length - 1" class="p-1 bg-white hover:bg-slate-200 text-slate-900 rounded-sm" title="Move Right">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                                                    </button>
                                                                </div>
                                                                <button type="button" @click.prevent="removeImage(idx)" class="px-2 py-0.5 bg-red-500 hover:bg-red-600 text-white text-[9px] font-bold rounded-sm" title="Remove">
                                                                    DEL
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-for="rm in removed">
                                                        <input type="hidden" name="remove_images[]" :value="rm">
                                                    </template>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Wholesale Price ($)</label>
                                                <input type="number" step="0.01" min="0" name="wholesale_price" value="{{ $design->wholesale_price }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                                <input type="number" name="sort_order" value="{{ $design->sort_order }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Add More Images</label>
                                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                <input type="checkbox" name="has_name_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" {{ $design->has_name_field ? 'checked' : '' }}>
                                                Name on Item
                                            </label>
                                            <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                <input type="checkbox" name="has_number_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" {{ $design->has_number_field ? 'checked' : '' }}>
                                                Player Number
                                            </label>
                                        </div>
                                        <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider rounded hover:bg-slate-700 transition-colors">
                                            Save Changes
                                        </button>
                                    </form>
                                </details>
                            </div>
                        </div>
                            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                                <div class="flex items-center gap-3">
                                    <form action="{{ route('admin.design.assign-to-coach-profile', $design) }}" method="POST" class="flex items-center gap-1">
                                        @csrf
                                        <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2 py-1 w-32 focus:border-primary focus:outline-none">
                                            <option value="">Assign to coach...</option>
                                            @foreach($allCoaches as $c)
                                                <option value="{{ $c->id }}">{{ $c->organization ?? 'No Org' }} ({{ $c->first_name }} {{ $c->last_name }})</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="text-[10px] font-bold uppercase px-2 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors" title="Assign Design to Coach">Assign</button>
                                    </form>
                                    <form action="{{ route('admin.design.delete', $design) }}" method="POST" onsubmit="return confirm('Delete this design from the catalog?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div x-show="filteredItems.length === 0" x-cloak class="p-6 text-center text-sm text-slate-400">
                            No designs found matching "<span x-text="search"></span>".
                        </div>
                    </div>
                    <div class="p-5 border-t border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4" x-show="totalPages > 1" x-cloak>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider text-center md:text-left">
                            Showing <span x-text="((page - 1) * perPage) + 1"></span> to <span x-text="Math.min(page * perPage, filteredItems.length)"></span> of <span x-text="filteredItems.length"></span> results
                        </div>
                        <div class="flex items-center justify-center md:justify-end gap-1">
                            <button @click="if (page > 1) page--" :disabled="page === 1" :class="page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200 text-slate-700'" class="px-3 py-1.5 rounded bg-white border border-slate-300 text-xs font-bold uppercase transition-colors">Prev</button>
                            <span class="text-xs font-bold px-2 text-slate-600"><span x-text="page"></span> / <span x-text="totalPages"></span></span>
                            <button @click="if (page < totalPages) page++" :disabled="page === totalPages" :class="page === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200 text-slate-700'" class="px-3 py-1.5 rounded bg-white border border-slate-300 text-xs font-bold uppercase transition-colors">Next</button>
                        </div>
                    </div>
                    @else
                    <div class="p-6 text-center text-sm text-slate-400">No designs in catalog yet.</div>
                    @endif
                    </div> 
</div>
</div>
                            @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ADD NEW PACKAGE -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ expandedAddPackage: false, init() { const k = 'admin_add_new_package'; this.expandedAddPackage = localStorage.getItem(k) === 'true'; $watch('expandedAddPackage', v => localStorage.setItem(k, v)) } }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer" @click="expandedAddPackage = !expandedAddPackage">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Package / Design</h2>
                            <p class="text-xs text-slate-500 mt-1">Create catalog entries for coaches and stores.</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedAddPackage ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="p-5" x-show="expandedAddPackage" x-cloak>
                        <form action="{{ route('admin.design.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Design Name</label>
                                <input type="text" name="name" required placeholder="e.g. Springfield Eagles - Home Jersey" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name (Optional)</label>
                                <div class="relative">
                                    <select name="design_collection_id" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select a Collection...</option>
                                        @foreach($designCollections as $collection)
                                            <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div x-data="{
                                open: false,
                                search: '',
                                options: {{ json_encode(is_array($availableSports) ? array_values($availableSports) : $availableSports->values()->all()) }},
                                get filteredOptions() {
                                    if (this.search === '') return this.options;
                                    return this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                selectOption(val) {
                                    this.search = val;
                                    this.open = false;
                                }
                            }" class="relative">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Categories</label>
                                <div class="relative">
                                    <input type="text" name="sport" x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Football, Basketball" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 pr-10 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" autocomplete="off">
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-700 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <div x-show="open && filteredOptions.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="opt in filteredOptions" :key="opt">
                                        <div @click="selectOption(opt)" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer font-medium" x-text="opt"></div>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Package Category</label>
                                <div class="relative">
                                    <select name="category" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select Category</option>
                                        <option value="package_a">Package A - Base Kit</option>
                                        <option value="package_b">Package B - Standard</option>
                                        <option value="package_c">Package C - Full Program</option>
                                        <option value="individual">Individual Item</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Item Types (Select all that apply)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($typeOptions as $val => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="types[]" value="{{ $val }}" class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                    <span class="text-xs">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Product Description (Optional)</label>
                            <textarea name="description" rows="3" placeholder="Outline the item(s) included, especially for packages..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Upload Images</label>
                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Wholesale Price ($)</label>
                                <input type="number" step="0.01" name="wholesale_price" required placeholder="e.g. 45.00" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" placeholder="Auto" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_name_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Name on Item</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_number_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Player Number</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                            Add Package to Catalog
                        </button>
                        </form>
                    </div>
                </div>

                <!-- ADD NEW DESIGN -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ expandedAddDesign: false, init() { const k = 'admin_add_new_design'; this.expandedAddDesign = localStorage.getItem(k) === 'true'; $watch('expandedAddDesign', v => localStorage.setItem(k, v)) } }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer" @click="expandedAddDesign = !expandedAddDesign">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Design</h2>
                            <p class="text-xs text-slate-500 mt-1">Create catalog entries for coaches and stores.</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedAddDesign ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="p-5" x-show="expandedAddDesign" x-cloak>
                        <form action="{{ route('admin.design.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Design Name</label>
                                <input type="text" name="name" required placeholder="e.g. Springfield Eagles - Home Jersey" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name (Optional)</label>
                                <div class="relative">
                                    <select name="design_collection_id" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select a Collection...</option>
                                        @foreach($designCollections as $collection)
                                            <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div x-data="{
                                open: false,
                                search: '',
                                options: {{ json_encode(is_array($availableSports) ? array_values($availableSports) : $availableSports->values()->all()) }},
                                get filteredOptions() {
                                    if (this.search === '') return this.options;
                                    return this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                selectOption(val) {
                                    this.search = val;
                                    this.open = false;
                                }
                            }" class="relative">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Categories</label>
                                <div class="relative">
                                    <input type="text" name="sport" x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Football, Basketball" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 pr-10 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" autocomplete="off">
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-700 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <div x-show="open && filteredOptions.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="opt in filteredOptions" :key="opt">
                                        <div @click="selectOption(opt)" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer font-medium" x-text="opt"></div>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Package Category</label>
                                <div class="relative">
                                    <select name="category" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select Category</option>
                                        <option value="package_a">Package A - Base Kit</option>
                                        <option value="package_b">Package B - Standard</option>
                                        <option value="package_c">Package C - Full Program</option>
                                        <option value="individual" selected>Individual Item</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Item Types (Select all that apply)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($typeOptions as $val => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="types[]" value="{{ $val }}" class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                    <span class="text-xs">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Product Description (Optional)</label>
                            <textarea name="description" rows="3" placeholder="Outline the item(s) included, especially for packages..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Upload Images</label>
                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Wholesale Price ($)</label>
                                <input type="number" step="0.01" name="wholesale_price" required placeholder="e.g. 45.00" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" placeholder="Auto" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_name_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Name on Item</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_number_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Player Number</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                            Add to Design Catalog
                        </button>
                        </form>
                    </div>
                </div>

                <!-- UPDATE EXISTING CATALOG -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ 
                    expandedCatalog: false,
                    search: '',
                    page: 1,
                    perPage: 100,
                    items: {{ json_encode($designCatalog->whereNull('design_collection_id')->values()->map(function($d) {
                        return [
                            'id' => $d->id,
                            'name' => $d->name,
                            'collection_name' => $d->designCollection ? $d->designCollection->name : '',
                            'coach_name' => $d->coaches->map(function($c) { return $c->first_name . ' ' . $c->last_name; })->implode(', ')
                        ];
                    })->values()) }},
                    selectedDesigns: [],
                    selectAll: false,
                    bulkCoachId: '',
                    get filteredItems() {
                        if (this.search === '') return this.items;
                        return this.items.filter(i => {
                            const name = (i.name || '').toLowerCase();
                            const coll = (i.collection_name || '').toLowerCase();
                            const coach = (i.coach_name || '').toLowerCase();
                            const query = this.search.toLowerCase();
                            return name.includes(query) || coll.includes(query) || coach.includes(query);
                        });
                    },
                    get totalPages() {
                        return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
                    },
                    get paginatedItemIds() {
                        const start = (this.page - 1) * this.perPage;
                        const end = start + this.perPage;
                        return this.filteredItems.slice(start, end).map(i => i.id);
                    },
                    init() {
                        const savedExpanded = localStorage.getItem('catalogExpanded');
                        if (savedExpanded === 'true') {
                            this.expandedCatalog = true;
                        }
                        $watch('expandedCatalog', value => {
                            localStorage.setItem('catalogExpanded', value);
                        });

                        const savedPage = localStorage.getItem('catalogActivePage');
                        if (savedPage) {
                            this.page = parseInt(savedPage) || 1;
                        }
                        $watch('page', value => {
                            localStorage.setItem('catalogActivePage', value);
                        });
                        $watch('search', () => { this.page = 1; });
                    }
                }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer" @click="expandedCatalog = !expandedCatalog">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Update Unassigned Catalog Items</h2>
                            <p class="text-xs text-slate-500 mt-1">Edit design details for items not assigned to any collection.</p>
                        </div>
                        <div class="flex gap-4 items-center">
                            <div class="flex gap-2" @click.stop>
                                <div class="relative">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input type="text" x-model="search" placeholder="Search designs..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm w-full md:w-64">
                                </div>
                                <button type="button" x-show="search !== ''" @click="search = ''" x-cloak class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">Clear</button>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedCatalog ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expandedCatalog" x-cloak>
                        @if($designCatalog->isNotEmpty())
                        <form id="bulk-sort-form" action="{{ route('admin.design.bulk-sort') }}" method="POST">
                            @csrf
                        </form>
                        <div class="p-3 bg-white border-b border-slate-200 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjust sort orders below and click save</span>
                            <button type="submit" form="bulk-sort-form" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
                        </div>
                        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="selectedDesigns = (selectedDesigns.length === filteredItems.length) ? [] : filteredItems.map(i => i.id)" 
                                        class="px-2.5 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">
                                    <span x-text="selectedDesigns.length === filteredItems.length ? 'Deselect All' : 'Select All'"></span>
                                </button>
                                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider" x-show="selectedDesigns.length > 0">
                                    <span x-text="selectedDesigns.length"></span> selected
                                </span>
                            </div>
                            
                            <form action="{{ route('admin.design.bulk-assign') }}" method="POST" class="flex items-center gap-1.5" @submit="if(selectedDesigns.length === 0) { event.preventDefault(); alert('Please select at least one design to assign.'); }">
                                @csrf
                                <template x-for="id in selectedDesigns" :key="id">
                                    <input type="hidden" name="design_ids[]" :value="id">
                                </template>
                                <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2.5 py-1.5 w-48 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="">Assign selected to...</option>
                                    @foreach($allCoaches as $c)
                                        <option value="{{ $c->id }}">{{ $c->organization ?? 'No Org' }} ({{ $c->first_name }} {{ $c->last_name }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-xs font-bold uppercase px-3 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors">Mass Assign</button>
                            </form>
                        </div>
                        <div id="update-catalog-sortable-list-unassigned" class="update-catalog-sortable-list max-h-[900px] overflow-y-auto" data-is-collection="false">
                            @foreach($designCatalog as $design)
                        <div x-show="paginatedItemIds.includes({{ $design->id }})" x-cloak class="flex flex-col sm:flex-row sm:items-start justify-between px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 gap-4 bg-white">
                            <div class="flex items-start gap-3 flex-1 pr-4">
                                <div class="cursor-move text-slate-300 hover:text-slate-500 transition-colors px-1 mt-1" title="Drag to reorder">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </div>
                                <input type="checkbox" value="{{ $design->id }}" x-model="selectedDesigns" class="mt-1.5 rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                @php
                                    $imageSrc = null;
                                    if (!empty($design->image_paths)) {
                                        $imageSrc = $design->image_paths[0];
                                    } elseif ($design->image_url) {
                                        $imageSrc = $design->image_url;
                                    }
                                @endphp
                                @if($imageSrc)
                                    <div class="w-12 h-16 rounded overflow-hidden flex-shrink-0 border border-slate-200">
                                        <img src="{{ asset($imageSrc) }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-12 h-16 rounded bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">No Img</span>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <div class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-primary mt-0.5">
                                    {{ $design->sport ? $design->sport . ' · ' : '' }}{{ $design->type_label }} · {{ $design->category_label }}
                                </div>
                                <div class="mt-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Sort Order</label>
                                    <input type="number" 
                                           name="designs[{{ $design->id }}][sort_order]"
                                           value="{{ $design->sort_order }}" 
                                           form="bulk-sort-form"
                                           class="sort-order-input-catalog-col w-16 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                </div>
                                <details class="mt-2">
                                    <summary class="text-[10px] font-bold uppercase tracking-wider text-slate-500 cursor-pointer hover:text-primary">Edit design</summary>
                                    <form action="{{ route('admin.design.update', $design) }}" method="POST" enctype="multipart/form-data" class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Name</label>
                                                <input type="text" name="name" value="{{ $design->name }}" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name</label>
                                                <div class="relative">
                                                    <select name="design_collection_id" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none appearance-none">
                                                        <option value="">No Collection</option>
                                                        @foreach($designCollections as $collection)
                                                            <option value="{{ $collection->id }}" {{ $design->design_collection_id == $collection->id ? 'selected' : '' }}>{{ $collection->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div x-data="{
                                                open: false,
                                                search: '{{ addslashes($design->sport) }}',
                                                options: {{ json_encode(is_array($availableSports) ? array_values($availableSports) : $availableSports->values()->all()) }},
                                                get filteredOptions() {
                                                    if (this.search === '') return this.options;
                                                    return this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase()));
                                                },
                                                selectOption(val) {
                                                    this.search = val;
                                                    this.open = false;
                                                }
                                            }" class="relative">
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Sport</label>
                                                <div class="relative">
                                                    <input type="text" name="sport" x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Football" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 pr-8 text-xs text-slate-900 focus:border-primary focus:outline-none" autocomplete="off">
                                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-2 text-slate-500 hover:text-slate-700 focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                </div>
                                                <div x-show="open && filteredOptions.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded shadow-lg max-h-40 overflow-y-auto">
                                                    <template x-for="opt in filteredOptions" :key="opt">
                                                        <div @click="selectOption(opt)" class="px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer font-medium" x-text="opt"></div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Category</label>
                                                <input list="edit_category_options_{{ $design->id }}" name="category" value="{{ $design->category }}" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                                <datalist id="edit_category_options_{{ $design->id }}">
                                                    <option value="package_a">Package A — Base</option>
                                                    <option value="package_b">Package B — Standard</option>
                                                    <option value="package_c">Package C — Full</option>
                                                    <option value="individual">Individual Item</option>
                                                </datalist>
                                            </div>
                                        </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Item Types</label>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                    @foreach($typeOptions as $val => $label)
                                                        <label class="flex items-center gap-1.5 text-[10px] text-slate-700">
                                                            <input
                                                                type="checkbox"
                                                                name="types[]"
                                                                value="{{ $val }}"
                                                                class="rounded border-slate-300 text-primary focus:ring-primary"
                                                                {{ in_array($val, $design->types ?? []) ? 'checked' : '' }}
                                                            >
                                                            <span>{{ $label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Product Description (Optional)</label>
                                                <textarea name="description" rows="3" placeholder="Outline the item(s) included, especially for packages..." class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">{{ $design->description }}</textarea>
                                            </div>
                                        @php
                                            $currentImages = is_array($design->image_paths) ? $design->image_paths : [];
                                            if (empty($currentImages) && !empty($design->image_url)) {
                                                $currentImages[] = $design->image_url;
                                            }
                                        @endphp
                                        @if(!empty($currentImages))
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Current Images (Use arrows to reorder. Click DEL to remove)</label>
                                                <div x-data="{
                                                    images: {{ json_encode($currentImages) }},
                                                    removed: [],
                                                    moveLeft(idx) {
                                                        if (idx > 0) {
                                                            let temp = this.images[idx];
                                                            this.images[idx] = this.images[idx - 1];
                                                            this.images[idx - 1] = temp;
                                                        }
                                                    },
                                                    moveRight(idx) {
                                                        if (idx < this.images.length - 1) {
                                                            let temp = this.images[idx];
                                                            this.images[idx] = this.images[idx + 1];
                                                            this.images[idx + 1] = temp;
                                                        }
                                                    },
                                                    removeImage(idx) {
                                                        this.removed.push(this.images[idx]);
                                                        this.images.splice(idx, 1);
                                                    }
                                                }" class="flex flex-wrap gap-2">
                                                    <template x-for="(imgPath, idx) in images" :key="imgPath">
                                                        <div class="relative group block w-16 h-16 bg-white rounded-md border border-slate-200 shadow-sm">
                                                            <input type="hidden" name="existing_images[]" :value="imgPath">
                                                            <img :src="imgPath" class="w-full h-full object-cover rounded-md">

                                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center rounded-md">
                                                                <div class="flex gap-1 mb-1">
                                                                    <button type="button" @click.prevent="moveLeft(idx)" x-show="idx > 0" class="p-1 bg-white hover:bg-slate-200 text-slate-900 rounded-sm" title="Move Left">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                                                                    </button>
                                                                    <button type="button" @click.prevent="moveRight(idx)" x-show="idx < images.length - 1" class="p-1 bg-white hover:bg-slate-200 text-slate-900 rounded-sm" title="Move Right">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                                                    </button>
                                                                </div>
                                                                <button type="button" @click.prevent="removeImage(idx)" class="px-2 py-0.5 bg-red-500 hover:bg-red-600 text-white text-[9px] font-bold rounded-sm" title="Remove">
                                                                    DEL
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-for="rm in removed">
                                                        <input type="hidden" name="remove_images[]" :value="rm">
                                                    </template>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Wholesale Price ($)</label>
                                                <input type="number" step="0.01" min="0" name="wholesale_price" value="{{ $design->wholesale_price }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                                <input type="number" name="sort_order" value="{{ $design->sort_order }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Add More Images</label>
                                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                <input type="checkbox" name="has_name_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" {{ $design->has_name_field ? 'checked' : '' }}>
                                                Name on Item
                                            </label>
                                            <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                <input type="checkbox" name="has_number_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" {{ $design->has_number_field ? 'checked' : '' }}>
                                                Player Number
                                            </label>
                                        </div>
                                        <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider rounded hover:bg-slate-700 transition-colors">
                                            Save Changes
                                        </button>
                                    </form>
                                </details>
                            </div>
                        </div>
                            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                                <div class="flex items-center gap-3">
                                    <form action="{{ route('admin.design.assign-to-coach-profile', $design) }}" method="POST" class="flex items-center gap-1">
                                        @csrf
                                        <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2 py-1 w-32 focus:border-primary focus:outline-none">
                                            <option value="">Assign to coach...</option>
                                            @foreach($allCoaches as $c)
                                                <option value="{{ $c->id }}">{{ $c->organization ?? 'No Org' }} ({{ $c->first_name }} {{ $c->last_name }})</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="text-[10px] font-bold uppercase px-2 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors" title="Assign Design to Coach">Assign</button>
                                    </form>
                                    <form action="{{ route('admin.design.delete', $design) }}" method="POST" onsubmit="return confirm('Delete this design from the catalog?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div x-show="filteredItems.length === 0" x-cloak class="p-6 text-center text-sm text-slate-400">
                            No designs found matching "<span x-text="search"></span>".
                        </div>
                    </div>
                    <div class="p-5 border-t border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4" x-show="totalPages > 1" x-cloak>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider text-center md:text-left">
                            Showing <span x-text="((page - 1) * perPage) + 1"></span> to <span x-text="Math.min(page * perPage, filteredItems.length)"></span> of <span x-text="filteredItems.length"></span> results
                        </div>
                        <div class="flex items-center justify-center md:justify-end gap-1">
                            <button @click="if (page > 1) page--" :disabled="page === 1" :class="page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200 text-slate-700'" class="px-3 py-1.5 rounded bg-white border border-slate-300 text-xs font-bold uppercase transition-colors">Prev</button>
                            <span class="text-xs font-bold px-2 text-slate-600"><span x-text="page"></span> / <span x-text="totalPages"></span></span>
                            <button @click="if (page < totalPages) page++" :disabled="page === totalPages" :class="page === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200 text-slate-700'" class="px-3 py-1.5 rounded bg-white border border-slate-300 text-xs font-bold uppercase transition-colors">Next</button>
                        </div>
                    </div>
                    @else
                    <div class="p-6 text-center text-sm text-slate-400">No designs in catalog yet.</div>
                    @endif
                    </div> <!-- Closing expandedCatalog wrapper -->
                </div>
            </div>
        </div>

        {{-- ═══ TESTIMONIALS TAB ═══ --}}
        <div x-show="activeAdminTab === 'testimonials'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Add New Testimonial -->
            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Testimonial</h2>
                        <p class="text-xs text-slate-500 mt-1">Publish a new client testimonial to the landing page.</p>
                    </div>
                    <div class="p-6">
                        <form action="{{ url('/admin/testimonials') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Client Name *</label>
                                <input type="text" name="client_name" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Organization / Title</label>
                                <input type="text" name="organization" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" placeholder="e.g. USA Track & Field">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Testimonial Content *</label>
                                <textarea name="content" required rows="4" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Sort Order *</label>
                                    <input type="number" name="sort_order" value="0" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Client Photo / Logo</label>
                                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                                </div>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                                Add Testimonial
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing Testimonials -->
            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Manage Testimonials</h2>
                            <p class="text-xs text-slate-500 mt-1">Review and delete active testimonials.</p>
                        </div>
                        <div>
                            <form id="bulk-testimonials-sort-form" action="{{ route('admin.testimonials.bulk-sort') }}" method="POST">
                                @csrf
                            </form>
                            <button type="submit" form="bulk-testimonials-sort-form" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
                        </div>
                    </div>
                    @if($testimonials->isNotEmpty())
                    <div class="divide-y divide-slate-100 max-h-[800px] overflow-y-auto">
                        @foreach($testimonials as $testimonial)
                        <div x-data="{ editModal: false }" class="p-5 hover:bg-slate-50 flex gap-4 transition-colors">
                            @if($testimonial->image_path)
                                <img src="{{ $testimonial->image_path }}" alt="Photo" class="w-12 h-12 rounded-full object-cover shrink-0 border border-slate-200">
                            @else
                                <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center shrink-0 border border-slate-300 text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $testimonial->client_name }}</h3>
                                        @if($testimonial->organization)
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-secondary">{{ $testimonial->organization }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="editModal = true" class="text-blue-500 hover:text-blue-700 p-1 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ url('/admin/testimonials/' . $testimonial->id) }}" method="POST" onsubmit="return confirm('Delete this testimonial permanently?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600 p-1 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p class="text-sm text-slate-600 mt-2 font-medium line-clamp-3">"{{ $testimonial->content }}"</p>
                                <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <span>Sort Order:</span>
                                        <input type="number" 
                                               name="testimonials[{{ $testimonial->id }}][sort_order]" 
                                               value="{{ $testimonial->sort_order }}" 
                                               form="bulk-testimonials-sort-form"
                                               class="w-16 bg-white border border-slate-300 rounded px-2 py-1 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                    </div>
                                    <span class="{{ $testimonial->is_active ? 'text-green-500' : 'text-slate-400' }}">{{ $testimonial->is_active ? 'Active' : 'Hidden' }}</span>
                                </div>
                            </div>

                            <!-- Edit Modal -->
                            <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                                <div @click.away="editModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-xl overflow-hidden animate-fade-in">
                                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Edit Testimonial</h2>
                                        <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                    <div class="p-6">
                                        <form action="{{ url('/admin/testimonials/' . $testimonial->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                            @csrf @method('PUT')
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Client Name *</label>
                                                <input type="text" name="client_name" value="{{ $testimonial->client_name }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Organization / Title</label>
                                                <input type="text" name="organization" value="{{ $testimonial->organization }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Testimonial Content *</label>
                                                <textarea name="content" required rows="4" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">{{ $testimonial->content }}</textarea>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Sort Order *</label>
                                                    <input type="number" name="sort_order" value="{{ $testimonial->sort_order }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Active Status</label>
                                                    <div class="pt-2">
                                                        <label class="flex items-center cursor-pointer">
                                                            <input type="checkbox" name="is_active" value="1" {{ $testimonial->is_active ? 'checked' : '' }} class="sr-only peer">
                                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                                            <span class="ml-3 text-sm font-medium text-slate-700">Visible on site</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Replace Photo / Logo</label>
                                                <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                                            </div>
                                            <div class="pt-2 flex gap-3">
                                                <button type="submit" class="flex-1 py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">Save Changes</button>
                                                <button type="button" @click="editModal = false" class="px-6 py-2.5 border border-slate-300 text-slate-700 text-sm font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-8 text-center text-slate-400 text-sm">No testimonials added yet.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ SIZING CHARTS TAB ═══ --}}
        <div x-show="activeAdminTab === 'sizing_charts'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Add New Sizing Chart -->
            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Sizing Chart</h2>
                        <p class="text-xs text-slate-500 mt-1">Upload a new sizing chart image.</p>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.sizing-charts.create') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Chart Title *</label>
                                <input type="text" name="title" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" placeholder="e.g. Volleyball: Female Tops">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Sort Order *</label>
                                    <input type="number" name="sort_order" value="0" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Chart Image *</label>
                                    <input type="file" name="image" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                                </div>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                                Add Sizing Chart
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing Sizing Charts -->
            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Manage Sizing Charts</h2>
                            <p class="text-xs text-slate-500 mt-1">Review, reorder, and delete charts.</p>
                        </div>
                        <div>
                            <form id="bulk-sizing-charts-sort-form" action="{{ route('admin.sizing-charts.bulk-sort') }}" method="POST">
                                @csrf
                            </form>
                            <button type="submit" form="bulk-sizing-charts-sort-form" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort</button>
                        </div>
                    </div>
                    @if($sizingCharts->isNotEmpty())
                    <div class="divide-y divide-slate-100 max-h-[800px] overflow-y-auto">
                        @foreach($sizingCharts as $chart)
                        <div class="p-5 hover:bg-slate-50 flex gap-4 transition-colors">
                            @if($chart->image_path)
                                <img src="{{ $chart->image_path }}" alt="Sizing Chart" class="w-16 h-16 rounded object-cover shrink-0 border border-slate-200">
                            @endif
                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $chart->title }}</h3>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('admin.sizing-charts.delete', $chart->id) }}" method="POST" onsubmit="return confirm('Delete this sizing chart permanently?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600 p-1 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <span>Sort Order:</span>
                                        <input type="number" 
                                               name="items[{{ $loop->index }}][order]" 
                                               value="{{ $chart->sort_order }}" 
                                               form="bulk-sizing-charts-sort-form"
                                               class="w-16 bg-white border border-slate-300 rounded px-2 py-1 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $chart->id }}" form="bulk-sizing-charts-sort-form">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-8 text-center text-slate-400 text-sm">No sizing charts added yet.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ SECURITY LOGS TAB ═══ --}}
        <div x-show="activeAdminTab === 'security'" x-cloak>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Security Audit Logs</h2>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Date / Time</th>
                            <th class="px-5 py-3">Action</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($passwordResetLogs as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 text-slate-500 text-xs">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700 border border-orange-200">Password Reset</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $log->user->name ?? 'Deleted User' }}</div>
                                <div class="text-xs text-slate-500">{{ $log->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs font-bold uppercase tracking-wider">{{ $log->user->role ?? 'N/A' }}</td>
                            <td class="px-5 py-4 text-slate-500 font-mono text-xs">{{ $log->ip_address ?? 'Unknown' }}</td>
                        </tr>
                        @endforeach
                        @if($passwordResetLogs->isEmpty())
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">No security logs recorded yet.</td></tr>
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- ═══ CAMPAIGN STORES TAB ═══ --}}
        <div x-show="activeAdminTab === 'campaigns'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 flex items-center gap-2">
                                Campaign Stores
                                <span class="ml-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-[10px] font-black">{{ $campaignStores->count() }}</span>
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">Manage public storefronts owned by The Commission Apparel.</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.stores.create') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Campaign Name</label>
                                <input type="text" name="name" required placeholder="e.g. Bahamas Independence" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Description</label>
                                <textarea name="description" rows="2" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-primary outline-none text-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Order Deadline (Optional)</label>
                                <input type="date" name="order_deadline" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-primary outline-none text-sm">
                            </div>
                            <button type="submit" class="btn btn-primary w-full py-3 uppercase tracking-widest text-xs font-bold shadow-md">Create Campaign Store</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                @forelse($campaignStores as $store)
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-start bg-slate-50">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-black text-slate-900 uppercase text-lg leading-none">{{ $store->name }}</h3>
                                <span class="bg-green-100 text-green-800 border border-green-200 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">Active</span>
                            </div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">{{ $store->items->count() }} Items Assigned</p>
                        </div>
                        <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wide rounded-lg shadow-sm transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Manage Store
                        </a>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div class="mb-5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Public Link</div>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ url('/store/' . $store->slug) }}" class="flex-1 bg-slate-50 border border-slate-200 rounded text-xs px-3 py-2 font-mono text-slate-600 outline-none">
                                <button type="button" onclick="navigator.clipboard.writeText('{{ url('/store/' . $store->slug) }}'); alert('Link copied!')" class="p-2 text-slate-500 hover:text-primary transition-colors border border-slate-200 rounded hover:border-primary hover:bg-primary/5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </button>
                                <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="p-2 text-slate-500 hover:text-primary transition-colors border border-slate-200 rounded hover:border-primary hover:bg-primary/5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-sm border-2 border-dashed border-slate-200 rounded-xl">
                    No campaign stores have been created yet.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateCollectionsList = document.getElementById('update-collections-sortable-list');
        if (updateCollectionsList) {
            new Sortable(updateCollectionsList, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-slate-50',
                onEnd: function () {
                    const inputs = Array.from(updateCollectionsList.querySelectorAll('.sort-order-input-update-col'));
                    let values = inputs.map(input => parseInt(input.value) || 0).sort((a, b) => a - b); // ascending 0, 1, 2...
                    
                    inputs.forEach((input, index) => {
                        input.value = values[index];
                    });
                }
            });
        }

        const updateCatalogLists = document.querySelectorAll('.update-catalog-sortable-list');
        updateCatalogLists.forEach(listEl => {
            new Sortable(listEl, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-slate-50',
                onEnd: function (evt) {
                    const inputs = Array.from(listEl.querySelectorAll('.sort-order-input-catalog-col'));
                    
                    // If it is a collection list, re-number them 1, 2, 3...
                    // Otherwise (global unassigned items), keep existing values and just sort them.
                    const isCollection = listEl.getAttribute('data-is-collection') === 'true';
                    
                    if (isCollection) {
                        inputs.forEach((input, index) => {
                            input.value = index + 1;
                        });
                    } else {
                        let values = inputs.map(input => parseInt(input.value) || 0).sort((a, b) => a - b);
                        inputs.forEach((input, index) => {
                            input.value = values[index];
                        });
                    }
                }
            });
        });
    });
</script>
@endsection
