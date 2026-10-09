@extends('layouts.app')
@section('title', $article->title . ' | The Commission Apparel')

@section('meta')
    @php
        $rawImage = $article->cover_image;
        if (!empty($rawImage)) {
            $altJpg = str_replace('.png', '.jpg', $rawImage);
            if (str_ends_with(strtolower($rawImage), '.png') && file_exists(public_path(ltrim($altJpg, '/')))) {
                $articleOgImg = asset(ltrim($altJpg, '/'));
            } else {
                $articleOgImg = asset(ltrim($rawImage, '/'));
            }
        } elseif (!empty($article->video_url) && preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $article->video_url, $ytMatches)) {
            $articleOgImg = 'https://img.youtube.com/vi/' . $ytMatches[1] . '/maxresdefault.jpg';
        } else {
            $articleOgImg = asset('images/og-home.jpg');
        }

        if (!str_starts_with($articleOgImg, 'http')) {
            $articleOgImg = url($articleOgImg);
        }
        if (str_contains($articleOgImg, 'thecommissionapparel.com')) {
            $articleOgImg = str_replace('http://', 'https://', $articleOgImg);
        }

        $articleOgUrl = route('news.show', $article->slug);
        if (str_contains($articleOgUrl, 'thecommissionapparel.com')) {
            $articleOgUrl = str_replace('http://', 'https://', $articleOgUrl);
        }

        $articleDesc = !empty($article->summary) 
            ? $article->summary 
            : Str::limit(strip_tags($article->content ?? ''), 160);

        $articleImageType = 'image/jpeg';
        if (str_ends_with(strtolower(parse_url($articleOgImg, PHP_URL_PATH) ?? ''), '.png')) {
            $articleImageType = 'image/png';
        }
    @endphp
    <meta name="description" content="{{ $articleDesc }}">
    <meta property="og:site_name" content="The Commission Apparel">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $articleOgUrl }}">
    <meta property="og:title" content="{{ $article->title }} | The Commission Apparel">
    <meta property="og:description" content="{{ $articleDesc }}">
    <meta property="og:image" content="{{ $articleOgImg }}">
    <meta property="og:image:secure_url" content="{{ $articleOgImg }}">
    <meta property="og:image:type" content="{{ $articleImageType }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $article->title }}">
    <meta property="article:published_time" content="{{ ($article->published_at ?? $article->created_at)->toIso8601String() }}">
    <meta property="article:section" content="{{ $article->category }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $article->title }} | The Commission Apparel">
    <meta name="twitter:description" content="{{ $articleDesc }}">
    <meta name="twitter:image" content="{{ $articleOgImg }}">
@endsection

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
            <img src="{{ asset(ltrim($article->cover_image, '/')) }}" alt="{{ $article->title }}" onerror="this.onerror=null; this.src='{{ asset('images/group.jpg') }}';" class="w-full h-full object-cover">
        </div>
        @endif

        <!-- Main Article Content Body -->
        <div class="grid grid-cols-1 {{ isset($relatedArticles) && $relatedArticles->isNotEmpty() ? 'lg:grid-cols-[1fr_340px]' : '' }} gap-8 items-start">
            
            <!-- Left: Article Write-up Card -->
            <div class="space-y-6 bg-white border border-slate-200 rounded-2xl md:rounded-3xl p-6 md:p-10 shadow-sm">
                @if($article->summary)
                <p class="text-lg md:text-xl font-bold text-slate-900 leading-snug border-l-4 border-[#cd202c] pl-5 py-1">
                    {{ $article->summary }}
                </p>
                <hr class="border-slate-100 my-6">
                @endif

                <div class="article-body prose max-w-none text-slate-700 text-base md:text-lg leading-relaxed font-sans space-y-6 text-justify">
                    {!! $article->content !!}
                </div>

                <style>
                    .article-body p, .article-body span, .article-body li, .article-body div {
                        color: #334155 !important; /* slate-700 */
                        text-align: justify !important;
                        text-justify: inter-word;
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
                        text-align: justify !important;
                    }
                    .article-body blockquote p {
                        color: #0f172a !important;
                        text-align: justify !important;
                    }
                </style>
            </div>

            @if(isset($relatedArticles) && $relatedArticles->isNotEmpty())
            <!-- Right: Sticky Sidebar / More Stories -->
            <aside class="space-y-6 lg:sticky lg:top-24">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4 pb-2 border-b border-slate-100">More Stories</h4>
                    <div class="space-y-4">
                        @foreach($relatedArticles as $rel)
                        <a href="{{ route('news.show', $rel->slug) }}" class="flex gap-3 group items-center">
                            @if($rel->cover_image)
                            <img src="{{ asset(ltrim($rel->cover_image, '/')) }}" alt="{{ $rel->title }}" onerror="this.onerror=null; this.src='{{ asset('images/group.jpg') }}';" class="w-14 h-14 rounded-lg object-cover shrink-0 border border-slate-200 shadow-sm">
                            @endif
                            <div>
                                <span class="text-[9px] font-black uppercase text-[#cd202c] tracking-widest block mb-0.5">{{ $rel->category }}</span>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-[#cd202c] transition-colors line-clamp-2">{{ $rel->title }}</h5>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </aside>
            @endif

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
