@extends('layouts.app')

@section('content')

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 1. HERO BANNER SECTION (Dynamic Performance Treatment)                -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section class="w-full bg-[#08090c] text-white pt-24 md:pt-28 pb-10 md:pb-14 border-b border-slate-900 relative overflow-hidden">
    <!-- Stadium Floodlight & Running Track Arena Glows -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_90%_70%_at_75%_40%,rgba(205,32,44,0.22),transparent_70%),radial-gradient(ellipse_60%_50%_at_20%_30%,rgba(255,255,255,0.06),transparent_60%)] pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-[#08090c] via-[#08090c]/90 to-transparent z-10 pointer-events-none"></div>

    <div class="max-w-[1500px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center relative z-20 min-h-[520px]">
        
        <!-- Left: Athletic Headline, Subtitle, & Glowing Pill CTA -->
        <div class="lg:col-span-7 space-y-6 text-left py-2">
            
            <div class="inline-flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">1</span>
                <span class="text-xs md:text-sm font-black uppercase tracking-widest text-[#ff4a58]">
                    CUSTOM GEAR BUILT FOR THE COMMITTED
                </span>
            </div>
            
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black uppercase tracking-tight text-white leading-[0.95] drop-shadow-2xl">
                {{ $heroSettings['title'] ?? 'CUSTOM GEAR BUILT FOR THE COMMITTED' }}
            </h1>
            
            <p class="text-slate-300 text-sm md:text-base lg:text-lg max-w-xl font-normal leading-relaxed">
                {{ $heroSettings['subtitle'] ?? 'Dominate the competition with elite performance apparel designed for champion athletes. Elevate your team\'s game.' }}
            </p>

            <div class="pt-2 flex flex-wrap items-center gap-4">
                <a href="{{ $heroSettings['cta_primary_url'] ?? '/quote' }}" class="px-10 py-4 bg-gradient-to-r from-[#e52d27] to-[#b31217] hover:from-[#f5352e] hover:to-[#c7171d] text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-[0_0_35px_rgba(205,32,44,0.7)] hover:shadow-[0_0_50px_rgba(205,32,44,0.95)] hover:scale-105 transition-all inline-flex items-center justify-center gap-2">
                    {{ $heroSettings['cta_primary_text'] ?? 'START DESIGNING' }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ $heroSettings['cta_secondary_url'] ?? '/catalog' }}" class="px-8 py-4 bg-slate-900/80 hover:bg-white/10 text-white border border-slate-700 hover:border-white font-black text-xs md:text-sm uppercase tracking-wider rounded-full transition-all inline-flex items-center justify-center backdrop-blur-sm">
                    {{ $heroSettings['cta_secondary_text'] ?? 'VIEW CATALOG' }}
                </a>
            </div>

            <!-- Trust Stats Bar -->
            <div class="pt-6 border-t border-slate-800/80 grid grid-cols-3 gap-6 text-left max-w-lg">
                <div>
                    <div class="text-2xl lg:text-3xl font-black text-white drop-shadow">1,000+</div>
                    <div class="text-[10px] md:text-xs uppercase font-bold text-slate-400 tracking-wider mt-0.5">Programs Outfitted</div>
                </div>
                <div>
                    <div class="text-2xl lg:text-3xl font-black text-white drop-shadow">2–3 Wks</div>
                    <div class="text-[10px] md:text-xs uppercase font-bold text-slate-400 tracking-wider mt-0.5">Fast Turnaround</div>
                </div>
                <div>
                    <div class="text-2xl lg:text-3xl font-black text-[#ff4a58] drop-shadow-[0_0_10px_rgba(205,32,44,0.5)]">100%</div>
                    <div class="text-[10px] md:text-xs uppercase font-bold text-slate-400 tracking-wider mt-0.5">Custom Artwork</div>
                </div>
            </div>
        </div>

        <!-- Right: Athletic Models Seamlessly Blended on Field -->
        <div class="lg:col-span-5 relative flex justify-center items-end self-end h-full">
            <div class="relative w-full max-w-[520px] flex justify-center items-end">
                <img src="{{ $heroSettings['media_path'] }}" alt="Commission Apparel Athletes" class="w-full h-auto max-h-[540px] object-contain drop-shadow-[0_20px_50px_rgba(0,0,0,0.9)] hover:scale-105 transition-transform duration-700" fetchpriority="high">
            </div>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 2. FROM VISION TO VICTORY: CONCEPT TO REALITY                         -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="proof" class="w-full bg-[#0b0c10] text-white py-10 md:py-14 border-b border-slate-900 relative">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">2</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#ff4a58]">PRECISION CRAFTSMANSHIP</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-white">
                    {{ $proofSettings['heading'] ?? 'From Vision to Victory: Concept to Reality' }}
                </h2>
            </div>
            <p class="text-slate-400 text-xs md:text-sm max-w-md text-left md:text-right">
                {{ $proofSettings['subheading'] ?? 'Precision craftsmanship from 3D digital blueprint to final sublimated uniform.' }}
            </p>
        </div>

        <!-- High-Octane Concept to Reality Canvas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch bg-[#11131a] border border-slate-800 rounded-3xl p-6 lg:p-8 shadow-2xl relative overflow-hidden">
            
            <!-- Left: 3D Vector Concept Proof -->
            <div class="relative rounded-2xl overflow-hidden border border-slate-800 bg-[#07080b] p-6 flex flex-col justify-between group">
                <div class="flex items-center justify-between z-10 mb-4">
                    <span class="bg-slate-800/90 text-slate-300 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg backdrop-blur-sm border border-slate-700">
                        Phase 1: 3D Vector Design Proof
                    </span>
                    <span class="text-[10px] font-mono text-slate-500">DIGITAL SPEC</span>
                </div>
                
                <div class="aspect-[4/3] flex items-center justify-center overflow-hidden my-4 relative">
                    <img src="{{ $proofSettings['concept_image'] }}" alt="3D Jersey Concept" class="max-h-full w-auto object-contain group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    
                    <!-- Callout Pin 1 -->
                    <div class="absolute top-1/4 right-6 bg-black/80 backdrop-blur border border-slate-700 px-3 py-1 rounded-lg text-[10px] font-bold text-white uppercase hidden sm:flex items-center gap-1.5 shadow-xl">
                        <span class="w-2 h-2 rounded-full bg-[#cd202c]"></span> {{ $proofSettings['feature_1'] ?? '1. Full Custom Graphics' }}
                    </div>
                    
                    <!-- Callout Pin 2 -->
                    <div class="absolute bottom-1/4 right-6 bg-black/80 backdrop-blur border border-slate-700 px-3 py-1 rounded-lg text-[10px] font-bold text-white uppercase hidden sm:flex items-center gap-1.5 shadow-xl">
                        <span class="w-2 h-2 rounded-full bg-[#cd202c]"></span> {{ $proofSettings['feature_2'] ?? '2. Premium Moisture-Wicking Fabric' }}
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 flex justify-around text-[11px] font-bold text-slate-400 uppercase">
                    <span>✓ Exact Pantone Matching</span>
                    <span>✓ Unlimited Custom Details</span>
                </div>
            </div>

            <!-- Right: Actual Finished Sublimated Uniform in Action -->
            <div class="relative rounded-2xl overflow-hidden border border-[#cd202c]/50 bg-[#07080b] p-6 flex flex-col justify-between group shadow-[0_0_30px_rgba(205,32,44,0.15)]">
                <div class="flex items-center justify-between z-10 mb-4">
                    <span class="bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg shadow-lg shadow-red-900/50 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> Phase 2: Live Production Jersey
                    </span>
                    <span class="text-[10px] font-mono text-[#ff4a58]">100% SUBLIMATED</span>
                </div>

                <div class="aspect-[4/3] flex items-center justify-center overflow-hidden my-4">
                    <img src="{{ $proofSettings['reality_image'] }}" alt="Actual Sublimated Jersey" class="max-h-full w-auto object-contain group-hover:scale-105 transition-transform duration-500" loading="lazy">
                </div>

                <div class="pt-4 border-t border-slate-800/80 flex justify-around text-[11px] font-bold text-[#ff4a58] uppercase">
                    <span>✓ {{ $proofSettings['feature_1'] ?? '1. Full Custom Graphics' }}</span>
                    <span>✓ {{ $proofSettings['feature_3'] ?? '3. Reinforced Athletic Stitching' }}</span>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 3. EXPLORE SPORTS CATEGORIES (Spacious Responsive Carousel)           -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="sports" class="w-full bg-[#08090c] text-white py-10 md:py-14 border-b border-slate-900"
    x-data="{ 
        activePage: 0,
        itemsPerPage: window.innerWidth < 768 ? 1 : (window.innerWidth < 1200 ? 2 : 3),
        totalItems: {{ count($landingCollections ?? []) }},
        get totalPages() { return Math.max(1, Math.ceil(this.totalItems / this.itemsPerPage)) },
        next() { this.activePage = (this.activePage + 1) % this.totalPages },
        prev() { this.activePage = (this.activePage - 1 + this.totalPages) % this.totalPages },
        init() {
            window.addEventListener('resize', () => {
                this.itemsPerPage = window.innerWidth < 768 ? 1 : (window.innerWidth < 1200 ? 2 : 3);
                if (this.activePage >= this.totalPages) this.activePage = Math.max(0, this.totalPages - 1);
            });
        }
    }">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <!-- Header with Title and Carousel Controls -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">3</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#ff4a58]">EXPLORE OUR SPORTS CATEGORIES</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-white">
                    CUSTOM UNIFORM COLLECTIONS
                </h2>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#ff4a58] hover:text-white transition-colors mr-2">
                    View Full Catalog <span>&rarr;</span>
                </a>
                
                <!-- Prev / Next Navigation Buttons -->
                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    <button @click="prev()" aria-label="Previous sport collections" class="w-10 h-10 rounded-full bg-[#11131a] border border-slate-800 hover:border-[#cd202c] hover:bg-[#cd202c] text-white flex items-center justify-center transition-all shadow-lg active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="next()" aria-label="Next sport collections" class="w-10 h-10 rounded-full bg-[#11131a] border border-slate-800 hover:border-[#cd202c] hover:bg-[#cd202c] text-white flex items-center justify-center transition-all shadow-lg active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        @if($landingCollections && $landingCollections->isNotEmpty())
        <!-- Carousel Track Container -->
        <div class="overflow-hidden relative -mx-3">
            <div class="flex transition-transform duration-500 ease-out"
                 :style="'transform: translateX(-' + (activePage * 100) + '%)'">
                @foreach($landingCollections as $collection)
                <div class="shrink-0 p-3 flex"
                     :style="'width: ' + (100 / itemsPerPage) + '%'">
                    <div class="bg-[#11131a] border border-slate-800 hover:border-[#cd202c] hover:shadow-[0_0_30px_rgba(205,32,44,0.3)] rounded-3xl overflow-hidden transition-all duration-300 flex flex-col justify-between h-full w-full group">
                        
                        <!-- Full Graphic Banner Artwork (Un-cropped Aspect Ratio) -->
                        <a href="{{ route('catalog.index') }}" class="relative block overflow-hidden bg-slate-950 w-full" style="aspect-ratio: 406.7 / 305.017;">
                            @if($loop->first)
                            <div class="absolute top-3 left-3 bg-[#cd202c] text-white text-[9px] font-black tracking-widest uppercase px-2.5 py-1 rounded shadow-md z-10 animate-pulse">
                                NEWEST ARRIVAL
                            </div>
                            @endif
                            <img src="{{ $collection->image_path }}" alt="{{ $collection->tab_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        </a>

                        <!-- Card Body with Original Proportions -->
                        <div class="p-4 md:p-5 flex flex-col flex-1 justify-between bg-[#11131a]">
                            <div>
                                <div class="text-secondary text-[10px] font-black tracking-widest uppercase mb-1 md:mb-2">
                                    {{ $collection->tab_name }}
                                </div>
                                <h3 class="text-xs md:text-sm font-black uppercase text-white leading-tight mb-2 line-clamp-2 group-hover:text-[#ff4a58] transition-colors" title="{{ $collection->title }}">
                                    {{ $collection->title }}
                                </h3>
                                <p class="text-slate-300 text-[10px] md:text-xs mb-4 font-medium line-clamp-3 leading-relaxed">
                                    {{ $collection->description }}
                                </p>
                            </div>
                            
                            <div class="mt-auto">
                                <a href="/quote" class="block w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-[10px] md:text-xs font-bold uppercase tracking-wider text-center rounded-lg shadow-sm transition-colors">
                                    TALK TO AN EXPERT
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Carousel Pagination Dots -->
        <div class="flex items-center justify-center gap-2 mt-6" x-show="totalPages > 1">
            <template x-for="i in totalPages" :key="i">
                <button @click="activePage = i - 1" class="h-1.5 rounded-full transition-all duration-300" :class="activePage === i - 1 ? 'bg-[#cd202c] w-8 shadow-[0_0_10px_rgba(205,32,44,0.8)]' : 'bg-slate-800 hover:bg-slate-700 w-2.5'"></button>
            </template>
        </div>
        @else
        <div class="py-12 text-center text-slate-500 font-bold uppercase">No collections available.</div>
        @endif

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 4. COMMISSION NEWS & STORIES (Balanced 3-Card Teaser)                 -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="news" class="w-full bg-[#0b0c10] text-white py-10 md:py-14 border-b border-slate-900">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 md:mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">4</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#ff4a58]">COMMISSION NEWS & STORIES</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-white">
                    PROGRAM SPOTLIGHTS & MEDIA
                </h2>
            </div>
            <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#ff4a58] hover:text-white transition-colors">
                View All News & Stories <span>&rarr;</span>
            </a>
        </div>

        @if($newsArticles->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
            @foreach($newsArticles as $article)
            <article class="bg-[#11131a] border border-slate-800 hover:border-[#cd202c]/60 rounded-3xl overflow-hidden shadow-2xl flex flex-col group transition-all duration-300 hover:shadow-[0_0_30px_rgba(205,32,44,0.2)]">
                <!-- Media Card Header -->
                <a href="{{ route('news.show', $article->slug) }}" class="relative block w-full overflow-hidden aspect-[16/10] bg-slate-950">
                    @if($article->cover_image)
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    
                    <div class="absolute top-3.5 left-3.5 bg-[#cd202c] text-white text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-md shadow-lg">
                        {{ $article->category }}
                    </div>

                    @if($article->video_url)
                    <div class="absolute bottom-3 left-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/80 backdrop-blur-md border border-white/20 text-white text-[10px] font-black uppercase tracking-wider group-hover:bg-[#cd202c] group-hover:border-[#cd202c] transition-all shadow-xl">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <span>WATCH VIDEO</span>
                    </div>
                    @endif
                </a>

                <!-- Card Content -->
                <div class="p-5 md:p-6 flex flex-col justify-between flex-1 space-y-4">
                    <div class="space-y-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">
                            {{ $article->published_at ? $article->published_at->format('M d, Y') : $article->created_at->format('M d, Y') }}
                        </span>
                        <h3 class="text-lg md:text-xl font-black uppercase text-white leading-snug group-hover:text-[#ff4a58] transition-colors line-clamp-2">
                            <a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a>
                        </h3>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3 font-normal">
                            {{ $article->summary }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between mt-auto">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">{{ $article->author ?? 'The Commission Editorial' }}</span>
                        <a href="{{ route('news.show', $article->slug) }}" class="text-xs font-black uppercase text-[#ff4a58] group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                            Read Story <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @else
        <div class="py-8 text-center text-slate-500 font-bold uppercase">No stories published yet.</div>
        @endif

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 5. LAUNCH YOUR TEAM STORE (Original High-Impact Panoramic Setup)       -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="team-store" class="w-full bg-[#08090c] text-white py-10 md:py-14 border-b border-slate-900 relative overflow-hidden">
    <!-- Stadium Glow Atmosphere -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_40%,rgba(205,32,44,0.15),transparent_70%)] pointer-events-none"></div>

    <div class="max-w-[1500px] mx-auto px-6 relative z-20 space-y-8 md:space-y-10">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto space-y-2">
            <div class="inline-flex items-center gap-3 justify-center">
                <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">5</span>
                <span class="text-xs md:text-sm font-black uppercase tracking-widest text-[#ff4a58]">
                    COACH & PROGRAM PLATFORM
                </span>
            </div>
            
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tighter uppercase text-white leading-tight drop-shadow-2xl">
                TEAM <span class="text-secondary ml-1">STORE</span>
            </h2>
            
            <p class="text-slate-100 text-base md:text-lg font-medium tracking-wide">
                {{ $teamStoreSettings['subheading'] ?? 'Empower your program with a custom online store that eliminates hassle and generates revenue.' }}
            </p>
        </div>

        <!-- Full-Width Panoramic Team Store UI Platform Mockup Banner -->
        <div class="w-full max-w-[1400px] mx-auto rounded-3xl overflow-hidden shadow-[0_25px_70px_rgba(0,0,0,0.9)] border border-slate-800 bg-[#11131a] p-2.5 md:p-3.5 group">
            <div class="flex items-center gap-1.5 pb-2 px-2 border-b border-slate-800 mb-2.5">
                <div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/80"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-green-500/80"></div>
                <span class="text-[10px] font-mono text-slate-500 ml-2 tracking-wide">thecommissionapparel.com/store/live</span>
            </div>
            <div class="w-full rounded-2xl overflow-hidden relative bg-black">
                <img src="{{ $teamStoreSettings['image'] }}" alt="Team Store Digital Platform" class="w-full h-auto object-contain block transition-transform duration-700 group-hover:scale-[1.01]" loading="lazy">
            </div>
        </div>

        <!-- 4 Core Benefits (2x2 Grid) -->
        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-x-12 md:gap-x-16 gap-y-6 md:gap-y-8 pt-1">
            
            <!-- Feature 1 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-base md:text-lg font-black uppercase tracking-wide mb-1.5">{{ $teamStoreSettings['bullet_1'] }}</h3>
                    <p class="text-slate-300 text-xs md:text-sm leading-relaxed font-medium">No collecting cash or forms. Families simply use your custom team link to order jerseys and fan gear directly online.</p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-base md:text-lg font-black uppercase tracking-wide mb-1.5">{{ $teamStoreSettings['bullet_2'] }}</h3>
                    <p class="text-slate-300 text-xs md:text-sm leading-relaxed font-medium">Exclusive bespoke hoodies, athletic tees, gym bags, and official uniform packages custom tailored for your school.</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-base md:text-lg font-black uppercase tracking-wide mb-1.5">{{ $teamStoreSettings['bullet_3'] }}</h3>
                    <p class="text-slate-300 text-xs md:text-sm leading-relaxed font-medium">Speedy direct-to-door delivery with orders shipped individually to athletes or batched straight to team practice.</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-base md:text-lg font-black uppercase tracking-wide mb-1.5">{{ $teamStoreSettings['bullet_4'] }}</h3>
                    <p class="text-slate-300 text-xs md:text-sm leading-relaxed font-medium">Centralized coach & athletic director portal allowing full management of multiple sports teams from one profile.</p>
                </div>
            </div>

        </div>

        <!-- Call to Action Trigger -->
        <div class="text-center pt-2">
            <a href="/store/search" class="px-9 py-3.5 bg-gradient-to-r from-[#e52d27] to-[#b31217] hover:from-[#f5352e] hover:to-[#c7171d] text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-[0_0_30px_rgba(205,32,44,0.65)] hover:shadow-[0_0_45px_rgba(205,32,44,0.9)] hover:scale-105 transition-all inline-flex items-center justify-center gap-2">
                Explore Active Team Stores &rarr;
            </a>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 6. COACH & TEAM TESTIMONIALS                                          -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="testimonials" class="w-full bg-[#0b0c10] text-white py-10 md:py-14 border-b border-slate-900">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 md:mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">6</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#ff4a58]">COACH TESTIMONIALS</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-white">
                    WHAT COACHES & TEAMS SAY
                </h2>
            </div>
            <a href="{{ route('testimonials.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#ff4a58] hover:text-white transition-colors">
                Read All Testimonials <span>&rarr;</span>
            </a>
        </div>

        @if(isset($testimonials) && $testimonials->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($testimonials as $testimonial)
            <div class="bg-[#11131a] border border-slate-800 rounded-3xl p-6 md:p-7 flex flex-col justify-between shadow-2xl hover:border-[#cd202c]/40 transition-all duration-300">
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex gap-1 text-[#ffb703] text-sm">
                            ★ ★ ★ ★ ★
                        </div>
                        <span class="text-3xl text-[#cd202c] font-serif leading-none opacity-60">“</span>
                    </div>
                    <p class="text-slate-300 text-xs md:text-sm leading-relaxed italic">
                        "{{ $testimonial->content }}"
                    </p>
                </div>
                <div class="flex items-center gap-3.5 pt-3.5 border-t border-slate-800/80">
                    @if($testimonial->image_path)
                        <img src="{{ $testimonial->image_path }}" alt="{{ $testimonial->client_name }}" class="w-10 h-10 rounded-full object-cover shrink-0 border-2 border-slate-700">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#cd202c] to-[#ff4a58] text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md">
                            {{ substr($testimonial->client_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="text-xs font-black uppercase text-white tracking-wide">{{ $testimonial->client_name }}</h4>
                        @if($testimonial->organization)
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">{{ $testimonial->organization }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 7. FINAL HIGH-CONVERSION CTA BANNER                                   -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section class="w-full bg-gradient-to-r from-[#7a0f17] via-[#cd202c] to-[#7a0f17] text-white py-12 md:py-16 relative overflow-hidden text-center">
    <div class="max-w-[1200px] mx-auto px-6 relative z-10 space-y-4 md:space-y-5">
        <span class="inline-block bg-black/50 text-white text-[10px] font-black tracking-widest uppercase px-4 py-1.5 rounded-full border border-white/10 shadow-lg">
            DOMINATE THE FIELD
        </span>
        <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-white leading-tight drop-shadow-2xl">
            READY TO DESIGN YOUR PROGRAM'S LEGACY?
        </h2>
        <p class="text-red-100 text-xs md:text-sm max-w-2xl mx-auto leading-relaxed">
            Get bespoke 3D custom uniform mockups tailored specifically for your organization within 24 hours.
        </p>
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/quote" class="w-full sm:w-auto px-9 py-3.5 bg-black hover:bg-slate-900 text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-2xl transition-all hover:scale-105">
                Request Free Custom Mockup
            </a>
            <a href="{{ route('store.search') }}" class="w-full sm:w-auto px-9 py-3.5 bg-white/20 hover:bg-white/30 text-white border border-white/40 font-black text-xs md:text-sm uppercase tracking-widest rounded-full transition-all backdrop-blur-sm">
                Explore Team Stores
            </a>
        </div>
    </div>
</section>

@endsection
