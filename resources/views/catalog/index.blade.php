@extends('layouts.app')

@section('title', 'Design Catalog | The Commission Apparel')

@section('content')
<section class="pt-32 pb-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6">
        <div class="max-w-3xl">
            <p class="text-xs font-black uppercase tracking-widest text-secondary mb-3">Design Collections</p>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight uppercase text-slate-900">View Full Catalog</h1>
            <p class="text-slate-600 mt-4 text-base md:text-lg">
                Explore our latest team apparel concepts across all packages and individual items.
            </p>
        </div>
    </div>
</section>

<section class="py-12 bg-slate-50 min-h-[50vh]" x-data="{ previewOpen: false, previewImgs: [], previewIdx: 0, previewAlt: '', touchStartX: 0, touchEndX: 0 }" @keydown.escape.window="previewOpen = false; document.body.style.overflow = 'auto';" @keydown.right.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx + 1) % previewImgs.length" @keydown.left.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length">
    <div class="max-w-7xl mx-auto px-6">
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <form method="GET" action="{{ route('catalog.index') }}" class="flex flex-wrap items-center gap-2">
                <label for="sport" class="text-xs font-black uppercase tracking-wider text-slate-600">Filter by Sport</label>
                <select id="sport" name="sport" class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none">
                    <option value="">All Sports</option>
                    @foreach($availableSports as $sport)
                        <option value="{{ $sport }}" {{ $selectedSport === $sport ? 'selected' : '' }}>{{ $sport }}</option>
                    @endforeach
                </select>

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
                <label for="item_type" class="text-xs font-black uppercase tracking-wider text-slate-600 ml-2">Item Type</label>
                <select id="item_type" name="item_type" class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none">
                    <option value="">All Types</option>
                    @foreach($typeOptions as $val => $label)
                        <option value="{{ $val }}" {{ (isset($selectedType) && $selectedType === $val) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-3 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#a11825] transition-colors ml-1">
                    Apply
                </button>
                @if(!empty($selectedSport) || !empty($selectedType))
                    <a href="{{ route('catalog.index') }}" class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </form>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Showing {{ $designCatalog->count() }} design{{ $designCatalog->count() === 1 ? '' : 's' }}
            </p>
        </div>

        @if($designCatalog->isEmpty())
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-xl p-10 text-center text-slate-500 font-bold uppercase tracking-widest text-sm">
                No designs found for this sport yet.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach($designCatalog as $design)
                    @php
                        $imageSrc = null;
                        if (!empty($design->image_paths)) {
                            $imageSrc = $design->image_paths[0];
                        } elseif ($design->image_url) {
                            $imageSrc = $design->image_url;
                        }
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="bg-white border rounded-2xl overflow-hidden shadow-sm flex flex-col group relative transition-all duration-300 border-slate-200 hover:border-secondary/50 hover:shadow-lg h-full">
                            <div class="aspect-[4/3] bg-[#f0f2f5] relative overflow-hidden group-hover:bg-[#e4e7ec] transition-colors flex items-center justify-center">
                            @if(!empty($design->image_paths) && count($design->image_paths) > 1)
                                <div class="w-full h-full relative" x-data="{ imgIdx: 0, imgs: {{ json_encode($design->image_paths) }}, imgInterval: null }" @mouseenter="imgInterval = setInterval(() => { imgIdx = (imgIdx + 1) % imgs.length }, 1500)" @mouseleave="clearInterval(imgInterval); imgIdx = 0">
                                    <button
                                        type="button"
                                        class="w-full h-full block"
                                        @click="previewOpen = true; previewImgs = imgs; previewIdx = imgIdx; previewAlt = '{{ addslashes($design->name) }}'; document.body.style.overflow = 'hidden';"
                                    >
                                        <img :src="imgs[imgIdx]" alt="{{ $design->name }}" class="w-full h-full object-cover object-top transition-opacity duration-300 cursor-zoom-in">
                                    </button>
                                    <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 pointer-events-none">
                                        <template x-for="(img, idx) in imgs" :key="idx">
                                            <div class="w-1.5 h-1.5 rounded-full transition-colors shadow-sm" :class="idx === imgIdx ? 'bg-secondary' : 'bg-white/60'"></div>
                                        </template>
                                    </div>
                                </div>
                            @elseif($imageSrc)
                                <button
                                    type="button"
                                    class="w-full h-full block"
                                    @click="previewOpen = true; previewImgs = ['{{ $imageSrc }}']; previewIdx = 0; previewAlt = '{{ addslashes($design->name) }}'; document.body.style.overflow = 'hidden';"
                                >
                                    <img src="{{ $imageSrc }}" alt="{{ $design->name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500 cursor-zoom-in">
                                </button>
                            @else
                                <div class="text-slate-400 font-medium text-xs">No Image</div>
                            @endif
                            </div>

                            <div class="p-4 flex flex-col flex-1 bg-white border-t border-slate-100 justify-center">
                                <span class="text-[10px] font-black uppercase tracking-widest text-red-600 mb-1">{{ $design->type_label }}</span>
                                <h2 class="text-[10px] font-black text-slate-900 uppercase tracking-widest truncate" title="{{ $design->name }}">{{ $design->name }}</h2>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-8"
         @touchstart="touchStartX = $event.changedTouches[0].screenX"
         @touchend="touchEndX = $event.changedTouches[0].screenX; if(touchStartX - touchEndX > 50) { previewIdx = (previewIdx + 1) % previewImgs.length; } else if(touchEndX - touchStartX > 50) { previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length; }">
        
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md" @click="previewOpen = false; document.body.style.overflow = 'auto';"></div>
        
        <button
            type="button"
            class="absolute top-6 left-6 text-white/60 hover:text-white transition-colors z-[60]"
            @click="previewOpen = false; document.body.style.overflow = 'auto';"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <template x-if="previewImgs.length > 1">
            <button @click.stop="previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length" class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-[60] p-3 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
        </template>

        <template x-if="previewImgs.length > 1">
            <button @click.stop="previewIdx = (previewIdx + 1) % previewImgs.length" class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-[60] p-3 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </template>

        <template x-if="previewImgs.length > 1">
            <div class="absolute bottom-8 left-0 right-0 flex justify-center gap-3 z-[60]">
                <template x-for="(img, idx) in previewImgs" :key="idx">
                    <button type="button" @click.stop="previewIdx = idx" class="w-3 h-3 rounded-full transition-colors shadow-sm" :class="idx === previewIdx ? 'bg-primary' : 'bg-white/40 hover:bg-white/80'"></button>
                </template>
            </div>
        </template>

        <div class="relative max-w-6xl w-full max-h-[90vh] flex items-center justify-center pointer-events-none">
            <img :src="previewImgs[previewIdx]" :alt="previewAlt" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl border border-white/20 select-none pointer-events-auto">
        </div>
    </div>
</section>
@endsection
