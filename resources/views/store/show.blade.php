@extends('layouts.app')

@section('title', $store->name . ' | The Commission Apparel')

@section('content')
{{-- Hero --}}
<div class="relative w-full min-h-[40vh] flex flex-col pt-32 pb-16 justify-end overflow-hidden">
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-10 mix-blend-screen"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>

    <div class="relative z-10 max-w-[1400px] w-full mx-auto px-6 flex flex-col md:flex-row items-end justify-between gap-6">
        <div class="flex items-end gap-5">
            @if($store->user->logo_path)
                <div class="w-20 h-20 rounded-xl bg-slate-900 border-2 border-white/20 overflow-hidden flex items-center justify-center p-2">
                    <img src="{{ Storage::url($store->user->logo_path) }}" alt="Team Logo" class="max-w-full max-h-full object-contain">
                </div>
            @endif
            <div class="mb-1">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 border border-white/20 text-white/80 text-xs font-bold uppercase tracking-widest rounded-full mb-2">
                    {{ $store->user->sport ?? 'Team Athletics' }}
                </div>
                <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-white">{{ $store->name }}</h1>
                <p class="text-slate-300 text-base mt-1.5">Official Custom Apparel Storefront · Coach {{ $store->user->name }}</p>
            </div>
        </div>
        @if($store->order_deadline)
            <div class="bg-white/10 backdrop-blur border border-white/20 rounded-xl p-4 text-center flex-shrink-0">
                <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mb-1">Order Deadline</p>
                <div class="text-xl font-black text-white">{{ $store->order_deadline->format('M d, Y') }}</div>
                @if($store->order_deadline->isPast())
                    <div class="text-red-400 text-[10px] font-bold uppercase mt-1">Deadline Passed</div>
                @else
                    <div class="text-green-400 text-[10px] font-bold uppercase mt-1">{{ $store->order_deadline->diffForHumans() }}</div>
                @endif
            </div>
        @endif
    </div>
</div>

