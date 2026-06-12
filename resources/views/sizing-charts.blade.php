@extends('layouts.app')

@section('title', 'Sizing Charts | The Commission Apparel')

@section('content')
<div class="bg-slate-50 min-h-screen pt-32 pb-24" x-data="{ search: '', activeImages: [], activeIndex: 0, openLightbox(images) { this.activeImages = images; this.activeIndex = 0; }, closeLightbox() { this.activeImages = []; } }">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-slate-900 mb-4">Sizing Charts</h1>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto">Review our sizing charts below to ensure the perfect fit for your custom apparel.</p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-md mx-auto mb-12 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" x-model="search" placeholder="Search sizing charts (e.g. Jersey, Hoodie)..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3.5 text-slate-950 focus:border-secondary focus:ring-1 focus:ring-secondary focus:outline-none shadow-sm transition-all placeholder-slate-400">
        </div>

        <div x-ref="grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 mb-10">
            @forelse($sizingCharts as $chart)
            @php
                $chartImages = $chart->image_paths && count($chart->image_paths) > 0 ? json_encode($chart->image_paths) : ($chart->image_path ? json_encode([$chart->image_path]) : json_encode([]));
                $firstImage = $chart->image_paths && count($chart->image_paths) > 0 ? $chart->image_paths[0] : $chart->image_path;
            @endphp
            <div class="flex flex-col group cursor-pointer sizing-chart-card" 
                 x-show="search === '' || '{{ strtolower(addslashes($chart->title)) }}'.includes(search.toLowerCase())" 
                 @click="openLightbox(imgs)" 
                 x-data="{ imgIdx: 0, imgs: {{ $chartImages }}, imgInterval: null }"
                 @mouseenter="if(imgs.length > 1) imgInterval = setInterval(() => { imgIdx = (imgIdx + 1) % imgs.length }, 1500)" 
                 @mouseleave="clearInterval(imgInterval); imgIdx = 0">
                <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center border border-slate-200">
                    <img :src="imgs.length > 0 ? imgs[imgIdx] : '{{ $firstImage }}'" src="{{ $firstImage }}" alt="{{ $chart->title }}" class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105">
                    
                    <template x-if="imgs.length > 1">
                        <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <template x-for="(img, idx) in imgs" :key="idx">
                                <span class="w-1.5 h-1.5 rounded-full transition-colors shadow-sm" :class="idx === imgIdx ? 'bg-red-600' : 'bg-white/60'"></span>
                            </template>
                        </div>
                    </template>
                </div>
                <div class="pt-3 flex flex-col text-center items-center w-full px-1">
                    <span class="text-xs md:text-sm font-medium text-red-600 mb-0.5 w-full truncate">View Size Chart</span>
                    <h2 class="text-sm md:text-base font-bold truncate w-full group-hover:text-secondary transition-colors" style="font-family: 'Arial Narrow', 'Franklin Gothic Medium', sans-serif; color: #0f172a;" title="{{ $chart->title }}">
                        {{ $chart->title }}
                    </h2>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center text-slate-500 py-12">
                <p>Sizing charts are coming soon. Please check back later!</p>
            </div>
            @endforelse
        </div>

        <!-- No Results Message -->
        @if($sizingCharts->isNotEmpty())
        <div x-show="search !== '' && $refs.grid && !$refs.grid.querySelector('.sizing-chart-card:not([style*=\x22display: none\x22])')" x-cloak class="text-center text-slate-500 py-12 bg-white rounded-2xl border border-slate-200 shadow-sm max-w-md mx-auto">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="font-bold text-slate-700">No sizing charts found</p>
            <p class="text-sm text-slate-500 mt-1">Try checking your spelling or search for a different item.</p>
        </div>
        @endif
        <!-- Lightbox Modal -->
        <div x-show="activeImages.length > 0" x-cloak class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-black/90 p-4 backdrop-blur-sm" @click.self="closeLightbox()" @keydown.escape.window="closeLightbox()">
            <button @click="closeLightbox()" class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors z-[110]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <button x-show="activeImages.length > 1" @click.stop="activeIndex = (activeIndex === 0) ? activeImages.length - 1 : activeIndex - 1" class="absolute left-6 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2 z-[110]">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button x-show="activeImages.length > 1" @click.stop="activeIndex = (activeIndex === activeImages.length - 1) ? 0 : activeIndex + 1" class="absolute right-6 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2 z-[110]">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <!-- Main Image Wrapper -->
            <div class="relative flex items-center justify-center max-w-full max-h-[70vh] mb-4">
                <img :src="activeImages[activeIndex]" alt="Expanded View" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-2xl">
            </div>

            <!-- Thumbnails Navigation -->
            <div x-show="activeImages.length > 1" class="flex gap-2 overflow-x-auto max-w-full p-2 bg-white/10 backdrop-blur-sm rounded-xl mb-4">
                <template x-for="(img, idx) in activeImages" :key="idx">
                    <img :src="img" @click.stop="activeIndex = idx" 
                         class="w-14 h-14 object-cover rounded-lg cursor-pointer border-2 transition-all hover:scale-105"
                         :class="activeIndex === idx ? 'border-red-600 scale-105 opacity-100' : 'border-transparent opacity-50 hover:opacity-100'">
                </template>
            </div>
            
            <div x-show="activeImages.length > 1" class="text-white text-xs font-bold bg-black/50 backdrop-blur-sm px-4 py-1.5 rounded-full">
                <span x-text="activeIndex + 1"></span> / <span x-text="activeImages.length"></span>
            </div>
        </div>
    </div>
</div>
@endsection
