@extends('layouts.app')

@section('title', 'Design Catalog | The Commission Apparel')

@section('meta')
    <meta property="og:image" content="{{ asset('images/hero-banner.jpeg') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Design Catalog | The Commission Apparel">
    <meta property="og:description" content="Explore our latest team apparel concepts across all packages and individual items.">
@endsection

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

    if (!isset($search)) {
        $search = trim((string)request()->query('q', request()->query('search', '')));
    }
    if (!isset($selectedSport)) {
        $selectedSport = request()->query('sport');
    }
    if (!isset($allSports)) {
        $allSports = config('sports.categories') ?? [];
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

<section class="py-4 md:py-8 bg-slate-50 min-h-[50vh]" x-data="{
    previewOpen: false,
    previewImgs: [],
    previewIdx: 0,
    previewAlt: '',
    touchStartX: 0,
    touchEndX: 0,
    query: '{{ addslashes($search) }}',
    sport: '{{ addslashes($selectedSport && $selectedSport !== '' ? $selectedSport : 'All') }}',
    hasFilters() {
        return (this.query && this.query.trim().length > 0) || (this.sport && this.sport !== 'All');
    },
    clearSearch() {
        this.query = '';
        const el = document.getElementById('catalog-search');
        if (el) {
            el.value = '';
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
    },
    clearSport() {
        this.sport = 'All';
        const el = document.getElementById('catalog-sport');
        if (el) {
            el.value = 'All';
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    },
    resetFilters() {
        this.query = '';
        this.sport = 'All';
        const qEl = document.getElementById('catalog-search');
        const sEl = document.getElementById('catalog-sport');
        if (qEl) qEl.value = '';
        if (sEl) sEl.value = 'All';
        if (sEl) sEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
}" @keydown.escape.window="previewOpen = false; document.body.style.overflow = 'auto';" @keydown.right.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx + 1) % previewImgs.length" @keydown.left.window="if(previewOpen && previewImgs.length > 1) previewIdx = (previewIdx - 1 + previewImgs.length) % previewImgs.length">
    <div class="max-w-[1500px] mx-auto px-6">
        <!-- Search & Filter Controls -->
        <div class="mb-8">
            <form action="{{ route('catalog.index') }}" method="GET" class="w-full"
                  hx-get="{{ route('catalog.index') }}"
                  hx-target="#catalog-results"
                  hx-select="#catalog-results"
                  hx-swap="outerHTML"
                  hx-trigger="input from:input[name='q'] delay:300ms, change from:select[name='sport'], submit"
                  hx-push-url="true">
                <div class="flex flex-col md:flex-row gap-4 md:gap-5 items-stretch md:items-end">
                    <!-- Category Filter (Left Side) -->
                    <div class="w-full md:w-[280px] shrink-0">
                        <label for="catalog-sport" class="block text-sm font-black text-slate-600 uppercase tracking-wide mb-2">Filter By Categories</label>
                        <div class="relative">
                            <select id="catalog-sport" name="sport" x-model="sport" @change="sport = $event.target.value" class="w-full appearance-none bg-white border border-slate-200 text-slate-900 py-3.5 pl-5 pr-10 rounded-xl text-[13px] font-black uppercase tracking-wide focus:outline-none focus:border-slate-300 focus:ring-1 focus:ring-slate-300 transition-all cursor-pointer shadow-sm hover:border-slate-300">
                                <option value="All" {{ $selectedSport === 'All' || !$selectedSport ? 'selected' : '' }}>All Categories</option>
                                @foreach($allSports as $cat)
                                    <option value="{{ $cat }}" {{ $selectedSport === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Search Filter (Beside Category Filter) -->
                    <div class="w-full md:flex-1 md:max-w-2xl">
                        <label for="catalog-search" class="block text-sm font-black text-slate-600 uppercase tracking-wide mb-2">Search Catalog</label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"></path></svg>
                                </div>
                                <input
                                    id="catalog-search"
                                    type="text"
                                    name="q"
                                    x-model="query"
                                    @input="query = $event.target.value"
                                    placeholder="Search by collection, sport, or apparel..."
                                    class="w-full bg-white border border-slate-200 text-slate-900 py-3.5 pl-11 pr-10 rounded-xl text-[13px] font-medium placeholder:text-slate-400 focus:outline-none focus:border-slate-300 focus:ring-1 focus:ring-slate-300 transition-all shadow-sm hover:border-slate-300"
                                >
                                <button type="button" x-show="query && query.trim().length > 0" x-cloak @click="clearSearch()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-700 transition-colors" title="Clear search">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <!-- Reset Button -->
                            <button
                                type="button"
                                x-show="hasFilters()"
                                x-cloak
                                @click="resetFilters()"
                                class="py-3.5 px-4 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 hover:text-slate-900 rounded-xl text-xs uppercase tracking-wider font-bold transition-all shadow-sm flex items-center gap-1.5 shrink-0"
                                title="Reset all filters"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Catalog Results Container -->
        <div id="catalog-results">
            <!-- Active Filter Badges & Results Count -->
            @if(!empty($search) || ($selectedSport && $selectedSport !== 'All'))
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-200/80">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 font-medium">
                        <span class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Active Filters:</span>
                        @if(!empty($search))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 text-slate-800 rounded-full font-bold text-xs shadow-sm">
                                <span>Search: <span class="text-secondary font-black">"{{ $search }}"</span></span>
                                <button type="button" @click="clearSearch()" class="text-slate-400 hover:text-red-600 ml-0.5 focus:outline-none" title="Remove search filter">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </span>
                        @endif
                        @if($selectedSport && $selectedSport !== 'All')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 text-slate-800 rounded-full font-bold text-xs shadow-sm">
                                <span>Category: <span class="text-secondary font-black">{{ $selectedSport }}</span></span>
                                <button type="button" @click="clearSport()" class="text-slate-400 hover:text-red-600 ml-0.5 focus:outline-none" title="Remove category filter">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </span>
                        @endif
                        <button type="button" @click="resetFilters()" class="text-red-600 hover:text-red-700 font-bold hover:underline ml-1 text-xs focus:outline-none">Clear all</button>
                    </div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Found {{ $collectionsPaginator->total() }} collection{{ $collectionsPaginator->total() === 1 ? '' : 's' }}
                        @if($orphanedDesigns->total() > 0)
                            &amp; {{ $orphanedDesigns->total() }} individual design{{ $orphanedDesigns->total() === 1 ? '' : 's' }}
                        @endif
                    </p>
                </div>
            @endif

            @if($collections->isEmpty() && $orphanedDesigns->isEmpty())
                <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center shadow-sm">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"></path></svg>
                    </div>
                    @if(!empty($search) || ($selectedSport && $selectedSport !== 'All'))
                        <h3 class="text-lg font-black uppercase tracking-tight text-slate-800 mb-1">No Matching Collections or Designs</h3>
                        <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">We couldn't find anything matching your criteria. Try adjusting your search term or category.</p>
                        <button type="button" @click="resetFilters()" class="inline-flex items-center gap-2 btn btn-primary py-2.5 px-6 rounded-xl text-xs uppercase tracking-wider font-bold">
                            <span>View Full Catalog</span>
                        </button>
                    @else
                        <h3 class="text-lg font-black uppercase tracking-tight text-slate-800 mb-1">No Design Collections Available</h3>
                        <p class="text-slate-500 text-sm">Please check back soon for our latest apparel releases.</p>
                    @endif
                </div>
            @else
                <!-- Results Count Summary -->
                @if(!empty($search) || ($selectedSport && $selectedSport !== 'All'))
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Found {{ $collectionsPaginator->total() }} collection{{ $collectionsPaginator->total() === 1 ? '' : 's' }}
                            @if($orphanedDesigns->total() > 0)
                                &amp; {{ $orphanedDesigns->total() }} individual design{{ $orphanedDesigns->total() === 1 ? '' : 's' }}
                            @endif
                        </p>
                    </div>
                @endif

            <!-- Collections Grid -->
            @if($collections->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 mb-10">
                    @foreach($collections as $collection)
                        <div class="flex flex-col group">
                            <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center">
                                @if($collection->image)
                                    <img src="{{ asset($collection->image) }}" alt="{{ $collection->name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @else
                                    <div class="text-slate-400 font-medium text-xs uppercase tracking-widest">No Cover Image</div>
                                @endif
                                <a href="{{ route('catalog.show', ['collection' => \Illuminate\Support\Str::slug($collection->name)]) }}" class="absolute inset-0 z-10" aria-label="View {{ $collection->name }}"></a>
                            </div>
                            <div class="pt-3 flex flex-col text-center items-center w-full px-1">
                                <span class="text-xs md:text-sm font-medium text-red-600 mb-0.5 w-full truncate">View Collection</span>
                                <h2 class="text-sm md:text-base font-bold truncate w-full group-hover:text-secondary transition-colors" style="font-family: 'Arial Narrow', 'Franklin Gothic Medium', sans-serif; color: #0f172a;" title="{{ $collection->name }}">
                                    <a href="{{ route('catalog.show', ['collection' => \Illuminate\Support\Str::slug($collection->name)]) }}">{{ $collection->name }}</a>
                                </h2>

                                <div class="w-full flex items-center justify-center gap-3 mt-3 mb-1">
                                    {{-- Facebook --}}
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('catalog.show', ['collection' => \Illuminate\Support\Str::slug($collection->name)])) }}" target="_blank" class="text-slate-400 hover:text-blue-600 transition-colors" title="Share on Facebook">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </a>
                                    {{-- Twitter --}}
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('catalog.show', ['collection' => \Illuminate\Support\Str::slug($collection->name)])) }}&text={{ urlencode('Check out ' . $collection->name . ' Design Collection') }}" target="_blank" class="text-slate-400 hover:text-sky-500 transition-colors" title="Share on Twitter">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                    </a>
                                    {{-- WhatsApp --}}
                                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Check out ' . $collection->name . ' Design Collection: ' . route('catalog.show', ['collection' => \Illuminate\Support\Str::slug($collection->name)])) }}" target="_blank" class="text-slate-400 hover:text-green-500 transition-colors" title="Share on WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.03c0 2.127.555 4.195 1.613 6.012L.266 23.73l5.85-1.536A11.968 11.968 0 0012.03 24c6.646 0 12.03-5.385 12.03-12.03S18.677 0 12.03 0zM12.03 21.98c-1.802 0-3.565-.484-5.111-1.4l-.367-.217-3.799.997.997-3.702-.239-.38A9.96 9.96 0 012.05 12.03C2.05 6.526 6.527 2.05 12.03 2.05c5.503 0 9.98 4.476 9.98 9.98s-4.477 9.98-9.98 9.98zm5.474-7.48c-.3-.15-1.776-.877-2.051-.977-.275-.1-.476-.15-.675.15s-.777.977-.952 1.176c-.175.2-.35.225-.65.075-.3-.15-1.267-.468-2.414-1.492-.892-.797-1.496-1.782-1.671-2.083-.175-.3-.018-.463.131-.613.135-.135.3-.35.45-.525.15-.175.2-.3.3-.5.1-.2.05-.375-.025-.525-.075-.15-.675-1.626-.925-2.226-.242-.581-.487-.502-.675-.512-.175-.01-.375-.01-.575-.01s-.525.075-.8.375c-.275.3-1.051 1.026-1.051 2.502s1.076 2.895 1.226 3.095c.15.2 2.112 3.22 5.112 4.516.714.309 1.272.493 1.706.63.716.228 1.368.196 1.884.119.577-.086 1.776-.726 2.026-1.426.25-.7.25-1.3.175-1.426-.075-.125-.275-.2-.575-.35z"/></svg>
                                    </a>
                                    {{-- Copy Link --}}
                                    <a href="#" @click.prevent="navigator.clipboard.writeText('{{ route('catalog.show', ['collection' => \Illuminate\Support\Str::slug($collection->name)]) }}'); alert('Link copied for sharing!')" class="text-slate-400 hover:text-pink-600 transition-colors" title="Copy link for sharing">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 mb-10">
                    {{ $collectionsPaginator->appends(request()->except('collection_page'))->links() }}
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
                        <div class="flex flex-col group">
                            <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center">
                                @if(!empty($design->image_paths) && count($design->image_paths) > 1)
                                    <div class="w-full h-full relative group/slider" x-data="{ imgIdx: 0, imgs: {{ json_encode($design->image_paths) }}, imgInterval: null }" @mouseenter="imgInterval = setInterval(() => { imgIdx = (imgIdx + 1) % imgs.length }, 1500)" @mouseleave="clearInterval(imgInterval); imgIdx = 0">
                                        <button
                                            type="button"
                                            class="w-full h-full block focus:outline-none"
                                            @click="previewOpen = true; previewImgs = imgs; previewIdx = imgIdx; previewAlt = '{{ addslashes($design->name) }}'; document.body.style.overflow = 'hidden';"
                                        >
                                            <img :src="imgs[imgIdx]" alt="{{ $design->name }}" class="w-full h-full object-cover object-top transition-opacity duration-300 cursor-zoom-in" loading="lazy">
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
                                        <img src="{{ asset($imageSrc) }}" alt="{{ $design->name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500 cursor-zoom-in" loading="lazy">
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
                <div class="mt-6">
                    {{ $orphanedDesigns->appends(request()->except('orphaned_page'))->links() }}
                </div>
            @endif
        @endif
        </div>
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

<script>
    document.addEventListener('htmx:afterSwap', function(evt) {
        if (window.Alpine && evt.detail.target) {
            window.Alpine.initTree(evt.detail.target);
        }
    });
</script>
@endsection
