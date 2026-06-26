@extends('layouts.app')

@section('title', $collection . ' | Design Collections | The Commission Apparel')

@section('meta')
    @php
        $ogImage = null;
        if (request()->filled('design')) {
            $sharedDesign = \App\Models\DesignCatalog::find(request()->query('design'));
            if ($sharedDesign) {
                if (!empty($sharedDesign->image_paths)) {
                    $ogImage = $sharedDesign->image_paths[0];
                } elseif ($sharedDesign->image_url) {
                    $ogImage = $sharedDesign->image_url;
                }
            }
        }
        // Fallback to the collection's cover photo (which is the first design's image in this collection)
        if (!$ogImage) {
            $firstDesign = isset($designCatalog) ? $designCatalog->first() : null;
            if ($firstDesign) {
                if (!empty($firstDesign->image_paths)) {
                    $ogImage = $firstDesign->image_paths[0];
                } elseif ($firstDesign->image_url) {
                    $ogImage = $firstDesign->image_url;
                }
            }
        }

        if ($ogImage && !str_starts_with($ogImage, 'http')) {
            $ogImage = asset($ogImage);
        }
    @endphp
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $collection }} | Design Collections | The Commission Apparel">
    <meta property="og:description" content="Explore our latest team apparel concepts for {{ $collection }} across all packages and individual items.">
@endsection

@php
    if (!isset($collection)) {
        $collection = request()->route('collection') ?? 'Collection';
    }
    if (!isset($selectedSport)) {
        $selectedSport = request()->query('sport');
    }
    if (!isset($designCatalog)) {
        $designCatalogQuery = \App\Models\DesignCatalog::where('collection_name', $collection);
        if (!empty($selectedSport)) {
            $designCatalogQuery->where('sport', $selectedSport);
        }
        $designCatalog = $designCatalogQuery->orderBy('sort_order', 'desc')->orderBy('created_at', 'desc')->get();
    }
    if (!isset($availableSports)) {
        $availableSports = \App\Models\DesignCatalog::where('collection_name', $collection)
            ->whereNotNull('sport')
            ->where('sport', '!=', '')
            ->distinct()
            ->orderBy('sport')
            ->pluck('sport');
    }
@endphp

@section('content')
<section class="pt-8 md:pt-24 pb-4 md:pb-6 bg-white border-b border-slate-200">
    <div class="max-w-[1500px] mx-auto px-6">
        <div class="max-w-3xl">
            <p class="text-xs font-black uppercase tracking-widest text-secondary mb-3">
                <a href="{{ route('catalog.index') }}" class="hover:underline">Design Collections</a> 
                <span class="text-slate-400 mx-1">/</span>
                @if($selectedSport)
                    {{ $collection }}: {{ $selectedSport }}
                @else
                    {{ $collection }}
                @endif
            </p>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight uppercase text-slate-900">
                @if($selectedSport)
                    {{ $collection }}: {{ $selectedSport }}
                @else
                    {{ $collection }}
                @endif
            </h1>
            <p class="text-slate-600 mt-4 text-base md:text-lg">
                Explore our latest team apparel concepts across all packages and individual items.
            </p>
        </div>
    </div>
</section>

