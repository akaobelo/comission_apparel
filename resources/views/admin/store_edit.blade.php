@extends('layouts.app')
@section('title', 'Edit Store | Admin')
@section('content')
<div class="max-w-5xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Store: {{ $store->name }}</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-8">
        {{-- Left: Store details --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Store Settings</h2>
                    <p class="text-sm text-slate-500 mt-1">Coach: {{ $store->user->name }} — {{ $store->user->organization }}</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.store.update', $store) }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-2 gap-5">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Store Name</label>
                                <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Package Type</label>
                                <select name="package_type" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="">Not set</option>
                                    <option value="package_a" {{ old('package_type', $store->package_type) === 'package_a' ? 'selected' : '' }}>Package A — Base Kit</option>
                                    <option value="package_b" {{ old('package_type', $store->package_type) === 'package_b' ? 'selected' : '' }}>Package B — Standard</option>
                                    <option value="package_c" {{ old('package_type', $store->package_type) === 'package_c' ? 'selected' : '' }}>Package C — Full Program</option>
                                    <option value="individual" {{ old('package_type', $store->package_type) === 'individual' ? 'selected' : '' }}>Individual Items</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Store Status</label>
                                <select name="status" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="pending" {{ old('status', $store->status) === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="approved" {{ old('status', $store->status) === 'approved' ? 'selected' : '' }}>Approved / Active</option>
                                    <option value="submitted_to_admin" {{ old('status', $store->status) === 'submitted_to_admin' ? 'selected' : '' }}>Finalized / In Production</option>
                                    <option value="declined" {{ old('status', $store->status) === 'declined' ? 'selected' : '' }}>Declined</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Pricing Status</label>
                                <select name="pricing_approved" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="0" {{ !old('pricing_approved', $store->pricing_approved) ? 'selected' : '' }}>Pending Coach Approval</option>
                                    <option value="1" {{ old('pricing_approved', $store->pricing_approved) ? 'selected' : '' }}>Approved by Coach</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Order Deadline</label>
                                <input type="date" name="order_deadline" value="{{ old('order_deadline', $store->order_deadline?->format('Y-m-d')) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description</label>
                                <textarea name="description" rows="3" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm" placeholder="Optional store description for the coach...">{{ old('description', $store->description) }}</textarea>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="btn btn-primary py-3 px-8 text-sm uppercase tracking-wider">Save Changes</button>
                            <a href="{{ route('admin.stores.export', $store) }}" class="btn btn-outline py-3 px-6 text-sm uppercase tracking-wider">Export CSV</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Branding & Artwork --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Branding & Artwork</h3>
                    <p class="text-xs text-slate-500 mt-1">Upload the organization logo and store cover image.</p>
                </div>
                <div class="p-5 space-y-6">
                    <!-- Logo Upload -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3">Organization Logo</h4>
                        <div class="flex items-start gap-5">
                            <div class="w-20 h-20 rounded-full bg-slate-100 border-2 border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                                @if($store->user->logo_path)
                                    <img src="{{ Str::startsWith($store->user->logo_path, 'http') ? $store->user->logo_path : Storage::url($store->user->logo_path) }}" alt="Logo" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <form action="{{ route('admin.store.logo', $store) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                                    @csrf
                                    <input type="file" name="logo" accept="image/jpeg,image/png,image/jpg,image/webp" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">
                                    <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-800 transition-colors shadow-sm whitespace-nowrap">Upload Logo</button>
                                </form>
                                <p class="text-[10px] text-slate-400 mt-2">Recommended: Square PNG with transparent background. Max 5MB.</p>
                                @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <!-- Cover Image Upload -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3">Store Cover Image</h4>
                        <div class="space-y-4">
                            @if($store->cover_image_path)
                                <div class="w-full h-32 rounded-xl border-2 border-slate-200 overflow-hidden relative group">
                                    <img src="{{ Storage::url($store->cover_image_path) }}" alt="Cover" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="text-white text-xs font-bold uppercase tracking-wider bg-black/50 px-3 py-1 rounded-full backdrop-blur-sm">Current Cover</span>
                                    </div>
                                </div>
                            @endif
                            <form action="{{ route('admin.store.cover', $store) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                                @csrf
                                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">
                                <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-800 transition-colors shadow-sm whitespace-nowrap">Upload Cover</button>
                            </form>
                            <p class="text-[10px] text-slate-400">Recommended: Wide 16:9 aspect ratio image. Max 5MB.</p>
                            @error('cover_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Store Builder --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Store Builder</h3>
                        <p class="text-xs text-slate-500 mt-1">Add items to the store from the catalog.</p>
                    </div>
                </div>
                <div class="p-5">
                    @if($allDesigns->isEmpty())
                        <div class="text-center py-6">
                            <p class="text-sm text-slate-500 font-medium">No designs in the catalog yet.</p>
                        </div>
                    @else
                        <div x-data="{ catalogOpen: false }" class="mb-8">
                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-5 shadow-sm">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Design Catalog</h4>
                                    <p class="text-[10px] text-slate-500 mt-1">Browse and add custom designs to this team store.</p>
                                </div>
                                <button type="button" @click="catalogOpen = true" class="px-5 py-2.5 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#a11825] transition-colors shadow-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Browse Catalog
                                </button>
                            </div>

                            <!-- Catalog Modal -->
                            <div x-show="catalogOpen" x-cloak class="fixed inset-0 z-50 flex justify-center items-center">
                                <!-- Backdrop -->
                                <div x-show="catalogOpen" x-transition.opacity @click="catalogOpen = false" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

                                <!-- Modal Content -->
                                <div x-show="catalogOpen" 
                                     x-transition:enter="transition ease-out duration-300 transform"
                                     x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-200 transform"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                                     class="relative w-full max-w-6xl max-h-[90vh] bg-slate-50 rounded-2xl shadow-2xl flex flex-col mx-4 overflow-hidden">
                                    
                                    <!-- Header -->
                                    <div class="flex items-center justify-between p-6 bg-white border-b border-slate-200">
                                        <div>
                                            <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Design Catalog</h2>
                                            <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-wider font-bold">Add designs to the store</p>
                                        </div>
                                        <button type="button" @click="catalogOpen = false" class="text-slate-400 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100 transition-colors">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <!-- Grid Container -->
                                    <div class="flex-1 overflow-y-auto p-4 md:p-6">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                            @foreach($allDesigns as $design)
                                                @php 
                                                    $alreadyAdded = $store->items->pluck('design_catalog_id')->contains($design->id); 
                                                @endphp
                                                <div class="flex flex-col group {{ $alreadyAdded ? 'opacity-80' : '' }}">
                                                    <!-- Image Hero -->
                                                    <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center {{ $alreadyAdded ? 'ring-2 ring-secondary ring-offset-2' : '' }}">
                                                        @if(!empty($design->image_paths))
                                                            @if(count($design->image_paths) > 1)
                                                                <div class="w-full h-full relative group/slider" x-data="{ imgIdx: 0, imgs: {{ json_encode($design->image_paths) }}, imgInterval: null }" @mouseenter="imgInterval = setInterval(() => { imgIdx = (imgIdx + 1) % imgs.length }, 1500)" @mouseleave="clearInterval(imgInterval); imgIdx = 0">
                                                                    <img :src="imgs[imgIdx]" alt="" class="w-full h-full object-cover object-top transition-opacity duration-300">
                                                                </div>
                                                            @else
                                                                <img src="{{ $design->image_paths[0] }}" alt="" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                                                            @endif
                                                        @elseif($design->image_url)
                                                            <img src="{{ $design->image_url }}" alt="" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                                                        @else
                                                            <div class="text-slate-400 font-medium text-xs">No Image</div>
                                                        @endif
                                                    </div>

                                                    <!-- Card Content -->
                                                    <div class="pt-4 flex flex-col text-center items-center">
                                                        <span class="text-base font-medium text-red-600 mb-1">{{ $design->type_label }}</span>
                                                        <h3 class="text-lg font-bahnschrift font-semibold tracking-wide text-slate-900 mb-1 line-clamp-2" title="{{ $design->name }}">{{ $design->name }}</h3>
                                                        <div class="text-base text-slate-500 mb-3">Base Cost: <span class="text-slate-900 font-medium">${{ number_format($design->wholesale_price, 2) }}</span></div>
                                                        
                                                        <div class="mt-auto w-full">
                                                            @if($alreadyAdded)
                                                                <div class="w-full py-2.5 bg-secondary/10 text-secondary text-[11px] font-black uppercase tracking-widest rounded-xl text-center shadow-sm flex items-center justify-center gap-2">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                                    Added
                                                                </div>
                                                            @else
                                                                <form action="{{ route('admin.store.item.add', $store) }}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="design_catalog_id" value="{{ $design->id }}">
                                                                    <button type="submit" class="w-full py-2.5 bg-white border-2 border-slate-200 hover:border-slate-900 hover:bg-slate-900 hover:text-white text-slate-900 text-[11px] font-black uppercase tracking-widest rounded-xl transition-all">
                                                                        Add to Store
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($store->items->isNotEmpty())
                    <div class="space-y-4 border-t border-slate-200 pt-6">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Current Store Items</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($store->items as $item)
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex flex-col">
                                    <div class="aspect-[4/5] bg-white rounded-lg mb-3 overflow-hidden">
                                        @if(!empty($item->image_paths))
                                            <img src="{{ $item->image_paths[0] }}" class="w-full h-full object-cover object-top">
                                        @elseif($item->image_url)
                                            <img src="{{ $item->image_url }}" class="w-full h-full object-cover object-top">
                                        @endif
                                    </div>
                                    <div class="text-xs font-bold text-slate-900 truncate mb-1" title="{{ $item->name }}">{{ $item->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-medium mb-3">Retail: ${{ number_format($item->retail_price, 2) }}</div>
                                    <form action="{{ route('admin.store.item.remove', ['item' => $item->id]) }}" method="POST" class="mt-auto">
                                        @csrf
                                        <button type="submit" class="w-full py-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 text-[10px] font-black uppercase tracking-widest rounded transition-colors" onclick="return confirm('Remove this item from the store?')">Remove</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    @if($store->is_archived)
                        <form action="{{ route('admin.stores.unarchive', $store) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 py-3 px-8 text-sm uppercase tracking-wider">Unarchive Store</button>
                        </form>
                    @else
                        <form action="{{ route('admin.stores.archive', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this store?');">
                            @csrf
                            <button type="submit" class="w-full btn border border-slate-300 text-slate-500 bg-slate-100 hover:bg-slate-200 py-3 px-8 text-sm uppercase tracking-wider">Archive Store</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Set pricing for store items --}}
            @if($store->items->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Line-Item Pricing Checkout</h2>
                </div>
                <div class="p-5">
                    <form action="{{ route('admin.store.pricing.update', $store) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($store->items as $item)
                            <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                                <div class="font-bold text-slate-900 mb-2">{{ $item->name }}</div>
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Wholesale ($)</label>
                                        <input type="number" step="0.01" name="items[{{ $item->id }}][wholesale_price]" value="{{ old('items.'.$item->id.'.wholesale_price', $item->wholesale_price) }}" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Retail ($)</label>
                                        <input type="number" step="0.01" name="items[{{ $item->id }}][retail_price]" value="{{ old('items.'.$item->id.'.retail_price', $item->retail_price) }}" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary py-2 px-6 text-sm uppercase tracking-wider">Save Pricing</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Package Management --}}
            @php
                $packages = $store->items->filter->isPackage();
            @endphp
            @if($packages->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Package Configuration</h2>
                </div>
                <div class="p-5 space-y-6">
                    @foreach($packages as $package)
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                        <div class="font-bold text-slate-900 mb-3">{{ $package->name }}</div>
                        
                        @if($package->components->isNotEmpty())
                            <div class="mb-4 space-y-2">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">Included Items</label>
                                @foreach($package->components as $component)
                                    <div class="flex items-center justify-between bg-white border border-slate-200 p-2 rounded">
                                        <span class="text-sm font-medium text-slate-700">{{ $component->name }}</span>
                                        <form action="{{ route('admin.store.package.detach', [$store, $package, $component]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-[10px] font-black uppercase tracking-wider">Remove</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-slate-500 mb-4 italic">No items attached to this package yet.</div>
                        @endif

                        <form action="{{ route('admin.store.package.attach', [$store, $package]) }}" method="POST" class="flex gap-2">
                            @csrf
                            <select name="component_id" required class="flex-1 bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                <option value="">-- Select an item to add to this package --</option>
                                @foreach($store->items->where('id', '!=', $package->id) as $potentialComponent)
                                    @if(!$package->components->contains($potentialComponent->id) && !$potentialComponent->isPackage())
                                        <option value="{{ $potentialComponent->id }}">{{ $potentialComponent->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary py-2 px-4 text-xs uppercase tracking-wider">Add Item</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Order roster for this store --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Placed Orders ({{ $store->parentOrders->count() }} athletes)</h2>
                    <a href="{{ route('admin.stores.export', $store->id) }}" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wide rounded hover:bg-slate-50 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        Export CSV
                    </a>
                </div>
                @if($store->parentOrders->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-sm">No orders submitted yet.</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3 text-left">Athlete</th>
                                <th class="px-5 py-3 text-left">Items</th>
                                <th class="px-5 py-3 text-left">Submitted</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($store->parentOrders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-900">{{ $order->athlete_name }}</div>
                                    @if($order->is_edited)
                                        <div class="text-[10px] font-bold uppercase tracking-wide text-orange-500">Edited by {{ $order->edited_by }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ is_array($order->items_json) ? count($order->items_json) : 0 }} item(s)</td>
                                <td class="px-5 py-3 text-slate-500 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.order.edit', $order) }}" class="px-3 py-1.5 bg-white border border-secondary text-secondary text-xs font-bold rounded-lg hover:bg-secondary hover:text-white transition-colors">Edit</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Right: Quick stats --}}
        <div class="space-y-5">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 mb-4">Store Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Store Status</span><span class="font-bold text-primary uppercase">{{ str_replace('_', ' ', $store->status) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Pricing Status</span>
                        @if($store->pricing_approved)
                            <span class="font-bold text-green-600 uppercase text-xs inline-block ml-2">Approved</span>
                        @else
                            <span class="font-bold text-orange-500 uppercase text-xs inline-block ml-2">Pending</span>
                        @endif
                    </div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Athletes Ordered</span><span class="font-bold text-slate-900">{{ $store->parentOrders->count() }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Total Items</span><span class="font-bold text-slate-900">{{ $financials['total_items_sold'] ?? $store->parentOrders->sum(fn($o) => count(is_array($o->items_json) ? $o->items_json : [])) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Deadline</span><span class="font-bold text-slate-900">{{ $store->order_deadline?->format('M d, Y') ?? '—' }}</span></div>
                    
                    <div class="pt-3 mt-3 border-t border-slate-200 space-y-3">
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Total Sales</span><span class="font-bold text-slate-900">${{ number_format($financials['total_sales'] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Average Order</span><span class="font-bold text-slate-900">${{ number_format($financials['average_order_value'] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Due To TCA</span><span class="font-bold text-secondary">${{ number_format($financials['total_wholesale'] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Net Proceeds</span><span class="font-bold text-green-600">${{ number_format($financials['net_proceeds'] ?? 0, 2) }}</span></div>
                    </div>

                    <div class="pt-3 mt-3 border-t border-slate-200">
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Public URL</span>
                            <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="font-bold text-primary hover:underline text-xs truncate max-w-[150px]">View Store</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
