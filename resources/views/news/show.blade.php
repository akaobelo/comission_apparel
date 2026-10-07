@extends('layouts.app')
@section('title', $article->title . ' | The Commission Apparel')

@section('content')
<article class="bg-slate-50 text-slate-900 min-h-screen pt-24 pb-20">
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Breadcrumb & Tag -->
        <div class="flex items-center gap-2 text-xs uppercase font-bold tracking-wider text-slate-500">
            <a href="/" class="hover:text-[#cd202c] transition-colors">Home</a>
            <span>/</span>
            <a href="{{ route('news.index') }}" class="hover:text-[#cd202c] transition-colors">News</a>
            <span>/</span>
            <span class="text-[#cd202c]">{{ $article->category }}</span>
        </div>

        <!-- Article Header Card -->
        <header class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl p-6 md:p-10 shadow-sm">
            <span class="inline-block bg-[#cd202c] text-white text-[10px] font-black tracking-widest uppercase px-3 py-1 rounded mb-4 shadow-sm">
                {{ $article->category }}
            </span>
            <h1 class="text-2xl md:text-4xl lg:text-5xl font-black uppercase tracking-tight text-slate-900 leading-tight mb-6">
                {{ $article->title }}
            </h1>
            
            <div class="flex items-center gap-4 text-xs text-slate-500 font-medium pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#cd202c] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ substr($article->author ?? 'C', 0, 1) }}
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 block uppercase tracking-wider">{{ $article->author ?? 'The Commission Apparel' }}</span>
                        <span>{{ $article->published_at ? $article->published_at->format('F d, Y') : $article->created_at->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Media: Video Embed OR Hero Image -->
        @if($article->video_url && $article->embed_url)
        <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-md bg-slate-950 aspect-video relative">
            <iframe 
                src="{{ $article->embed_url }}" 
                title="{{ $article->title }}" 
                class="w-full h-full border-0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        </div>
        @elseif($article->cover_image)
        <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-md bg-slate-100 max-h-[600px]">
            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
        </div>
        @endif

        <!-- Main Article Content Body -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-8 items-start">
            
            <!-- Left: Article Write-up Card -->
            <div class="space-y-6 bg-white border border-slate-200 rounded-2xl md:rounded-3xl p-6 md:p-10 shadow-sm">
                @if($article->summary)
                <p class="text-lg md:text-xl font-bold text-slate-900 leading-snug border-l-4 border-[#cd202c] pl-5 py-1">
                    {{ $article->summary }}
                </p>
                <hr class="border-slate-100 my-6">
                @endif

                <div class="article-body prose max-w-none text-slate-700 text-base md:text-lg leading-relaxed font-sans space-y-6">
                    {!! $article->content !!}
                </div>

                <style>
                    .article-body p, .article-body span, .article-body li, .article-body div {
                        color: #334155 !important; /* slate-700 */
                    }
                    .article-body h1, .article-body h2, .article-body h3, .article-body h4, .article-body h5, .article-body h6, .article-body strong {
                        color: #0f172a !important; /* slate-900 */
                    }
                    .article-body blockquote {
                        background-color: #f8fafc !important; /* slate-50 */
                        color: #0f172a !important;
                        border-left: 4px solid #cd202c !important;
                        padding: 1.5rem !important;
                        border-radius: 0.75rem !important;
                        margin: 2rem 0 !important;
                        border: 1px solid #e2e8f0;
                        border-left-width: 4px;
                    }
                    .article-body blockquote p {
                        color: #0f172a !important;
                    }
                </style>

                <!-- Uniform Photo Gallery Grid -->
                @if(!empty($article->gallery_images) && count($article->gallery_images) > 0)
                <div class="pt-8 border-t border-slate-100 mt-10">
                    <h3 class="text-lg font-black uppercase text-slate-900 tracking-wider mb-5 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#cd202c]"></span>
                        Uniform Craftsmanship & Action Gallery
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($article->gallery_images as $img)
                        <div class="rounded-xl overflow-hidden border border-slate-200 aspect-square group bg-slate-100 shadow-sm">
                            <img src="{{ $img }}" alt="Gallery photo" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right: Sticky Sidebar / Quick CTA -->
            <aside class="space-y-6 lg:sticky lg:top-24">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
                    <div class="w-12 h-12 bg-[#cd202c]/10 text-[#cd202c] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#cd202c]/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h3 class="text-base font-black uppercase text-slate-900 mb-2">Outfitting Your Team?</h3>
                    <p class="text-slate-600 text-xs mb-5 leading-relaxed font-normal">
                        Get bespoke 3D custom uniform mockups tailored specifically for your program within 24 hours.
                    </p>
                    <a href="{{ $article->cta_url ?? '/quote' }}" class="block w-full py-3 bg-[#cd202c] hover:bg-[#a11825] text-white text-xs font-black uppercase tracking-wider rounded-lg shadow-sm transition-colors">
                        {{ $article->cta_text ?? 'Request A Custom Quote' }}
                    </a>
                </div>

                <!-- Related Stories -->
                @if($relatedArticles->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4 pb-2 border-b border-slate-100">More Stories</h4>
                    <div class="space-y-4">
                        @foreach($relatedArticles as $rel)
                        <a href="{{ route('news.show', $rel->slug) }}" class="flex gap-3 group items-center">
                            @if($rel->cover_image)
                            <img src="{{ $rel->cover_image }}" alt="{{ $rel->title }}" class="w-14 h-14 rounded-lg object-cover shrink-0 border border-slate-200 shadow-sm">
                            @endif
                            <div>
                                <span class="text-[9px] font-black uppercase text-[#cd202c] tracking-widest block mb-0.5">{{ $rel->category }}</span>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-[#cd202c] transition-colors line-clamp-2">{{ $rel->title }}</h5>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </aside>

        </div>

        <!-- Big Bottom Conversion Banner -->
        <section class="bg-gradient-to-r from-[#8b111a] via-[#cd202c] to-[#8b111a] border border-red-800/80 rounded-2xl md:rounded-3xl p-8 md:p-12 text-center text-white shadow-lg relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-4 relative z-10">
                <span class="inline-block bg-black/40 text-white text-[10px] font-black tracking-widest uppercase px-3 py-1 rounded-full border border-white/10 shadow-sm">
                    COMMISSION QUALITY
                </span>
                <h3 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-white leading-tight">
                    READY TO BRING YOUR TEAM'S VISION TO LIFE?
                </h3>
                <p class="text-red-100 text-xs md:text-sm font-normal leading-relaxed">
                    Join hundreds of championship programs outfitted in elite Commission sublimated uniforms.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="/quote" class="w-full sm:w-auto px-8 py-3.5 bg-black hover:bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-full shadow-md transition-all hover:scale-105">
                        Start Custom Design
                    </a>
                    <a href="{{ route('store.search') }}" class="w-full sm:w-auto px-8 py-3.5 bg-white/20 hover:bg-white/30 text-white border border-white/40 font-black text-xs uppercase tracking-widest rounded-full transition-all backdrop-blur-sm">
                        Find A Team Store
                    </a>
                </div>
            </div>
        </section>

    </div>
</article>
@endsection