<section class="py-4 md:py-8 bg-slate-50 min-h-[50vh]" x-data="{ previewOpen: false, previewImgs: [], previewIdx: 0, previewAlt: '', touchStartX: 0, touchEndX: 0 }" @keydown.escape.window="previewOpen = false; document.body.style.overflow = 'auto';" @keydown.right.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx + 1) % previewImgs.length" @keydown.left.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length">
    <div class="max-w-[1500px] mx-auto px-6">
        <div class="mb-8 flex flex-col gap-4">
            <form method="GET" action="{{ route('catalog.show', ['collection' => $collection]) }}" class="w-full"
                  hx-get="{{ route('catalog.show', ['collection' => $collection]) }}"
                  hx-target="#catalog-results"
                  hx-select="#catalog-results"
                  hx-swap="outerHTML"
                  hx-trigger="change from:select, change from:input[type='checkbox'] delay:200ms"
                  hx-push-url="true">
                <div class="flex flex-col gap-6">
                    <!-- Filters Layout -->
                    <div class="flex flex-col gap-3">
                        <!-- Row 1: Labels -->
                        <div class="flex items-center justify-between w-full">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-600">Filter by Categories</label>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Showing {{ $designCatalog->count() }} design{{ $designCatalog->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <!-- Row 2: Controls -->
                        <div class="flex flex-row items-center gap-2">
                            <select id="sport" name="sport" class="flex-1 bg-white border border-slate-300 rounded-lg px-2 sm:px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none min-w-0">
                                <option value="">All Sports</option>
                                @foreach($availableSports as $sport)
                                    <option value="{{ $sport }}" {{ $selectedSport === $sport ? 'selected' : '' }}>{{ $sport }}</option>
                                @endforeach
                            </select>
                            
                            <div x-data="{ open: false }" class="relative flex-1 min-w-0">
                                <button type="button" @click="open = !open" @click.away="open = false" class="w-full bg-white border border-slate-300 rounded-lg px-2 sm:px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none flex items-center justify-between shadow-sm">
                                    <span class="truncate text-left">
                                        @if(count($selectedTypes) > 0)
                                            <span class="font-bold text-secondary">{{ count($selectedTypes) }} Selected</span>
                                        @else
                                            All Item Types
                                        @endif
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400 ml-1 sm:ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                
                                @php
                                    $allTypesList = [
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
                                        'uniform_set_2' => 'Uniform Set #2 (top/bottom)',
                                        'warmup_top' => 'Warm-up (top)',
                                        'warmup_bottom' => 'Warm-up (bottom)',
                                        'warmup_set' => 'Warm-up (top/bottom)',
                                        'warmup_set_2' => 'Warm-up Set #2 (top/bottom)',
                                        'uniform_package_gold' => 'Uniform Package (Gold)',
                                        'uniform_package_silver' => 'Uniform Package (Silver)',
                                        'uniform_package_bronze' => 'Uniform Package (Bronze)',
                                        'uniform_package_custom' => 'Uniform Package (Custom)',
                                    ];
                                    
                                    $filteredTypesList = [];
                                    if (isset($availableTypeKeys)) {
                                        foreach ($allTypesList as $key => $label) {
                                            if (in_array($key, $availableTypeKeys)) {
                                                $filteredTypesList[$key] = $label;
                                            }
                                        }
                                    } else {
                                        $filteredTypesList = $allTypesList;
                                    }
                                @endphp

                                <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="absolute z-50 mt-1 left-0 w-[calc(200vw-4rem)] max-w-64 sm:w-64 bg-white border border-slate-200 rounded-lg shadow-xl max-h-72 overflow-y-auto">
                                    <div class="p-2 space-y-0.5">
                                        @if(empty($filteredTypesList))
                                            <div class="px-2 py-2 text-sm text-slate-500 italic">No types available</div>
                                        @else
                                            @foreach($filteredTypesList as $typeKey => $typeLabel)
                                                <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-slate-50 rounded-md transition-colors group">
                                                    <input type="checkbox" name="types[]" value="{{ $typeKey }}" {{ in_array($typeKey, $selectedTypes ?? []) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-secondary focus:ring-secondary cursor-pointer">
                                                    <span class="text-sm text-slate-700 group-hover:text-slate-900 transition-colors">{{ $typeLabel }}</span>
                                                </label>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="px-4 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#a11825] transition-colors ml-1 hidden">
                                Apply
                            </button>
                            @if(!empty($selectedSport) || !empty($selectedTypes))
                                <a href="{{ route('catalog.show', ['collection' => $collection]) }}" class="px-3 sm:px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-100 transition-colors shadow-sm whitespace-nowrap">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div id="catalog-results">
        @if($designCatalog->isEmpty())
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-xl p-10 text-center text-slate-500 font-bold uppercase tracking-widest text-sm">
                No designs found for this collection yet.
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-4">
                @foreach($designCatalog as $design)
                    @php
                        $imageSrc = null;
                        if (!empty($design->image_paths)) {
                            $imageSrc = $design->image_paths[0];
                        } elseif ($design->image_url) {
                            $imageSrc = $design->image_url;
                        }
                    @endphp
                    <div class="flex flex-col group">
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
                            <h2 class="text-sm md:text-base font-bold truncate w-full" style="font-family: 'Arial Narrow', 'Franklin Gothic Medium', sans-serif; color: #0f172a;" title="{{ $design->name }}">{{ $design->name }}</h2>
                            @if($design->sport)
                                <div class="text-xs md:text-sm text-slate-500 mt-0.5 w-full truncate">{{ $design->sport }}</div>
                            @endif

                            <div class="w-full flex items-center justify-center gap-3 mt-3 mb-1">
                                {{-- Facebook --}}
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('catalog.show', ['collection' => $collection, 'design' => $design->id])) }}" target="_blank" class="text-slate-400 hover:text-blue-600 transition-colors" title="Share on Facebook">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                {{-- Twitter --}}
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('catalog.show', ['collection' => $collection, 'design' => $design->id])) }}&text={{ urlencode('Check out ' . $design->name . ' Design Concept') }}" target="_blank" class="text-slate-400 hover:text-sky-500 transition-colors" title="Share on Twitter">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                </a>
                                {{-- WhatsApp --}}
                                <a href="https://api.whatsapp.com/send?text={{ urlencode('Check out ' . $design->name . ' Design Concept: ' . route('catalog.show', ['collection' => $collection, 'design' => $design->id])) }}" target="_blank" class="text-slate-400 hover:text-green-500 transition-colors" title="Share on WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.03c0 2.127.555 4.195 1.613 6.012L.266 23.73l5.85-1.536A11.968 11.968 0 0012.03 24c6.646 0 12.03-5.385 12.03-12.03S18.677 0 12.03 0zM12.03 21.98c-1.802 0-3.565-.484-5.111-1.4l-.367-.217-3.799.997.997-3.702-.239-.38A9.96 9.96 0 012.05 12.03C2.05 6.526 6.527 2.05 12.03 2.05c5.503 0 9.98 4.476 9.98 9.98s-4.477 9.98-9.98 9.98zm5.474-7.48c-.3-.15-1.776-.877-2.051-.977-.275-.1-.476-.15-.675.15s-.777.977-.952 1.176c-.175.2-.35.225-.65.075-.3-.15-1.267-.468-2.414-1.492-.892-.797-1.496-1.782-1.671-2.083-.175-.3-.018-.463.131-.613.135-.135.3-.35.45-.525.15-.175.2-.3.3-.5.1-.2.05-.375-.025-.525-.075-.15-.675-1.626-.925-2.226-.242-.581-.487-.502-.675-.512-.175-.01-.375-.01-.575-.01s-.525.075-.8.375c-.275.3-1.051 1.026-1.051 2.502s1.076 2.895 1.226 3.095c.15.2 2.112 3.22 5.112 4.516.714.309 1.272.493 1.706.63.716.228 1.368.196 1.884.119.577-.086 1.776-.726 2.026-1.426.25-.7.25-1.3.175-1.426-.075-.125-.275-.2-.575-.35z"/></svg>
                                </a>
                                {{-- Copy Link --}}
                                <a href="#" @click.prevent="navigator.clipboard.writeText('{{ route('catalog.show', ['collection' => $collection, 'design' => $design->id]) }}'); alert('Link copied for sharing!')" class="text-slate-400 hover:text-pink-600 transition-colors" title="Copy link for sharing">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-8"
         @touchstart="touchStartX = $event.changedTouches[0].screenX"
         @touchend="touchEndX = $event.changedTouches[0].screenX; if(touchStartX - touchEndX > 50) { previewIdx = (previewIdx + 1) % previewImgs.length; } else if(touchEndX - touchStartX > 50) { previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length; }">
        
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md" @click="previewOpen = false; document.body.style.overflow = 'auto';"></div>
        
        <button
            type="button"
            class="absolute top-6 left-6 text-white/60 hover:text-white transition-colors z-[60] focus:outline-none"
            @click="previewOpen = false; document.body.style.overflow = 'auto';"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <template x-if="previewImgs.length > 1">
            <button @click.stop="previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length" class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-[60] p-3 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
        </template>

        <template x-if="previewImgs.length > 1">
            <button @click.stop="previewIdx = (previewIdx + 1) % previewImgs.length" class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-[60] p-3 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </template>

        <template x-if="previewImgs.length > 1">
            <div class="absolute bottom-8 left-0 right-0 flex justify-center gap-3 z-[60]">
                <template x-for="(img, idx) in previewImgs" :key="idx">
                    <button type="button" @click.stop="previewIdx = idx" class="w-3 h-3 rounded-full transition-colors shadow-sm focus:outline-none" :class="idx === previewIdx ? 'bg-primary' : 'bg-white/40 hover:bg-white/80'"></button>
                </template>
            </div>
        </template>

        <div class="relative max-w-6xl w-full max-h-[90vh] flex items-center justify-center pointer-events-none">
            <img :src="previewImgs[previewIdx]" :alt="previewAlt" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl border border-white/20 select-none pointer-events-auto">
        </div>
    </div>
</section>
@endsection
