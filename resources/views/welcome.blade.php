@extends('layouts.app')

@section('content')

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- HERO SECTION (Full-Bleed Unified Athletic Banner with Layered Typography) -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section class="w-full relative min-h-[640px] md:min-h-[720px] lg:min-h-[780px] xl:min-h-[820px] flex items-center overflow-hidden border-b border-slate-900 bg-[#08090c] pt-[64px] md:pt-[72px]">
    <!-- Unified Full-Bleed Background Image (Athletes on Track in Stadium) -->
    <div class="absolute inset-x-0 bottom-0 top-[64px] md:top-[72px] z-0">
        <img src="{{ $heroSettings['banner_image'] ?? asset('images/hero-banner.jpeg') }}" 
             alt="{{ $heroSettings['title'] ?? 'The Commission Apparel Athletes' }}" 
             class="w-full h-full object-cover object-[80%_top] lg:object-[right_top]" 
             fetchpriority="high"
             onerror="this.onerror=null; this.src='{{ asset('images/hero-banner.jpeg') }}';">
        
        <!-- Athletic Directional Gradient Overlays (Text Legibility + Atmospheric Mood) -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#08090c] via-[#08090c]/90 sm:via-[#08090c]/75 md:via-[#08090c]/50 lg:via-[#08090c]/25 to-transparent pointer-events-none"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#08090c] via-transparent to-black/20 pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_40%,rgba(205,32,44,0.18),transparent_60%)] pointer-events-none"></div>
    </div>

    <!-- Content Container Layered Directly On The Image -->
    <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 lg:py-24 relative z-10 w-full">
        <div class="max-w-2xl lg:max-w-3xl space-y-6 text-left">
            
            <!-- Red Pill Tag -->
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-black/60 backdrop-blur-md border border-[#cd202c]/50 text-white shadow-lg">
                <span class="w-2 h-2 rounded-full bg-[#cd202c] animate-pulse"></span>
                <span class="text-[11px] font-black uppercase tracking-widest text-red-100">
                    {{ $heroSettings['badge'] ?? 'Official Team Uniforms & Fan Gear' }}
                </span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl xl:text-7xl font-black uppercase tracking-tight text-white leading-[0.95] drop-shadow-2xl">
                @php
                    $hTitle = $heroSettings['title'] ?? 'CUSTOM GEAR BUILT FOR THE COMMITTED';
                    $words = explode(' ', trim($hTitle));
                    $lastWord = array_pop($words);
                    $firstPart = implode(' ', $words);
                @endphp
                @if($firstPart)
                    {{ $firstPart }} <span class="text-[#ff3b49] drop-shadow-[0_0_30px_rgba(205,32,44,0.8)]">{{ $lastWord }}</span>
                @else
                    <span class="text-[#ff3b49] drop-shadow-[0_0_30px_rgba(205,32,44,0.8)]">{{ $lastWord }}</span>
                @endif
            </h1>

            <!-- Subtitle -->
            <p class="text-slate-200 text-sm md:text-base lg:text-lg max-w-xl font-normal leading-relaxed drop-shadow">
                {{ $heroSettings['subtitle'] ?? 'Dominate the competition with elite performance apparel designed for champion athletes. Precision craftsmanship, fast 2–3 week turnaround, and dedicated online team stores.' }}
            </p>

            <!-- Dual Action CTAs -->
            <div class="pt-2 flex flex-wrap items-center gap-4">
                <a href="{{ $heroSettings['cta_primary_url'] ?? '/quote' }}" class="px-8 py-4 bg-[#cd202c] hover:bg-[#b01621] text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-[0_0_25px_rgba(205,32,44,0.6)] hover:shadow-[0_0_35px_rgba(205,32,44,0.9)] hover:scale-105 transition-all inline-flex items-center justify-center gap-2">
                    <span>{{ $heroSettings['cta_primary_text'] ?? 'Request Free 3D Mockup' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ $heroSettings['cta_secondary_url'] ?? route('store.search') }}" class="px-8 py-4 bg-black/50 hover:bg-white/15 text-white border border-white/30 hover:border-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-lg backdrop-blur-md transition-all inline-flex items-center justify-center gap-2">
                    <span>{{ $heroSettings['cta_secondary_text'] ?? 'Find Your Team Store' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </a>
            </div>

            <!-- Proof Points -->
            <div class="pt-6 border-t border-white/15 flex flex-wrap items-center gap-6 text-xs text-slate-300 font-bold uppercase tracking-wider">
                <div class="flex items-center gap-1.5 text-amber-400">
                    <span class="text-sm">★ ★ ★ ★ ★</span>
                    <span class="text-white font-black ml-1">{{ $landingSettings['hero_proof_1_rating'] ?? '5.0' }}</span>
                    <span class="text-slate-300 font-normal lowercase">{{ $landingSettings['hero_proof_1_text'] ?? 'rated by coaches' }}</span>
                </div>
                <span class="text-white/20">•</span>
                <div class="flex items-center gap-1.5 text-slate-200">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $landingSettings['hero_proof_2'] ?? 'Zero Design Fees' }}</span>
                </div>
                <span class="text-white/20">•</span>
                <div class="flex items-center gap-1.5 text-slate-200">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $landingSettings['hero_proof_3'] ?? '2–3 Wk Turnaround' }}</span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- VALUE PROPOSITIONS / TRUST BAR (Wooter Features + Adidas Clean)        -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<section class="w-full bg-white border-b border-slate-200 py-6 sm:py-8 shadow-sm">
    <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-[#cd202c] border border-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black uppercase text-slate-900 tracking-tight">{{ $landingSettings['trust_1_title'] ?? '2–3 Week Turnaround' }}</h4>
                    <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5 leading-relaxed" style="color: #475569;">{{ $landingSettings['trust_1_desc'] ?? 'Fastest guaranteed production cycle in custom team sports.' }}</p>
                </div>
            </div>

            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-[#cd202c] border border-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black uppercase text-slate-900 tracking-tight">{{ $landingSettings['trust_2_title'] ?? 'Free 3D Design Proofs' }}</h4>
                    <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5 leading-relaxed" style="color: #475569;">{{ $landingSettings['trust_2_desc'] ?? 'Professional 3D vector artwork rendered in 24 hours.' }}</p>
                </div>
            </div>

            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-[#cd202c] border border-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black uppercase text-slate-900 tracking-tight">{{ $landingSettings['trust_3_title'] ?? 'Full Dye-Sublimation' }}</h4>
                    <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5 leading-relaxed" style="color: #475569;">{{ $landingSettings['trust_3_desc'] ?? 'Colors, logos & numbers infused into fabric. Never peels or fades.' }}</p>
                </div>
            </div>

            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-[#cd202c] border border-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black uppercase text-slate-900 tracking-tight">{{ $landingSettings['trust_4_title'] ?? 'Turnkey Team Stores' }}</h4>
                    <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5 leading-relaxed" style="color: #475569;">{{ $landingSettings['trust_4_desc'] ?? 'Direct parent ordering online. Zero paperwork or cash handling.' }}</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- MAIN CONTENT CONTAINER (Crisp Bordered Sections with Light Palette)    -->
<!-- ══════════════════════════════════════════════════════════════════════ -->
<div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10 space-y-8">

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 1. EXPLORE SPORTS CATEGORIES: CUSTOM UNIFORM COLLECTIONS          -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="sports" class="bg-white border border-slate-200 rounded-xl shadow-sm p-5 md:p-6 relative">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg md:text-xl font-black tracking-tight uppercase text-slate-900">
                    @php
                        $customHeading = $landingSettings['collections_heading'] ?? 'VIEW OUR CUSTOM COLLECTIONS';
                    @endphp
                    @if(stripos($customHeading, 'COLLECTIONS') !== false)
                        {!! preg_replace('/(COLLECTIONS)/i', '<span class="text-[#cd202c]">$1</span>', e($customHeading)) !!}
                    @else
                        {{ $customHeading }}
                    @endif
                </h2>
                <p class="text-slate-500 text-xs font-medium mt-1">
                    {{ $landingSettings['collections_subheading'] ?? 'Check out with custom designs crafted for sports teams and fans all over your group and/or organization.' }}
                </p>
            </div>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary whitespace-nowrap px-4 py-2 rounded shadow-sm text-xs uppercase tracking-wider font-bold bg-[#cd202c] hover:bg-[#a11825] text-white transition-colors self-start md:self-auto">
                {{ $landingSettings['collections_cta_text'] ?? 'View Design Collection' }}
            </a>
        </div>

        @if($landingCollections && $landingCollections->isNotEmpty())
        <!-- Gallery Carousel (5 Items Desktop, Side Arrows, Zero Wasted Space) -->
        <div x-data="{ 
            activePage: 0,
            itemsPerPage: window.innerWidth < 640 ? 1 : (window.innerWidth < 768 ? 2 : (window.innerWidth < 1024 ? 3 : 5)),
            totalItems: {{ count($landingCollections ?? []) }},
            get totalPages() { return Math.max(1, Math.ceil(this.totalItems / this.itemsPerPage)) },
            next() { this.activePage = (this.activePage + 1) % this.totalPages },
            prev() { this.activePage = (this.activePage - 1 + this.totalPages) % this.totalPages },
            init() {
                window.addEventListener('resize', () => {
                    this.itemsPerPage = window.innerWidth < 640 ? 1 : (window.innerWidth < 768 ? 2 : (window.innerWidth < 1024 ? 3 : 5));
                    if (this.activePage >= this.totalPages) this.activePage = Math.max(0, this.totalPages - 1);
                });
            }
        }" class="relative w-full">
            
            <div class="overflow-hidden relative w-full -mx-2 px-2 pb-2">
                <div class="flex transition-transform duration-500 ease-out"
                     :style="'transform: translateX(-' + (activePage * 100) + '%)'">
                    @foreach($landingCollections as $collection)
                    <div class="shrink-0 p-2 flex text-left" 
                         :style="'width: ' + (100 / itemsPerPage) + '%'">
                        <div class="bg-white overflow-hidden group border border-slate-200 shadow-sm hover:shadow-lg transition-shadow duration-300 flex flex-col h-full w-full rounded-xl">
                            <!-- Enforced exact aspect ratio bounds: 406.7 x 305.017 -->
                            <a href="{{ route('catalog.index') }}" class="relative overflow-hidden bg-slate-100 w-full block cursor-pointer" style="aspect-ratio: 406.7 / 305.017;">
                                @if($loop->first)
                                <div class="absolute top-2 left-2 bg-[#cd202c] text-white text-[8px] md:text-[9px] font-black tracking-widest uppercase px-2 py-0.5 rounded shadow z-10">
                                    NEWEST ARRIVAL
                                </div>
                                @endif
                                <img src="{{ Str::startsWith($collection->image_path, 'http') ? $collection->image_path : asset(ltrim($collection->image_path, '/')) }}" alt="{{ $collection->tab_name }}" onerror="this.onerror=null; this.src='{{ asset('images/basketball.png') }}';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            </a>
                            <div class="p-3 md:p-4 flex flex-col flex-1">
                                <div class="text-[#cd202c] text-[10px] font-black tracking-widest uppercase mb-1 md:mb-1.5">{{ $collection->tab_name }}</div>
                                <h3 class="text-xs md:text-sm font-black uppercase text-slate-900 leading-tight mb-2 line-clamp-2" title="{{ $collection->title }}">{{ $collection->title }}</h3>
                                <p class="text-slate-600 text-[10px] md:text-xs mb-4 font-normal line-clamp-3 leading-relaxed" style="color: #475569;">{{ $collection->description }}</p>
                                <div class="mt-auto">
                                    <a href="/quote" class="block w-full py-2 bg-[#cd202c] hover:bg-[#a11825] text-white text-[10px] md:text-xs font-bold uppercase tracking-wider text-center rounded shadow-sm transition-colors">TALK TO AN EXPERT</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Navigation Arrows on the Sides -->
            <button @click="prev()" x-show="totalPages > 1" x-cloak class="absolute -left-2 sm:-left-3 md:-left-4 top-[38%] -translate-y-1/2 w-8 h-8 md:w-9 md:h-9 rounded-full bg-white border border-slate-200 shadow-md hover:shadow-lg text-slate-700 flex items-center justify-center hover:bg-slate-50 hover:text-[#cd202c] transition-all z-20 cursor-pointer active:scale-95" aria-label="Previous">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="next()" x-show="totalPages > 1" x-cloak class="absolute -right-2 sm:-right-3 md:-right-4 top-[38%] -translate-y-1/2 w-8 h-8 md:w-9 md:h-9 rounded-full bg-white border border-slate-200 shadow-md hover:shadow-lg text-slate-700 flex items-center justify-center hover:bg-slate-50 hover:text-[#cd202c] transition-all z-20 cursor-pointer active:scale-95" aria-label="Next">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        @else
        <div class="py-8 text-center text-slate-400 font-bold uppercase text-xs">No collections available.</div>
        @endif

    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 2. TURNKEY TEAM STORE: INTERACTIVE PROCESS SLIDER                 -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="team-store" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden"
        x-data="{
            activeStep: 0,
            totalSteps: 5,
            autoPlay: true,
            timer: null,
            init() {
                this.startTimer();
            },
            startTimer() {
                this.timer = setInterval(() => {
                    if (this.autoPlay) {
                        this.activeStep = (this.activeStep + 1) % this.totalSteps;
                    }
                }, 7500);
            },
            pause() {
                this.autoPlay = false;
            },
            resume() {
                this.autoPlay = true;
            },
            setStep(idx) {
                this.activeStep = idx;
                this.pause();
            },
            next() {
                this.activeStep = (this.activeStep + 1) % this.totalSteps;
                this.pause();
            },
            prev() {
                this.activeStep = (this.activeStep - 1 + this.totalSteps) % this.totalSteps;
                this.pause();
            }
        }"
        @mouseenter="pause()"
        @mouseleave="resume()"
    >
        
        <div class="space-y-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto space-y-2">
                <div class="inline-flex items-center gap-2.5 justify-center">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">2</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">
                        {{ $teamStoreSettings['badge'] ?? 'COACH & PROGRAM PLATFORM' }}
                    </span>
                </div>
                
                <h2 class="text-3xl md:text-5xl font-black tracking-tight uppercase text-slate-900 leading-tight">
                    @php
                        $tsHeading = $teamStoreSettings['heading'] ?? 'HOW THE TEAM STORE WORKS STEP-BY-STEP';
                        $tsWords = explode(' ', trim($tsHeading));
                        $tsLastWord = array_pop($tsWords);
                        $tsFirstPart = implode(' ', $tsWords);
                    @endphp
                    @if($tsFirstPart)
                        {{ $tsFirstPart }} <span class="text-[#cd202c]">{{ $tsLastWord }}</span>
                    @else
                        <span class="text-[#cd202c]">{{ $tsLastWord }}</span>
                    @endif
                </h2>
                
                <p class="text-slate-700 text-sm md:text-base font-normal max-w-2xl mx-auto" style="color: #475569;">
                    {{ $teamStoreSettings['subheading'] ?? 'Empower your program with a custom online store that eliminates coach paperwork and generates automatic fundraising revenue.' }}
                </p>
            </div>

            <!-- Interactive Stepper Navigation Bar -->
            <div class="w-full max-w-[1300px] mx-auto overflow-x-auto pb-2 scrollbar-none">
                <div class="flex items-center justify-between gap-2 md:gap-3 min-w-[720px] lg:min-w-0 p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200">
                    
                    <!-- Step 0 Tab -->
                    <button type="button" @click="setStep(0)"
                        class="flex-1 py-3 px-3 rounded-xl transition-all duration-300 text-left flex items-center gap-2.5 group"
                        :class="activeStep === 0 ? 'bg-[#cd202c] text-white shadow-md shadow-[#cd202c]/25' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/60'">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-colors"
                            :class="activeStep === 0 ? 'bg-white text-[#cd202c]' : 'bg-slate-100 text-slate-800 group-hover:bg-slate-200'">01</span>
                        <div class="truncate">
                            <span class="block text-[11px] font-black uppercase tracking-wider truncate">3D Artwork</span>
                            <span class="block text-[9px] font-semibold opacity-80 uppercase tracking-tight truncate">Free 24-48h Mockup</span>
                        </div>
                    </button>

                    <!-- Step 1 Tab -->
                    <button type="button" @click="setStep(1)"
                        class="flex-1 py-3 px-3 rounded-xl transition-all duration-300 text-left flex items-center gap-2.5 group"
                        :class="activeStep === 1 ? 'bg-[#cd202c] text-white shadow-md shadow-[#cd202c]/25' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/60'">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-colors"
                            :class="activeStep === 1 ? 'bg-white text-[#cd202c]' : 'bg-slate-100 text-slate-800 group-hover:bg-slate-200'">02</span>
                        <div class="truncate">
                            <span class="block text-[11px] font-black uppercase tracking-wider truncate">Store Launch</span>
                            <span class="block text-[9px] font-semibold opacity-80 uppercase tracking-tight truncate">Custom School Link</span>
                        </div>
                    </button>

                    <!-- Step 2 Tab -->
                    <button type="button" @click="setStep(2)"
                        class="flex-1 py-3 px-3 rounded-xl transition-all duration-300 text-left flex items-center gap-2.5 group"
                        :class="activeStep === 2 ? 'bg-[#cd202c] text-white shadow-md shadow-[#cd202c]/25' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/60'">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-colors"
                            :class="activeStep === 2 ? 'bg-white text-[#cd202c]' : 'bg-slate-100 text-slate-800 group-hover:bg-slate-200'">03</span>
                        <div class="truncate">
                            <span class="block text-[11px] font-black uppercase tracking-wider truncate">Direct Orders</span>
                            <span class="block text-[9px] font-semibold opacity-80 uppercase tracking-tight truncate">Parents Pay Online</span>
                        </div>
                    </button>

                    <!-- Step 3 Tab -->
                    <button type="button" @click="setStep(3)"
                        class="flex-1 py-3 px-3 rounded-xl transition-all duration-300 text-left flex items-center gap-2.5 group"
                        :class="activeStep === 3 ? 'bg-[#cd202c] text-white shadow-md shadow-[#cd202c]/25' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/60'">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-colors"
                            :class="activeStep === 3 ? 'bg-white text-[#cd202c]' : 'bg-slate-100 text-slate-800 group-hover:bg-slate-200'">04</span>
                        <div class="truncate">
                            <span class="block text-[11px] font-black uppercase tracking-wider truncate">Sublimation</span>
                            <span class="block text-[9px] font-semibold opacity-80 uppercase tracking-tight truncate">2-3 Week Craft</span>
                        </div>
                    </button>

                    <!-- Step 4 Tab -->
                    <button type="button" @click="setStep(4)"
                        class="flex-1 py-3 px-3 rounded-xl transition-all duration-300 text-left flex items-center gap-2.5 group"
                        :class="activeStep === 4 ? 'bg-[#cd202c] text-white shadow-md shadow-[#cd202c]/25' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/60'">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-colors"
                            :class="activeStep === 4 ? 'bg-white text-[#cd202c]' : 'bg-slate-100 text-slate-800 group-hover:bg-slate-200'">05</span>
                        <div class="truncate">
                            <span class="block text-[11px] font-black uppercase tracking-wider truncate">Pack & Ship</span>
                            <span class="block text-[9px] font-semibold opacity-80 uppercase tracking-tight truncate">Athlete Bags & Store Mgmt</span>
                        </div>
                    </button>

                </div>
            </div>

            <!-- Main Interactive Slider Showcase Frame -->
            <div class="w-full max-w-[1300px] mx-auto rounded-2xl md:rounded-3xl shadow-2xl border border-slate-800 bg-[#0e1017] p-6 md:p-10 relative overflow-hidden text-white">
                
                <!-- Ambient Subtle Glow Effect -->
                <div class="absolute -top-32 -right-32 w-96 h-96 bg-[#cd202c]/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>

                <!-- SLIDE 0: 3D Artwork & Store Creation -->
                <div x-show="activeStep === 0" x-cloak
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <div class="lg:col-span-6 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-sm">
                                STEP 01 OF 05 &bull; ZERO COST
                            </span>
                            <span class="text-xs font-mono text-slate-400">DESIGN &amp; LAUNCH</span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-white leading-tight">
                                CUSTOM 3D ARTWORK &amp; STORE SETUP
                            </h3>
                            <p class="text-amber-400 font-semibold text-sm uppercase tracking-wide">
                                100% Free Mockups &bull; Unlimited Revisions &bull; Live in 24–48 Hours
                            </p>
                        </div>

                        <p class="text-slate-300 text-sm md:text-base leading-relaxed font-normal" style="color: #cbd5e1;">
                            Send us your school colors, team mascot, and uniform vision. Our dedicated in-house graphic designers build photorealistic 3D uniform mockups and configure your custom branded online store completely free of charge.
                        </p>

                        <!-- Key Benefits List -->
                        <div class="space-y-3 pt-2">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">24–48 Hour Turnaround:</strong> Photorealistic 3D uniform renders ready for team approval.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Full Team Catalog:</strong> Official uniforms, shooting shirts, travel hoodies, bags &amp; fan apparel.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Zero Financial Commitment:</strong> Free setup with no deposit or credit card required.</p>
                            </div>
                        </div>

                        <!-- Action Bar -->
                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <a href="/quote" class="px-6 py-3 bg-[#cd202c] hover:bg-[#a11825] text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-[#cd202c]/30 hover:scale-105 transition-all inline-flex items-center gap-2">
                                Request Free 3D Mockup <span>&rarr;</span>
                            </a>
                            <button type="button" @click="next()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                Next: Step 02 <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <!-- Visual Tech Frame -->
                    <div class="lg:col-span-6">
                        <div class="relative rounded-2xl overflow-hidden border border-slate-800 bg-[#07090e] p-3 shadow-inner">
                            <div class="flex items-center justify-between pb-3 px-2 border-b border-slate-800/80 mb-3 text-slate-400 text-xs font-mono">
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> 3D Digital Concept Stage</span>
                                <span class="text-slate-500">24-48 HR DELIVERABLE</span>
                            </div>
                            <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-black/60 border border-slate-800/60">
                                <img src="{{ $teamStoreSettings['step_1_image'] ?? asset('images/design.jpeg') }}" alt="3D Uniform Digital Blueprint" class="w-full h-full object-cover object-center" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/design.jpeg') }}'">
                                <div class="absolute bottom-3 right-3 bg-black/80 backdrop-blur-md border border-slate-700 text-white text-[10px] font-mono px-3 py-1.5 rounded-lg shadow-lg">
                                    Unlimited Revisions &bull; Free
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SLIDE 1: Dedicated Store Launch & Parent Link -->
                <div x-show="activeStep === 1" x-cloak
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <div class="lg:col-span-6 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-sm">
                                STEP 02 OF 05 &bull; TURNKEY PLATFORM
                            </span>
                            <span class="text-xs font-mono text-slate-400">ONLINE STOREFRONT</span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-white leading-tight">
                                SHARE CUSTOM LINK WITH PARENTS
                            </h3>
                            <p class="text-amber-400 font-semibold text-sm uppercase tracking-wide">
                                Zero Paper Forms &bull; Zero Cash Handling &bull; Mobile-First
                            </p>
                        </div>

                        <p class="text-slate-300 text-sm md:text-base leading-relaxed font-normal" style="color: #cbd5e1;">
                            We publish a dedicated, mobile-optimized online team store customized with your school branding. Coaches receive a single shareable link to text or email directly to athletes, parents, and booster clubs.
                        </p>

                        <div class="space-y-3 pt-2">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Custom Web URL:</strong> Dedicated storefront link tailored for your school program.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Parent &amp; Fan Gear:</strong> Fans, alumni, and families can order matching gear alongside player uniforms.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">No Coach Liability:</strong> Never handle envelopes of cash or balance checks again.</p>
                            </div>
                        </div>

                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <button type="button" @click="prev()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                <span>&larr;</span> Step 01
                            </button>
                            <a href="/store/search" class="px-6 py-3 bg-[#cd202c] hover:bg-[#a11825] text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-[#cd202c]/30 hover:scale-105 transition-all inline-flex items-center gap-2">
                                Search Active Stores <span>&rarr;</span>
                            </a>
                            <button type="button" @click="next()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                Next: Step 03 <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="relative rounded-2xl overflow-hidden border border-slate-800 bg-[#07090e] p-3 shadow-inner">
                            <div class="flex items-center justify-between pb-3 px-3 border-b border-slate-800/80 mb-3">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></div>
                                </div>
                                <div class="bg-black/80 border border-slate-800 text-slate-400 text-[10px] font-mono px-4 py-1 rounded-full flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-[#cd202c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    thecommissionapparel.com/store/your-school
                                </div>
                                <div class="w-8"></div>
                            </div>
                            <div class="relative rounded-xl overflow-hidden bg-black/60 border border-slate-800/60 aspect-[16/10]">
                                <img src="{{ $teamStoreSettings['step_2_image'] ?? asset('images/team store.png') }}" alt="Custom Team Store Platform" class="w-full h-full object-cover object-top" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/team store.png') }}'">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SLIDE 2: Direct Parent Ordering & Personalization -->
                <div x-show="activeStep === 2" x-cloak
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <div class="lg:col-span-6 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-sm">
                                STEP 03 OF 05 &bull; EASY CHECKOUT
                            </span>
                            <span class="text-xs font-mono text-slate-400">ONLINE ORDERING</span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-white leading-tight">
                                PARENTS ORDER DIRECTLY ONLINE
                            </h3>
                            <p class="text-amber-400 font-semibold text-sm uppercase tracking-wide">
                                Apple Pay &bull; Custom Jersey Numbers &bull; Accurate Sizing
                            </p>
                        </div>

                        <p class="text-slate-300 text-sm md:text-base leading-relaxed font-normal" style="color: #cbd5e1;">
                            Forget deciphering handwritten size charts and chasing unpaid orders. Every parent selects their athlete's size, enters their custom roster number, and pays securely online in seconds.
                        </p>

                        <div class="space-y-3 pt-2">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Player Personalization:</strong> Athletes select exact jersey numbers and custom back names.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Instant Order Confirmation:</strong> Parents receive real-time email receipts and tracking updates.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Coach Dashboard Tracking:</strong> Watch orders roll in live from your coach portal.</p>
                            </div>
                        </div>

                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <button type="button" @click="prev()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                <span>&larr;</span> Step 02
                            </button>
                            <a href="/sizing-charts" class="px-6 py-3 bg-[#cd202c] hover:bg-[#a11825] text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-[#cd202c]/30 hover:scale-105 transition-all inline-flex items-center gap-2">
                                View Sizing Charts <span>&rarr;</span>
                            </a>
                            <button type="button" @click="next()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                Next: Step 04 <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="relative rounded-2xl overflow-hidden border border-slate-800 bg-[#07090e] p-3 shadow-inner">
                            <div class="flex items-center justify-between pb-3 px-3 border-b border-slate-800/80 mb-3 text-slate-400 text-xs font-mono">
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Live Order Ingestion</span>
                                <span class="text-emerald-400 font-bold">100% SECURE CHECKOUT</span>
                            </div>
                            <div class="relative rounded-xl overflow-hidden bg-black/60 border border-slate-800/60 aspect-[4/3]">
                                <img src="{{ $teamStoreSettings['step_3_image'] ?? asset('images/direct order.jpeg') }}" alt="Direct Parent Ordering Storefront" class="w-full h-full object-cover object-top" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/direct order.jpeg') }}'">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SLIDE 3: Rapid Dye-Sublimation Production -->
                <div x-show="activeStep === 3" x-cloak
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <div class="lg:col-span-6 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-sm">
                                STEP 04 OF 05 &bull; CRAFTSMANSHIP
                            </span>
                            <span class="text-xs font-mono text-slate-400">RAPID SUBLIMATION</span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-white leading-tight">
                                RAPID 2–3 WEEK PRODUCTION
                            </h3>
                            <p class="text-amber-400 font-semibold text-sm uppercase tracking-wide">
                                Never Peels, Cracks, or Fades &bull; Guaranteed Turnaround
                            </p>
                        </div>

                        <p class="text-slate-300 text-sm md:text-base leading-relaxed font-normal" style="color: #cbd5e1;">
                            Once your ordering window closes, our automated production pipeline begins immediately. High-tensile moisture-wicking fabrics are dye-sublimated at high heat so colors and numbers are permanently infused into the fabric.
                        </p>

                        <div class="space-y-3 pt-2">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">2–3 Week Turnaround:</strong> Guaranteed delivery so your squad takes the field fully equipped.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Dye-Sublimated Durability:</strong> Permanent inks will never crack, peel, or fade in the wash.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Reinforced Athletic Seams:</strong> 4-way stretch flex threading built for brutal game contact.</p>
                            </div>
                        </div>

                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <button type="button" @click="prev()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                <span>&larr;</span> Step 03
                            </button>
                            <a href="/catalog" class="px-6 py-3 bg-[#cd202c] hover:bg-[#a11825] text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-[#cd202c]/30 hover:scale-105 transition-all inline-flex items-center gap-2">
                                Browse Uniform Styles <span>&rarr;</span>
                            </a>
                            <button type="button" @click="next()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                Next: Step 05 <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="relative rounded-2xl overflow-hidden border border-slate-800 bg-[#07090e] p-3 shadow-inner">
                            <div class="flex items-center justify-between pb-3 px-2 border-b border-slate-800/80 mb-3 text-slate-400 text-xs font-mono">
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Sublimation Production Line</span>
                                <span class="text-slate-400">PHYSICAL SAMPLE</span>
                            </div>
                            <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-black/60 border border-slate-800/60">
                                <img src="{{ $teamStoreSettings['step_4_image'] ?? asset('images/week production.jpeg') }}" alt="Manufactured Sublimated Uniform Reality" class="w-full h-full object-cover object-center" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/week production.jpeg') }}'">
                                <div class="absolute bottom-3 left-3 bg-black/80 backdrop-blur-md border border-slate-700 text-white text-[10px] font-mono px-3 py-1.5 rounded-lg shadow-lg">
                                    Infused Sublimation &bull; Zero Cracking
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SLIDE 4: Individually Bagged by Athlete • Simplified Team Management -->
                <div x-show="activeStep === 4" x-cloak
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <div class="lg:col-span-6 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="bg-[#cd202c] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-sm">
                                STEP 05 OF 05 &bull; PACK &amp; SHIP
                            </span>
                            <span class="text-xs font-mono text-slate-400">DISTRIBUTION &amp; MANAGEMENT</span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-white leading-tight">
                                INDIVIDUALLY BAGGED BY ATHLETE
                            </h3>
                            <p class="text-emerald-400 font-semibold text-sm uppercase tracking-wide">
                                Individually Bagged by Athlete &bull; Simplified Team Management
                            </p>
                        </div>

                        <p class="text-slate-300 text-sm md:text-base leading-relaxed font-normal" style="color: #cbd5e1;">
                            Say goodbye to chaotic uniform distribution! Every athlete’s order arrives individually packaged and clearly labeled with their name, making distribution fast and effortless. Plus, turn your team's online store into a fundraising opportunity that generates revenue to support your program.
                        </p>

                        <div class="space-y-3 pt-2">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Individually Bagged &amp; Labeled:</strong> Every athlete's order is organized by name, allowing you to distribute uniforms in minutes instead of hours.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Free Batch Shipping:</strong> All batch orders are shipped together to one designated location at no additional cost.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Generate Revenue for Your Program:</strong> Turn your team store into a fundraising powerhouse! Offer custom apparel and merchandise to athletes, families, and supporters while building your program's operational budget.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-[#cd202c]/20 border border-[#cd202c] text-[#cd202c] flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">✓</div>
                                <p class="text-slate-200 text-xs md:text-sm font-medium"><strong class="text-white">Manage Multiple Stores:</strong> Oversee multiple teams or programs from one convenient account. Create separate stores, switch between them effortlessly, and manage everything through a single dashboard.</p>
                            </div>
                        </div>

                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <button type="button" @click="prev()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                <span>&larr;</span> Step 04
                            </button>
                            <a href="/quote" class="px-6 py-3 bg-[#cd202c] hover:bg-[#a11825] text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-[#cd202c]/30 hover:scale-105 transition-all inline-flex items-center gap-2">
                                Launch Your Team Store &rarr;
                            </a>
                            <button type="button" @click="setStep(0)" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all inline-flex items-center gap-2 border border-slate-700">
                                Back to Step 01
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="relative rounded-2xl overflow-hidden border border-slate-800 bg-[#07090e] p-3 shadow-inner">
                            <div class="flex items-center justify-between pb-3 px-3 border-b border-slate-800/80 mb-3 text-slate-400 text-xs font-mono">
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Admin Operations &amp; Management</span>
                                <span class="text-emerald-400 font-bold">CENTRALIZED DASHBOARD</span>
                            </div>
                            <div class="relative rounded-xl overflow-hidden bg-black/60 border border-slate-800/60 aspect-[16/10]">
                                <img src="{{ $teamStoreSettings['step_5_image'] ?? asset('images/5.jpeg') }}" alt="Individually Bagged Orders & Simplified Management" class="w-full h-full object-cover object-center" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/5.jpeg') }}'">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Slider Bottom Indicator & Manual Controls -->
                <div class="pt-8 mt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <template x-for="i in totalSteps" :key="i">
                            <button type="button" @click="setStep(i - 1)"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="activeStep === (i - 1) ? 'w-8 bg-[#cd202c]' : 'w-2 bg-slate-700 hover:bg-slate-500'">
                            </button>
                        </template>
                        <span class="text-xs font-mono text-slate-400 ml-3">
                            STEP <span x-text="'0' + (activeStep + 1)" class="text-white font-bold"></span> / 05
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="prev()" aria-label="Previous Step" class="p-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white transition-colors border border-slate-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" @click="next()" aria-label="Next Step" class="p-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white transition-colors border border-slate-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

            </div>

            <!-- 4 Quick Proof Metrics (Unified Compact Strip - Space Maximized) -->
            <div class="max-w-5xl mx-auto bg-slate-50/80 border border-slate-200 rounded-xl md:rounded-2xl overflow-hidden shadow-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                    
                    <!-- Metric 1: $0 Setup Fee -->
                    <div class="p-3.5 sm:p-4 flex items-center justify-center gap-3">
                        <div class="text-2xl md:text-3xl font-black text-[#cd202c] font-heading tracking-tight shrink-0">
                            {{ $landingSettings['slider_metric_1_val'] ?? '$0' }}
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-black uppercase text-slate-900 tracking-tight leading-tight">
                                {{ $landingSettings['slider_metric_1_label'] ?? 'SETUP FEE' }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">
                                {{ $landingSettings['slider_metric_1_sub'] ?? '100% Free Store Creation' }}
                            </div>
                        </div>
                    </div>

                    <!-- Metric 2: 24-48h Rapid Mockups -->
                    <div class="p-3.5 sm:p-4 flex items-center justify-center gap-3">
                        <div class="text-xl md:text-2xl font-black text-slate-900 font-heading tracking-tight shrink-0">
                            {{ $landingSettings['slider_metric_2_val'] ?? '24-48h' }}
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-black uppercase text-slate-900 tracking-tight leading-tight">
                                {{ $landingSettings['slider_metric_2_label'] ?? 'RAPID MOCKUPS' }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">
                                {{ $landingSettings['slider_metric_2_sub'] ?? 'Photorealistic 3D concepts' }}
                            </div>
                        </div>
                    </div>

                    <!-- Metric 3: 2-3 WKS Production -->
                    <div class="p-3.5 sm:p-4 flex items-center justify-center gap-3">
                        <div class="text-xl md:text-2xl font-black text-slate-900 font-heading tracking-tight shrink-0">
                            {{ $landingSettings['slider_metric_3_val'] ?? '2-3 WKS' }}
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-black uppercase text-slate-900 tracking-tight leading-tight">
                                {{ $landingSettings['slider_metric_3_label'] ?? 'PRODUCTION' }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">
                                {{ $landingSettings['slider_metric_3_sub'] ?? 'Guaranteed game-ready' }}
                            </div>
                        </div>
                    </div>

                    <!-- Metric 4: 100% Customer Service -->
                    <div class="p-3.5 sm:p-4 flex flex-col items-center justify-center text-center">
                        <div class="text-2xl md:text-3xl font-black text-slate-900 font-heading tracking-tight leading-none">
                            {{ $landingSettings['slider_metric_4_val'] ?? '100%' }}
                        </div>
                        <div class="text-xs font-bold text-slate-700 leading-tight mt-1">
                            {{ $landingSettings['slider_metric_4_label'] ?? 'Customer Service' }}
                        </div>
                        @if(!empty($landingSettings['slider_metric_4_sub']))
                            <div class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">
                                {{ $landingSettings['slider_metric_4_sub'] }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            <!-- Call to Action Trigger -->
            <div class="text-center pt-2">
                <a href="{{ $landingSettings['team_store_cta_url'] ?? '/quote' }}" class="px-8 py-4 bg-[#cd202c] hover:bg-[#a11825] text-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-lg hover:scale-105 transition-all inline-flex items-center justify-center gap-2">
                    {{ $landingSettings['team_store_cta_text'] ?? 'Start Your Custom Team Store Today →' }}
                </a>
            </div>

        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 3. PROGRAM SPOTLIGHTS & MEDIA (Primary Highlight + Side Stories)  -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="news" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">3</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">{{ $landingSettings['news_badge'] ?? 'COMMISSION NEWS & STORIES' }}</span>
                </div>
                <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-slate-900">
                    {{ $landingSettings['news_heading'] ?? 'PROGRAM SPOTLIGHTS & MEDIA' }}
                </h2>
            </div>
            <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#cd202c] hover:text-slate-900 transition-colors">
                {{ $landingSettings['news_cta_text'] ?? 'View All News & Stories' }} <span>&rarr;</span>
            </a>
        </div>

        @php
            $displayArticles = ($newsArticles && $newsArticles->isNotEmpty()) ? $newsArticles : collect([
                (object)[
                    'id' => 2,
                    'title' => 'Legacy Athletics: Modernizing High School Programs',
                    'slug' => 'legacy-athletics-modernizing-high-school-programs',
                    'category' => 'PROGRAM SPOTLIGHT',
                    'author' => 'The Commission Editorial',
                    'summary' => 'Discover how Legacy Athletics equipped over 400 student-athletes across 6 varsity sports without a single paper order form. The athletic director tracked production status visually and received automated team fundraising kickbacks.',
                    'cover_image' => '/images/group.jpg',
                    'video_url' => null,
                    'published_at' => \Carbon\Carbon::now()->subDays(3),
                    'created_at' => \Carbon\Carbon::now()->subDays(3),
                    'is_featured' => true,
                ],
                (object)[
                    'id' => 1,
                    'title' => "The Journey: Coach Mike's Championship Run",
                    'slug' => 'the-journey-coach-mikes-championship-run',
                    'category' => 'CHAMPIONSHIP RUN',
                    'author' => 'The Commission Editorial',
                    'summary' => 'How Coach Mike led his program to a historic state championship victory wearing bespoke Commission performance uniforms.',
                    'cover_image' => '/images/basketball.png',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'published_at' => \Carbon\Carbon::now()->subDays(5),
                    'created_at' => \Carbon\Carbon::now()->subDays(5),
                    'is_featured' => false,
                ],
                (object)[
                    'id' => 3,
                    'title' => 'Justin Gatlin Signature Track & Field Collection Revealed',
                    'slug' => 'justin-gatlin-signature-track-collection',
                    'category' => 'UNIFORM REVEAL',
                    'author' => 'The Commission Editorial',
                    'summary' => 'Olympic gold medalist Justin Gatlin partners with The Commission to introduce ultra-aerodynamic singlets and speed suits for youth track clubs.',
                    'cover_image' => '/images/gatlin.png',
                    'video_url' => null,
                    'published_at' => \Carbon\Carbon::now()->subDays(8),
                    'created_at' => \Carbon\Carbon::now()->subDays(8),
                    'is_featured' => false,
                ],
                (object)[
                    'id' => 4,
                    'title' => 'The Science of Sublimation: Why Modern Teams Ditch Screenprint',
                    'slug' => 'science-of-sublimation-modern-teams',
                    'category' => 'CRAFTSMANSHIP',
                    'author' => 'The Commission Technical Lab',
                    'summary' => 'Why heavy screen printed numbers crack and peel in the wash, and how molecular dye-sublimation preserves athletic breathability forever.',
                    'cover_image' => '/images/reality-spartan.png',
                    'video_url' => null,
                    'published_at' => \Carbon\Carbon::now()->subDays(12),
                    'created_at' => \Carbon\Carbon::now()->subDays(12),
                    'is_featured' => false,
                ],
            ]);

            $highlightArticle = $displayArticles->firstWhere('is_featured', true) ?? $displayArticles->first();
            $sideArticles = $displayArticles->filter(fn($a) => $a->id !== ($highlightArticle->id ?? null));
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-stretch">
            <!-- Left Column: Primary Highlight -->
            @if($highlightArticle)
            <div class="lg:col-span-7 xl:col-span-8 flex">
                <article class="bg-white border border-slate-200 hover:border-[#cd202c]/50 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between w-full group">
                    <!-- Media Card Header -->
                    <a href="{{ route('news.show', $highlightArticle->slug) }}" class="relative block w-full overflow-hidden aspect-[16/10] bg-slate-100">
                        @if($highlightArticle->cover_image)
                            <img src="{{ Str::startsWith($highlightArticle->cover_image, 'http') ? $highlightArticle->cover_image : asset(ltrim($highlightArticle->cover_image, '/')) }}" 
                                 alt="{{ $highlightArticle->title }}" 
                                 onerror="this.onerror=null; this.src='{{ asset('images/group.jpg') }}';" 
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" 
                                 loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        
                        <div class="absolute top-4 left-4 bg-[#cd202c] text-white text-[9px] md:text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded shadow-md">
                            {{ $highlightArticle->category }}
                        </div>

                        @if($highlightArticle->video_url)
                        <div class="absolute bottom-4 left-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/80 backdrop-blur-md border border-white/20 text-white text-[10px] font-black uppercase tracking-wider group-hover:bg-[#cd202c] group-hover:border-[#cd202c] transition-all shadow-md">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span>WATCH VIDEO</span>
                        </div>
                        @endif
                    </a>

                    <!-- Card Body -->
                    <div class="p-6 md:p-8 flex flex-col justify-between flex-1 space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <span>{{ $highlightArticle->published_at ? $highlightArticle->published_at->format('M d, Y') : $highlightArticle->created_at->format('M d, Y') }}</span>
                                <span>•</span>
                                <span>{{ $highlightArticle->author ?? 'The Commission Editorial' }}</span>
                            </div>
                            <h3 class="text-xl md:text-2xl lg:text-3xl font-black uppercase text-slate-900 leading-tight group-hover:text-[#cd202c] transition-colors">
                                <a href="{{ route('news.show', $highlightArticle->slug) }}">{{ $highlightArticle->title }}</a>
                            </h3>
                            <p class="text-slate-700 text-xs md:text-sm leading-relaxed font-normal text-justify" style="color: #475569;">
                                {{ $highlightArticle->summary }}
                            </p>
                        </div>

                        <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-auto">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Featured Program Story</span>
                            <a href="{{ route('news.show', $highlightArticle->slug) }}" class="text-xs font-black uppercase text-[#cd202c] group-hover:translate-x-1 transition-transform inline-flex items-center gap-1.5">
                                Read Full Story <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            @endif

            <!-- Right Column: Side Stories List -->
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 shadow-sm flex flex-col h-full justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-400">SIDE STORIES</h4>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Latest Updates</span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($sideArticles as $side)
                            <article class="py-3.5 first:pt-1 last:pb-1 group {{ $loop->iteration > 3 ? 'hidden lg:block' : 'block' }}">
                                <a href="{{ route('news.show', $side->slug) }}" class="flex items-start gap-3.5">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden shrink-0 bg-slate-100 border border-slate-200">
                                        @if($side->cover_image)
                                            <img src="{{ Str::startsWith($side->cover_image, 'http') ? $side->cover_image : asset(ltrim($side->cover_image, '/')) }}" 
                                                 alt="{{ $side->title }}" 
                                                 onerror="this.onerror=null; this.src='{{ asset('images/basketball.png') }}';" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                                                 loading="lazy">
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-[9px] md:text-[10px] font-black uppercase text-[#cd202c] tracking-widest block mb-1">
                                            {{ $side->category }}
                                        </span>
                                        <h5 class="text-xs md:text-sm font-bold uppercase text-slate-900 group-hover:text-[#cd202c] transition-colors line-clamp-2 leading-snug">
                                            {{ $side->title }}
                                        </h5>
                                        <span class="text-[10px] text-slate-400 font-medium block mt-1">
                                            {{ $side->published_at ? $side->published_at->format('M d, Y') : $side->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                </a>
                            </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 mt-4">
                        <a href="{{ route('news.index') }}" class="block w-full py-2.5 bg-slate-50 hover:bg-[#cd202c] hover:text-white text-slate-700 text-xs font-bold uppercase tracking-wider text-center rounded-lg border border-slate-200 hover:border-[#cd202c] shadow-sm transition-all">
                            View All News & Stories &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- 4. COACH & TEAM TESTIMONIALS                                      -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <section id="testimonials" class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl shadow-sm p-6 md:p-10 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#cd202c] text-white font-black text-[10px] flex items-center justify-center shadow-sm">4</span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#cd202c]">{{ $landingSettings['testimonials_badge'] ?? 'COACH TESTIMONIALS' }}</span>
                </div>
                <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-slate-900">
                    {{ $landingSettings['testimonials_heading'] ?? 'Testimonials | Feedback from our clients' }}
                </h2>
            </div>
            <a href="{{ route('testimonials.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#cd202c] hover:text-slate-900 transition-colors">
                {{ $landingSettings['testimonials_cta_text'] ?? 'Read All Testimonials' }} <span>&rarr;</span>
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
                    <p class="text-slate-800 text-xs md:text-sm leading-relaxed italic font-normal" style="color: #334155;">
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
    <section class="bg-[#cd202c] border border-red-700 rounded-2xl md:rounded-3xl text-white py-12 md:py-16 px-6 md:px-12 relative overflow-hidden text-center shadow-xl">
        <!-- Subtle athletic diagonal stripe background -->
        <div class="absolute inset-0 bg-[linear-gradient(45deg,rgba(0,0,0,0.06)_25%,transparent_25%,transparent_50%,rgba(0,0,0,0.06)_50%,rgba(0,0,0,0.06)_75%,transparent_75%,transparent)] bg-[size:3rem_3rem] pointer-events-none"></div>

        <div class="max-w-[1200px] mx-auto space-y-5 relative z-10">
            <span class="inline-block bg-white/20 text-white text-[11px] font-black tracking-widest uppercase px-4 py-1.5 rounded-full border border-white/30 shadow-sm backdrop-blur-sm">
                {{ $landingSettings['final_cta_badge'] ?? 'DOMINATE THE COMPETITION' }}
            </span>
            <h2 class="text-3xl md:text-5xl lg:text-6xl font-black uppercase tracking-tight text-white leading-tight drop-shadow-sm">
                {{ $landingSettings['final_cta_title'] ?? "READY TO DESIGN YOUR PROGRAM'S LEGACY?" }}
            </h2>
            <p class="text-red-50 text-sm md:text-base max-w-2xl mx-auto leading-relaxed font-medium">
                {{ $landingSettings['final_cta_subtitle'] ?? 'Get bespoke 3D custom uniform mockups tailored specifically for your organization within 24 hours. Zero risk, 100% free.' }}
            </p>
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ $landingSettings['final_cta_primary_url'] ?? '/quote' }}" class="w-full sm:w-auto px-9 py-4 bg-white hover:bg-slate-100 text-slate-950 font-black text-xs md:text-sm uppercase tracking-widest rounded-full shadow-xl hover:scale-105 transition-all inline-flex items-center justify-center gap-2">
                    <span>{{ $landingSettings['final_cta_primary_text'] ?? 'Request Free Custom Mockup' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ $landingSettings['final_cta_secondary_url'] ?? route('store.search') }}" class="w-full sm:w-auto px-8 py-4 bg-transparent hover:bg-white hover:text-slate-950 text-white border-2 border-white font-black text-xs md:text-sm uppercase tracking-widest rounded-full transition-all inline-flex items-center justify-center">
                    {{ $landingSettings['final_cta_secondary_text'] ?? 'Explore Team Stores' }}
                </a>
            </div>
        </div>
    </section>

</div>

@endsection
