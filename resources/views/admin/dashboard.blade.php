@extends('layouts.app')
@section('title', 'Admin Portal | The Commission Apparel')
@section('content')
<div class="max-w-[1600px] mx-auto px-6 py-8 mt-16">
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

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-red-100 border border-red-200 text-red-700 text-xs font-bold uppercase tracking-widest rounded-full mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> Super Admin
            </div>
            <h1 class="text-4xl font-black uppercase tracking-tight text-slate-900">Admin <span class="text-primary">Control Center</span></h1>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline py-2 px-4 text-xs uppercase tracking-wider">Log Out</button>
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

    <div x-data="{ activeAdminTab: 'stores' }">
        {{-- Admin Navigation Tabs --}}
        <div class="flex overflow-x-auto pb-0 mb-8 border-b border-slate-200 gap-8">
            <button @click="activeAdminTab = 'stores'" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'stores' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-900'">Stores & Orders</button>
            <button @click="activeAdminTab = 'coaches'" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'coaches' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-900'">Coaches</button>
            <button @click="activeAdminTab = 'catalog'" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'catalog' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-900'">Design Catalog</button>
            <button @click="activeAdminTab = 'landing'" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'landing' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-900'">Landing Page Settings</button>
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
                                <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1.5 bg-white border border-primary text-primary text-xs font-bold rounded-lg hover:bg-primary hover:text-white transition-colors flex-shrink-0">Edit</a>
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
                                        <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-primary text-white text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-blue-700 transition-colors text-center">Review / Edit</a>
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
                            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors">Search</button>
                            @if(request('search'))
                                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-600 text-sm font-bold rounded-lg hover:bg-slate-50 transition-colors">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Coach</th>
                            <th class="px-5 py-3">Organization</th>
                            <th class="px-5 py-3">Sport</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Store</th>
                            <th class="px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($coaches as $coach)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $coach->name }}</div>
                                <div class="text-xs text-slate-500">{{ $coach->email }}</div>
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
                                    <a href="{{ route('admin.coach.edit', $coach) }}" class="px-3 py-1.5 bg-white border border-primary text-primary text-xs font-bold rounded-lg hover:bg-primary hover:text-white transition-colors">Edit</a>
                                    <form action="{{ route('admin.coach.delete', $coach) }}" method="POST" onsubmit="return confirm('Remove coach {{ addslashes($coach->name) }} from the system? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1.5 bg-white border border-red-300 text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($coaches->isEmpty())
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm">No coaches found{{ request('search') ? ' matching "' . request('search') . '"' : '' }}.</td></tr>
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
                                <input type="file" name="image" required accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-blue-700">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" required value="0" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary shadow-sm">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-primary hover:bg-blue-700 text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
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

        {{-- ═══ DESIGN CATALOG TAB ═══ --}}
        <div x-show="activeAdminTab === 'catalog'" x-cloak class="max-w-4xl">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Design Catalog</h2>
                    <p class="text-xs text-slate-500 mt-1">Add a custom design and assign it to coaches.</p>
                </div>
                <div class="p-5">
                    <form action="{{ route('admin.design.create') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Design Name</label>
                            <input type="text" name="name" required placeholder="e.g. Springfield Eagles - Home Jersey" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Item Type</label>
                                <select name="type" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="uniform_top">Uniform Top</option>
                                    <option value="uniform_bottom">Uniform Bottom</option>
                                    <option value="warmup_top">Warm-up Top</option>
                                    <option value="warmup_bottom">Warm-up Bottom</option>
                                    <option value="arm_sleeve">Arm Sleeve</option>
                                    <option value="backpack">Backpack</option>
                                    <option value="accessory">Accessory</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Package Category</label>
                                <select name="category" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="package_a">Package A — Base</option>
                                    <option value="package_b">Package B — Standard</option>
                                    <option value="package_c">Package C — Full</option>
                                    <option value="individual">Individual Item</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Image URL (optional)</label>
                            <input type="url" name="image_url" placeholder="https://..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
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
                        <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-700 text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                            Add to Design Catalog
                        </button>
                    </form>
                </div>

                {{-- Existing catalog items --}}
                @if($designCatalog->isNotEmpty())
                <div class="border-t border-slate-200 max-h-80 overflow-y-auto">
                    @foreach($designCatalog as $design)
                    <div class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0">
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-primary mt-0.5">{{ $design->type_label }} · {{ $design->category_label }}</div>
                        </div>
                        <form action="{{ route('admin.design.delete', $design) }}" method="POST" onsubmit="return confirm('Delete this design from the catalog?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
