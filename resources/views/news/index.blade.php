@extends('layouts.app')
@section('title', 'News & Stories | The Commission Apparel')

@section('content')
<div class="bg-black min-h-screen text-white pt-24 pb-16">
    <div class="max-w-[1400px] mx-auto px-6">
        
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="inline-block bg-[#cd202c] text-white text-[10px] font-black tracking-widest uppercase px-3 py-1 rounded-full mb-3">
                THE COMMISSION CHRONICLES
            </span>
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-white mb-4">
                LATEST NEWS & STORIES
            </h1>
            <p class="text-slate-400 text-sm md:text-base">
                Explore program spotlights, championship moments, athlete interviews, and exclusive custom uniform reveals from across the nation.
            </p>
        </div>

        <!-- Category Filters -->
        @if($categories->isNotEmpty())
        <div class="flex flex-wrap items-center justify-center gap-2 mb-10">
            <a href="{{ route('news.index') }}" class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ !request('category') ? 'bg-[#cd202c] text-white shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800' }}">
                All Stories
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('news.index', ['category' => $cat]) }}" class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ request('category') === $cat ? 'bg-[#cd202c] text-white shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
        @endif

        <!-- Articles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @forelse($articles as $article)
            <article class="bg-[#12141a] border border-slate-800 hover:border-[#cd202c]/50 rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col group">
                <!-- Thumbnail -->
                <a href="{{ route('news.show', $article->slug) }}" class="relative block overflow-hidden aspect-[16/10] bg-slate-900">
                    @if($article->cover_image)
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-600 font-bold">COMMISSION APPAREL</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-60"></div>
                    <div class="absolute top-3 left-3 bg-[#cd202c] text-white text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded">
                        {{ $article->category }}
                    </div>
                    @if($article->video_url)
                    <div class="absolute bottom-3 right-3 w-8 h-8 rounded-full bg-black/70 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white">
                        <svg class="w-4 h-4 text-white fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                    @endif
                </a>

                <!-- Content -->
                <div class="p-6 flex flex-col flex-1">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        {{ $article->published_at ? $article->published_at->format('M d, Y') : $article->created_at->format('M d, Y') }}
                    </div>
                    <h2 class="text-lg font-black uppercase text-white leading-tight mb-3 group-hover:text-[#cd202c] transition-colors line-clamp-2">
                        <a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a>
                    </h2>
                    <p class="text-slate-400 text-xs leading-relaxed line-clamp-3 mb-6 flex-1">
                        {{ $article->summary }}
                    </p>
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between mt-auto">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">{{ $article->author ?? 'Commission Apparel' }}</span>
                        <a href="{{ route('news.show', $article->slug) }}" class="text-xs font-black uppercase text-[#cd202c] group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                            Read Story <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </article>
            @empty
            <div class="col-span-full text-center py-16 bg-[#12141a] rounded-2xl border border-slate-800">
                <p class="text-slate-400 text-sm font-bold uppercase tracking-wider">No articles published yet in this category.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $articles->links() }}
        </div>

    </div>
</div>
@endsection
