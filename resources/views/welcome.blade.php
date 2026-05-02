@extends('layouts.app')

@section('content')

<!-- Hero Section -->
<section class="relative bg-white min-h-[80vh] flex items-center justify-center pt-4 lg:pt-20 overflow-hidden">
    <!-- Subtle Background Dot Pattern -->
    <div class="absolute inset-0 z-0 opacity-40" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;"></div>

    <div class="relative z-10 max-w-[1500px] mx-auto px-6 w-full grid lg:grid-cols-2 gap-12 items-stretch pt-4 lg:pt-12 pb-4 lg:pb-8">
        <div class="max-w-2xl animate-slide-up flex flex-col justify-center order-2 lg:order-1">
           
            
            <h1 class="text-7xl md:text-7xl lg:text-7xl font-black tracking-tighter leading-[0.85] mb-6 uppercase text-slate-950">
                Elite Custom <br/>
                <span class="text-secondary">Apparel</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-slate-600 font-medium max-w-xl mb-8 leading-relaxed">
                {{ $heroSettings['subtitle'] }}
            </p>

            <ul class="space-y-3 mb-10 text-slate-700 font-bold tracking-wide text-base md:text-lg">
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Pro-Grade Quality Materials
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Automated Team Stores for Parents
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Lightning Fast Turnaround
                </li>
            </ul>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('catalog.index') }}" class="btn btn-primary px-8 py-4 text-base font-black uppercase tracking-wider rounded-md shadow-md transition-all">View Design Collection</a>
                <a href="/coach/dashboard" class="btn border-2 border-slate-300 bg-white !text-slate-900 hover:bg-slate-50 px-8 py-4 text-base font-black uppercase tracking-wider rounded-md shadow-sm transition-all">Create Team Store</a>
            </div>
        </div>

        <div class="relative flex items-center justify-center animate-fade-in h-full w-full min-h-[450px] order-1 lg:order-2">
             <!-- Background geometric elements to match mockup flair -->
             <div class="absolute top-1/4 right-0 w-32 h-2 bg-secondary transform -rotate-12 rounded-full opacity-80 z-0"></div>
             <div class="absolute bottom-1/3 left-10 w-24 h-2 bg-secondary transform rotate-6 rounded-full opacity-80 z-0"></div>
             <div class="absolute top-1/3 left-0 w-4 h-4 bg-slate-800 transform rotate-45 z-0"></div>
             <div class="absolute bottom-1/4 right-10 w-3 h-3 bg-secondary transform rotate-12 z-0"></div>

             <!-- Clean bright background media -->
             @if(($heroSettings['media_type'] ?? 'image') === 'video')
                 @php
                     $ext = strtolower(pathinfo($heroSettings['media_path'], PATHINFO_EXTENSION));
                     $mime = 'video/mp4';
                     if ($ext === 'mov') $mime = 'video/quicktime';
                     elseif ($ext === 'webm') $mime = 'video/webm';
                     elseif ($ext === 'ogg') $mime = 'video/ogg';
                 @endphp
                 <video autoplay loop muted playsinline class="relative z-10 w-full h-full max-h-[600px] object-contain drop-shadow-2xl">
                     <source src="{{ asset($heroSettings['media_path']) }}" type="{{ $mime }}">
                     Your browser does not support the video tag.
                 </video>
             @else
                 <img src="{{ $heroSettings['media_path'] }}" alt="Elite Custom Uniform Models" class="relative z-10 w-full h-full max-h-[600px] object-contain drop-shadow-2xl">
             @endif
             
             <!-- Floating Badge -->
            
    </div>
</section>

<!-- Sports Tabs / Uniform Gallery -->
<section id="sports" class="pt-2 pb-12 lg:pt-4 lg:pb-16 bg-white">
    <div class="max-w-[1500px] mx-auto px-6 relative z-10">
        <div class="flex flex-col md:flex-row md:items-start justify-between mb-12 gap-6">
            <div>
                <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-2 text-slate-900">View our Custom <span class="text-secondary">Collections</span></h2>
                <p class="text-slate-600 text-lg font-medium max-w-3xl">Stand out with fully custom designs crafted to capture the essence of your program or organization</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary whitespace-nowrap px-8 py-3 rounded md:mt-2">View Design Collection</a>
        </div>



        <!-- Gallery Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 text-left">
            @forelse($landingCollections ?? [] as $collection)
            <div class="bg-white overflow-hidden group border border-slate-200 shadow-sm hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between h-full">
                <!-- Enforced exact aspect ratio bounds as requested: 406.7 x 305.017 -->
                <div class="relative overflow-hidden bg-slate-100 w-full" style="aspect-ratio: 406.7 / 305.017;">
                    <img src="{{ Str::startsWith($collection->image_path, 'http') ? $collection->image_path : $collection->image_path }}" alt="{{ $collection->tab_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <div class="text-secondary text-xs font-black tracking-widest uppercase mb-2">{{ $collection->tab_name }}</div>
                    <h3 class="text-base font-black uppercase text-slate-900 leading-tight mb-2 truncate" title="{{ $collection->title }}">{{ $collection->title }}</h3>
                    <p class="text-slate-600 text-sm mb-6 font-medium">{{ $collection->description }}</p>
                    <div class="flex items-center gap-3">
                    <!-- class="btn btn-primary px-8 py-4 text-base font-black uppercase tracking-wider rounded-md shadow-md transition-all"     -->
                    <a href="/quote" class="border btn btn-primary border-slate-300  rounded-md bg-secondary  w-full py-2 shadow-sm font-bold text-sm  uppercase text-center">Talk to an Expert</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-1 border-2 border-dashed border-slate-300 p-12 text-center text-slate-500 font-bold uppercase tracking-widest text-sm rounded-lg lg:col-span-3">
                No catalogs active at the moment.
            </div>
            @endforelse
        </div>

        <!-- Custom CTA Full Width Banner -->
        
    </div>
