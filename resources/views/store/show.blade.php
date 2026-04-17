@extends('layouts.app')

@section('title', $store->name . ' | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-[50vh] flex flex-col pt-32 pb-20 justify-end overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-10 mix-blend-screen"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>

    <div class="relative z-10 max-w-[1400px] w-full mx-auto px-6 flex flex-col md:flex-row items-end justify-between gap-8">
        <div class="flex items-end gap-6">
            @if($store->user->logo_path)
                <div class="w-24 h-24 md:w-32 md:h-32 rounded-xl bg-slate-900 border-4 border-primary overflow-hidden flex items-center justify-center p-2 shadow-[0_0_30px_rgba(56,189,248,0.2)]">
                    <img src="{{ Storage::url($store->user->logo_path) }}" alt="Team Logo" class="max-w-full max-h-full object-contain">
                </div>
            @endif
            <div class="mb-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-secondary/20 border border-secondary/30 text-secondary text-xs font-bold uppercase tracking-widest rounded-full mb-3">
                    {{ $store->user->sport ?? 'Team Athletics' }}
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black uppercase tracking-tight text-white drop-shadow-lg">{{ $store->name }}</h1>
                <p class="text-slate-300 text-lg md:text-xl mt-2 max-w-2xl font-medium tracking-wide">Official Custom Apparel Storefront</p>
                <p class="text-primary text-sm font-bold uppercase tracking-widest mt-1">Managed By: Coach {{ $store->user->name }}</p>
            </div>
        </div>

        @if($store->order_deadline)
            <div class="glass-panel p-4 text-center border-white/10 shrink-0">
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Order Submission Deadline</p>
                <div class="text-2xl font-black text-secondary tracking-wider">{{ $store->order_deadline->format('M d, Y') }}</div>
            </div>
        @endif
    </div>
</div>

<div class="max-w-[1400px] mx-auto px-6 py-12">
    
    @if(session('success'))
        <div class="mb-12 p-6 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 font-bold text-lg flex items-center gap-4 animate-slide-up shadow-[0_0_40px_rgba(34,197,94,0.15)]">
            <svg class="w-8 h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-12 p-6 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 font-bold text-lg flex items-center gap-4 animate-slide-up">
            <svg class="w-8 h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_450px] gap-12">
        
        <!-- Left: Open Roster Transparency -->
        <div class="space-y-8 order-2 xl:order-1">
            <div class="glass-panel p-0 overflow-hidden">
                <div class="p-8 border-b border-white/5 bg-gradient-to-r from-slate-800 to-slate-900">
                    <h3 class="text-2xl font-black uppercase text-white tracking-tight">Active Roster Submissions</h3>
                    <p class="text-slate-400 text-sm mt-2">Public ledger of athletes who have successfully submitted their apparel sizing requests. If your name is not listed below, please use the intake form.</p>
                </div>

                <div class="p-8 bg-slate-900/50">
                    @if($store->parentOrders->isEmpty())
                        <div class="text-center p-12 border-2 border-dashed border-slate-700/50 rounded-2xl">
                            <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">No Roster Entries Yet</p>
                            <p class="text-slate-600 text-xs mt-2">Be the first to submit your sizing information.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($store->parentOrders as $order)
                                <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4 flex items-center justify-between group hover:border-primary/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center font-black text-primary border border-slate-700">
                                            {{ substr($order->athlete_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white uppercase">{{ $order->athlete_name }}</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5 font-bold tracking-widest uppercase">
                                                {{ is_array($order->items_json) ? count($order->items_json) . ' Item(s)' : '0 Items' }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="w-8 h-8 flex items-center justify-center bg-green-500/10 text-green-400 rounded-full border border-green-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Frictionless Order Form -->
        <div class="order-1 xl:order-2">
            @if($store->status === 'submitted_to_admin')
                <div class="glass-panel p-8 text-center border-red-500/30">
                    <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/50">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black uppercase text-white mb-2">Store is Locked</h3>
                    <p class="text-slate-400 text-sm">The coach has finalized the master roster. Production is currently underway. No further sizing modifications can be made at this time.</p>
                </div>
            @else
                <div class="glass-panel p-0 overflow-hidden sticky top-32 border-primary/30 shadow-[0_10px_40px_rgba(56,189,248,0.1)]">
                    <div class="p-8 border-b border-primary/20 bg-gradient-to-br from-primary/10 to-slate-900">
                        <h3 class="text-2xl font-black uppercase text-white tracking-tight flex items-center gap-3">
                            <span class="w-8 h-8 rounded bg-primary/20 flex items-center justify-center text-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </span>
                            Sizing Intake
                        </h3>
                        <p class="text-primary/80 text-sm mt-3 font-semibold leading-relaxed">No account creation required. Sign your athlete's name and designate sizes for catalog items below.</p>
                    </div>

                    <div class="p-8 bg-slate-900/80">
                        <form action="{{ route('store.order.submit', $store->slug) }}" method="POST" class="space-y-8">
                            @csrf

                            <!-- Athlete Identity -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Athlete Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="athlete_name" required placeholder="e.g. Jordan Smith" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3.5 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600 font-bold">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Biological Gender Base</label>
                                    <select name="gender" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3.5 text-white focus:border-primary focus:outline-none appearance-none font-medium">
                                        <option value="">Select Base Pattern...</option>
                                        <option value="Mens / Boys">Men's / Boy's Cut</option>
                                        <option value="Womens / Girls">Women's / Girl's Cut</option>
                                        <option value="Unisex">Unisex</option>
                                    </select>
                                </div>
                            </div>
                            
                            <hr class="border-slate-800">

                            <!-- Catalog Selections -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Required & Optional Uniform Pieces</label>
                                
                                <div class="space-y-3">
                                    @if($store->items->isEmpty())
                                        <div class="p-4 rounded-lg bg-slate-800/50 border border-slate-700 text-center text-sm text-slate-500">
                                            The coach has not added any catalog items to this store yet.
                                        </div>
                                    @else
                                        @foreach($store->items as $item)
                                            <!-- Item Row -->
                                            <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-700 bg-slate-800/40 relative" x-data="{ selected: false }">
                                                <div class="pt-1">
                                                    <input type="checkbox" id="item_{{ $item->id }}" name="items[{{ $item->id }}][selected]" value="1" x-model="selected" class="w-5 h-5 bg-slate-950 border-slate-700 rounded text-primary focus:ring-primary focus:ring-offset-slate-900 cursor-pointer">
                                                </div>
                                                <div class="flex-1">
                                                    <label for="item_{{ $item->id }}" class="block font-bold text-white cursor-pointer select-none">{{ $item->name }}</label>
                                                    <div class="text-[10px] uppercase font-bold tracking-widest text-primary mt-1">{{ $item->type }}</div>
                                                    
                                                    <input type="hidden" name="items[{{ $item->id }}][name]" value="{{ $item->name }}">
                                                    
                                                    <!-- Expandable Form Options -->
                                                    <div x-show="selected" x-transition class="mt-4 pt-4 border-t border-slate-700/50 grid grid-cols-2 gap-4" style="display: none;">
                                                        <div>
                                                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Select Size</label>
                                                            <select name="items[{{ $item->id }}][size]" class="w-full bg-slate-950 border border-slate-700 rounded p-2 text-xs text-white focus:border-primary appearance-none">
                                                                <option value="YS">Youth Small</option>
                                                                <option value="YM">Youth Medium</option>
                                                                <option value="YL">Youth Large</option>
                                                                <option value="AS">Adult Small</option>
                                                                <option value="AM" selected>Adult Medium</option>
                                                                <option value="AL">Adult Large</option>
                                                                <option value="AXL">Adult XL</option>
                                                                <option value="AXXL">Adult 2XL</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Qty</label>
                                                            <input type="number" name="items[{{ $item->id }}][qty]" value="1" min="1" class="w-full bg-slate-950 border border-slate-700 rounded p-2 text-xs text-white focus:border-primary">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <hr class="border-slate-800">

                            <!-- Notes & Submit -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Special Sizing Notes (Optional)</label>
                                    <textarea name="special_notes" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-sm text-white focus:border-primary focus:outline-none placeholder:text-slate-700" placeholder="e.g. Needs extra length on pants..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-full py-5 text-base font-black uppercase tracking-widest shadow-[0_10px_30px_rgba(56,189,248,0.25)]">
                                Submit Order Roster
                            </button>
                            <p class="text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-4">No Payment Processed on this Portal</p>
                        </form>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
