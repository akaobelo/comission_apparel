@extends('layouts.app')

@section('content')

<!-- Hero Section -->
<section class="relative bg-white min-h-[90vh] flex items-center justify-center pt-20 border-b border-slate-200 overflow-hidden">
    <!-- Diagonal background accent to give an athletic feel -->
    <div class="absolute top-0 right-0 w-[50vw] h-full bg-slate-50 origin-bottom-left transform -skew-x-12 z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 w-full grid lg:grid-cols-2 gap-12 items-center">
        <div class="max-w-2xl animate-slide-up">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-sm bg-secondary/10 border-l-2 border-secondary text-secondary text-xs tracking-widest font-bold mb-6 uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                The Commission Apparel
            </div>
            
            <h1 class="text-6xl md:text-8xl font-black italic tracking-tighter leading-[0.9] mb-6 uppercase text-slate-900">
                Elite Custom <br/>
                <span class="text-secondary">Uniforms</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-slate-600 font-medium max-w-xl mb-8">
                Premium armor tailored for programs that demand greatness. Built for the modern athlete, delivered with lightning speed.
            </p>

            <ul class="space-y-3 mb-10 text-slate-700 font-bold tracking-wide">
                <li class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Pro-Grade Quality Materials
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Automated Team Stores for Parents
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Lightning Fast Turnaround
                </li>
            </ul>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="#sports" class="btn btn-primary px-8 py-4 text-lg">View Uniform Gallery</a>
                <a href="/coach/dashboard" class="btn btn-outline px-8 py-4 text-lg bg-white border-slate-300">Create Team Store</a>
            </div>
        </div>

        <div class="relative h-[600px] flex items-center justify-center animate-fade-in pl-8 hidden md:flex">
             <div class="absolute inset-0 bg-gradient-to-tr from-slate-200 to-slate-50 rounded-[4rem] transform rotate-3 scale-105 z-0"></div>
             <!-- Clean bright background image -->
             <img src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&q=80&w=1200" alt="Track and Field" class="relative z-10 w-full h-[500px] object-cover rounded-3xl shadow-2xl skew-y-0 transform -rotate-2 hover:rotate-0 transition-transform duration-500">
             
             <!-- Floating Badge -->
             <div class="absolute top-10 -left-6 z-20 bg-white p-4 rounded-xl shadow-xl flex items-center gap-4 animate-bounce">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <div class="text-xs uppercase font-bold text-slate-500">Order Status</div>
                    <div class="font-black text-slate-900">Arriving Early</div>
                </div>
             </div>
        </div>
    </div>
</section>