</section>

<!-- Team Store Section -->
<section id="team-store" class="py-24 bg-[#0a0a0a] border-y border-slate-900">
    <div class="max-w-[90rem] mx-auto px-6">
        
        <!-- UI Mockup Image -->
        <div class="mb-6 md:mb-10 px-4 lg:px-6 max-w-[85rem] mx-auto">
             <div class="w-full rounded-xl overflow-hidden shadow-2xl relative" style="aspect-ratio: 3.2 / 1;">
                 <img src="/images/team-store-background-v2.png" alt="Team Store UI Previews" class="absolute w-full max-w-none h-auto left-0 -top-[9%] md:-top-[12%] lg:-top-[15%]">
             </div>
        </div>

        <!-- Text Content -->
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tighter uppercase text-white mb-6">TEAM <span class="text-secondary ml-1">STORE</span></h2>
            <p class="text-slate-100 text-lg md:text-xl font-medium tracking-wide">Empower your program with a custom online store that eliminates hassle and generates revenue.</p>
        </div>

        <!-- Features Grid -->
        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-12">
            
            <!-- Feature 1 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1.5">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-lg font-black uppercase tracking-wide mb-2.5">CUSTOM DESIGNS THAT DRIVE DEMAND</h3>
                    <p class="text-slate-300 text-[15px] leading-relaxed font-medium">We create high-quality, exclusive apparel designs tailored to your team—making your store something people actually want to shop from, not just another generic merch page.</p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1.5">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-lg font-black uppercase tracking-wide mb-2.5">PARENTS ORDER DIRECTLY</h3>
                    <p class="text-slate-300 text-[15px] leading-relaxed font-medium">Families simply use your team store link to place their own orders, eliminating the need for coaches to collect forms, track sizes, or manage money.</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1.5">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-lg font-black uppercase tracking-wide mb-2.5">TURN YOUR PROGRAM INTO A REVENUE STREAM</h3>
                    <p class="text-slate-300 text-[15px] leading-relaxed font-medium">Your custom team store allows you to generate ongoing income from every purchase—helping fund travel, equipment, and program growth without additional fundraising efforts.</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="flex gap-4">
                <div class="shrink-0 mt-1.5">
                    <span class="w-6 h-6 rounded bg-secondary text-white text-sm font-black flex items-center justify-center">✓</span>
                </div>
                <div>
                    <h3 class="text-white text-lg font-black uppercase tracking-wide mb-2.5">PROFESSIONAL, BRANDED EXPERIENCE</h3>
                    <p class="text-slate-300 text-[15px] leading-relaxed font-medium">Your athletes, parents, and supporters get access to a clean, custom-designed online store that reflects your team's identity and elevates your brand.</p>
                </div>
            </div>

        </div>
    </div>
</section>



