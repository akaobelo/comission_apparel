@extends('layouts.app')
@section('title', 'Coach Portal | The Commission Apparel')
@section('content')
<div class="max-w-[1400px] mx-auto px-6 pb-8" style="padding-top: clamp(2rem, 10vw, 7rem);">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div class="flex items-center gap-5">
            {{-- Profile Logo / Uploader --}}
            <div x-data="{ openLogoModal: false }" class="relative">
                <button @click="openLogoModal = true" class="w-16 h-16 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center overflow-hidden hover:border-primary transition-colors group relative shadow-sm">
                    @if($user->logo_path)
                        <img src="{{ Str::startsWith($user->logo_path, 'http') ? $user->logo_path : asset('storage/' . $user->logo_path) }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <span class="text-xl font-black text-slate-400 group-hover:text-primary transition-colors">{{ substr($user->organization ?? $user->name, 0, 1) }}</span>
                    @endif
                    <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </button>

                <!-- Modal -->
                <div x-show="openLogoModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                    <div @click.away="openLogoModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 animate-slide-up mx-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-black uppercase text-slate-900">Update Organization Logo</h3>
                            <button @click="openLogoModal = false" class="text-slate-400 hover:text-red-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form action="{{ route('coach.profile.logo') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Upload New Logo</label>
                                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex gap-3 text-left">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div class="text-xs text-blue-800">
                                        <strong class="block uppercase tracking-wider mb-1">Display Note</strong>
                                        Your logo will be displayed in a circular frame. For best results, use a <strong>square PNG with a transparent background</strong>. Max size: 5MB.
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-full py-3 uppercase tracking-widest text-xs font-bold shadow-md">Upload & Save</button>
                        </form>
                    </div>
                </div>
            </div>

            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 border border-primary/20 text-primary text-xs font-bold uppercase tracking-widest rounded-full mb-2">
                    Coach Portal
                </div>
                <h1 class="text-3xl font-black uppercase text-slate-900">Welcome, {{ $user->name }}</h1>
                <p class="text-slate-600 text-sm mt-1">{{ $user->organization }} · {{ $user->sport }}</p>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline bg-secondary py-2 px-4 text-xs uppercase tracking-wider text-white">Sign Out</button>
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


    @php
        $totalAthletes = $store ? $store->parentOrders->count() : 0;
        $isLocked = $store && $store->status === 'submitted_to_admin';
    @endphp

    <div x-data="{ activeCoachTab: '{{ !$store ? 'create_order' : 'overview' }}' }" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-2 inline-flex gap-2">
            <button
                type="button"
                @click="activeCoachTab = 'create_order'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'create_order' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Create An Order
            </button>
            <button
                type="button"
                @click="activeCoachTab = 'overview'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'overview' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Store Overview
            </button>
            @if($store && in_array($store->status, ['approved', 'submitted_to_admin']))
            <button
                type="button"
                @click="activeCoachTab = 'sales'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'sales' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Sales
            </button>
            @endif
        </div>

        {{-- ════ CREATE AN ORDER TAB ════ --}}
        <div x-show="activeCoachTab === 'create_order'" x-cloak class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-8">
            <div class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-lg border border-slate-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Direct Order Builder</h2>
                            <p class="text-sm text-slate-600 mt-1">Place direct orders for your organization without requiring a public team store.</p>
                        </div>
                    </div>
                    
                    <div class="p-6" x-data="{ orderMode: 'person' }">
                        <div class="flex gap-4 mb-6">
                            <button type="button" @click="orderMode = 'person'" class="flex-1 py-2 px-3 rounded-lg border-2 transition-all font-bold text-xs uppercase tracking-wider text-center" :class="orderMode === 'person' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-500 hover:border-slate-300'">
                                Order by Person(s)
                            </button>
                            <button type="button" @click="orderMode = 'item'" class="flex-1 py-2 px-3 rounded-lg border-2 transition-all font-bold text-xs uppercase tracking-wider text-center" :class="orderMode === 'item' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-500 hover:border-slate-300'">
                                Order by Item(s)
                            </button>
                        </div>

                        <form action="{{ route('coach.direct-order.submit') }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_type" x-model="orderMode">
                            
                            {{-- By Person Fields --}}
                            <div x-show="orderMode === 'person'" class="space-y-4 mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Athlete Details</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">First Name</label>
                                        <input type="text" name="athlete_first_name" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Last Name</label>
                                        <input type="text" name="athlete_last_name" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Gender</label>
                                        <select name="gender" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Unisex">Unisex</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jersey # (Opt)</label>
                                        <input type="text" name="jersey_number" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jersey Name (Opt)</label>
                                        <input type="text" name="jersey_name" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                </div>
                            </div>

                            {{-- By Item Fields (Bulk) --}}
                            <div x-show="orderMode === 'item'" class="space-y-4 mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Bulk Order Details</h3>
                                <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm p-4 rounded-lg">
                                    <p class="font-bold mb-1">Bulk Order Mode</p>
                                    <p>Select the items below and enter the desired quantities and sizes. This will be added to your draft as a bulk entry.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Gender</label>
                                    <select name="gender" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                        <option value="Unisex">Unisex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>

                            <div x-data="{ search: '', isExpanded: true }" class="mb-6">
                                <button type="button" @click="isExpanded = !isExpanded" class="w-full flex items-center justify-between px-4 py-3 bg-slate-100 hover:bg-slate-200 transition-colors rounded-t-xl border border-slate-200">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Select Assigned Items <span class="bg-slate-300 text-slate-800 px-2 py-0.5 rounded-full ml-2">{{ $assignedDesigns->count() }}</span></h3>
                                    <svg class="w-5 h-5 text-slate-500 transition-transform" :class="isExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                
                                <div x-show="isExpanded" x-collapse class="border-x border-b border-slate-200 rounded-b-xl p-4 bg-white">
                                    @if($assignedDesigns->count() > 0)
                                    <div class="mb-4">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </div>
                                            <input type="text" x-model="search" placeholder="Search assigned items..." class="w-full bg-slate-50 border border-slate-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                                        @forelse($assignedDesigns as $design)
                                            @php
                                                $sizedTypes = \App\Models\DesignCatalog::sizedTypes();
                                                $types = $design->types ?? [];
                                                $hasSizes = count(array_intersect($types, $sizedTypes)) > 0;
                                            @endphp
                                            <div x-data="{ selected: false }" x-show="search === '' || '{{ strtolower(addslashes($design->name)) }}'.includes(search.toLowerCase()) || selected" class="border border-slate-200 rounded-xl p-4 transition-colors" :class="selected ? 'bg-primary/5 border-primary' : 'bg-white hover:border-slate-300'">
                                                <div class="flex items-start gap-4">
                                                    <div class="pt-1">
                                                        <input type="checkbox" name="items[{{ $design->id }}][selected]" value="1" x-model="selected" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                                                    </div>
                                                    <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0 flex items-center justify-center">
                                                        @if(!empty($design->image_paths))
                                                            <img src="{{ Str::startsWith($design->image_paths[0], 'http') ? $design->image_paths[0] : asset('storage/' . $design->image_paths[0]) }}" alt="{{ $design->name }}" class="w-full h-full object-cover">
                                                        @else
                                                            <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="font-bold text-slate-900 leading-tight font-heading">{{ $design->name }}</h4>
                                                        <div class="text-xs text-slate-500 mt-1 uppercase tracking-wider">{{ implode(', ', $types) }}</div>
                                                        
                                                        <div x-show="selected" x-collapse class="mt-4 pt-4 border-t border-slate-200/60">
                                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                                <div>
                                                                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Quantity</label>
                                                                    <input type="number" name="items[{{ $design->id }}][qty]" value="1" min="1" class="w-full border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" :required="selected">
                                                                </div>
                                                                @foreach($types as $t)
                                                                    @if(in_array($t, $sizedTypes))
                                                                    <div>
                                                                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">{{ $t }} Size</label>
                                                                        <select name="items[{{ $design->id }}][sizes][{{ $t }}]" class="w-full border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" :required="selected">
                                                                            <option value="">Select Size</option>
                                                                            @foreach(\App\Models\DesignCatalog::sizeChart() as $size)
                                                                                <option value="{{ $size }}">{{ $size }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-4 bg-slate-50 rounded-lg text-center text-slate-500 text-sm">
                                                You have no assigned designs yet. Please contact The Commission Apparel.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8">
                                <button type="submit" class="btn btn-primary w-full py-3 text-xs font-bold uppercase tracking-widest shadow-md">
                                    Add To Draft
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Draft Orders & Submissions --}}
            <div class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 mb-4 flex items-center justify-between">
                        Draft Orders
                        <span class="bg-amber-100 text-amber-800 text-xs py-1 px-2 rounded-md">{{ $directOrders->where('status', 'Draft')->count() }}</span>
                    </h2>
                    
                    @if($directOrders->where('status', 'Draft')->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm text-slate-500">Your draft is empty.</p>
                        </div>
                    @else
                        <div class="space-y-3 mb-6 max-h-[300px] overflow-y-auto pr-2">
                            @foreach($directOrders->where('status', 'Draft') as $draft)
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="font-bold text-sm text-slate-900">{{ $draft->athlete_name }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase">{{ $draft->gender }}</div>
                                    </div>
                                    <ul class="text-xs text-slate-600 space-y-1">
                                        @foreach($draft->items_json as $item)
                                            <li>{{ $item['qty'] }}x {{ $item['name'] }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-slate-100 pt-5">
                            <form action="{{ route('coach.direct-order.finalize') }}" method="POST" x-data="{ confirming: false }">
                                @csrf
                                <button type="button" x-show="!confirming" @click="confirming = true" class="btn bg-secondary hover:bg-[#a11825] text-white w-full py-3 text-xs font-bold uppercase tracking-widest shadow-sm">
                                    Submit Draft To Production
                                </button>
                                <div x-show="confirming" x-cloak class="bg-red-50 border border-red-200 p-4 rounded-xl">
                                    <p class="text-xs text-red-800 font-bold mb-3 text-center">Are you sure? This is a final submission and cannot be undone.</p>
                                    <div class="flex gap-2">
                                        <button type="button" @click="confirming = false" class="flex-1 py-2 bg-white border border-slate-300 text-slate-600 rounded-lg text-xs font-bold uppercase">Cancel</button>
                                        <button type="submit" class="flex-1 py-2 bg-secondary text-white rounded-lg text-xs font-bold uppercase shadow-sm">Confirm Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                @if($directOrderBatches->filter(fn($v, $k) => $k !== '')->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 mb-4">Submitted Batches</h2>
                    <div class="space-y-3">
                        @foreach($directOrderBatches->filter(fn($v, $k) => $k !== '') as $batchId => $batchOrders)
                            <div class="border border-slate-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold uppercase text-slate-500">{{ $batchOrders->first()->created_at->format('M d, Y') }}</span>
                                    <span class="bg-blue-100 text-blue-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded">Submitted</span>
                                </div>
                                <div class="text-sm font-bold text-slate-900 mb-3">{{ $batchOrders->count() }} Orders in Batch</div>
                                <div class="flex gap-3 items-center">
                                    <a href="{{ route('coach.direct-order.export', $batchId) }}" class="text-xs font-bold text-primary hover:text-secondary uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download CSV
                                    </a>
                                    <form action="{{ route('coach.direct-order.archive', $batchId) }}" method="POST" onsubmit="return confirm('Archive this batch? You can still view it in the archived section.')">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-slate-500 hover:text-slate-700 uppercase tracking-wider flex items-center gap-1">
                                            Archive
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($archivedOrderBatches->filter(fn($v, $k) => $k !== '')->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mt-6 opacity-75 hover:opacity-100 transition-opacity">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-500 mb-4 flex items-center justify-between">
                        Archived Batches
                        <span class="bg-slate-100 text-slate-600 text-xs py-1 px-2 rounded-md">{{ $archivedOrderBatches->filter(fn($v, $k) => $k !== '')->count() }}</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($archivedOrderBatches->filter(fn($v, $k) => $k !== '') as $batchId => $batchOrders)
                            <div class="border border-slate-200 bg-slate-50 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold uppercase text-slate-500">{{ $batchOrders->first()->created_at->format('M d, Y') }}</span>
                                    <span class="bg-slate-200 text-slate-600 text-[10px] font-bold uppercase px-2 py-0.5 rounded">Archived</span>
                                </div>
                                <div class="text-sm font-bold text-slate-700 mb-3">{{ $batchOrders->count() }} Orders in Batch</div>
                                <a href="{{ route('coach.direct-order.export', $batchId) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download CSV
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ════ STORE OVERVIEW TAB ════ --}}
        <div x-show="activeCoachTab === 'overview'" x-cloak>
            @if(!$store)
    <div class="max-w-2xl mx-auto">
        @if($assignedDesigns->isEmpty())
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6 flex gap-4">
            <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-bold text-blue-900">Waiting on Designs</p>
                <p class="text-sm text-blue-700 mt-1">Please contact our design experts to begin creating your custom items. Once your designs are completed, submitted, and approved, they will appear here for you to add to your team store and price as you see fit.</p>
                <p class="text-sm text-blue-700 mt-1">In the meantime, you may proceed with the setting up your team store and can upload the designs once they have been approved.</p>

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
                    <select name="package_type" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        <option value="" disabled selected>Select a package type (Optional)...</option>
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
                                <button type="submit" class="bg-secondary hover:bg-[#a11825] text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wide transition-colors shadow-sm">I Approve This Pricing</button>
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
                    <div class="flex gap-2">
                        <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase rounded-lg transition-colors">Share Link</a>
                        <a href="{{ route('coach.store.export', $store) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase rounded-lg transition-colors">Export CSV</a>
                        @if(!$isLocked && $totalAthletes > 0)
                        <div x-data="{ openSubmitModal: false }">
                            <button @click="openSubmitModal = true" type="button" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold uppercase rounded-lg hover:bg-slate-700 transition-colors">Approve/Submit</button>
                            
                            <!-- Submit Confirmation Modal -->
                            <div x-show="openSubmitModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                                <div @click.away="openSubmitModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 animate-slide-up mx-4 relative">
                                    <button @click="openSubmitModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-red-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    
                                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    
                                    <h3 class="text-xl font-black uppercase tracking-tight text-slate-900 text-center mb-2">Finalize Master Order</h3>
                                    
                                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl text-sm text-slate-700 mb-6 space-y-3">
                                        <p>Please review your order for accuracy before finalizing.</p>
                                        <p><strong>Instructions:</strong> Use the <strong>Export CSV</strong> option to download and verify all items, sizes, and quantities. Submit once you have confirmed everything is 100% accurate.</p>
                                        <p class="text-red-600 font-bold text-xs uppercase tracking-widest mt-2">The store will be closed to new orders.</p>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <button @click="openSubmitModal = false" type="button" class="flex-1 py-3 bg-white border border-slate-300 text-slate-700 font-bold uppercase text-xs tracking-wider rounded-lg hover:bg-slate-50 transition-colors">Review Again</button>
                                        <form action="{{ route('coach.store.submit', $store) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full py-3 bg-secondary text-white font-bold uppercase text-xs tracking-wider rounded-lg hover:bg-[#a11825] transition-colors">Submit Final</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif($isLocked)
                        <form action="{{ route('coach.store.reopen', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to re-open the store? Parents will be able to submit orders again.')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold uppercase rounded-lg hover:bg-slate-700 transition-colors">Re-Open Store</button>
                        </form>
                        @endif
                    </div>
            </div>

            {{-- Order Progress Tracker --}}
            <div x-data="{ expanded: false }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <button type="button" @click="expanded = !expanded" class="w-full p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between hover:bg-slate-100 transition-colors focus:outline-none">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-black uppercase tracking-tight text-slate-900">Order Progress</h3>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <span class="text-2xl font-black text-primary">{{ $totalAthletes }}</span>
                </button>
                <div x-show="expanded" x-cloak>
                    <div class="p-5">
                        @if($totalAthletes === 0)
                            <div class="text-center py-6 text-slate-500 text-sm">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                No orders received yet. Share your store link with your team.
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
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
            </div>

            {{-- Set Deadline --}}
            @if(!$isLocked)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 mb-4">Order Deadline</h3>
                <form action="{{ route('coach.store.deadline', $store) }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="date" name="deadline" value="{{ $store->order_deadline?->format('Y-m-d') }}" class="flex-1 bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-slate-900 focus:border-primary focus:outline-none shadow-sm text-sm">
                    <button type="submit" class="px-5 py-2.5 bg-secondary text-white text-sm font-bold rounded-lg hover:bg-[#a11825] transition-colors">Set Deadline</button>
                </form>
            </div>
            @endif

            {{-- Current Store Items (Moved from Right Column) --}}
            <div x-data="{ expandedItems: true }" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <button type="button" @click="expandedItems = !expandedItems" class="w-full p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between hover:bg-slate-100 transition-colors focus:outline-none">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-black uppercase tracking-tight text-slate-900">Current Store Items</h3>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="expandedItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <span class="text-2xl font-black text-primary">{{ $store->items->count() }}</span>
                </button>
                <div x-show="expandedItems" x-cloak>
                    <div class="p-5 bg-slate-50">
                        @if($store->items->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($store->items as $item)
                                <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg shadow-sm group hover:border-primary transition-colors">
                                    <div class="flex-1 pr-3">
                                        <div class="text-base font-bahnschrift font-semibold tracking-wide text-slate-900">{{ $item->name }}</div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mt-1 flex gap-3 flex-wrap">
                                            <span>Type: <span class="text-primary">{{ $item->designCatalog ? $item->designCatalog->type_label : implode(', ', array_map(fn($t) => str_replace('_', ' ', $t), $item->types ?? [])) }}</span></span>
                                            <span>Manufacturer's Price: <span class="text-slate-700">${{ number_format($item->wholesale_price, 2) }}</span></span>
                                            <span>Store Price: <span class="text-green-700">${{ number_format($item->retail_price, 2) }}</span></span>
                                        </div>
                                        <div class="mt-2">
                                            <form action="{{ route('coach.store.item.markup', $item) }}" method="POST" class="flex flex-wrap items-end gap-2">
                                                @csrf
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Enter your retail price ($)</label>
                                                    <input
                                                        type="number"
                                                        name="retail_price"
                                                        min="{{ $item->wholesale_price }}"
                                                        step="0.01"
                                                        value="{{ number_format($item->retail_price, 2, '.', '') }}"
                                                        class="w-24 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Sort Order</label>
                                                    <input
                                                        type="number"
                                                        name="sort_order"
                                                        value="{{ $item->sort_order }}"
                                                        class="w-16 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none"
                                                    >
                                                </div>
                                                <button type="submit" class="px-3 py-1.5 bg-secondary text-white text-[10px] font-bold uppercase tracking-wider rounded-lg hover:bg-[#a11825] transition-colors">
                                                    Update
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <form action="{{ route('coach.store.item.remove', $item) }}" method="POST" onsubmit="return confirm('Remove this item from your store?')">
                                        @csrf
                                        <button class="text-white transition-colors px-3 py-1.5 bg-slate-900 border border-slate-900 rounded-lg hover:bg-black flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Remove
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
            </div>
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
                        <button onclick="const input = document.getElementById('storeUrl'); input.select(); if(navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(input.value); } else { document.execCommand('copy'); } this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 2000)" class="px-3 py-2 bg-secondary text-white text-xs font-bold rounded-lg hover:bg-[#a11825] transition-colors">Copy</button>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-2">Share this link with your athletes and parents.</p>
                @endif
            </div>

            {{-- Branding & Artwork --}}
            @if(!$isLocked)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Branding & Artwork</h3>
                    <p class="text-xs text-slate-500 mt-1">Customize how your store appears to parents.</p>
                </div>
                
                {{-- Organization Logo --}}
                <div class="p-5 border-b border-slate-100">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-3">Organization Logo (Circle Display)</h4>
                    <form action="{{ route('coach.profile.logo') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                @if($user->logo_path)
                                    <img src="{{ Str::startsWith($user->logo_path, 'http') ? $user->logo_path : asset('storage/' . $user->logo_path) }}" alt="Logo" class="w-full h-full object-cover">
                                @else
                                    <span class="text-lg font-black text-slate-400">{{ substr($user->organization ?? $user->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="flex-1 flex gap-2 flex-col sm:flex-row">
                                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" required class="flex-1 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-300 rounded-lg bg-white focus:outline-none">
                                <button type="submit" class="px-4 py-2 bg-secondary text-white text-xs font-bold uppercase rounded-lg hover:bg-[#a11825] transition-colors">Save Logo</button>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-500">Max 5MB. Square PNG with transparent background recommended.</p>
                        @error('logo')<p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p>@enderror
                    </form>
                </div>

                {{-- Store Cover --}}
                <div class="p-5">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-3">Store Cover Image (Wide Display)</h4>
                    <form action="{{ route('coach.store.cover', $store) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if($store->cover_image_path)
                            <div class="mb-3 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 aspect-[3/1] relative">
                                <img src="{{ Storage::url($store->cover_image_path) }}" alt="Cover" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="flex gap-2 flex-col sm:flex-row">
                            <input type="file" name="cover_image" accept="image/*" required class="flex-1 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-300 rounded-lg bg-white focus:outline-none">
                            <button type="submit" class="px-4 py-2 bg-secondary text-white text-xs font-bold uppercase rounded-lg hover:bg-[#a11825] transition-colors">Save Cover</button>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-2">Max 5MB. High-resolution landscape image recommended.</p>
                        @error('cover_image')<p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p>@enderror
                    </form>
                </div>
            </div>
            @endif

            {{-- Team Builder: Add Items --}}
            @if(!$isLocked)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Store Builder</h3>
                    <p class="text-xs text-slate-500 mt-1">Add your approved custom designs to the store.</p>
                </div>
                <div class="p-5">
                    @if($globalCatalog->isEmpty())
                        <div class="text-center py-6">
                            <p class="text-sm text-slate-500 font-medium">No designs in the catalog yet.</p>
                        </div>
                    @else
                        <div x-data="{ catalogOpen: false }" class="mb-8">
                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-5 shadow-sm">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Your Assigned Designs</h4>
                                    <p class="text-[10px] text-slate-500 mt-1">Browse and add custom designs to your team store.</p>
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
                                            <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Assigned Design Catalog</h2>
                                            <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-wider font-bold">Parents will not see your wholesale costs</p>
                                        </div>
                                        <button type="button" @click="catalogOpen = false" class="text-slate-400 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100 transition-colors">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <!-- Grid Container -->
                                    <div class="flex-1 overflow-y-auto p-4 md:p-6">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                            @forelse($assignedDesigns as $design)
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
                                                                    
                                                                    <!-- Manual Navigation Arrows -->
                                                                    <button type="button" @click.stop="imgIdx = (imgIdx - 1 + imgs.length) % imgs.length; clearInterval(imgInterval)" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/80 text-slate-700 flex items-center justify-center shadow hover:bg-white transition-colors lg:opacity-0 lg:group-hover/slider:opacity-100 focus:outline-none">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                                                    </button>
                                                                    <button type="button" @click.stop="imgIdx = (imgIdx + 1) % imgs.length; clearInterval(imgInterval)" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/80 text-slate-700 flex items-center justify-center shadow hover:bg-white transition-colors lg:opacity-0 lg:group-hover/slider:opacity-100 focus:outline-none">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                                    </button>

                                                                    <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 z-10">
                                                                        <template x-for="(img, idx) in imgs" :key="idx">
                                                                            <button type="button" @click.stop="imgIdx = idx; clearInterval(imgInterval)" class="w-1.5 h-1.5 rounded-full transition-colors shadow-sm focus:outline-none" :class="idx === imgIdx ? 'bg-secondary' : 'bg-white/60'"></button>
                                                                        </template>
                                                                    </div>
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
                                                        
                                                        <div class="mt-auto">
                                                            @if($alreadyAdded)
                                                                <div class="w-full py-2.5 bg-secondary/10 text-secondary text-[11px] font-black uppercase tracking-widest rounded-xl text-center shadow-sm flex items-center justify-center gap-2">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                                    Added
                                                                </div>
                                                            @else
                                                                <form action="{{ route('coach.store.item.add', $store) }}" method="POST">
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
                                            @empty
                                                <div class="col-span-full py-12 text-center">
                                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-200">
                                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">No Designs Assigned</h3>
                                                    <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">You do not currently have any approved designs assigned to your profile. Please contact The Commission Apparel to request designs.</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

@if($store && in_array($store->status, ['approved', 'submitted_to_admin']))
<div x-show="activeCoachTab === 'sales'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Total Sales</p>
                <p class="text-3xl font-black text-green-700">${{ number_format($salesSummary['total_sales'], 2) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Orders Submitted</p>
                <p class="text-3xl font-black text-slate-900">{{ $salesSummary['orders_count'] }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Average Order</p>
                <p class="text-3xl font-black text-primary">${{ number_format($salesSummary['average_order_value'], 2) }}</p>
                <p class="text-[10px] uppercase tracking-wider text-slate-500 mt-1">Items sold: {{ $salesSummary['total_items_sold'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white border-2 border-slate-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Due to The Commission Apparel</p>
                <p class="text-3xl font-black text-red-600">${{ number_format($salesSummary['total_wholesale'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border-2 border-green-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-green-600 mb-1">Net Proceeds</p>
                <p class="text-3xl font-black text-green-700">${{ number_format($salesSummary['net_proceeds'] ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Sales by Parent Order</h3>
            </div>
            @if(empty($salesSummary['order_rows']))
                <div class="p-8 text-center text-sm text-slate-500">No parent orders yet. Share your parent order link to start generating sales.</div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($salesSummary['order_rows'] as $row)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $row['athlete_name'] }}</p>
                                <p class="text-[10px] uppercase tracking-wider text-slate-500">{{ $row['items_count'] }} item(s) · {{ $row['submitted_at']->format('M d, Y') }}</p>
                            </div>
                            <p class="text-base font-black text-green-700">${{ number_format($row['order_total'], 2) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif

</div>
@endsection
