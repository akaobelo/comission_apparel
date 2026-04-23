@extends('layouts.app')

@section('content')

<!-- Hero Section -->
<section class="relative bg-white min-h-[90vh] flex items-center justify-center pt-20 overflow-hidden">
    <!-- Subtle Background Dot Pattern -->
    <div class="absolute inset-0 z-0 opacity-40" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 w-full grid lg:grid-cols-2 gap-12 items-stretch py-12">
        <div class="max-w-2xl animate-slide-up flex flex-col justify-center">
            <div class="inline-flex items-center px-4 py-1.5 rounded bg-slate-100 border-l-[3px] border-slate-400 text-slate-700 text-[11px] tracking-widest font-bold mb-6 uppercase">
                THE COMMISSION APPAREL
            </div>
            
            <h1 class="text-7xl md:text-7xl lg:text-7xl font-black tracking-tighter leading-[0.85] mb-6 uppercase text-slate-950">
                Elite Custom <br/>
                <span class="text-secondary">Apparel</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-slate-600 font-medium max-w-xl mb-8 leading-relaxed">
                Premium armor tailored for programs that demand greatness. Built for the modern athlete, delivered with lightning speed.
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
                <a href="#sports" class="btn btn-primary px-8 py-4 text-base font-black uppercase tracking-wider rounded-md shadow-md transition-all">View Uniform Gallery</a>
                <a href="/coach/dashboard" class="btn border-2 border-slate-300 bg-white !text-slate-900 hover:bg-slate-50 px-8 py-4 text-base font-black uppercase tracking-wider rounded-md shadow-sm transition-all">Create Team Store</a>
            </div>
        </div>

        <div class="relative flex items-center justify-center animate-fade-in hidden md:flex h-full w-full min-h-[450px]">
             <!-- Background geometric elements to match mockup flair -->
             <div class="absolute top-1/4 right-0 w-32 h-2 bg-secondary transform -rotate-12 rounded-full opacity-80 z-0"></div>
             <div class="absolute bottom-1/3 left-10 w-24 h-2 bg-secondary transform rotate-6 rounded-full opacity-80 z-0"></div>
             <div class="absolute top-1/3 left-0 w-4 h-4 bg-slate-800 transform rotate-45 z-0"></div>
             <div class="absolute bottom-1/4 right-10 w-3 h-3 bg-secondary transform rotate-12 z-0"></div>

             <!-- Clean bright background image -->
             <img src="/images/hero-models.png" alt="Elite Custom Uniform Models" class="relative z-10 w-full h-full max-h-[600px] object-contain drop-shadow-2xl">
             
             <!-- Floating Badge -->
             <div class="absolute top-12 left-0 z-20 bg-white p-3 rounded-xl shadow-xl flex items-center gap-3 animate-bounce border border-slate-100">
                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-green-500 font-bold border border-green-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="pr-2">
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Order Status</div>
                    <div class="font-black text-slate-800 text-sm">Arriving Early</div>
                </div>
             </div>
        </div>
    </div>
</section>

<!-- Sports Tabs / Uniform Gallery -->
<section id="sports" class="py-24 bg-white" x-data="{ activeTab: 'football' }">
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="flex flex-col md:flex-row md:items-start justify-between mb-12 gap-6">
            <div>
                <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-2 text-slate-900">View our Custom <span class="text-secondary">Collections</span></h2>
                <p class="text-slate-600 text-lg font-medium max-w-xl">Purpose-built cuts and materials for every arena.</p>
            </div>
            <a href="/quote" class="btn btn-primary whitespace-nowrap px-8 py-3 rounded md:mt-2">View Full Catalog</a>
        </div>

        <!-- Alpine Tabs -->
        <div class="flex overflow-x-auto pb-4 mb-8 -mx-6 px-6 md:mx-0 md:px-0 hide-scrollbar gap-8 border-b border-slate-200">
            <template x-for="tab in ['football', 'basketball', 'track', 'soccer', 'baseball', 'merch']">
                <button @click="activeTab = tab" 
                        class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]"
                        :class="activeTab === tab ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">
                    <span x-text="tab === 'merch' ? 'Custom Merch' : tab"></span>
                </button>
            </template>
            <a href="/catalog" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 border-transparent text-slate-500 hover:text-secondary relative top-[1px]">View All</a>
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
                    <h3 class="text-xl font-black uppercase mb-2 text-slate-900">{{ $collection->title }}</h3>
                    <p class="text-slate-600 text-sm mb-6 font-medium">{{ $collection->description }}</p>
                    <div class="flex items-center gap-3">
                        <a href="/quote" class="btn border border-slate-300 text-slate-800 bg-white hover:bg-slate-50 w-full py-2 shadow-sm font-bold text-sm tracking-wider rounded transition-colors uppercase text-center">Start Design</a>
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
        <div class="mt-12 bg-white flex flex-col md:flex-row justify-center items-center gap-6 py-6 border-t border-slate-100">
            <h3 class="text-2xl font-black uppercase text-slate-900">Got a Vision?</h3>
            <a href="/quote" class="btn btn-primary py-3 px-8 rounded shadow-md text-sm font-bold tracking-wider uppercase">Talk to an Expert</a>
        </div>
    </div>
