@extends('layouts.app')

@section('title', 'Design Catalog | The Commission Apparel')

@php
    if (!isset($collections)) {
        $collections = \App\Models\DesignCatalog::whereNotNull('collection_name')
            ->where('collection_name', '!=', '')
            ->select('collection_name')
            ->distinct()
            ->orderBy('collection_name')
            ->get()
            ->map(function ($item) {
                $firstDesign = \App\Models\DesignCatalog::where('collection_name', $item->collection_name)
                    ->orderBy('sort_order', 'desc')
                    ->first();
                return (object) [
                    'name' => $item->collection_name,
                    'image' => !empty($firstDesign->image_paths) ? $firstDesign->image_paths[0] : ($firstDesign->image_url ?? null)
                ];
            });
    }

    if (!isset($orphanedDesigns)) {
        $orphanedDesigns = \App\Models\DesignCatalog::whereNull('collection_name')
            ->orWhere('collection_name', '')
            ->orderBy('sort_order', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
@endphp

@section('content')
<section class="pt-8 md:pt-24 pb-4 md:pb-6 bg-white border-b border-slate-200">
    <div class="max-w-[1500px] mx-auto px-6">
        <div class="max-w-3xl">
            <p class="text-xs font-black uppercase tracking-widest text-secondary mb-3">Design Collections</p>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight uppercase text-slate-900">View Full Catalog</h1>
            <p class="text-slate-600 mt-4 text-base md:text-lg">
                Explore our latest team apparel concepts across all packages and individual items.
            </p>
        </div>
    </div>
</section>

<section class="py-4 md:py-8 bg-slate-50 min-h-[50vh]" x-data="{ previewOpen: false, previewImgs: [], previewIdx: 0, previewAlt: '', touchStartX: 0, touchEndX: 0, activeCat: 'All', categories: ['All', ...{{ isset($allSports) ? json_encode($allSports) : '[]' }}] }" @keydown.escape.window="previewOpen = false; document.body.style.overflow = 'auto';" @keydown.right.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx + 1) % previewImgs.length" @keydown.left.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length">
    <div class="max-w-[1500px] mx-auto px-6">
        @if($collections->isEmpty() && $orphanedDesigns->isEmpty())
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-xl p-10 text-center text-slate-500 font-bold uppercase tracking-widest text-sm">
                No design collections available yet.
            </div>
        @else
            <!-- Filter Categories -->
            <div class="flex flex-wrap items-center gap-2 mb-8" x-show="categories.length > 1" x-cloak>
                <template x-for="cat in categories" :key="cat">
                    <button type="button" 
                            @click="activeCat = cat"
                            class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all"
                            :class="activeCat === cat ? 'bg-secondary text-white shadow-md' : 'bg-white border border-slate-200 text-slate-500 hover:border-secondary hover:text-secondary'">
                        <span x-text="cat"></span>
                    </button>
                </template>
            </div>

            <!-- Collections Grid -->
            @if($collections->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 mb-10">
                    @foreach($collections as $collection)
                        <div class="flex flex-col group" x-show="activeCat === 'All' || {{ json_encode($collection->sports ?? []) }}.includes(activeCat)">
                            <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center">
                                @if($collection->image)
                                    <img src="{{ asset($collection->image) }}" alt="{{ $collection->name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="text-slate-400 font-medium text-xs uppercase tracking-widest">No Cover Image</div>
                                @endif
                                <a href="{{ route('catalog.show', ['collection' => $collection->name]) }}" class="absolute inset-0 z-10" aria-label="View {{ $collection->name }}"></a>
                            </div>
                            <div class="pt-4 flex flex-col text-center items-center">
                                <span class="text-base font-medium text-red-600 mb-1">View Collection</span>
                                <h2 class="text-lg font-bahnschrift font-semibold [font-stretch:semi-condensed] text-slate-900 group-hover:text-secondary transition-colors tracking-wide" title="{{ $collection->name }}">
                                    <a href="{{ route('catalog.show', ['collection' => $collection->name]) }}">{{ $collection->name }}</a>
                                </h2>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Orphaned Designs Grid -->
            @if($orphanedDesigns->isNotEmpty())
                @if($collections->isNotEmpty())
                    <div class="mb-8 border-t border-slate-200 pt-12">
                        <h2 class="text-2xl font-black tracking-tight uppercase text-slate-900 mb-2">Individual Designs</h2>
                        <p class="text-sm text-slate-500">Additional concepts not part of a specific collection.</p>
                    </div>
                @endif
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-4">
                    @foreach($orphanedDesigns as $design)
                        @php
                            $imageSrc = null;
                            if (!empty($design->image_paths)) {
                                $imageSrc = $design->image_paths[0];
                            } elseif ($design->image_url) {
                                $imageSrc = $design->image_url;
                            }
                        @endphp
                        <div class="flex flex-col group" x-show="activeCat === 'All' || '{{ $design->sport }}' === activeCat">
                            <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center">
                                @if(!empty($design->image_paths) && count($design->image_paths) > 1)
                                    <div class="w-full h-full relative group/slider" x-data="{ imgIdx: 0, imgs: {{ json_encode($design->image_paths) }}, imgInterval: null }" @mouseenter="imgInterval = setInterval(() => { imgIdx = (imgIdx + 1) % imgs.length }, 1500)" @mouseleave="clearInterval(imgInterval); imgIdx = 0">
                                        <button
                                            type="button"
                                            class="w-full h-full block focus:outline-none"
                                            @click="previewOpen = true; previewImgs = imgs; previewIdx = imgIdx; previewAlt = '{{ addslashes($design->name) }}'; document.body.style.overflow = 'hidden';"
                                        >
                                            <img :src="imgs[imgIdx]" alt="{{ $design->name }}" class="w-full h-full object-cover object-top transition-opacity duration-300 cursor-zoom-in">
                                        </button>
                                        
                                        <!-- Manual Navigation Arrows -->
                                        <button type="button" @click.stop="imgIdx = (imgIdx - 1 + imgs.length) % imgs.length; clearInterval(imgInterval)" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/80 text-slate-700 flex items-center justify-center shadow hover:bg-white transition-colors lg:opacity-0 lg:group-hover/slider:opacity-100 focus:outline-none z-20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                        </button>
                                        <button type="button" @click.stop="imgIdx = (imgIdx + 1) % imgs.length; clearInterval(imgInterval)" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/80 text-slate-700 flex items-center justify-center shadow hover:bg-white transition-colors lg:opacity-0 lg:group-hover/slider:opacity-100 focus:outline-none z-20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>

                                        <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 z-10">
                                            <template x-for="(img, idx) in imgs" :key="idx">
                                                <button type="button" @click.stop="imgIdx = idx; clearInterval(imgInterval)" class="w-1.5 h-1.5 rounded-full transition-colors shadow-sm focus:outline-none" :class="idx === imgIdx ? 'bg-secondary' : 'bg-white/60'"></button>
                                            </template>
                                        </div>
                                    </div>
                                @elseif($imageSrc)
                                    <button
                                        type="button"
                                        class="w-full h-full block focus:outline-none"
                                        @click="previewOpen = true; previewImgs = ['{{ asset($imageSrc) }}']; previewIdx = 0; previewAlt = '{{ addslashes($design->name) }}'; document.body.style.overflow = 'hidden';"
                                    >
                                        <img src="{{ asset($imageSrc) }}" alt="{{ $design->name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500 cursor-zoom-in">
                                    </button>
                                @else
                                    <div class="text-slate-400 font-medium text-xs uppercase tracking-widest">No Image</div>
                                @endif
                            </div>

                            <div class="pt-3 flex flex-col text-center items-center w-full px-1">
                                <span class="text-xs md:text-sm font-medium text-red-600 mb-0.5 w-full truncate">{{ $design->type_label }}</span>
                                <h2 class="text-sm md:text-base font-bahnschrift font-semibold [font-stretch:semi-condensed] text-slate-900 truncate tracking-wide w-full" title="{{ $design->name }}">{{ $design->name }}</h2>
                                @if($design->sport)
                                    <div class="text-xs md:text-sm text-slate-500 mt-0.5 w-full truncate">{{ $design->sport }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
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