<div class="max-w-[1400px] mx-auto px-6 py-12">
    @if(session('success'))
        <div class="mb-8 p-5 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold flex items-center gap-4">
            <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-8 p-5 rounded-xl bg-red-50 border border-red-200 text-red-700 font-bold flex items-center gap-4">
            <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_480px] gap-12">
        {{-- LEFT: Roster --}}
        <div class="space-y-8 order-2 xl:order-1">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-xl font-black uppercase text-slate-900 tracking-tight">Submitted Roster ({{ $store->parentOrders->count() }} Athletes)</h2>
                    <p class="text-slate-600 text-sm mt-1">Athletes listed below have successfully submitted their order. If your name is not shown, please use the form.</p>
                </div>
                <div class="p-6 bg-white">
                    @if($store->parentOrders->isEmpty())
                        <div class="text-center p-10 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                            <svg class="w-10 h-10 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">No Roster Entries Yet</p>
                            <p class="text-slate-400 text-xs mt-1.5">Be the first to submit your sizing.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($store->parentOrders as $order)
                            <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between shadow-sm hover:border-primary/40 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-black text-primary">{{ substr($order->athlete_name, 0, 1) }}</div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $order->athlete_name }}</div>
                                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ count(is_array($order->items_json) ? $order->items_json : []) }} items</div>
                                    </div>
                                </div>
                                <span class="w-7 h-7 flex items-center justify-center bg-green-100 text-green-600 rounded-full border border-green-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: Order Form --}}
        <div class="order-1 xl:order-2">
            @if($store->status === 'submitted_to_admin')
                <div class="bg-red-50 border border-red-200 rounded-xl shadow-sm p-8 text-center">
                    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-200">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-xl font-black uppercase text-slate-900 mb-2">Store Closed — In Production</h3>
                    <p class="text-slate-600 text-sm">The coach has finalized the order roster. Production is underway. No new orders can be taken at this time.</p>
                </div>
            @elseif($store->status !== 'approved' && $store->status !== 'submitted_to_admin')
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-8 text-center">
                    <h3 class="text-xl font-black uppercase text-amber-900 mb-2">Store Not Yet Active</h3>
                    <p class="text-slate-600 text-sm">This store is awaiting approval. Check back soon.</p>
                </div>
            @elseif(!$store->pricing_approved)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-8 text-center">
                    <h3 class="text-xl font-black uppercase text-amber-900 mb-2">Pricing In Review</h3>
                    <p class="text-slate-600 text-sm">The coach is currently reviewing the finalized pricing. The store will open shortly.</p>
                </div>
            @elseif($store->items->isEmpty())
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 text-center">
                    <h3 class="text-xl font-black uppercase text-slate-900 mb-2">Items Coming Soon</h3>
                    <p class="text-slate-600 text-sm">The coach hasn't added any items yet. Check back soon once designs are finalized.</p>
                </div>
            @else
                {{-- ═══ ORDER FORM ═══ --}}
                <div class="bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden sticky top-28">
                    <div class="p-6 border-b border-slate-100 bg-slate-50">
                        <h3 class="text-xl font-black uppercase text-slate-900 tracking-tight flex items-center gap-2">
                            <span class="w-7 h-7 rounded bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </span>
                            Size Selection Form
                        </h3>
                        <p class="text-slate-600 text-sm mt-2">No account or payment required. Submit your athlete's sizing below.</p>
                    </div>

                    <div class="p-6">
                        <form action="{{ route('store.order.submit', $store->slug) }}" method="POST" class="space-y-8">
                            @csrf

                            {{-- Athlete Info --}}
                            <div class="space-y-4" x-data="{
                                items: {
                                    @foreach($store->items as $item)
                                    '{{ $item->id }}': { selected: false, qty: 1, price: {{ $item->retail_price ?? 0 }} },
                                    @endforeach
                                },
                                get total() {
                                    let sum = 0;
                                    for(const key in this.items) {
                                        if(this.items[key].selected) {
                                            sum += this.items[key].qty * this.items[key].price;
                                        }
                                    }
                                    return sum.toFixed(2);
                                }
                            }">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Athlete Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="athlete_name" required placeholder="e.g. Jordan Smith" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-bold shadow-sm">
                                    @error('athlete_name')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Gender / Pattern Base</label>
                                    <select name="gender" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                        <option value="">Select cut pattern...</option>
                                        <option value="Mens / Boys">Men's / Boy's Cut</option>
                                        <option value="Womens / Girls">Women's / Girl's Cut</option>
                                        <option value="Unisex">Unisex</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="border-slate-200">

                            {{-- Per-Item Sizing --}}
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-4">Select Items & Sizes</label>
                                <p class="text-xs text-slate-500 mb-4">Check each item you are ordering and fill in the required sizing below it.</p>

                                <div class="space-y-4" x-data>
                                    @foreach($store->items as $item)
                                    @php
                                        $isBackpack = $item->type === 'backpack';
                                        $hasSizes   = in_array($item->type, \App\Models\DesignCatalog::sizedTypes());
                                        $hasNumber  = $item->designCatalog?->has_number_field ?? false;
                                        $hasNameField = $item->designCatalog?->has_name_field ?? false;
                                    @endphp

                                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm" x-data="{ currentItemId: '{{ $item->id }}' }">
                                        {{-- Item header (checkbox) --}}
                                        <label for="item_sel_{{ $item->id }}" class="flex items-center gap-4 p-4 cursor-pointer hover:bg-slate-50 transition-colors" @click="items[currentItemId].selected = document.getElementById('item_sel_{{ $item->id }}').checked">
                                            <input type="checkbox" id="item_sel_{{ $item->id }}" name="items[{{ $item->id }}][selected]" value="1" x-model="items[currentItemId].selected" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary shadow-sm cursor-pointer">
                                            <div class="flex-1">
                                                <div class="font-bold text-slate-900 text-lg">{{ $item->name }}</div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs font-bold uppercase tracking-wider text-primary">{{ str_replace('_', ' ', $item->type) }}</span>
                                                    @if($item->retail_price > 0)
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-bold">${{ number_format($item->retail_price, 2) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($item->image_url)
                                                <img src="{{ $item->image_url }}" alt="" class="w-14 h-14 object-contain rounded-lg border border-slate-200 bg-slate-50 flex-shrink-0">
                                            @endif
                                        </label>

                                        <input type="hidden" name="items[{{ $item->id }}][name]" value="{{ $item->name }}">
                                        <input type="hidden" name="items[{{ $item->id }}][type]" value="{{ $item->type }}">

                                        {{-- Expandable sizing panel --}}
                                        <div x-show="items[currentItemId].selected" x-transition class="border-t border-slate-200 bg-slate-50 p-4" style="display:none;">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                                @if($isBackpack || $hasNameField)
                                                {{-- Backpack / personalized items: name input --}}
                                                <div class="sm:col-span-2">
                                                    <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1.5">Name on {{ str_replace('_', ' ', ucfirst($item->type)) }}</label>
                                                    <input type="text" name="items[{{ $item->id }}][name_on_item]" placeholder="Enter first and last name" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm font-medium">
                                                </div>
                                                @endif

                                                @if($hasSizes)
                                                {{-- Standard sized items --}}
                                                <div>
                                                    <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1.5">Size</label>
                                                    <select name="items[{{ $item->id }}][size]" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm text-sm">
                                                        <optgroup label="Youth Sizes">
                                                            @foreach(['YXXS', 'YXS', 'YS', 'YM', 'YL', 'YXL'] as $s)
                                                                <option value="{{ $s }}">{{ $s }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                        <optgroup label="Adult Sizes">
                                                            @foreach(['AXS', 'AS', 'AM', 'AL', 'AXL', 'A2XL', 'A3XL'] as $s)
                                                                <option value="{{ $s }}" {{ $s === 'AM' ? 'selected' : '' }}>{{ $s }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    </select>
                                                </div>
                                                @endif

                                                @if($hasNumber)
                                                {{-- Player number --}}
                                                <div>
                                                    <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1.5">Player Number (optional)</label>
                                                    <input type="text" name="items[{{ $item->id }}][number]" placeholder="e.g. 24" maxlength="3" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                                </div>
                                                @endif

                                                <div>
                                                    <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1.5">Quantity</label>
                                                    <input type="number" name="items[{{ $item->id }}][qty]" value="1" min="1" max="5" x-model.number="items[currentItemId].qty" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <hr class="border-slate-200">

                            {{-- Notes & Submit --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Special Sizing Notes (Optional)</label>
                                    <textarea name="special_notes" rows="2" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-sm text-slate-900 focus:border-primary focus:outline-none placeholder:text-slate-400 shadow-sm" placeholder="e.g. Needs extra length on pants..."></textarea>
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-center justify-between">
                                <span class="text-sm font-black uppercase tracking-widest text-slate-600">Estimated Total</span>
                                <span class="text-3xl font-black text-slate-900">$<span x-text="total">0.00</span></span>
                            </div>

                            <button type="submit" class="btn btn-primary w-full py-5 text-base font-black uppercase tracking-widest hover:-translate-y-0.5 transition-transform shadow-[0_8px_24px_rgba(26,86,204,0.25)]">
                                Submit My Order
                            </button>
                            <p class="text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-3">Total is for review only · No payment processed today</p>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