</section>

<!-- Team Store Section -->
<section id="team-store" class="py-24 relative border-b border-slate-900 overflow-hidden bg-slate-900" style="background-color: #0f172a;">
    <!-- Dashboard Background Image - Acting as a shadow texture -->
    <div class="absolute inset-0 z-0 opacity-20">
        <img src="/images/dashboard-concept.png" alt="Coach Dashboard" class="w-full h-full object-cover object-top object-left">
    </div>
    <div class="absolute inset-0 z-0 bg-black bg-opacity-50" style="background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.8));"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-4 text-white" style="text-shadow: 0 4px 10px rgba(0,0,0,0.5);">Team <span class="text-secondary drop-shadow-md ml-3">Store</span></h2>
            <p class="text-slate-200 max-w-2xl mx-auto text-lg font-medium font-black text-white  drop-shadow-sm">Empower your program with a custom online store that eliminates hassle and generates revenue.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 lg:gap-16 gap-y-16 max-w-5xl mx-auto">
            <div class="flex gap-5 relative">
                <div class="mt-1 w-10 h-10 rounded bg-secondary shadow-sm flex items-center justify-center shrink-0 text-white">
                    <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg md:text-xl font-black text-white uppercase mb-3 drop-shadow-md">Custom Designs That Drive Demand</h3>
                    <p class="text-slate-300 font-medium leading-relaxed drop-shadow-sm text-justify">We create high-quality, exclusive apparel designs tailored to your team—making your store something people actually want to shop from, not just another generic merch page.</p>
                </div>
            </div>

            <div class="flex gap-5 relative">
                <div class="mt-1 w-10 h-10 rounded bg-secondary shadow-sm flex items-center justify-center shrink-0 text-white">
                    <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg md:text-xl font-black text-white uppercase mb-3 drop-shadow-md">Parents Order Directly</h3>
                    <p class="text-slate-300 font-medium leading-relaxed drop-shadow-sm text-justify">Families simply use your team store link to place their own orders, eliminating the need for coaches to collect forms, track sizes, or manage money.</p>
                </div>
            </div>

            <div class="flex gap-5 relative">
                <div class="mt-1 w-10 h-10 rounded bg-secondary shadow-sm flex items-center justify-center shrink-0 text-white">
                    <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg md:text-xl font-black text-white uppercase mb-3 drop-shadow-md">Turn Your Program Into a Revenue Stream</h3>
                    <p class="text-slate-300 font-medium leading-relaxed drop-shadow-sm text-justify">Your custom team store allows you to generate ongoing income from every purchase—helping fund travel, equipment, and program growth without additional fundraising efforts.</p>
                </div>
            </div>

            <div class="flex gap-5 relative">
                <div class="mt-1 w-10 h-10 rounded bg-secondary shadow-sm flex items-center justify-center shrink-0 text-white">
                    <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg md:text-xl font-black text-white uppercase mb-3 drop-shadow-md">Professional, Branded Experience</h3>
                    <p class="text-slate-300 font-medium leading-relaxed drop-shadow-sm text-justify">Your athletes, parents, and supporters get access to a clean, custom-designed online store that reflects your team’s identity and elevates your brand.</p>
                </div>
            </div>
        </div>

        <!-- <div class="mt-20 text-center">
            <a href="/coach/dashboard" class="btn btn-primary px-10 py-4 text-base font-black uppercase tracking-wider rounded-md shadow-[0_0_20px_rgba(200,20,50,0.5)] transition-all hover:scale-105">Get Started Now</a>
        </div> -->
    </div>
</section>



<!-- Powerful Features (Team Stores & Dashboard Teaser) -->
<section id="system" class="py-24 bg-slate-50 border-t border-slate-200 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
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
                
                <div class="mt-12 flex items-center gap-6 pt-6">
                    <a href="/coach/dashboard" class="btn btn-primary px-6 py-3 font-bold rounded shadow-md">Dashboard Sign-in</a>
                    <a href="/sales-agents" class="font-bold text-xs uppercase tracking-wide text-secondary hover:text-[#a11825] transition-colors">EMPLOYEE SIGN-IN &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="py-24 bg-white border-t border-slate-200 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div class="max-w-xl">
            <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-8 text-slate-900">
                <span class="text-secondary">"</span> TESTIMONIALS <span class="text-secondary">"</span>
            </h2>
            <p class="text-slate-700 text-lg md:text-xl font-medium leading-relaxed mb-6">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
            </p>
            <div class="font-black text-slate-900 text-lg mb-10">
                - Justin Gatlin
            </div>
            <a href="/testimonials" class="btn btn-primary px-8 py-3 font-bold text-sm tracking-wider uppercase rounded shadow-md transition-all">More Testimonials</a>
        </div>
        <div class="relative flex justify-center">
             <div class="absolute inset-0 bg-slate-50 rounded-full scale-110 -z-10 origin-center blur-2xl opacity-50"></div>
             <!-- Testimonial Image -->
             <img src="/images/testimonials.png" alt="Justin Gatlin Testimonial" class="w-full max-w-[600px] h-auto object-contain shrink-0 drop-shadow-xl">
        </div>
    </div>
</section>

@endsection
