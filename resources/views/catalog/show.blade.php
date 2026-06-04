@extends('layouts.app')

@section('title', $collection . ' | Design Collections | The Commission Apparel')

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
