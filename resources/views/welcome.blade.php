@extends('layouts.app')

@section('content')

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- HERO BANNER (Full-Width Panoramic Visual with Navigation Clearance)    -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section class="w-full bg-black border-b border-slate-200" style="padding-top: 85px;">
    <div class="relative w-full overflow-hidden">
        <h1 class="sr-only">{{ $heroSettings['title'] ?? 'CUSTOM GEAR BUILT FOR THE COMMITTED' }}</h1>
        <img src="{{ asset('images/hero-banner.jpeg') }}" alt="{{ $heroSettings['title'] ?? 'Elite Custom Apparel' }}" class="w-full h-auto object-cover block" fetchpriority="high">
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- MAIN CONTENT CONTAINER (Crisp Bordered Sections with Light Palette)    -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10 space-y-8">

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 1. FROM VISION TO VICTORY: CONCEPT TO REALITY                     -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="proof" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">1</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">PRECISION CRAFTSMANSHIP</span>
                </div>
                <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-slate-900">
                    {{ $proofSettings['heading'] ?? 'From Vision to Victory: Concept to Reality' }}
                </h2>
            </div>
            <p class="text-slate-500 text-xs md:text-sm max-w-md text-left md:text-right font-normal">
                {{ $proofSettings['subheading'] ?? 'Precision craftsmanship from 3D digital blueprint to final sublimated uniform.' }}
            </p>
        </div>

        <!-- High-Octane Concept to Reality Canvas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8 items-stretch">
            
            <!-- Left: 3D Vector Concept Proof -->
            <div class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 p-6 flex flex-col justify-between group">
                <div class="flex items-center justify-between z-10 mb-4">
                    <span class="bg-white text-slate-800 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        Phase 1: 3D Vector Design Proof
                    </span>
                    <span class="text-[10px] font-mono text-slate-500 font-bold">DIGITAL SPEC</span>
                </div>
                
                <div class="aspect-[4/3] flex items-center justify-center overflow-hidden my-4 relative">
                    <img src="{{ $proofSettings['concept_image'] }}" alt="3D Jersey Concept" class="max-h-full w-auto object-contain group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    
                    <!-- Callout Pin 1 -->
                    <div class="absolute top-1/4 right-4 bg-slate-900/90 backdrop-blur border border-slate-700 px-3 py-1 rounded-lg text-[10px] font-bold text-white uppercase hidden sm:flex items-center gap-1.5 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-[#cd202c]"></span> {{ $proofSettings['feature_1'] ?? '1. Full Custom Graphics' }}
                    </div>
                    
                    <!-- Callout Pin 2 -->
                    <div class="absolute bottom-1/4 right-4 bg-slate-900/90 backdrop-blur border border-slate-700 px-3 py-1 rounded-lg text-[10px] font-bold text-white uppercase hidden sm:flex items-center gap-1.5 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-[#cd202c]"></span> {{ $proofSettings['feature_2'] ?? '2. Premium Moisture-Wicking Fabric' }}
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-around text-[11px] font-bold text-slate-600 uppercase">
                    <span>✓ Exact Pantone Matching</span>
                    <span>✓ Unlimited Custom Details</span>
                </div>
            </div>

            <!-- Right: Actual Finished Sublimated Uniform in Action -->
            <div class="relative rounded-2xl overflow-hidden border-2 border-[#cd202c]/30 bg-white p-6 flex flex-col justify-between group shadow-sm">
                <div class="flex items-center justify-between z-10 mb-4">
                    <span class="bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> Phase 2: Live Production Jersey
                    </span>
                    <span class="text-[10px] font-mono text-[#cd202c] font-bold">100% SUBLIMATED</span>
                </div>

                <div class="aspect-[4/3] flex items-center justify-center overflow-hidden my-4">
                    <img src="{{ $proofSettings['reality_image'] }}" alt="Actual Sublimated Jersey" class="max-h-full w-auto object-contain group-hover:scale-105 transition-transform duration-500" loading="lazy">
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-around text-[11px] font-bold text-[#cd202c] uppercase">
                    <span>✓ {{ $proofSettings['feature_1'] ?? '1. Full Custom Graphics' }}</span>
                    <span>✓ {{ $proofSettings['feature_3'] ?? '3. Reinforced Athletic Stitching' }}</span>
                </div>
            </div>

        </div>

    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 2. EXPLORE SPORTS CATEGORIES (Responsive Carousel)                -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="sports" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden"
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
        
        <!-- Header with Title and Carousel Controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">2</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">EXPLORE OUR SPORTS CATEGORIES</span>
                </div>
                <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-slate-900">
                    CUSTOM UNIFORM COLLECTIONS
                </h2>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#cd202c] hover:text-slate-900 transition-colors mr-2">
                    View Full Catalog <span>&rarr;</span>
                </a>
                
                <!-- Prev / Next Navigation Buttons -->
                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    <button @click="prev()" aria-label="Previous sport collections" class="w-9 h-9 rounded-full bg-slate-50 border border-slate-200 hover:border-[#cd202c] hover:bg-[#cd202c] hover:text-white text-slate-700 flex items-center justify-center transition-all shadow-sm active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="next()" aria-label="Next sport collections" class="w-9 h-9 rounded-full bg-slate-50 border border-slate-200 hover:border-[#cd202c] hover:bg-[#cd202c] hover:text-white text-slate-700 flex items-center justify-center transition-all shadow-sm active:scale-95">
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
                    <div class="bg-slate-50 hover:bg-white border border-slate-200 hover:border-[#cd202c] hover:shadow-lg rounded-2xl overflow-hidden transition-all duration-300 flex flex-col justify-between h-full w-full group">
                        
                        <!-- Full Graphic Banner Artwork (Un-cropped Aspect Ratio) -->
                        <a href="{{ route('catalog.index') }}" class="relative block overflow-hidden bg-slate-100 w-full" style="aspect-ratio: 406.7 / 305.017;">
                            @if($loop->first)
                            <div class="absolute top-3 left-3 bg-[#cd202c] text-white text-[9px] font-black tracking-widest uppercase px-2.5 py-1 rounded shadow-md z-10">
                                NEWEST ARRIVAL
                            </div>
                            @endif
                            <img src="{{ $collection->image_path }}" alt="{{ $collection->tab_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        </a>

                        <!-- Card Body -->
                        <div class="p-5 flex flex-col flex-1 justify-between">
                            <div>
                                <div class="text-[#cd202c] text-[10px] font-black tracking-widest uppercase mb-1.5">
                                    {{ $collection->tab_name }}
                                </div>
                                <h3 class="text-sm font-black uppercase text-slate-900 leading-tight mb-2 line-clamp-2 group-hover:text-[#cd202c] transition-colors" title="{{ $collection->title }}">
                                    {{ $collection->title }}
                                </h3>
                                <p class="text-slate-600 text-xs mb-4 font-normal line-clamp-3 leading-relaxed">
                                    {{ $collection->description }}
                                </p>
                            </div>
                            
                            <div class="mt-auto">
                                <a href="/quote" class="block w-full py-2.5 bg-[#cd202c] hover:bg-[#a11825] text-white text-xs font-bold uppercase tracking-wider text-center rounded-lg shadow-sm transition-colors">
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
                <button @click="activePage = i - 1" class="h-1.5 rounded-full transition-all duration-300" :class="activePage === i - 1 ? 'bg-[#cd202c] w-8' : 'bg-slate-200 hover:bg-slate-300 w-2.5'"></button>
            </template>
        </div>
        @else
        <div class="py-8 text-center text-slate-400 font-bold uppercase text-xs">No collections available.</div>
        @endif

    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 3. COMMISSION NEWS & STORIES (Balanced 3-Card Spotlight)          -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="news" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">3</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">COMMISSION NEWS & STORIES</span>
                </div>
                <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-slate-900">
                    PROGRAM SPOTLIGHTS & MEDIA
                </h2>
            </div>
            <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#cd202c] hover:text-slate-900 transition-colors">
                View All News & Stories <span>&rarr;</span>
            </a>
        </div>

        @if($newsArticles->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 items-stretch">
            @foreach($newsArticles as $article)
            <article class="bg-slate-50 hover:bg-white border border-slate-200 hover:border-[#cd202c]/50 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg flex flex-col group transition-all duration-300">
                <!-- Media Card Header -->
                <a href="{{ route('news.show', $article->slug) }}" class="relative block w-full overflow-hidden aspect-[16/10] bg-slate-100">
                    @if($article->cover_image)
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    
                    <div class="absolute top-3 left-3 bg-[#cd202c] text-white text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded shadow-sm">
                        {{ $article->category }}
                    </div>

                    @if($article->video_url)
                    <div class="absolute bottom-3 left-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/80 backdrop-blur-md border border-white/20 text-white text-[10px] font-black uppercase tracking-wider group-hover:bg-[#cd202c] group-hover:border-[#cd202c] transition-all shadow-md">
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
                        <h3 class="text-base md:text-lg font-black uppercase text-slate-900 leading-snug group-hover:text-[#cd202c] transition-colors line-clamp-2">
                            <a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a>
                        </h3>
                        <p class="text-slate-600 text-xs leading-relaxed line-clamp-3 font-normal">
                            {{ $article->summary }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex items-center justify-between mt-auto">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">{{ $article->author ?? 'The Commission Editorial' }}</span>
                        <a href="{{ route('news.show', $article->slug) }}" class="text-xs font-black uppercase text-[#cd202c] group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                            Read Story <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @else
        <div class="py-8 text-center text-slate-400 font-bold uppercase text-xs">No stories published yet.</div>
        @endif

    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 4. LAUNCH YOUR TEAM STORE                                         -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="team-store" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden">
        
        <div class="space-y-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto space-y-2">
                <div class="inline-flex items-center gap-2.5 justify-center">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">4</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">
                        COACH & PROGRAM PLATFORM
                    </span>
                </div>
                
                <h2 class="text-3xl md:text-5xl font-black tracking-tight uppercase text-slate-900 leading-tight">
                    TEAM <span class="text-[#cd202c] ml-1">STORE</span>
                </h2>
                
                <p class="text-slate-600 text-sm md:text-base font-normal max-w-2xl mx-auto">
                    {{ $teamStoreSettings['subheading'] ?? 'Empower your program with a custom online store that eliminates hassle and generates revenue.' }}
                </p>
            </div>

            <!-- Full-Width Panoramic Team Store UI Platform Mockup Banner -->
            <div class="w-full max-w-[1300px] mx-auto rounded-2xl overflow-hidden shadow-md border border-slate-200 bg-slate-900 p-2.5 md:p-3 group">
                <div class="flex items-center gap-1.5 pb-2 px-2 border-b border-slate-800 mb-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/80"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-500/80"></div>
                    <span class="text-[10px] font-mono text-slate-400 ml-2 tracking-wide">thecommissionapparel.com/store/live</span>
                </div>
                <div class="w-full rounded-xl overflow-hidden relative bg-black">
                    <img src="{{ $teamStoreSettings['image'] }}" alt="Team Store Digital Platform" class="w-full h-auto object-contain block transition-transform duration-700 group-hover:scale-[1.01]" loading="lazy">
                </div>
            </div>

            <!-- 4 Core Benefits (2x2 Grid) -->
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-x-12 md:gap-x-16 gap-y-6 md:gap-y-8 pt-2">
                
                <!-- Feature 1 -->
                <div class="flex gap-4">
                    <div class="shrink-0 mt-0.5">
                        <span class="w-6 h-6 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shadow-sm">✓</span>
                    </div>
                    <div>
                        <h3 class="text-slate-900 text-base font-black uppercase tracking-wide mb-1">{{ $teamStoreSettings['bullet_1'] }}</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed font-normal">No collecting cash or forms. Families simply use your custom team link to order jerseys and fan gear directly online.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="flex gap-4">
                    <div class="shrink-0 mt-0.5">
                        <span class="w-6 h-6 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shadow-sm">✓</span>
                    </div>
                    <div>
                        <h3 class="text-slate-900 text-base font-black uppercase tracking-wide mb-1">{{ $teamStoreSettings['bullet_2'] }}</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed font-normal">Exclusive bespoke hoodies, athletic tees, gym bags, and official uniform packages custom tailored for your school.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="flex gap-4">
                    <div class="shrink-0 mt-0.5">
                        <span class="w-6 h-6 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shadow-sm">✓</span>
                    </div>
                    <div>
                        <h3 class="text-slate-900 text-base font-black uppercase tracking-wide mb-1">{{ $teamStoreSettings['bullet_3'] }}</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed font-normal">Speedy direct-to-door delivery with orders shipped individually to athletes or batched straight to team practice.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="flex gap-4">
                    <div class="shrink-0 mt-0.5">
                        <span class="w-6 h-6 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shadow-sm">✓</span>
                    </div>
                    <div>
                        <h3 class="text-slate-900 text-base font-black uppercase tracking-wide mb-1">{{ $teamStoreSettings['bullet_4'] }}</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed font-normal">Centralized coach & athletic director portal allowing full management of multiple sports teams from one profile.</p>
                    </div>
                </div>

            </div>

            <!-- Call to Action Trigger -->
            <div class="text-center pt-2">
                <a href="/store/search" class="px-8 py-3.5 bg-[#cd202c] hover:bg-[#a11825] text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-md hover:scale-105 transition-all inline-flex items-center justify-center gap-2">
                    Explore Active Team Stores &rarr;
                </a>
            </div>

        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 5. COACH & TEAM TESTIMONIALS                                      -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="testimonials" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">5</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">COACH TESTIMONIALS</span>
                </div>
                <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-slate-900">
                    WHAT COACHES & TEAMS SAY
                </h2>
            </div>
            <a href="{{ route('testimonials.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#cd202c] hover:text-slate-900 transition-colors">
                Read All Testimonials <span>&rarr;</span>
            </a>
        </div>

        @if(isset($testimonials) && $testimonials->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($testimonials as $testimonial)
            <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-6 md:p-7 flex flex-col justify-between shadow-sm hover:border-[#cd202c]/40 hover:shadow-md transition-all duration-300">
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex gap-1 text-[#f59e0b] text-sm">
                            ★ ★ ★ ★ ★
                        </div>
                        <span class="text-3xl text-[#cd202c] font-serif leading-none opacity-40">“</span>
                    </div>
                    <p class="text-slate-700 text-xs md:text-sm leading-relaxed italic font-normal">
                        "{{ $testimonial->content }}"
                    </p>
                </div>
                <div class="flex items-center gap-3.5 pt-4 border-t border-slate-200">
                    @if($testimonial->image_path)
                        <img src="{{ $testimonial->image_path }}" alt="{{ $testimonial->client_name }}" class="w-10 h-10 rounded-full object-cover shrink-0 border border-slate-300">
                    @else
                        <div class="w-10 h-10 rounded-full bg-[#cd202c] text-white flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                            {{ substr($testimonial->client_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="text-xs font-black uppercase text-slate-900 tracking-wide">{{ $testimonial->client_name }}</h4>
                        @if($testimonial->organization)
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mt-0.5">{{ $testimonial->organization }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 6. FINAL HIGH-CONVERSION CTA BANNER                               -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section class="bg-gradient-to-r from-[#8b111a] via-[#cd202c] to-[#8b111a] border border-red-800/80 rounded-2xl md:rounded-3xl text-white py-10 md:py-14 px-6 md:px-12 relative overflow-hidden text-center shadow-lg">
        <div class="max-w-[1200px] mx-auto space-y-4">
            <span class="inline-block bg-black/40 text-white text-[10px] font-black tracking-widest uppercase px-3.5 py-1.5 rounded-full border border-white/10 shadow-sm">
                DOMINATE THE FIELD
            </span>
            <h2 class="text-2xl md:text-4xl lg:text-5xl font-black uppercase tracking-tight text-white leading-tight drop-shadow-md">
                READY TO DESIGN YOUR PROGRAM'S LEGACY?
            </h2>
            <p class="text-red-100 text-xs md:text-sm max-w-2xl mx-auto leading-relaxed font-normal">
                Get bespoke 3D custom uniform mockups tailored specifically for your organization within 24 hours.
            </p>
            <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/quote" class="w-full sm:w-auto px-8 py-3.5 bg-black hover:bg-slate-900 text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-lg transition-all hover:scale-105">
                    Request Free Custom Mockup
                </a>
                <a href="{{ route('store.search') }}" class="w-full sm:w-auto px-8 py-3.5 bg-white/20 hover:bg-white/30 text-white border border-white/40 font-black text-xs md:text-sm uppercase tracking-widest rounded-full transition-all backdrop-blur-sm">
                    Explore Team Stores
                </a>
            </div>
        </div>
    </section>

</div>

@endsection
