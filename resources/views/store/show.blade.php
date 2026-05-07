@extends('layouts.app')

@section('title', $store->name . ' | The Commission Apparel')

@section('content')
{{-- Hero --}}
<div class="relative w-full min-h-[40vh] flex flex-col pt-32 pb-16 justify-end overflow-hidden">
    <div class="absolute inset-0 bg-slate-950"></div>
    @if($store->cover_image_path)
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ Storage::url($store->cover_image_path) }}')"></div>
    @else
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center"></div>
    @endif
    <!-- Light gradient only at the bottom to ensure the white title text is readable -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>

    <div class="relative z-10 max-w-[1400px] w-full mx-auto px-6 flex flex-col md:flex-row items-end justify-between gap-6">
        <div class="flex items-end gap-5">
            @if($store->user->logo_path)
                <div class="w-20 h-20 rounded-full bg-slate-900 border-2 border-white/20 overflow-hidden flex items-center justify-center shadow-md">
                    <img src="{{ Str::startsWith($store->user->logo_path, 'http') ? $store->user->logo_path : Storage::url($store->user->logo_path) }}" alt="Team Logo" class="w-full h-full object-cover">
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

    <div class="max-w-6xl mx-auto space-y-8">
        @if($store->status === 'submitted_to_admin')
            <div class="bg-red-50 border border-red-200 rounded-2xl shadow-sm p-10 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5 border border-red-200">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-2xl font-black uppercase tracking-tight text-slate-900 mb-2">Store Closed — In Production</h3>
                <p class="text-slate-600 text-base max-w-lg mx-auto">The coach has finalized the order roster. Production is underway. No new orders can be taken at this time.</p>
            </div>
        @elseif($store->status !== 'approved' && $store->status !== 'submitted_to_admin')
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-10 text-center">
                <h3 class="text-2xl font-black uppercase tracking-tight text-amber-900 mb-2">Store Not Yet Active</h3>
                <p class="text-slate-600 text-base">This store is awaiting approval. Check back soon.</p>
            </div>
        @elseif(!$store->pricing_approved)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-10 text-center">
                <h3 class="text-2xl font-black uppercase tracking-tight text-amber-900 mb-2">Pricing In Review</h3>
                <p class="text-slate-600 text-base">The coach is currently reviewing the finalized pricing. The store will open shortly.</p>
            </div>
        @elseif($store->items->isEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-12 text-center">
                <h3 class="text-2xl font-black uppercase tracking-tight text-slate-900 mb-2">Items Coming Soon</h3>
                <p class="text-slate-600 text-base">The coach hasn't added any items yet. Check back soon once designs are finalized.</p>
            </div>
        @elseif($store->order_deadline && $store->order_deadline->isPast())
            <div class="bg-red-50 border border-red-200 rounded-2xl shadow-sm p-10 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5 border border-red-200">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-2xl font-black uppercase tracking-tight text-slate-900 mb-2">Deadline Passed</h3>
                <p class="text-slate-600 text-base max-w-lg mx-auto">The order deadline has passed. No new orders can be accepted at this time.</p>
            </div>
        @else
            {{-- ═══ NEW ORDER FORM GRID ═══ --}}
            <form action="{{ route('store.order.submit', $store->slug) }}" method="POST" 
                  @invalid.capture="athleteInfoOpen = true; setTimeout(() => document.getElementById('athlete-info-section').scrollIntoView({behavior: 'smooth', block: 'start'}), 100)"
                  x-data="{
                      athleteInfoOpen: {{ $errors->any() ? 'true' : 'false' }},
                      activeItemId: null,
                      slideOpen: false,
                      items: {
                          @foreach($store->items as $item)
                          '{{ $item->id }}': { selected: false, qty: 1 },
                          @endforeach
                      },
                      openPanel(id) {
                          this.activeItemId = id;
                          this.slideOpen = true;
                          document.body.style.overflow = 'hidden';
                      },
                      closePanel() {
                          this.slideOpen = false;
                          document.body.style.overflow = 'auto';
                          setTimeout(() => { this.activeItemId = null; }, 300);
                      }
                  }">
                @csrf

                {{-- Athlete Info --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm mb-4 overflow-hidden" id="athlete-info-section">
                    <button type="button" @click="athleteInfoOpen = !athleteInfoOpen" class="w-full flex items-center justify-between p-4 md:p-4 bg-white hover:bg-slate-50 transition-colors focus:outline-none text-left border-b border-transparent" :class="athleteInfoOpen ? 'border-slate-100 bg-slate-50/50' : ''">
                        <div>
                            <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Ready to Order?</h2>
                            <p class="text-xs font-bold text-slate-500 mt-1" x-show="!athleteInfoOpen"><span class="text-secondary font-black">CLICK HERE</span> to enter Athlete Information</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="hidden sm:inline-block text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/10 px-3 py-1 rounded-full border border-primary/20" x-show="!athleteInfoOpen">Required</span>
                            <svg class="w-6 h-6 text-slate-400 transition-transform duration-300" :class="athleteInfoOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    
                    <div x-show="athleteInfoOpen" x-transition.opacity class="px-6 md:px-8 pb-6 md:pb-8 pt-6">
                        <div class="mb-6 text-sm text-slate-600 space-y-3 border-l-4 border-secondary pl-4 py-1">
                        <p class="font-bold text-slate-900 uppercase">Athlete Information</p>
                        <p>Please enter your athlete's information below to begin your order. Once completed, you'll be able to select individual items or choose from our available packages.</p>
                        <p class="font-bold text-secondary">If you are ordering for multiple athletes, please note that a separate order must be completed for each athlete.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="athlete_first_name" required placeholder="e.g. Jordan" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-bold transition-all">
                            @error('athlete_first_name')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="athlete_last_name" required placeholder="e.g. Smith" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-bold transition-all">
                            @error('athlete_last_name')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">Gender / Pattern Base <span class="text-red-500">*</span></label>
                            <select name="gender" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none transition-all font-medium">
                                <option value="">Select cut pattern...</option>
                                <option value="Mens / Boys">Men's / Boy's Cut</option>
                                <option value="Womens / Girls">Women's / Girl's Cut</option>
                                <option value="Unisex">Unisex</option>
                            </select>
                            @error('gender')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">Name on Jersey (if applicable)</label>
                            <input type="text" name="jersey_name" placeholder="e.g. SMITH" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-medium transition-all">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">Jersey Number (if applicable)</label>
                            <input type="text" name="jersey_number" placeholder="e.g. 24" maxlength="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-medium transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">Name on Backpack (if applicable)</label>
                            <input type="text" name="backpack_name" placeholder="e.g. Jordan Smith" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-medium transition-all">
                        </div>
                        
                    </div>
                    </div>
                </div>

                {{-- Store Items Grid --}}
                <div class="mb-8">
                    <h2 class="text-xl font-black uppercase tracking-tight text-slate-900 mb-6 flex items-center justify-between">
                        <span>Available Merchandise</span>
                        <span class="text-sm font-bold text-slate-500"><span x-text="Object.values(items).filter(i => i.selected).length">0</span> Selected</span>
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                        @foreach($store->items as $item)
                        @php
                            $types = $item->types ?? [$item->type];
                            $isBackpack = in_array('backpack', $types);
                            $sizedTypes = \App\Models\DesignCatalog::sizedTypes();
                            $itemSizedTypes = array_intersect($types, $sizedTypes);
                            $hasNumber  = $item->designCatalog?->has_number_field ?? false;
                            $hasNameField = $item->designCatalog?->has_name_field ?? false;
                            $typeLabel = $item->designCatalog ? $item->designCatalog->type_label : implode(', ', array_map(fn($t) => str_replace('_', ' ', $t), $types));
                        @endphp

                        <div class="flex flex-col group relative transition-all duration-300"
                             :class="items['{{ $item->id }}'].selected ? 'opacity-90' : ''">
                            
                            <!-- Hidden Select -->
                            <input type="checkbox" name="items[{{ $item->id }}][selected]" value="1" x-model="items['{{ $item->id }}'].selected" class="hidden">
                            <input type="hidden" name="items[{{ $item->id }}][name]" value="{{ $item->name }}">

                            <!-- Selected Badge -->
                            <div x-show="items['{{ $item->id }}'].selected" x-transition class="absolute top-4 right-4 bg-secondary text-white text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-full z-20 flex items-center gap-1 shadow-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                Selected
                            </div>

                            <!-- Image Hero -->
                            <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center cursor-pointer" 
                                 :class="items['{{ $item->id }}'].selected ? 'ring-2 ring-secondary ring-offset-2' : ''"
                                 @click="openPanel('{{ $item->id }}')">
                                @if(!empty($item->image_paths))
                                    @if(count($item->image_paths) > 1)
                                        <div class="w-full h-full relative group/slider" x-data="{ imgIdx: 0, imgs: {{ json_encode($item->image_paths) }}, imgInterval: null }" @mouseenter="imgInterval = setInterval(() => { imgIdx = (imgIdx + 1) % imgs.length }, 1500)" @mouseleave="clearInterval(imgInterval); imgIdx = 0">
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
                                        <img src="{{ $item->image_paths[0] }}" alt="" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                                    @endif
                                @elseif($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="text-slate-400 font-medium">No Image</div>
                                @endif
                            </div>

                            <!-- Card Content -->
                            <div class="pt-3 flex flex-col text-center items-center flex-1 w-full px-1">
                                <span class="text-xs md:text-sm font-medium text-red-600 mb-0.5 w-full truncate">{{ $typeLabel }}</span>
                                <h3 class="text-sm md:text-base font-bold truncate w-full" style="font-family: 'Arial Narrow', 'Franklin Gothic Medium', sans-serif; color: #0f172a;" title="{{ $item->name }}">{{ $item->name }}</h3>
                                <div class="text-xs md:text-sm text-slate-500 mt-0.5 mb-3 w-full truncate">
                                    Store Price:
                                    <span class="text-slate-900 font-bold">${{ number_format($item->retail_price, 2) }}</span>
                                </div>
                                
                                <div class="mt-auto w-full">
                                    <button type="button" @click.prevent="openPanel('{{ $item->id }}')" class="w-full py-2.5 text-[11px] font-black uppercase tracking-widest rounded-xl transition-all"
                                            :class="items['{{ $item->id }}'].selected ? 'bg-slate-50 border-2 border-slate-300 text-slate-600' : 'bg-secondary text-white hover:bg-[#a11825] shadow-sm hover:shadow-md border-2 border-transparent'"
                                            x-text="items['{{ $item->id }}'].selected ? 'EDIT SIZING' : 'ORDER'">
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Special Notes --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8">
                    <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">Special Sizing Notes (Optional)</label>
                    <input type="text" name="special_notes" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 transition-all font-medium" placeholder="e.g. Needs extra length on pants...">
                </div>

                {{-- SIZING SLIDE-OVER PANEL --}}
                <div x-show="slideOpen" x-cloak class="fixed inset-0 z-50 flex justify-end">
                    <!-- Full-Size Image Preview & Backdrop -->
                    <div x-show="slideOpen" x-transition.opacity @click="closePanel()" class="absolute inset-0 bg-slate-950/80 backdrop-blur-md flex flex-col p-4 md:p-8 md:pr-[480px]">
                        <button type="button" class="absolute top-6 left-6 text-white/50 hover:text-white transition-colors z-10" @click="closePanel()">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        
                        <!-- Image Viewer -->
                        <div class="w-full h-full flex items-center justify-center relative pointer-events-none" @click.stop>
                            @foreach($store->items as $item)
                                <div x-show="activeItemId === '{{ $item->id }}'" class="w-full h-full flex items-center justify-center p-2 md:p-4">
                                    @if(!empty($item->image_paths))
                                        <div class="relative w-full h-full flex items-center justify-center" x-data="{ imgIdx: 0, imgs: {{ json_encode($item->image_paths) }}, touchStartX: 0, touchEndX: 0 }"
                                             @touchstart.window="if(activeItemId === '{{ $item->id }}') touchStartX = $event.changedTouches[0].screenX"
                                             @touchend.window="if(activeItemId === '{{ $item->id }}') { touchEndX = $event.changedTouches[0].screenX; if(touchStartX - touchEndX > 50) { imgIdx = (imgIdx + 1) % imgs.length; } else if(touchEndX - touchStartX > 50) { imgIdx = (imgIdx - 1 + imgs.length) % imgs.length; } }"
                                             @keydown.right.window="if(activeItemId === '{{ $item->id }}' && slideOpen && imgs.length > 1) imgIdx = (imgIdx + 1) % imgs.length"
                                             @keydown.left.window="if(activeItemId === '{{ $item->id }}' && slideOpen && imgs.length > 1) imgIdx = (imgIdx - 1 + imgs.length) % imgs.length">
                                            
                                            <template x-if="imgs.length > 1">
                                                <button type="button" @click.stop="imgIdx = (imgIdx - 1 + imgs.length) % imgs.length" class="absolute left-4 z-10 p-2 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors pointer-events-auto">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                                </button>
                                            </template>

                                            <img :src="imgs[imgIdx]" class="max-w-full max-h-full object-contain drop-shadow-2xl pointer-events-auto rounded-lg select-none">
                                            
                                            <template x-if="imgs.length > 1">
                                                <button type="button" @click.stop="imgIdx = (imgIdx + 1) % imgs.length" class="absolute right-4 md:right-12 z-10 p-2 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors pointer-events-auto">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </button>
                                            </template>

                                            @if(count($item->image_paths) > 1)
                                                <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 pointer-events-auto">
                                                    <template x-for="(img, idx) in imgs" :key="idx">
                                                        <button type="button" @click.stop="imgIdx = idx" class="w-2.5 h-2.5 rounded-full transition-colors shadow-sm" :class="idx === imgIdx ? 'bg-primary' : 'bg-white/40 hover:bg-white/80'"></button>
                                                    </template>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($item->image_url)
                                        <img src="{{ $item->image_url }}" class="max-w-full max-h-full object-contain drop-shadow-2xl pointer-events-auto rounded-lg">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Panel -->
                    <div x-show="slideOpen" 
                         @click.stop
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col">
                        
                        <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50">
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Select Sizes</h2>
                            <button type="button" @click="closePanel()" class="text-slate-400 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-6">
                            @foreach($store->items as $item)
                            @php
                                $types = $item->types ?? [$item->type];
                                $isBackpack = in_array('backpack', $types);
                                $sizedTypes = \App\Models\DesignCatalog::sizedTypes();
                                $itemSizedTypes = array_intersect($types, $sizedTypes);
                                $hasNumber  = $item->designCatalog?->has_number_field ?? false;
                                $hasNameField = $item->designCatalog?->has_name_field ?? false;
                            @endphp
                            
                            <div x-show="activeItemId === '{{ $item->id }}'" class="space-y-6">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 mb-1">{{ $item->name }}</h3>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary">{{ $item->designCatalog ? $item->designCatalog->type_label : implode(', ', array_map(fn($t) => str_replace('_', ' ', $t), $types)) }}</p>
                                    <p class="text-xs font-bold text-green-700 mt-2">Store Price: ${{ number_format($item->retail_price, 2) }}</p>
                                    @if($item->designCatalog?->description)
                                        <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Product Details</p>
                                            <p class="text-sm text-slate-700 font-medium whitespace-pre-wrap">{{ $item->designCatalog->description }}</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="space-y-5">
                                    @foreach($itemSizedTypes as $t)
                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">{{ str_replace('_', ' ', $t) }} Size <span class="text-red-500">*</span></label>
                                        <select name="items[{{ $item->id }}][sizes][{{ $t }}]" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none font-medium">
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
                                    @endforeach

                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-widest text-slate-600 mb-1">Quantity</label>
                                        <input type="number" name="items[{{ $item->id }}][qty]" value="1" min="1" max="5" x-model.number="items['{{ $item->id }}'].qty" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-medium">
                                    </div>
                                </div>

                                <div class="pt-8 border-t border-slate-100 mt-8">
                                    <button type="button" @click="items['{{ $item->id }}'].selected = true; closePanel()" class="w-full py-4 bg-slate-900 text-white font-black uppercase tracking-widest text-sm rounded-xl hover:bg-secondary transition-colors shadow-lg shadow-slate-900/20">Save & Select</button>
                                    
                                    <button type="button" @click="items['{{ $item->id }}'].selected = false; closePanel()" x-show="items['{{ $item->id }}'].selected" class="w-full py-3 mt-3 bg-red-50 text-red-600 font-bold uppercase tracking-widest text-xs rounded-xl hover:bg-red-100 transition-colors">Remove Item</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- STICKY BOTTOM SUBMIT BAR --}}
                <div class="fixed bottom-0 left-0 right-0 p-3 md:p-4 bg-white/90 backdrop-blur-md border-t border-slate-200 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] z-40 flex justify-center">
                    <div class="max-w-[1400px] w-full flex items-center justify-between gap-6 px-4">
                        <div class="hidden md:block">
                            <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-500 mb-1">Ready to complete?</h4>
                            <p class="text-lg font-black text-slate-900"><span x-text="Object.values(items).filter(i => i.selected).length">0</span> Items Selected</p>
                        </div>
                        <button type="submit" class="w-full md:w-auto px-8 py-3 md:py-3.5 bg-secondary text-white text-xs font-black uppercase tracking-widest rounded-xl hover:-translate-y-1 hover:shadow-[0_10px_20px_rgba(192,30,46,0.3)] transition-all flex-shrink-0">
                            Submit My Order
                        </button>
                    </div>
                </div>
            </form>
        @endif

        {{-- Submitted Roster Accordion --}}
        <div x-data="{ rosterOpen: true, search: '' }" class="bg-white border border-slate-200 rounded-2xl shadow-sm mb-24 overflow-hidden">
            <button type="button" @click="rosterOpen = !rosterOpen" class="w-full flex items-center justify-between p-4 md:p-6 bg-white hover:bg-slate-50 transition-colors focus:outline-none text-left border-b border-transparent" :class="rosterOpen ? 'border-slate-100 bg-slate-50/50' : ''">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Submitted Roster</h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">Athletes who have successfully submitted their order.</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline-block text-[10px] font-bold uppercase tracking-widest text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">{{ $store->parentOrders->count() }} Entries</span>
                    <svg class="w-6 h-6 text-slate-400 transition-transform duration-300" :class="rosterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            
            <div x-show="rosterOpen" x-transition.opacity class="p-6 md:p-8">
                <div class="mb-6 flex justify-between items-center gap-4">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-model="search" placeholder="Search athletes by name..." class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-medium transition-all">
                    </div>
                </div>

                @if($store->parentOrders->isEmpty())
                    <div class="text-center p-12 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-slate-500 font-black uppercase tracking-widest text-sm">No Roster Entries Yet</p>
                        <p class="text-slate-400 text-xs mt-2 font-medium">Be the first to submit your sizing.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($store->parentOrders as $order)
                        <div x-data="{ viewModal: false, name: '{{ strtolower(addslashes($order->athlete_name)) }}' }" 
                             x-show="search === '' || name.includes(search.toLowerCase())"
                             class="bg-white border border-slate-200 rounded-xl p-3 flex items-center justify-between shadow-sm hover:border-primary/40 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-black text-primary text-xs flex-shrink-0">{{ substr($order->athlete_name, 0, 1) }}</div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-xs truncate max-w-[120px] sm:max-w-[150px]">{{ $order->athlete_name }}</div>
                                    <div class="text-[9px] text-slate-500 font-black uppercase tracking-widest">{{ count(is_array($order->items_json) ? $order->items_json : []) }} items</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button type="button" @click="viewModal = true" class="text-[9px] font-black uppercase tracking-widest text-red-500 hover:text-red-700 px-2 py-1 transition-colors">view order</button>
                                <span class="w-5 h-5 flex items-center justify-center bg-green-100 text-green-600 rounded-full border border-green-200 flex-shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>

                            <!-- Modal -->
                            <div x-show="viewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                                <div @click.away="viewModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in relative max-h-[90vh] flex flex-col text-left">
                                    <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                                        <div>
                                            <h3 class="font-black uppercase tracking-tight text-slate-900">Order Details</h3>
                                            <p class="text-xs font-bold text-slate-500">{{ $order->athlete_name }}</p>
                                        </div>
                                        <button @click="viewModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 overflow-y-auto flex-1 bg-white">
                                        @if(is_array($order->items_json) && count($order->items_json) > 0)
                                            <div class="space-y-4">
                                                @foreach($order->items_json as $item)
                                                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50">
                                                        <div class="font-bold text-slate-900 text-sm mb-1">{{ $item['name'] ?? 'Unknown Item' }}</div>
                                                        <div class="text-[10px] font-black uppercase tracking-widest text-primary mb-3">Qty: {{ $item['qty'] ?? 1 }}</div>
                                                        
                                                        @if(!empty($item['sizes']) && is_array($item['sizes']))
                                                            <div class="grid grid-cols-2 gap-2 mt-2">
                                                                @foreach($item['sizes'] as $type => $size)
                                                                    <div class="bg-white border border-slate-200 rounded text-[11px] px-2 py-1.5 flex justify-between items-center shadow-sm">
                                                                        <span class="text-slate-500 font-bold uppercase">{{ str_replace('_', ' ', $type) }}:</span>
                                                                        <span class="text-slate-900 font-black">{{ $size }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-5 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                                                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <p class="text-xs font-medium text-amber-800">If you spot an error, please contact your coach to adjust the order before production begins.</p>
                                            </div>
                                        @else
                                            <p class="text-sm text-slate-500 text-center py-4">No items recorded.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div x-show="search !== '' && !Array.from($el.previousElementSibling.children).some(el => el.style.display !== 'none')" class="text-center p-8 text-slate-500 font-medium text-sm" style="display: none;">
                        No athletes found matching "<span x-text="search" class="font-bold text-slate-900"></span>"
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>



@endsection
