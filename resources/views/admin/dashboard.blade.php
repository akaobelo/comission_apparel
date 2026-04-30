@extends('layouts.app')
@section('title', 'Admin Portal | The Commission Apparel')
@section('content')
<div class="max-w-[1600px] mx-auto px-6 pb-8 pt-32 lg:pt-40">
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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-primary">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Total Coaches</p>
            <div class="text-3xl font-black text-slate-900">{{ $coaches->total() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-orange-400">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Stores Awaiting Approval</p>
            <div class="text-3xl font-black text-orange-500">{{ $pendingStores->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-green-500">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Active Production Stores</p>
            <div class="text-3xl font-black text-green-600">{{ $productionStores->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm border-l-4 border-l-slate-400">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Finalized Orders</p>
            <div class="text-3xl font-black text-slate-900">{{ $finalizedStores->count() }}</div>
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
        <div class="flex overflow-x-auto pb-0 mb-8 border-b border-slate-200 gap-8">
            <button @click="setTab('stores')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'stores' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Stores & Orders</button>
            <button @click="setTab('coaches')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'coaches' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Coaches</button>
            <button @click="setTab('catalog')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'catalog' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Design Catalog</button>
            <button @click="setTab('landing')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'landing' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Landing Page Settings</button>
            <button @click="setTab('testimonials')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'testimonials' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Testimonials</button>
            <button @click="setTab('security')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'security' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Security Logs</button>
        </div>

        {{-- ═══ STORES & ORDERS TAB ═══ --}}
        <div x-show="activeAdminTab === 'stores'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Quote Inquiries</h2>
                        <p class="text-sm text-slate-500 mt-1">Public quote requests submitted from the website.</p>
                    </div>
                    @if($quoteRequests->isEmpty())
                        <div class="p-8 text-center text-slate-400 text-sm">No quote inquiries yet.</div>
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
                                        <div class="text-xs text-slate-400 flex-shrink-0">
                                            {{ $quoteRequest->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

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

            </div>
            <div class="space-y-8">
                {{-- ═══ ACTIVE STORES IN PRODUCTION ═══ --}}
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Active Team Stores</h2>
                    </div>
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
                                <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1.5 bg-white border border-secondary text-secondary text-xs font-bold rounded-lg hover:bg-secondary hover:text-white transition-colors flex-shrink-0">Edit</a>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ═══ FINALIZED MASTER ORDERS ═══ --}}
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Finalized Master Orders</h2>
                        <p class="text-sm text-slate-500 mt-1">Aggregate totals per team store that have reached end of registration.</p>
                    </div>
                    @if($finalizedStores->isEmpty())
                        <div class="p-12 text-center text-slate-400 text-sm">No finalized orders yet.</div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($finalizedStores as $store)
                            @php
                                $totalAthletes = $store->parentOrders->count();
                                $totalItems = $store->parentOrders->sum(fn($o) => count(is_array($o->items_json) ? $o->items_json : []));
                            @endphp
                            <div class="p-5">
                                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                    <div>
                                        <div class="font-black text-slate-900 uppercase text-base">{{ $store->name }}</div>
                                        <div class="text-sm text-slate-500 mt-0.5">
                                            Coach: {{ $store->user->name }} — {{ $store->user->organization }}
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
                                                <div class="text-xs font-bold text-slate-900">{{ $store->order_deadline ? $store->order_deadline->format('M d') : '—' }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Deadline</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 flex-shrink-0">
                                        <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-[#a11825] transition-colors text-center">Review / Edit</a>
                                        <a href="{{ route('admin.stores.export', $store) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-slate-50 transition-colors text-center">Export CSV</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ COACHES TAB ═══ --}}
        <div x-show="activeAdminTab === 'coaches'" x-cloak>
            {{-- ═══ COACHES DATABASE ═══ --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Coaches Database</h2>
                        <form action="{{ route('admin.dashboard') }}" method="GET" class="flex gap-2">
                            <div class="relative">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or org..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none w-72 shadow-sm">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-secondary text-white text-sm font-bold rounded-lg hover:bg-[#a11825] transition-colors">Search</button>
                            @if(request('search'))
                                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-600 text-sm font-bold rounded-lg hover:bg-slate-50 transition-colors">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>
                <table class="w-full text-left">
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
                @if($coaches->hasPages())
                    <div class="p-5 border-t border-slate-200">{{ $coaches->links() }}</div>
                @endif
            </div>

        </div>


        {{-- ═══ LANDING PAGE SETTINGS TAB ═══ --}}
        <div x-show="activeAdminTab === 'landing'" x-cloak class="max-w-4xl">

            {{-- ═══ LANDING PAGE COLLECTIONS ═══ --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Landing Page Collections</h2>
                        <p class="text-xs text-slate-500 mt-1">Manage the showcase catalog cards on the public storefront.</p>
                    </div>
                </div>
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <form action="{{ route('admin.landing.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sport / Tab Name</label>
                                <input type="text" name="tab_name" required placeholder="e.g. Tackle Football" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary shadow-sm">
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
                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                    @foreach($landingCollections as $collection)
                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-slate-50">
                        @if($collection->image_path)
                            <img src="{{ $collection->image_path }}" class="w-16 h-12 object-cover rounded shadow-sm border border-slate-200">
                        @else
                            <div class="w-16 h-12 bg-slate-200 rounded flex items-center justify-center text-slate-400 text-xs shadow-sm border border-slate-200">No Img</div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-slate-900 truncate">{{ $collection->title }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-primary mt-0.5">{{ $collection->tab_name }} · Sort: {{ $collection->sort_order }}</div>
                        </div>
                        <form action="{{ route('admin.landing.delete', $collection) }}" method="POST" onsubmit="return confirm('Remove this collection from the landing page?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-6 text-center text-sm text-slate-400">No collections configured.</div>
                @endif
            </div>
        </div>

        {{-- ═══ DESIGN CATALOG TAB ═══ --}}
        <div x-show="activeAdminTab === 'catalog'" x-cloak>
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
                    'warmup_top' => 'Warm-up (top)',
                    'warmup_bottom' => 'Warm-up (bottom)',
                    'warmup_set' => 'Warm-up (top/bottom)',
                ];
            @endphp
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Design</h2>
                        <p class="text-xs text-slate-500 mt-1">Create catalog entries for coaches and stores.</p>
                    </div>
                    <div class="p-5">
                        <form action="{{ route('admin.design.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Design Name</label>
                            <input type="text" name="name" required placeholder="e.g. Springfield Eagles - Home Jersey" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                        </div>
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
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sport</label>
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
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Item Types (Select all that apply)</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($typeOptions as $val => $label)
                                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="types[]" value="{{ $val }}" class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                        <span class="text-xs">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Package Category</label>
                                <input list="create_category_options" name="category" required placeholder="e.g. package_a or Custom Package" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                <datalist id="create_category_options">
                                    <option value="package_a">Package A — Base</option>
                                    <option value="package_b">Package B — Standard</option>
                                    <option value="package_c">Package C — Full</option>
                                    <option value="individual">Individual Item</option>
                                </datalist>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Upload Images</label>
                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Wholesale Price ($)</label>
                                <input type="number" step="0.01" name="wholesale_price" required placeholder="e.g. 45.00" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
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

                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden" x-data="{
                    search: '',
                    page: 1,
                    perPage: 20,
                    items: {{ json_encode($designCatalog->map(function($d) { return ['id' => $d->id, 'name' => strtolower($d->name)]; })) }},
                    get filteredItems() {
                        if (this.search === '') return this.items;
                        const lowerSearch = this.search.toLowerCase();
                        return this.items.filter(i => i.name.includes(lowerSearch));
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
                        this.$watch('search', () => { this.page = 1; });
                    }
                }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Update Existing Catalog</h2>
                            <p class="text-xs text-slate-500 mt-1">Edit design details and push items to team stores.</p>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" x-model="search" placeholder="Search designs..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm w-full md:w-64">
                            </div>
                            <button type="button" x-show="search !== ''" @click="search = ''" x-cloak class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">Clear</button>
                        </div>
                    </div>
                    @if($designCatalog->isNotEmpty())
                    <div class="max-h-[900px] overflow-y-auto">
                        @foreach($designCatalog as $design)
                        <div x-show="paginatedItemIds.includes({{ $design->id }})" x-cloak class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0">
                            <div class="flex-1 pr-4">
                                <div class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-primary mt-0.5">
                                    {{ $design->sport ? $design->sport . ' · ' : '' }}{{ $design->type_label }} · {{ $design->category_label }}
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
                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
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
                                        @php
                                            $currentImages = is_array($design->image_paths) ? $design->image_paths : [];
                                            if (empty($currentImages) && !empty($design->image_url)) {
                                                $currentImages[] = $design->image_url;
                                            }
                                        @endphp
                                        @if(!empty($currentImages))
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Current Images (Select to remove)</label>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($currentImages as $idx => $imgPath)
                                                        <label x-data="{ checked: false }" class="relative cursor-pointer group rounded-md overflow-hidden border-2 transition-all block w-14 h-14 bg-slate-100" :class="checked ? 'border-red-500' : 'border-transparent hover:border-red-300'">
                                                            <input type="checkbox" name="remove_images[]" value="{{ $idx }}" x-model="checked" class="sr-only">
                                                            <img src="{{ $imgPath }}" class="w-full h-full object-cover transition-opacity" :class="checked ? 'opacity-40' : 'opacity-100'">
                                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity" :class="checked ? 'bg-red-500/20 opacity-100' : 'opacity-0'">
                                                                <svg class="w-5 h-5 text-red-600 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Wholesale Price ($)</label>
                                                <input type="number" step="0.01" min="0" name="wholesale_price" value="{{ $design->wholesale_price }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Add More Images (optional)</label>
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
                            <div class="flex items-center gap-3">
                                <form action="{{ route('admin.design.assign-to-store', $design) }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    <select name="team_store_id" required class="text-xs bg-white border border-slate-300 rounded px-2 py-1 w-32 focus:border-primary focus:outline-none">
                                        <option value="">Assign to store...</option>
                                        @foreach($allStores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->user->name }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="text-[10px] font-bold uppercase px-2 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors" title="Push Design to Store">Push</button>
                                </form>
                                <form action="{{ route('admin.design.delete', $design) }}" method="POST" onsubmit="return confirm('Delete this design from the catalog?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
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
                        <form action="{{ route('admin.testimonials.create') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
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
                                        <form action="{{ route('admin.testimonials.delete', $testimonial) }}" method="POST" onsubmit="return confirm('Delete this testimonial permanently?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600 p-1 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p class="text-sm text-slate-600 mt-2 font-medium line-clamp-3">"{{ $testimonial->content }}"</p>
                                <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-3">
                                    <span>Sort Order: {{ $testimonial->sort_order }}</span>
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
                                        <form action="{{ route('admin.testimonials.update', $testimonial) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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

        {{-- ═══ SECURITY LOGS TAB ═══ --}}
        <div x-show="activeAdminTab === 'security'" x-cloak>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Security Audit Logs</h2>
                </div>
                <table class="w-full text-left">
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
</div>
@endsection
