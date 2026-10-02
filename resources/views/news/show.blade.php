@extends('layouts.app')
@section('title', $article->title . ' | The Commission Apparel')

@section('content')
<article class="bg-black text-white min-h-screen pt-24 pb-20">
    <div class="max-w-[1200px] mx-auto px-6">
        
        <!-- Breadcrumb & Tag -->
        <div class="flex items-center gap-3 mb-6 text-xs uppercase font-bold tracking-wider text-slate-400">
            <a href="/" class="hover:text-white transition-colors">Home</a>
            <span>/</span>
            <a href="{{ route('news.index') }}" class="hover:text-white transition-colors">News</a>
            <span>/</span>
            <span class="text-[#cd202c]">{{ $article->category }}</span>
        </div>

        <!-- Article Header -->
        <header class="mb-10 max-w-4xl">
            <span class="inline-block bg-[#cd202c] text-white text-[10px] font-black tracking-widest uppercase px-3 py-1 rounded mb-4">
                {{ $article->category }}
            </span>
            <h1 class="text-3xl md:text-5xl lg:text-6xl font-black uppercase tracking-tight text-white leading-tight mb-6">
                {{ $article->title }}
            </h1>
            
            <div class="flex items-center gap-4 text-xs text-slate-400 font-medium">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-white text-xs">
                        {{ substr($article->author ?? 'C', 0, 1) }}
                    </div>
                    <div>
                        <span class="font-bold text-slate-200 block uppercase tracking-wider">{{ $article->author ?? 'The Commission Apparel' }}</span>
                        <span>{{ $article->published_at ? $article->published_at->format('F d, Y') : $article->created_at->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Media: Video Embed OR Hero Image -->
        @if($article->video_url && $article->embed_url)
        <div class="mb-12 rounded-2xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-900 aspect-video relative">
            <iframe 
                src="{{ $article->embed_url }}" 
                title="{{ $article->title }}" 
                class="w-full h-full border-0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        </div>
        @elseif($article->cover_image)
        <div class="mb-12 rounded-2xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-900 max-h-[600px]">
            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
        </div>
        @endif

        <!-- Main Article Content Body -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-12 items-start mb-16">
            
            <!-- Left: Article Write-up -->
            <div class="space-y-6 text-slate-300 text-base md:text-lg leading-relaxed bg-[#12141a] border border-slate-800/80 rounded-2xl p-8 md:p-10 shadow-xl">
                @if($article->summary)
                <p class="text-xl md:text-2xl font-bold text-white leading-snug border-l-4 border-[#cd202c] pl-5 py-1">
                    {{ $article->summary }}
                </p>
                <hr class="border-slate-800 my-6">
                @endif

                <div class="prose prose-invert max-w-none text-slate-300 leading-relaxed font-sans space-y-5">
                    {!! $article->content !!}
                </div>

                <!-- Uniform Photo Gallery Grid -->
                @if(!empty($article->gallery_images) && count($article->gallery_images) > 0)
                <div class="pt-8 border-t border-slate-800 mt-10">
                    <h3 class="text-xl font-black uppercase text-white tracking-wider mb-6 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#cd202c]"></span>
                        Uniform Craftsmanship & Action Gallery
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($article->gallery_images as $img)
                        <div class="rounded-xl overflow-hidden border border-slate-800 aspect-square group bg-slate-900">
                            <img src="{{ $img }}" alt="Gallery photo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right: Sticky Sidebar / Quick CTA -->
            <aside class="space-y-6 lg:sticky lg:top-24">
                <div class="bg-[#12141a] border border-slate-800 rounded-2xl p-6 shadow-xl text-center">
                    <div class="w-12 h-12 bg-[#cd202c]/10 text-[#cd202c] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#cd202c]/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h3 class="text-base font-black uppercase text-white mb-2">Outfitting Your Team?</h3>
                    <p class="text-slate-400 text-xs mb-5 leading-relaxed">
                        Get bespoke 3D custom uniform mockups tailored specifically for your program within 24 hours.
                    </p>
                    <a href="{{ $article->cta_url ?? '/quote' }}" class="block w-full py-3 bg-[#cd202c] hover:bg-[#a11825] text-white text-xs font-black uppercase tracking-wider rounded-lg shadow-md transition-colors">
                        {{ $article->cta_text ?? 'Request A Custom Quote' }}
                    </a>
                </div>

                <!-- Related Stories -->
                @if($relatedArticles->isNotEmpty())
                <div class="bg-[#12141a] border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 pb-2 border-b border-slate-800">More Stories</h4>
                    <div class="space-y-4">
                        @foreach($relatedArticles as $rel)
                        <a href="{{ route('news.show', $rel->slug) }}" class="flex gap-3 group items-center">
                            @if($rel->cover_image)
                            <img src="{{ $rel->cover_image }}" alt="{{ $rel->title }}" class="w-14 h-14 rounded-lg object-cover shrink-0 border border-slate-800">
                            @endif
                            <div>
                                <span class="text-[9px] font-black uppercase text-[#cd202c] tracking-widest block">{{ $rel->category }}</span>
                                <h5 class="text-xs font-bold text-white group-hover:text-[#cd202c] transition-colors line-clamp-2">{{ $rel->title }}</h5>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </aside>

        </div>

        <!-- Big Bottom Conversion Banner -->
        <section class="bg-gradient-to-r from-[#12141a] via-[#1a1e28] to-[#12141a] border border-slate-800 rounded-2xl p-10 md:p-14 text-center shadow-2xl relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-[#cd202c]/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="text-xs font-black uppercase tracking-widest text-[#cd202c] block mb-2">BUILT FOR CHAMPIONS</span>
                <h2 class="text-3xl md:text-4xl font-black uppercase text-white tracking-tight mb-4">
                    Ready to gear up your team?
                </h2>
                <p class="text-slate-400 text-sm md:text-base mb-8">
                    Elevate your program's identity with 100% custom, sublimated performance armor delivered in lightning turnaround time.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/quote" class="w-full sm:w-auto px-8 py-3.5 bg-[#cd202c] hover:bg-[#a11825] text-white text-xs font-black uppercase tracking-wider rounded-lg shadow-lg transition-all">
                        Request Free 3D Mockup
                    </a>
                    <a href="/store/search" class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 hover:bg-slate-800 border border-slate-700 text-white text-xs font-black uppercase tracking-wider rounded-lg transition-all">
                        Explore Team Stores
                    </a>
                </div>
            </div>
        </section>

    </div>
</article>
@endsection
