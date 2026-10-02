@extends('layouts.app')

@section('content')

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 1. HERO BANNER SECTION (Dynamic Performance Treatment)                -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section class="w-full bg-[#08090c] text-white pt-28 md:pt-32 lg:pt-36 pb-16 lg:pb-20 border-b border-slate-900 relative overflow-hidden">
    <!-- Stadium Floodlight Glows -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_15%,rgba(205,32,44,0.18),transparent_70%)] pointer-events-none"></div>

    <div class="max-w-[1500px] mx-auto px-6 relative z-20 space-y-8">
        
        <!-- Top Section Bar with Headline and Action CTAs -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">1</span>
                    <span class="text-xs md:text-sm font-black uppercase tracking-widest text-[#ff4a58]">
                        CUSTOM GEAR BUILT FOR THE COMMITTED
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black uppercase tracking-tight text-white leading-tight drop-shadow-2xl">
                    {{ $heroSettings['title'] ?? 'CUSTOM GEAR BUILT FOR THE COMMITTED' }}
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm md:text-base max-w-2xl font-normal leading-relaxed">
                    {{ $heroSettings['subtitle'] ?? 'Dominate the competition with elite performance apparel designed for champion athletes. Elevate your team\'s game with custom uniforms crafted with speed and precision.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 shrink-0 pb-1">
                <a href="{{ $heroSettings['cta_primary_url'] ?? '/quote' }}" class="px-8 py-4 bg-gradient-to-r from-[#e52d27] to-[#b31217] hover:from-[#f5352e] hover:to-[#c7171d] text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-[0_0_30px_rgba(205,32,44,0.65)] hover:shadow-[0_0_45px_rgba(205,32,44,0.9)] hover:scale-105 transition-all inline-flex items-center justify-center gap-2">
                    {{ $heroSettings['cta_primary_text'] ?? 'START DESIGNING' }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ $heroSettings['cta_secondary_url'] ?? '/catalog' }}" class="px-7 py-4 bg-slate-900/80 hover:bg-white/10 text-white border border-slate-700 hover:border-white font-black text-xs md:text-sm uppercase tracking-wider rounded-full transition-all inline-flex items-center justify-center backdrop-blur-sm">
                    {{ $heroSettings['cta_secondary_text'] ?? 'VIEW CATALOG' }}
                </a>
            </div>
        </div>

        <!-- Full-Width Panoramic Hero Banner Container (100% visible, no cropped athletes or text) -->
        <div class="relative w-full rounded-3xl overflow-hidden border border-slate-800 shadow-[0_25px_70px_rgba(0,0,0,0.9)] bg-slate-950 group">
            <img src="{{ $heroSettings['media_path'] }}" alt="Commission Apparel - Elite Custom Apparel" class="w-full h-auto object-contain block transition-transform duration-700 group-hover:scale-[1.01]" fetchpriority="high">
        </div>

        <!-- Trust Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 pt-2 text-center">
            <div class="bg-[#11131a] border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <div class="text-2xl lg:text-4xl font-black text-white drop-shadow">1,000+</div>
                <div class="text-[10px] md:text-xs uppercase font-bold text-slate-400 tracking-wider mt-1">Programs Outfitted</div>
            </div>
            <div class="bg-[#11131a] border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <div class="text-2xl lg:text-4xl font-black text-white drop-shadow">2–3 Wks</div>
                <div class="text-[10px] md:text-xs uppercase font-bold text-slate-400 tracking-wider mt-1">Fast Turnaround</div>
            </div>
            <div class="bg-[#11131a] border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <div class="text-2xl lg:text-4xl font-black text-[#ff4a58] drop-shadow-[0_0_10px_rgba(205,32,44,0.5)]">100%</div>
                <div class="text-[10px] md:text-xs uppercase font-bold text-slate-400 tracking-wider mt-1">Custom Artwork</div>
            </div>
            <div class="bg-[#11131a] border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <div class="text-2xl lg:text-4xl font-black text-[#ffb703] drop-shadow">5.0 ★</div>
                <div class="text-[10px] md:text-xs uppercase font-bold text-slate-400 tracking-wider mt-1">Coach Satisfaction</div>
            </div>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 2. FROM VISION TO VICTORY: CONCEPT TO REALITY                         -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="proof" class="w-full bg-[#0b0c10] text-white py-24 border-b border-slate-900 relative">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-14">
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch bg-[#11131a] border border-slate-800 rounded-3xl p-6 lg:p-10 shadow-2xl relative overflow-hidden">
            
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
<!-- 3. EXPLORE SPORTS CATEGORIES GRID                                    -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="sports" class="w-full bg-[#08090c] text-white py-24 border-b border-slate-900">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">3</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#ff4a58]">EXPLORE OUR SPORTS CATEGORIES</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-white">
                    CUSTOM UNIFORM COLLECTIONS
                </h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#ff4a58] hover:text-white transition-colors">
                View Full Design Catalog <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($landingCollections as $collection)
            <div class="bg-[#11131a] border border-slate-800 hover:border-[#cd202c] hover:shadow-[0_0_25px_rgba(205,32,44,0.3)] rounded-3xl overflow-hidden transition-all duration-500 flex flex-col group">
                <a href="{{ route('catalog.index') }}" class="relative block overflow-hidden aspect-[4/5] bg-slate-950">
                    <img src="{{ $collection->image_path }}" alt="{{ $collection->tab_name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <span class="inline-block bg-[#cd202c] text-white text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md mb-1.5 shadow-md">
                            {{ $collection->tab_name }}
                        </span>
                        <h3 class="text-lg font-black uppercase text-white line-clamp-1 drop-shadow">{{ $collection->title }}</h3>
                    </div>
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <p class="text-slate-400 text-xs line-clamp-2 mb-4 leading-relaxed">{{ $collection->description }}</p>
                    <a href="/quote" class="mt-auto block w-full py-3 bg-slate-900 hover:bg-[#cd202c] text-white text-xs font-black uppercase tracking-wider text-center rounded-xl border border-slate-800 hover:border-[#cd202c] transition-all shadow-md">
                        Customize Gear &rarr;
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-500 font-bold uppercase">No collections available.</div>
            @endforelse
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 4. COMMISSION NEWS & STORIES (Option B 3-Card Teaser)                 -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="news" class="w-full bg-[#0b0c10] text-white py-24 border-b border-slate-900">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- Featured Story Card (Left 7 Columns) -->
            @php $featuredArticle = $newsArticles->first(); @endphp
            <div class="lg:col-span-7 bg-[#11131a] border border-slate-800 hover:border-[#cd202c]/60 rounded-3xl overflow-hidden shadow-2xl flex flex-col group transition-all duration-500 hover:shadow-[0_0_30px_rgba(205,32,44,0.2)]">
                <a href="{{ route('news.show', $featuredArticle->slug) }}" class="relative block overflow-hidden aspect-[4/3] sm:aspect-[16/11] lg:aspect-[16/11] bg-slate-950">
                    @if($featuredArticle->cover_image)
                        <img src="{{ $featuredArticle->cover_image }}" alt="{{ $featuredArticle->title }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700" loading="lazy">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>
                    <div class="absolute top-4 left-4 bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-md shadow-lg">
                        {{ $featuredArticle->category }}
                    </div>
                    
                    <!-- Option B Pill Play Button Overlay -->
                    <div class="absolute bottom-4 left-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-black/80 backdrop-blur-md border border-white/20 text-white text-xs font-black uppercase tracking-wider group-hover:bg-[#cd202c] group-hover:border-[#cd202c] transition-all shadow-2xl">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <span>PLAY VIDEO</span>
                    </div>
                </a>
                <div class="p-6 flex flex-col justify-between flex-1">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            {{ $featuredArticle->published_at ? $featuredArticle->published_at->format('M d, Y') : $featuredArticle->created_at->format('M d, Y') }}
                        </span>
                        <h3 class="text-xl md:text-2xl font-black uppercase text-white leading-tight mb-2.5 group-hover:text-[#ff4a58] transition-colors">
                            <a href="{{ route('news.show', $featuredArticle->slug) }}">{{ $featuredArticle->title }}</a>
                        </h3>
                        <p class="text-slate-400 text-xs md:text-sm leading-relaxed line-clamp-3">
                            {{ $featuredArticle->summary }}
                        </p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase">{{ $featuredArticle->author ?? 'The Commission Editorial' }}</span>
                        <a href="{{ route('news.show', $featuredArticle->slug) }}" class="text-xs font-black uppercase text-[#ff4a58] group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                            Read Full Story <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stacked Secondary Stories (Right 5 Columns with Full-Width Top Images) -->
            <div class="lg:col-span-5 flex flex-col gap-6 justify-between">
                @foreach($newsArticles->slice(1, 2) as $sideArticle)
                <article class="bg-[#11131a] border border-slate-800 hover:border-[#cd202c]/50 rounded-3xl overflow-hidden shadow-xl flex flex-col flex-1 group transition-all duration-300">
                    <a href="{{ route('news.show', $sideArticle->slug) }}" class="relative block w-full overflow-hidden aspect-[16/9] sm:aspect-[2/1] lg:aspect-[16/9] bg-slate-950">
                        @if($sideArticle->cover_image)
                            <img src="{{ $sideArticle->cover_image }}" alt="{{ $sideArticle->title }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                        <div class="absolute top-3 left-3 bg-[#cd202c] text-white text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md shadow">
                            {{ $sideArticle->category }}
                        </div>
                    </a>
                    <div class="p-5 flex flex-col justify-between flex-1">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">
                                {{ $sideArticle->published_at ? $sideArticle->published_at->format('M d, Y') : $sideArticle->created_at->format('M d, Y') }}
                            </span>
                            <h4 class="text-base font-black uppercase text-white leading-snug group-hover:text-[#ff4a58] transition-colors line-clamp-2 mb-1.5">
                                <a href="{{ route('news.show', $sideArticle->slug) }}">{{ $sideArticle->title }}</a>
                            </h4>
                            <p class="text-slate-400 text-xs line-clamp-2 leading-relaxed">
                                {{ $sideArticle->summary }}
                            </p>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $sideArticle->author ?? 'The Commission Editorial' }}</span>
                            <a href="{{ route('news.show', $sideArticle->slug) }}" class="text-[11px] font-black uppercase text-[#ff4a58] group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                                Read Story <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

        </div>
        @else
        <div class="py-12 text-center text-slate-500 font-bold uppercase">No stories published yet.</div>
        @endif

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 5. LAUNCH YOUR TEAM STORE                                             -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="team-store" class="w-full bg-[#08090c] text-white py-24 border-b border-slate-900">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left: Digital Platform Mockup -->
            <div class="lg:col-span-7 relative">
                <div class="rounded-3xl overflow-hidden border border-slate-800 shadow-[0_20px_60px_rgba(0,0,0,0.8)] bg-[#11131a] p-3 group">
                    <div class="flex items-center gap-1.5 pb-2.5 px-2 border-b border-slate-800 mb-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/80"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-green-500/80"></div>
                        <span class="text-[10px] font-mono text-slate-500 ml-2 tracking-wide">thecommissionapparel.com/store/live</span>
                    </div>
                    <img src="{{ $teamStoreSettings['image'] }}" alt="Team Store Digital Platform" class="w-full h-auto rounded-xl object-contain group-hover:scale-[1.02] transition-transform duration-500 shadow-xl" loading="lazy">
                </div>
            </div>

            <!-- Right: Features List -->
            <div class="lg:col-span-5 space-y-6 text-left">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#cd202c] text-white font-black text-xs flex items-center justify-center shadow-[0_0_12px_rgba(205,32,44,0.8)]">5</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#ff4a58]">LAUNCH YOUR TEAM STORE</span>
                </div>

                <div class="inline-block px-3.5 py-1.5 rounded-lg bg-[#cd202c]/20 border border-[#cd202c]/50 text-[#ff4a58] text-xs font-black uppercase tracking-widest">
                    ⚡ ORDER DEADLINE: ACTIVE PORTAL
                </div>

                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-white leading-tight">
                    {{ $teamStoreSettings['heading'] ?? 'LAUNCH YOUR TEAM STORE' }}
                </h2>
                <p class="text-slate-300 text-sm md:text-base leading-relaxed font-normal">
                    {{ $teamStoreSettings['subheading'] ?? 'Empower your program with a custom online store that eliminates coach hassle and generates revenue.' }}
                </p>

                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shrink-0 mt-0.5 shadow-md">✓</span>
                        <div>
                            <h4 class="text-white font-black text-sm uppercase tracking-wide">{{ $teamStoreSettings['bullet_1'] }}</h4>
                            <p class="text-slate-400 text-xs">No collecting cash or forms. Parents order directly online.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shrink-0 mt-0.5 shadow-md">✓</span>
                        <div>
                            <h4 class="text-white font-black text-sm uppercase tracking-wide">{{ $teamStoreSettings['bullet_2'] }}</h4>
                            <p class="text-slate-400 text-xs">Custom hoodies, tees, bags, and uniform kits tailored to your school.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shrink-0 mt-0.5 shadow-md">✓</span>
                        <div>
                            <h4 class="text-white font-black text-sm uppercase tracking-wide">{{ $teamStoreSettings['bullet_3'] }}</h4>
                            <p class="text-slate-400 text-xs">Orders shipped individually or batched straight to practice.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded bg-[#cd202c] text-white text-xs font-black flex items-center justify-center shrink-0 mt-0.5 shadow-md">✓</span>
                        <div>
                            <h4 class="text-white font-black text-sm uppercase tracking-wide">{{ $teamStoreSettings['bullet_4'] }}</h4>
                            <p class="text-slate-400 text-xs">Manage multiple sports (Football, Track, Basketball) from one coach profile.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <a href="/store/search" class="inline-flex items-center gap-2 px-9 py-4 bg-gradient-to-r from-[#e52d27] to-[#b31217] hover:from-[#f5352e] hover:to-[#c7171d] text-white text-xs md:text-sm font-black uppercase tracking-widest rounded-full shadow-[0_0_25px_rgba(205,32,44,0.6)] hover:shadow-[0_0_40px_rgba(205,32,44,0.85)] hover:scale-105 transition-all">
                        Explore Active Team Stores &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- 6. COACH & TEAM TESTIMONIALS                                          -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section id="testimonials" class="w-full bg-[#0b0c10] text-white py-24 border-b border-slate-900">
    <div class="max-w-[1500px] mx-auto px-6">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
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
            <div class="bg-[#11131a] border border-slate-800 rounded-3xl p-7 flex flex-col justify-between shadow-2xl hover:border-[#cd202c]/40 transition-all duration-300">
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex gap-1 text-[#ffb703] text-sm">
                            ★ ★ ★ ★ ★
                        </div>
                        <span class="text-3xl text-[#cd202c] font-serif leading-none opacity-60">“</span>
                    </div>
                    <p class="text-slate-300 text-xs md:text-sm leading-relaxed italic">
                        "{{ $testimonial->content }}"
                    </p>
                </div>
                <div class="flex items-center gap-3.5 pt-4 border-t border-slate-800/80">
                    @if($testimonial->image_path)
                        <img src="{{ $testimonial->image_path }}" alt="{{ $testimonial->client_name }}" class="w-11 h-11 rounded-full object-cover shrink-0 border-2 border-slate-700">
                    @else
                        <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-[#cd202c] to-[#ff4a58] text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md">
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
<section class="w-full bg-gradient-to-r from-[#7a0f17] via-[#cd202c] to-[#7a0f17] text-white py-24 relative overflow-hidden text-center">
    <div class="max-w-[1200px] mx-auto px-6 relative z-10 space-y-7">
        <span class="inline-block bg-black/50 text-white text-[10px] font-black tracking-widest uppercase px-4 py-1.5 rounded-full border border-white/10 shadow-lg">
            DOMINATE THE FIELD
        </span>
        <h2 class="text-4xl md:text-6xl font-black uppercase tracking-tight text-white leading-tight drop-shadow-2xl">
            READY TO DESIGN YOUR PROGRAM'S LEGACY?
        </h2>
        <p class="text-red-100 text-sm md:text-base max-w-2xl mx-auto leading-relaxed">
            Get bespoke 3D custom uniform mockups tailored specifically for your organization within 24 hours.
        </p>
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/quote" class="w-full sm:w-auto px-10 py-4 bg-black hover:bg-slate-900 text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-2xl transition-all hover:scale-105">
                Request Free Custom Mockup
            </a>
            <a href="{{ route('store.search') }}" class="w-full sm:w-auto px-10 py-4 bg-white/20 hover:bg-white/30 text-white border border-white/40 font-black text-xs md:text-sm uppercase tracking-widest rounded-full transition-all backdrop-blur-sm">
                Explore Team Stores
            </a>
        </div>
    </div>
</section>

@endsection
