@extends('layouts.app')

@section('content')

<!-- Hero Section -->
<section class="relative min-h-[100vh] flex items-center justify-center overflow-hidden">
    <!-- Background Video Placeholder (Image for now till actual video is sourced) -->
    <div class="absolute inset-0 bg-slate-900 z-0">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-40 mix-blend-luminosity"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/60 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900/40 to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 w-full pt-20">
        <div class="max-w-3xl animate-slide-up">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-primary text-sm font-semibold tracking-wider mb-6 uppercase">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                The Commission Apparel
            </div>
            
            <h1 class="text-6xl md:text-8xl font-black tracking-tighter leading-[0.9] mb-6 uppercase">
                Elite Custom <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-secondary to-purple-400">Uniforms</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-slate-300 font-light max-w-2xl mb-10 border-l-4 border-primary pl-6">
                Premium armor tailored for programs that demand greatness. Built for the modern athlete, designed to dominate the field.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="#sports" class="btn btn-primary px-8 py-4 text-lg">View Uniform Gallery</a>
                <a href="/coach/dashboard" class="btn bg-white text-slate-900 hover:bg-slate-200 px-8 py-4 text-lg shadow-[0_4px_20px_rgba(255,255,255,0.2)]">Create Team Store</a>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-2 text-white/50 animate-bounce">
        <span class="text-xs uppercase tracking-widest font-bold">Discover</span>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
    </div>
</section>

<!-- Featured Work (Before/After Concept) -->
<section id="featured" class="py-24 bg-slate-950 relative border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-4">From Concept to <span class="text-primary">Reality</span></h2>
            <p class="text-slate-400 max-w-2xl mx-auto text-lg">We turn bold visions into high-performance gear. What you see on screen is exactly what arrives on game day.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 items-center bg-slate-900 rounded-3xl p-4 border border-white/10 shadow-2xl">
            <!-- Digital Design side -->
            <div class="relative group rounded-2xl overflow-hidden aspect-[4/5] md:aspect-auto md:h-[600px] bg-slate-800">
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&q=80&w=1200')] bg-cover bg-center opacity-80 group-hover:scale-105 transition-transform duration-700"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6">
                    <div class="glass-panel p-4 py-3 flex justify-between items-center rounded-xl bg-slate-900/80">
                        <span class="font-bold text-lg uppercase tracking-wide">Design Concept</span>
                        <span class="text-primary"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></span>
                    </div>
                </div>
            </div>
            
            <!-- Real World side -->
            <div class="relative group rounded-2xl overflow-hidden aspect-[4/5] md:aspect-auto md:h-[600px] bg-slate-800 shadow-[0_0_40px_rgba(56,189,248,0.15)] ring-1 ring-primary/30">
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1508344928928-7157b87de15d?auto=format&fit=crop&q=80&w=1200')] bg-cover bg-center group-hover:scale-105 transition-transform duration-700"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
                <!-- Interactive Tag -->
                <div class="absolute top-1/2 left-1/2 w-8 h-8 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white flex items-center justify-center cursor-pointer group-hover:scale-125 transition-transform shadow-[0_0_20px_white] z-20">
                    <div class="w-3 h-3 rounded-full bg-primary animate-ping absolute"></div>
                    <div class="w-3 h-3 rounded-full bg-primary relative"></div>
                </div>
                
                <div class="absolute bottom-6 left-6 right-6">
                    <div class="glass-panel p-4 py-3 flex justify-between items-center rounded-xl bg-primary/20 border-primary/50">
                        <span class="font-bold text-lg uppercase tracking-wide text-white drop-shadow-md">Game Day Reality</span>
                        <span class="text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sports Tabs / Uniform Gallery -->