<!-- Powerful Features (Team Stores & Dashboard Teaser) -->
<section id="system" class="py-12 lg:py-16 bg-slate-50 border-t border-slate-200 overflow-hidden">
    <div class="max-w-[1500px] mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            
            <div class="order-2 lg:order-1 relative rounded-xl overflow-hidden shadow-2xl border border-slate-200">
                <img src="/images/group.jpg" alt="Dashboard Concept" class="w-full h-auto object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-8">
                    <div class="bg-white rounded-lg p-4 shadow-xl border border-slate-100 flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center border border-slate-200 shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-black tracking-wider uppercase text-slate-900 text-sm">Coach Dashboard</h4>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Automated workflow entirely centralized.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-6 text-slate-900">More Than Gear.<br><span class="text-secondary">A Platform.</span></h2>
                <div class="space-y-10 mt-10">
                    
                    <div class="flex gap-5 box-border">
                        <div class="mt-1 w-10 h-10 rounded bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-200">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-black uppercase mb-1 text-slate-900">Automated Team Stores</h4>
                            <p class="text-slate-600 font-medium leading-relaxed text-sm max-w-sm">Coaches create a store, parents order directly. No messy spreadsheets, no collecting cash. We manage the logistics.</p>
                        </div>
                    </div>

                    <div class="flex gap-5 box-border">
                        <div class="mt-1 w-10 h-10 rounded bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-200">
                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-black uppercase mb-1 text-slate-900">Order Deadlines & Tracking</h4>
                            <p class="text-slate-600 font-medium leading-relaxed text-sm max-w-sm">Set a firm deadline. Parents get email reminders. Once closed, track production visually until shipping.</p>
                        </div>
                    </div>
                    
                   

                </div>
                
                <div class="mt-8 flex items-center gap-6 pt-4 border-t border-slate-200/50">
                    <a href="/coach/dashboard" class="btn btn-primary px-6 py-3 font-bold rounded shadow-md flex items-center justify-center gap-2">
                        <img src="/images/LR.png" alt="LR Logo" class="h-5 w-auto object-contain">
                        Dashboard Sign-in
                    </a>
                    <a href="/sales-agents" class="font-bold text-xs uppercase tracking-wide text-secondary hover:text-[#a11825] transition-colors">EMPLOYEE SIGN-IN &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="py-12 lg:py-16 bg-white border-t border-slate-200 overflow-hidden">
    <div class="max-w-[1500px] mx-auto px-6">
        <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-10 text-slate-900">
            <span class="text-secondary">"</span> TESTIMONIALS <span class="text-secondary">"</span>
        </h2>

        @if(isset($testimonials) && $testimonials->isNotEmpty())
        <div x-data="{ 
            activePage: 0,
            itemsPerPage: window.innerWidth < 768 ? 1 : ({{ $testimonials->count() }} > 1 ? 2 : 1),
            get totalPages() { return Math.ceil({{ $testimonials->count() }} / this.itemsPerPage) },
            next() { this.activePage = (this.activePage + 1) % this.totalPages },
            prev() { this.activePage = (this.activePage - 1 + this.totalPages) % this.totalPages },
            init() {
                window.addEventListener('resize', () => {
                    this.itemsPerPage = window.innerWidth < 768 ? 1 : ({{ $testimonials->count() }} > 1 ? 2 : 1);
                    if (this.activePage >= this.totalPages) this.activePage = 0;
                });
                
                this.$watch('totalPages', (val) => {
                    if (val > 1 && !this.interval) {
                        this.interval = setInterval(() => { this.next() }, 6000);
                    } else if (val <= 1 && this.interval) {
                        clearInterval(this.interval);
                        this.interval = null;
                    }
                });
                
                if (this.totalPages > 1) {
                    this.interval = setInterval(() => { this.next() }, 6000);
                }
            }
        }" class="relative w-full">
            <div class="overflow-hidden relative w-full -mx-3">
                <div class="flex transition-transform duration-500 ease-out"
                     :style="'transform: translateX(-' + (activePage * 100) + '%)'">
                    @foreach($testimonials as $testimonial)
                    <div class="shrink-0 p-3 flex" 
                         :style="'width: ' + (100 / itemsPerPage) + '%'">
                        <article class="bg-slate-50 border border-slate-100 rounded-xl p-8 md:p-10 w-full flex flex-col justify-between shadow-sm">
                            <p class="text-black text-sm md:text-base font-medium leading-relaxed mb-8" style="color: #000000;">
                                "{{ $testimonial->content }}"
                            </p>
                            <div class="flex items-center gap-4 mt-auto">
                                @if($testimonial->image_path)
                                    <img src="{{ $testimonial->image_path }}" alt="{{ $testimonial->client_name }}" class="w-12 h-12 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-[#1e40af] text-white flex items-center justify-center font-black text-lg shrink-0">
                                        {{ substr($testimonial->client_name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="border-l-[3px] border-secondary pl-4">
                                    <p class="font-black text-slate-900 text-sm uppercase tracking-wide">{{ $testimonial->client_name }}</p>
                                    @if($testimonial->organization)
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-0.5">{{ $testimonial->organization }}</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Carousel Controls -->
            <div class="flex items-center gap-2 mt-8" x-show="totalPages > 1" x-cloak>
                <template x-for="i in totalPages" :key="i">
                    <button @click="activePage = i - 1" class="h-2.5 rounded-full transition-all" :class="activePage === i - 1 ? 'bg-secondary w-6' : 'bg-slate-200 hover:bg-slate-300 w-2.5'"></button>
                </template>
            </div>
        </div>
        @else
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-10 text-center text-slate-500 text-sm font-bold uppercase tracking-wider max-w-4xl">
                No testimonials yet.
            </div>
        @endif

        <div class="mt-12 text-left">
            <a href="{{ route('testimonials.index') }}" class="btn btn-primary px-8 py-3.5 font-bold text-sm tracking-widest uppercase rounded shadow-md transition-all inline-flex items-center gap-2">
                More Testimonials
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
