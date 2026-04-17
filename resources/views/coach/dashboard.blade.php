@extends('layouts.app')

@section('title', 'Coach Portal | The Commission Apparel')

@section('content')
<div class="max-w-[1600px] mx-auto px-6 py-8 mt-16">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-black uppercase text-white">Welcome, {{ $user->name }}</h1>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline py-2 px-4 shadow-none text-xs">Secure Logout</button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 font-medium flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(!$store)
        <!-- NO STORE STATE: Create Team Store -->
        <div class="glass-panel max-w-2xl mx-auto p-10 text-center animate-slide-up mt-12">
            <div class="w-20 h-20 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-6 border border-primary/50">
                <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h2 class="text-3xl font-black uppercase mb-2">Create Your Team Store</h2>
            <p class="text-slate-400 mb-8">To begin collecting orders from parents and athletes, you must initialize your digital storefront.</p>

            <form action="{{ route('coach.store.create') }}" method="POST" class="space-y-6 text-left">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Official Store Title</label>
                    <input type="text" name="name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:outline-none" placeholder="e.g. {{ $user->organization }} Track Store">
                </div>
                
                <!-- If we wanted logo upload on store creation we could put it here -->

                <button type="submit" class="btn btn-primary w-full py-4 text-sm font-bold uppercase tracking-widest shadow-[0_4px_20px_rgba(56,189,248,0.3)]">Submit Store Request</button>
            </form>
        </div>

    @elseif($store->status === 'pending')
        <!-- PENDING STATE -->
        <div class="glass-panel max-w-lg mx-auto p-12 text-center animate-slide-up mt-20 border-yellow-500/30">
            <div class="w-20 h-20 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-6 border border-yellow-500/50">
                <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-2xl font-black uppercase mb-3">Store Awaiting Approval</h2>
            <p class="text-slate-400 leading-relaxed">Your request for <strong>{{ $store->name }}</strong> has been sent to our production queue. An administrator will verify your request and activate the store builder tools shortly.</p>
        </div>
    
    @elseif($store->status === 'submitted_to_admin')
        <!-- LOCKED/SUBMITTED STATE -->
        <div class="glass-panel max-w-lg mx-auto p-12 text-center animate-slide-up mt-20 border-green-500/30">
            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6 border border-green-500/50">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h2 class="text-2xl font-black uppercase mb-3">Master Order Submitted</h2>
            <p class="text-slate-400 leading-relaxed">Your store (<strong>{{ $store->name }}</strong>) has been locked and the master manifest has been securely transmitted to The Commission Apparel. Production will begin shortly.</p>
        </div>

    @else
        <!-- ACTIVE STORE DASHBOARD -->
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8" x-data="{ activeTab: 'overview', sidebarOpen: false }">
            
            <!-- Mobile Toggle & Sidebar Container -->
            <div class="flex flex-col gap-4">
                <!-- Mobile Sidebar Toggle -->
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden glass-panel flex items-center justify-between w-full p-4 text-left font-bold uppercase tracking-wide text-slate-300 hover:text-white transition-colors">
                    <span class="flex items-center gap-2">
                        <svg x-show="!sidebarOpen" class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg x-show="sidebarOpen" style="display: none;" class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Coach Menu
                    </span>
                    <span class="text-[10px] bg-primary/20 text-primary px-2 py-1 rounded" x-text="activeTab.replace('_', ' ')"></span>
                </button>

                <!-- Sidebar -->
                <aside class="glass-panel self-start lg:sticky top-28 p-6 z-20" :class="{ 'hidden lg:block': !sidebarOpen, 'block': sidebarOpen }" x-cloak>
                    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-white/5">
                        @if($user->logo_path)
                            <div class="w-14 h-14 rounded bg-slate-800 border-2 border-primary overflow-hidden flex items-center justify-center p-1 shrink-0">
                                <img src="{{ Storage::url($user->logo_path) }}" alt="Organization Logo" class="max-w-full max-h-full object-contain">
                            </div>
                        @else
                            <div class="w-14 h-14 rounded-full bg-slate-800 border-2 border-primary overflow-hidden flex items-center justify-center p-2 shrink-0">
                                 <svg class="w-full h-full text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                        @endif
                        <div class="overflow-hidden">
                            <h3 class="font-bold text-lg uppercase tracking-tight leading-tight truncate">{{ $user->organization }}</h3>
                            <p class="text-xs text-primary font-bold tracking-widest uppercase truncate">{{ $user->sport }}</p>
                        </div>
                    </div>

                    <nav class="flex flex-col gap-2">
                        <button @click="activeTab = 'overview'; sidebarOpen = false;" :class="activeTab === 'overview' ? 'bg-primary/20 text-white border-l-2 border-primary' : 'text-slate-400 hover:bg-slate-800 hover:text-white'" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold tracking-wide uppercase transition-all rounded-r-lg text-left">
                            Dashboard
                        </button>
                        <button @click="activeTab = 'store_builder'; sidebarOpen = false;" :class="activeTab === 'store_builder' ? 'bg-primary/20 text-white border-l-2 border-primary' : 'text-slate-400 hover:bg-slate-800 hover:text-white'" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold tracking-wide uppercase transition-all rounded-r-lg text-left">
                            Team Store Builder
                        </button>
                        <button @click="activeTab = 'parent_orders'; sidebarOpen = false;" :class="activeTab === 'parent_orders' ? 'bg-primary/20 text-white border-l-2 border-primary' : 'text-slate-400 hover:bg-slate-800 hover:text-white'" class="flex items-center justify-between px-4 py-3 text-sm font-semibold tracking-wide uppercase transition-all rounded-r-lg text-left">
                            <span class="flex items-center gap-3">Parent Orders</span>
                            @if($store->parentOrders->count() > 0)
                                <span class="w-5 h-5 flex items-center justify-center bg-secondary text-white rounded-full text-[10px]">{{ $store->parentOrders->count() }}</span>
                            @endif
                        </button>
                        <button @click="activeTab = 'settings'; sidebarOpen = false;" :class="activeTab === 'settings' ? 'bg-primary/20 text-white border-l-2 border-primary' : 'text-slate-400 hover:bg-slate-800 hover:text-white'" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold tracking-wide uppercase transition-all rounded-r-lg text-left mt-8">
                            Order Deadline
                        </button>
                    </nav>
                </aside>
            </div>

            <!-- Main Content Area -->
            <div class="flex flex-col gap-8">
                <!-- Header Info -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 glass-panel p-6 bg-gradient-to-r from-slate-900 to-slate-800">
                    <div>
                        <h2 class="text-3xl font-black uppercase tracking-tight mb-1">{{ $store->name }}</h2>
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <span class="flex items-center gap-1.5 text-green-400 font-bold tracking-wider uppercase bg-green-400/10 px-3 py-1 rounded-full"><span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Open for Ordering</span>
                        </div>
                    </div>
                    
                    <div class="bg-slate-950 p-4 rounded-xl border border-white/5 flex flex-col items-center min-w-[200px]">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Order Deadline</p>
                        @if($store->order_deadline)
                            <div class="text-xl font-black text-secondary tracking-wider">{{ $store->order_deadline->format('M d, Y') }}</div>
                        @else
                            <div class="text-sm font-bold text-slate-500 uppercase">Not Set</div>
                        @endif
                    </div>
                </div>

                <!-- TAB: OVERVIEW -->
                <div x-show="activeTab === 'overview'" class="space-y-8 animate-fade-in">
                    <!-- Share Store Link -->
                    <div class="glass-panel flex flex-col xl:flex-row items-center justify-between gap-6 bg-gradient-to-r from-primary/10 to-transparent border-primary/30 p-6">
                        <div>
                            <h3 class="text-xl font-bold uppercase mb-1 flex items-center gap-2"><svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg> Public Store Link</h3>
                            <p class="text-slate-400 text-sm">Share this unique link with parents to collect orders anonymously. No payment processing is required.</p>
                        </div>
                        <div class="flex w-full xl:w-auto max-w-full overflow-hidden bg-slate-900 rounded-lg border border-slate-700">
                            <div class="px-4 py-3 font-mono text-sm text-primary truncate border-r border-slate-700 bg-slate-950/50 w-full xl:w-auto">
                                {{ url('/store/' . $store->slug) }}
                            </div>
                            <a href="/store/{{ $store->slug }}" target="_blank" class="btn-primary px-6 py-3 text-sm font-bold uppercase tracking-wider flex items-center whitespace-nowrap">Open</a>
                        </div>
                    </div>

                    <!-- Final Submission -->
                    <div class="glass-panel p-8 text-center bg-secondary/5 border-secondary/20 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-secondary/10 to-transparent opacity-50 pointer-events-none"></div>
                        <div class="relative z-10 w-full flex flex-col md:flex-row items-center justify-between gap-6 text-left">
                            <div>
                                <h3 class="text-2xl font-black uppercase text-white mb-2">Final Master Submission</h3>
                                <p class="text-slate-400 max-w-lg text-sm leading-relaxed">Once all parents have placed their orders required to complete the team roster, hit this button to lock the store and submit the finalized spreadsheet to The Commission Apparel.</p>
                            </div>
                            <form action="{{ route('coach.store.submit', $store) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Are you absolutely sure you want to finalize this store? You will not be able to modify orders after this!')" class="btn bg-white text-secondary hover:bg-slate-200 border-none px-8 py-4 text-base font-black uppercase tracking-widest shadow-[0_0_20px_rgba(255,255,255,0.2)] whitespace-nowrap">
                                    Finalize Master Order
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB: STORE BUILDER -->
                <div x-show="activeTab === 'store_builder'" class="glass-panel p-0 overflow-hidden animate-fade-in" style="display: none;">
                    <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                        <h3 class="text-xl font-bold uppercase text-white">Team Store Builder</h3>
                        <p class="text-slate-400 text-sm">Add catalog items to your store for parents to choose from.</p>
                    </div>

                    <!-- Add New Item Form -->
                    <div class="p-6 border-b border-white/5 bg-slate-900/50">
                        <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Add Custom Item</h4>
                        <form action="{{ route('coach.store.item.add', $store) }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                            @csrf
                            <div class="w-full">
                                <label class="text-[10px] uppercase text-slate-500 font-bold block mb-1">Item Title</label>
                                <input type="text" name="name" required placeholder="e.g. Sublimated Competition Jersey" class="w-full bg-slate-950 border border-slate-700 rounded p-3 text-sm text-white focus:border-primary focus:outline-none">
                            </div>
                            <div class="w-full md:w-64">
                                <label class="text-[10px] uppercase text-slate-500 font-bold block mb-1">Item Category</label>
                                <select name="type" required class="w-full bg-slate-950 border border-slate-700 rounded p-3 text-sm text-white focus:border-primary focus:outline-none appearance-none">
                                    <option value="uniform">Uniform</option>
                                    <option value="warmup">Warm-up Suit</option>
                                    <option value="accessory">Accessory (Bag/Hat)</option>
                                    <option value="merch">Fan Merch (Tee/Hoodie)</option>
                                </select>
                            </div>
                            <div class="w-full md:w-auto">
                                <button type="submit" class="btn btn-primary w-full py-3 px-6 h-[46px] block">Add</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="p-6">
                        @if($store->items->isEmpty())
                            <div class="text-center p-8 text-slate-500">
                                <p>You haven't added any items to your store yet.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                @foreach($store->items as $item)
                                    <div class="border border-slate-700 bg-slate-800/80 rounded-xl p-4 flex flex-col relative group overflow-hidden">
                                        <!-- Decorative accent based on type -->
                                        @if($item->type === 'uniform') <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div> @endif
                                        @if($item->type === 'warmup') <div class="absolute top-0 left-0 w-full h-1 bg-secondary"></div> @endif
                                        
                                        <h4 class="font-bold text-lg text-white mt-1">{{ $item->name }}</h4>
                                        <p class="text-slate-400 text-xs uppercase font-bold tracking-wider mb-6">{{ $item->type }}</p>
                                        
                                        <div class="mt-auto">
                                            <form action="{{ route('coach.store.item.remove', $item) }}" method="POST">
                                                @csrf
                                                <button onclick="return confirm('Are you sure you want to delete this item?')" class="w-full text-red-400 hover:text-white text-xs uppercase font-bold tracking-wider py-2 border border-red-500/30 rounded hover:bg-red-500 transition-colors">Permantly Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAB: PARENT ORDERS -->
                <div x-show="activeTab === 'parent_orders'" class="glass-panel p-0 overflow-hidden animate-fade-in" style="display: none;">
                    <div class="p-6 border-b border-slate-700 bg-slate-800/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-xl font-bold uppercase text-white">Parent Orders Ledger</h3>
                            <p class="text-slate-400 text-sm">Monitor public submissions from your shared order link.</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        @if($store->parentOrders->isEmpty())
                            <div class="p-8 text-center text-slate-500">
                                No orders have been submitted yet via the public link.
                            </div>
                        @else
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-900/50 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700">
                                        <th class="p-4 font-bold">Athlete Context</th>
                                        <th class="p-4 font-bold" style="width: 40%">Items Requested</th>
                                        <th class="p-4 font-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 text-sm">
                                    @foreach($store->parentOrders as $order)
                                    <tr class="hover:bg-slate-800/30 transition-colors">
                                        <td class="p-4 align-top">
                                            <div class="font-bold text-white uppercase">{{ $order->athlete_name }}</div>
                                            <div class="text-xs text-slate-400 mt-1">Gender Base: {{ $order->gender ?? 'Not Specified' }}</div>
                                            @if($order->special_notes)
                                                <div class="mt-2 text-[10px] text-slate-500 p-2 bg-slate-900 rounded italic">{{ $order->special_notes }}</div>
                                            @endif
                                        </td>
                                        <td class="p-4 align-top">
                                            <div class="flex flex-col gap-2">
                                                @if(is_array($order->items_json))
                                                    @foreach($order->items_json as $itemObj)
                                                        <div class="flex items-center justify-between bg-slate-950 border border-slate-700 rounded p-2 text-xs">
                                                            <span class="text-slate-300">{{ $itemObj['name'] ?? 'Unknown Item' }}</span>
                                                            <div class="flex gap-2 items-center">
                                                                <span class="font-mono bg-slate-800 px-2 py-0.5 rounded text-primary">Size: {{ $itemObj['size'] ?? 'N/A' }}</span>
                                                                <span class="font-mono text-slate-500">Qty: {{ $itemObj['qty'] ?? 1 }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="text-slate-500 italic">No valid items.</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4 align-top">
                                            <span class="bg-green-500/10 text-green-400 border border-green-500/20 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider flex items-center w-max gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Logged
                                            </span>
                                            <div class="text-[10px] text-slate-500 mt-2">{{ $order->created_at->format('M d, g:i A') }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                <!-- TAB: SETTINGS -->
                <div x-show="activeTab === 'settings'" class="glass-panel p-8 animate-fade-in" style="display: none;">
                    <h3 class="text-xl font-bold uppercase text-white mb-6 border-b border-slate-700 pb-2">Store Configuration</h3>
                    
                    <form action="{{ route('coach.store.deadline', $store) }}" method="POST" class="max-w-md">
                        @csrf
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Enforce Order Deadline</label>
                        <p class="text-slate-500 text-xs mb-4">Set the absolute cut-off date. Parents will see a countdown on the public store.</p>
                        
                        <div class="flex gap-4">
                            <input type="date" name="deadline" value="{{ $store->order_deadline ? $store->order_deadline->format('Y-m-d') : '' }}" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:outline-none">
                            <button type="submit" class="btn btn-outline py-3 px-6 whitespace-nowrap text-sm">Save</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    @endif
</div>
@endsection