<section id="sports" class="py-24 relative" x-data="{ activeTab: 'football' }">
    <!-- Background element -->
    <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-primary/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-4">Command Your <span class="text-secondary">Sport</span></h2>
                <p class="text-slate-400 text-lg max-w-xl">Purpose-built cuts and materials for every arena.</p>
            </div>
            <a href="/quote" class="btn btn-outline whitespace-nowrap">View Full Catalog</a>
        </div>

        <!-- Alpine Tabs -->
        <div class="flex overflow-x-auto pb-4 mb-8 -mx-6 px-6 md:mx-0 md:px-0 hide-scrollbar gap-2">
            <template x-for="tab in ['football', 'basketball', 'track', 'soccer', 'baseball', 'merch']">
                <button @click="activeTab = tab" 
                        class="px-6 py-3 rounded-full font-bold uppercase tracking-wider text-sm whitespace-nowrap transition-all border"
                        :class="activeTab === tab ? 'bg-primary border-primary text-white shadow-[0_0_20px_rgba(56,189,248,0.4)]' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800'">
                    <span x-text="tab === 'merch' ? 'Custom Merch' : tab.charAt(0).toUpperCase() + tab.slice(1)"></span>
                </button>
            </template>
        </div>

        <!-- Gallery Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="glass-panel p-0 overflow-hidden group">
                <div class="relative h-80 overflow-hidden bg-slate-800">
                    <img src="https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=800" alt="Football" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-90 saturate-50 group-hover:saturate-100">
                    <div class="absolute top-4 right-4 bg-slate-900/80 backdrop-blur-sm text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full border border-white/10">
                        Pro Cut
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-primary text-xs font-bold tracking-widest uppercase mb-2">Tackle Football</div>
                    <h3 class="text-2xl font-black uppercase mb-2">Neon Tigers Legacy</h3>
                    <p class="text-slate-400 text-sm mb-6 line-clamp-2">4-Way stretch compression fit with sublimated side panels and robust stitching.</p>
                    <div class="flex items-center gap-4">
                        <button class="btn btn-outline py-2 px-4 flex-1 text-sm">View Details</button>
                        <button class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center hover:bg-primary transition-colors hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg></button>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="glass-panel p-0 overflow-hidden group">
                <div class="relative h-80 overflow-hidden bg-slate-800">
                    <img src="https://images.unsplash.com/photo-1518656641040-52ce648fc92c?auto=format&fit=crop&q=80&w=800" alt="Track" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-90 saturate-50 group-hover:saturate-100">
                    <div class="absolute top-4 right-4 bg-slate-900/80 backdrop-blur-sm text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full border border-white/10">
                        Lightweight
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-primary text-xs font-bold tracking-widest uppercase mb-2">Track & Field</div>
                    <h3 class="text-2xl font-black uppercase mb-2">Aero Speed Singlet</h3>
                    <p class="text-slate-400 text-sm mb-6 line-clamp-2">Featherweight moisture-wicking core with laser-cut ventilation holes for max aerodynamics.</p>
                    <div class="flex items-center gap-4">
                        <button class="btn btn-outline py-2 px-4 flex-1 text-sm">View Details</button>
                        <button class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center hover:bg-primary transition-colors hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg></button>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="glass-panel p-0 overflow-hidden group flex flex-col justify-center items-center h-full bg-slate-900 border-dashed border-2 border-slate-700 hover:border-primary cursor-pointer transition-colors">
                <div class="w-20 h-20 rounded-full bg-slate-800 flex items-center justify-center mb-4 group-hover:bg-primary/20 transition-colors">
                     <svg class="w-10 h-10 text-slate-400 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="text-xl font-bold uppercase mb-2">Design Your Own</h3>
                <p class="text-slate-500 text-sm text-center px-6">Open the design lab to create something completely unique.</p>
            </div>
        </div>
    </div>
</section>

<!-- Powerful Features (Team Stores & 3D Viewer Teaser) -->
<section id="system" class="py-24 bg-slate-950 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="order-2 lg:order-1 relative h-[600px] w-full bg-slate-900 rounded-3xl border border-white/10 overflow-hidden flex items-center justify-center group shadow-[0_0_50px_rgba(0,0,0,0.5)]">
                <!-- Abstract 3D shape placehoder -->
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&q=80&w=1200')] bg-cover opacity-30 mix-blend-screen scale-110 group-hover:scale-100 transition-transform duration-1000"></div>
                <div class="relative z-10 glass-panel border-white/20 p-8 text-center animate-pulse shadow-2xl">
                    <svg class="w-16 h-16 text-primary mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                    <h3 class="text-2xl font-black uppercase tracking-wider mb-2">3D Design Engine</h3>
                    <p class="text-sm text-slate-300">Rotate. Customize. Render.</p>
                    <div class="mt-6 inline-block bg-primary text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">Interactive Beta</div>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <h2 class="text-4xl md:text-5xl font-black tracking-tighter uppercase mb-6">More Than Gear.<br><span class="text-gradient">A Platform.</span></h2>
                <div class="space-y-8">
                    
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center flex-shrink-0 border border-primary/30">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold uppercase mb-2">Automated Team Stores</h4>
                            <p class="text-slate-400">Coaches create a store, parents order directly. No messy spreadsheets, no collecting cash. We manage the logistics.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-xl bg-secondary/20 flex items-center justify-center flex-shrink-0 border border-secondary/30">
                            <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold uppercase mb-2">Order Deadlines & Tracking</h4>
                            <p class="text-slate-400">Set a firm deadline. Parents get email reminders. Once closed, track production visually until shipping.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center flex-shrink-0 border border-purple-500/30">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold uppercase mb-2">Global Commission Sales</h4>
                            <p class="text-slate-400">Agents globally can track their referrals and commissions inside our powerhouse CRM dashboard.</p>
                        </div>
                    </div>

                </div>
                
                <div class="mt-12 flex items-center gap-6">
                    <a href="/coach/dashboard" class="btn btn-primary">Enter Coach Portal</a>
                    <a href="#" class="font-bold text-sm uppercase tracking-wide text-slate-300 hover:text-white flex items-center gap-2">Read The Docs &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
