@extends('layouts.app')
@section('title', 'Coach Portal | The Commission Apparel')
@section('content')
<div class="max-w-[1400px] mx-auto px-6 pb-8 pt-32 lg:pt-40">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 border border-primary/20 text-primary text-xs font-bold uppercase tracking-widest rounded-full mb-2">
                Coach Portal
            </div>
            <h1 class="text-3xl font-black uppercase text-slate-900">Welcome, {{ $user->name }}</h1>
            <p class="text-slate-600 text-sm mt-1">{{ $user->organization }} · {{ $user->sport }}</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline py-2 px-4 text-xs uppercase tracking-wider">Sign Out</button>
        </form>
    </div>

    {{-- Flash Messages --}}
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

    {{-- ════ NO STORE YET ════ --}}
    @if(!$store)
    <div class="max-w-2xl mx-auto">
        @if($assignedDesigns->isEmpty())
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6 flex gap-4">
            <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-bold text-blue-900">Waiting on Designs</p>
                <p class="text-sm text-blue-700 mt-1">No custom designs have been assigned to your profile yet. Your designs will appear here once Ryan completes them and admin approves them. You can still request your Team Store now.</p>
            </div>
        </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-5 border border-primary/20">
                <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h2 class="text-2xl font-black uppercase text-slate-900 mb-2">Request Your Team Store</h2>
            <p class="text-slate-600 text-sm mb-8 max-w-md mx-auto">Submit a store request to The Commission Apparel. Once our team approves it and your designs are finalized, you'll be able to set up your ordering page for parents.</p>

            <form action="{{ route('coach.store.create') }}" method="POST" class="space-y-5 text-left max-w-md mx-auto">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Official Store Name</label>
                    <input type="text" name="name" required placeholder="e.g. {{ $user->organization }} — Fall 2025 Uniforms" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    @error('name')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description (optional)</label>
                    <textarea name="description" rows="2" placeholder="Brief description for your athletes and parents..." class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Package / Order Type</label>
                    <select name="package_type" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        <option value="" disabled selected>Select a package type...</option>
                        <option value="package_a">Package A — Base Kit (1 Uniform Set)</option>
                        <option value="package_b">Package B — Standard (Uniform + Warm-up)</option>
                        <option value="package_c">Package C — Full Program (Complete Kit)</option>
                        <option value="individual">Individual Items — Custom Selection</option>
                    </select>
                    @error('package_type')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-xs text-amber-800 font-medium">
                    ⚠ Your store will require admin approval before parents can place orders. Designs must be finalized and approved before the store can go live.
                </div>
                <button type="submit" class="btn btn-primary w-full py-4 text-sm font-bold uppercase tracking-widest">Submit Store Request</button>
            </form>
        </div>
    </div>

    {{-- ════ STORE PENDING ════ --}}
    @elseif($store->status === 'pending')
    <div class="max-w-lg mx-auto bg-white border border-amber-200 rounded-xl shadow-sm p-10 text-center">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5 border border-amber-200">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h2 class="text-2xl font-black uppercase text-slate-900 mb-3">Store Awaiting Approval</h2>
        <p class="text-slate-600 text-sm mb-4">Your store <span class="font-bold text-slate-900">{{ $store->name }}</span> has been submitted and is pending review by The Commission Apparel. You'll be notified when it's approved.</p>
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm text-slate-600">
            In the meantime, your custom designs are being finalized by Ryan. Once approved, they'll be assigned to your profile and ready to add to your store.
        </div>
    </div>

    {{-- ════ STORE DECLINED ════ --}}
    @elseif($store->status === 'declined')
    <div class="max-w-lg mx-auto bg-white border border-red-200 rounded-xl shadow-sm p-10 text-center">
        <h2 class="text-2xl font-black uppercase text-slate-900 mb-3 text-red-600">Store Request Declined</h2>
        <p class="text-slate-600 text-sm">Your store request was declined. Please contact The Commission Apparel for more information.</p>
    </div>

    {{-- ════ ACTIVE STORE (approved or submitted_to_admin) ════ --}}
    @else
    @php
        $totalAthletes = $store->parentOrders->count();
        $isLocked = $store->status === 'submitted_to_admin';
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-8">
        {{-- LEFT: Main management --}}
        <div class="space-y-6">

            {{-- ════ PRICING REVIEW BANNER ════ --}}
            @if($store->items->isNotEmpty() && !$store->pricing_approved)
                @php
                    $allPricesSet = $store->items->every(fn($item) => $item->wholesale_price > 0 && $item->retail_price > 0);
                @endphp
                @if($allPricesSet)
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-black uppercase text-blue-900 mb-1">Pricing Ready for Review</h3>
                            <p class="text-sm text-blue-800 mb-4">The Commission Apparel has set the pricing for your items. Please review your wholesale cost, retail price (which parents will see), and your profit margins below. You must approve this pricing before your storefront can go live.</p>
                            
                            <div class="space-y-2 mb-5 bg-white bg-opacity-60 rounded-lg p-4">
                                @foreach($store->items as $item)
                                <div class="flex justify-between items-center text-sm border-b border-blue-100 pb-2 last:border-0 last:pb-0">
                                    <span class="font-bold text-blue-900">{{ $item->name }}</span>
                                    <div class="flex gap-4 text-right">
                                        <div class="w-20"><span class="text-[10px] uppercase text-blue-500 block leading-tight">Your Cost</span><span class="font-medium text-blue-800">${{ number_format($item->wholesale_price, 2) }}</span></div>
                                        <div class="w-20"><span class="text-[10px] uppercase text-blue-500 block leading-tight">Store Price</span><span class="font-medium text-blue-800">${{ number_format($item->retail_price, 2) }}</span></div>
                                        <div class="w-20"><span class="text-[10px] uppercase text-green-600 font-bold block leading-tight">Your Profit</span><span class="font-bold text-green-700">${{ number_format($item->retail_price - $item->wholesale_price, 2) }}</span></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <form action="{{ route('coach.store.pricing.approve', $store) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wide transition-colors shadow-sm">I Approve This Pricing</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            {{-- Store status bar --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black uppercase text-slate-900">{{ $store->name }}</h2>
                    <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                        @if($isLocked)
                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider bg-green-100 text-green-700 border border-green-200">✓ Submitted to Production</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider bg-green-100 text-green-700 border border-green-200">● Live & Accepting Orders</span>
                        @endif
                        <span class="text-xs text-slate-500">Package: <strong class="text-slate-700 uppercase">{{ str_replace('_', ' ', $store->package_type ?? 'custom') }}</strong></span>
                        @if($store->order_deadline)
                            <span class="text-xs text-slate-500">Deadline: <strong class="text-slate-700">{{ $store->order_deadline->format('M d, Y') }}</strong></span>
                        @endif
                    </div>
                </div>
                @if(!$isLocked)
                    <div class="flex gap-2">
                        <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase rounded-lg transition-colors">Share Link</a>
                        @if($totalAthletes > 0)
                        <form action="{{ route('coach.store.submit', $store) }}" method="POST" onsubmit="return confirm('Finalize this roster and send to production? No new orders can be added after this.')">
                            @csrf
                            <button class="px-4 py-2 bg-slate-900 text-white text-xs font-bold uppercase rounded-lg hover:bg-slate-700 transition-colors">Finalize & Submit</button>
                        </form>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Order Progress Tracker --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h3 class="text-base font-black uppercase tracking-tight text-slate-900">Order Progress</h3>
                    <span class="text-2xl font-black text-primary">{{ $totalAthletes }}</span>
                </div>
                <div class="p-5">
                    @if($totalAthletes === 0)
                        <div class="text-center py-6 text-slate-500 text-sm">
                            <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            No orders received yet. Share your store link with your team.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($store->parentOrders as $order)
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center font-black text-primary text-sm">{{ substr($order->athlete_name, 0, 1) }}</div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $order->athlete_name }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase tracking-wide">{{ count(is_array($order->items_json) ? $order->items_json : []) }} item(s)</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($order->is_edited)
                                        <span class="text-[10px] font-bold text-orange-500 uppercase">Edited</span>
                                    @endif
                                    @if(!$isLocked)
                                        <a href="{{ route('coach.order.edit', $order) }}" class="px-2 py-1 bg-white border border-slate-300 text-slate-600 text-[10px] font-bold uppercase rounded hover:bg-slate-50 transition-colors">Edit</a>
                                    @endif
                                    <span class="w-6 h-6 flex items-center justify-center bg-green-100 text-green-600 rounded-full border border-green-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Set Deadline --}}
            @if(!$isLocked)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 mb-4">Order Deadline</h3>
                <form action="{{ route('coach.store.deadline', $store) }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="date" name="deadline" value="{{ $store->order_deadline?->format('Y-m-d') }}" class="flex-1 bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-slate-900 focus:border-primary focus:outline-none shadow-sm text-sm">
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-lg hover:bg-slate-700 transition-colors">Set Deadline</button>
                </form>
            </div>
            @endif
        </div>

        {{-- RIGHT: Team Builder --}}
        <div class="space-y-5">
            {{-- Public Link --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 mb-3">Parent Order Link</h3>
                @if(!$store->pricing_approved)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 font-medium">
                        ⚠ Store link is locked. You must approve the pricing structure before sharing the link with parents.
                    </div>
                @else
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ url('/store/' . $store->slug) }}" class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-700 focus:outline-none" id="storeUrl">
                        <button onclick="navigator.clipboard.writeText(document.getElementById('storeUrl').value); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 2000)" class="px-3 py-2 bg-primary text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors">Copy</button>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-2">Share this link with your athletes and parents. They will see the approved Retail Prices.</p>
                @endif
            </div>

            {{-- Team Builder: Add Items --}}
            @if(!$isLocked)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Store Builder</h3>
                    <p class="text-xs text-slate-500 mt-1">Add your approved custom designs to the store.</p>
                </div>
                <div class="p-5">
                    @if($assignedDesigns->isEmpty())
                        <div class="text-center py-6">
                            <p class="text-sm text-slate-500 font-medium">No designs assigned yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Admin will assign your approved designs once Ryan has completed them.</p>
                        </div>
                    @else
                        <form action="{{ route('coach.store.item.add', $store) }}" method="POST" class="flex gap-2 mb-4">
                            @csrf
                            <div class="flex-1">
                                <select name="design_catalog_id" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="">Select a design to add...</option>
                                    @foreach($assignedDesigns as $design)
                                        @php $alreadyAdded = $store->items->pluck('design_catalog_id')->contains($design->id); @endphp
                                        <option value="{{ $design->id }}" {{ $alreadyAdded ? 'disabled' : '' }}>
                                            {{ $design->name }} ({{ $design->type_label }}){{ $alreadyAdded ? ' — Added' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2.5 bg-primary text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors">Add</button>
                        </form>
                    @endif

                    {{-- Current items in store --}}
                    @if($store->items->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($store->items as $item)
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                <div>
                                    <div class="font-bold text-sm text-slate-900">{{ $item->name }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-primary">{{ str_replace('_', ' ', $item->type) }}</div>
                                </div>
                                <form action="{{ route('coach.store.item.remove', $item) }}" method="POST" onsubmit="return confirm('Remove this item from your store?')">
                                    @csrf
                                    <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 text-center py-4">No items added to store yet.</p>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-sm text-green-800">
                <p class="font-bold uppercase text-sm mb-1">✓ Store Submitted for Production</p>
                <p class="text-xs text-green-700">The master roster has been submitted. No further modifications can be made. Contact The Commission Apparel if corrections are needed.</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