<!-- Featured Work (Clean Style) -->
<section id="featured" class="py-24 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black italic tracking-tighter uppercase mb-4 text-slate-900">From Design to <span class="text-secondary">Reality</span></h2>
            <p class="text-slate-600 max-w-2xl mx-auto text-lg font-medium">We turn bold visions into high-performance gear. What you see on screen is exactly what arrives on game day.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 items-center">
            <!-- Digital Design side -->
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-200 group">
                <div class="relative rounded-xl overflow-hidden aspect-[4/3] bg-slate-100">
                    <img src="https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&q=80&w=1200" alt="Design Concept" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute top-4 left-4 bg-white shadow py-1.5 px-4 rounded-full text-xs font-black uppercase tracking-widest text-slate-900">
                        Concept Art
                    </div>
                </div>
            </div>
            
            <!-- Real World side -->
            <div class="bg-white p-3 rounded-2xl shadow-lg border border-slate-200 group relative md:-top-4 md:left-[-2rem] z-10 mt-8 md:mt-0">
                <div class="relative rounded-xl overflow-hidden aspect-[4/3] bg-slate-100 ring-4 ring-white">
                    <img src="https://images.unsplash.com/photo-1508344928928-7157b87de15d?auto=format&fit=crop&q=80&w=1200" alt="Game Day Reality" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute top-4 right-4 bg-secondary shadow py-1.5 px-4 rounded-full text-xs font-black uppercase tracking-widest text-white">
                        On The Field
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sports Tabs / Uniform Gallery -->
<section id="sports" class="py-24 bg-white" x-data="{ activeTab: 'football' }">
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <h2 class="text-4xl md:text-5xl font-black italic tracking-tighter uppercase mb-4 text-slate-900">Command Your <span class="text-primary">Sport</span></h2>
                <p class="text-slate-600 text-lg font-medium max-w-xl">Purpose-built cuts and materials for every arena.</p>
            </div>
            <a href="/quote" class="btn btn-primary whitespace-nowrap shadow-md px-8 py-3">View Full Catalog</a>
        </div>

        <!-- Alpine Tabs -->
        <div class="flex overflow-x-auto pb-4 mb-8 -mx-6 px-6 md:mx-0 md:px-0 hide-scrollbar gap-3">
            <template x-for="tab in ['football', 'basketball', 'track', 'soccer', 'baseball', 'merch']">
                <button @click="activeTab = tab" 
                        class="px-8 py-3 rounded text-sm font-bold uppercase tracking-wider whitespace-nowrap transition-all border-b-2"
                        :class="activeTab === tab ? 'bg-slate-50 border-secondary text-secondary' : 'bg-white border-transparent text-slate-500 hover:text-slate-900 hover:bg-slate-50'">
                    <span x-text="tab === 'merch' ? 'Custom Merch' : tab.charAt(0).toUpperCase() + tab.slice(1)"></span>
                </button>
            </template>
        </div>

        <!-- Gallery Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 text-left">
            <!-- Card 1 -->
            <div class="bg-white rounded-xl overflow-hidden group border border-slate-200 shadow-sm hover:shadow-xl transition-shadow duration-300">
                <div class="relative h-64 overflow-hidden bg-slate-100">
                    <img src="https://images.unsplash.com/photo-1508344928928-7157b87de15d?auto=format&fit=crop&q=80&w=800" alt="Football" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <div class="text-secondary text-xs font-black tracking-widest uppercase mb-2">Tackle Football</div>
                    <h3 class="text-xl font-black italic uppercase mb-2 text-slate-900">Neon Tigers Legacy</h3>
                    <p class="text-slate-600 text-sm mb-6 font-medium">4-Way stretch compression fit with sublimated side panels and robust stitching.</p>
                    <div class="flex items-center gap-3">
                         <a href="/quote" class="btn btn-outline py-2 px-4 flex-1 text-sm bg-white border-slate-300 text-center font-bold">Start Design</a>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-xl overflow-hidden group border border-slate-200 shadow-sm hover:shadow-xl transition-shadow duration-300">
                <div class="relative h-64 overflow-hidden bg-slate-100">
                    <img src="https://images.unsplash.com/photo-1518656641040-52ce648fc92c?auto=format&fit=crop&q=80&w=800" alt="Track" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <div class="text-secondary text-xs font-black tracking-widest uppercase mb-2">Track & Field</div>
                    <h3 class="text-xl font-black italic uppercase mb-2 text-slate-900">Aero Speed Singlet</h3>
                    <p class="text-slate-600 text-sm mb-6 font-medium">Featherweight moisture-wicking core with laser-cut ventilation holes for max aerodynamics.</p>
                    <div class="flex items-center gap-3">
                         <a href="/quote" class="btn btn-outline py-2 px-4 flex-1 text-sm bg-white border-slate-300 text-center font-bold">Start Design</a>
                    </div>
                </div>
            </div>

            <!-- Custom CTA Card -->
            <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl overflow-hidden group flex flex-col justify-center items-center h-full hover:border-secondary hover:bg-secondary/5 cursor-pointer transition-colors p-8 text-center min-h-[400px]">
                <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-6 group-hover:bg-secondary transition-colors">
                     <svg class="w-8 h-8 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="text-xl font-black italic uppercase mb-3 text-slate-900">Got a Vision?</h3>
                <p class="text-slate-500 text-sm font-medium mb-6">Open the design lab to create something completely unique for your program.</p>
                <a href="/quote" class="btn btn-primary py-2 px-6 shadow-sm">Talk to an Expert</a>
            </div>
        </div>
    </div>
</section>

<!-- Powerful Features (Team Stores & Dashboard Teaser) -->
<section id="system" class="py-24 bg-slate-50 border-t border-slate-200 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            
            <div class="order-2 lg:order-1 relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200">
                <img src="https://images.unsplash.com/photo-1526232761682-d26e03ac148e?auto=format&fit=crop&q=80&w=1200" alt="Dashboard Concept" class="w-full h-auto object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-8">
                    <div class="bg-white rounded-xl p-4 shadow-xl border border-slate-100 flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center border border-primary/20 shrink-0">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-black italic tracking-wider uppercase text-slate-900">Coach Dashboard</h4>
                            <p class="text-sm text-slate-500 font-medium mt-0.5">Automated workflow entirely centralized.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <h2 class="text-4xl md:text-5xl font-black italic tracking-tighter uppercase mb-6 text-slate-900">More Than Gear.<br><span class="text-secondary">A Platform.</span></h2>
                <div class="space-y-10 mt-10">
                    
                    <div class="flex gap-5 box-border">
                        <div class="mt-1 w-12 h-12 rounded bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-200">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-black uppercase mb-1 text-slate-900">Automated Team Stores</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">Coaches create a store, parents order directly. No messy spreadsheets, no collecting cash. We manage the logistics.</p>
                        </div>
                    </div>

                    <div class="flex gap-5 box-border">
                        <div class="mt-1 w-12 h-12 rounded bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-200">
                            <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-black uppercase mb-1 text-slate-900">Order Deadlines & Tracking</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">Set a firm deadline. Parents get email reminders. Once closed, track production visually until shipping.</p>
                        </div>
                    </div>

                    <div class="flex gap-5 box-border">
                        <div class="mt-1 w-12 h-12 rounded bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-200">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-black uppercase mb-1 text-slate-900">Global Commission Engine</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">Agents globally can track their referrals and commissions inside our powerhouse CRM dashboard.</p>
                        </div>
                    </div>

                </div>
                
                <div class="mt-12 flex items-center gap-6 pt-6 border-t border-slate-200">
                    <a href="/coach/dashboard" class="btn btn-primary px-8">Enter Coach Portal</a>
                    <a href="#" class="font-bold text-sm uppercase tracking-wide text-secondary hover:text-slate-900 transition-colors">For Sales Agents &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
