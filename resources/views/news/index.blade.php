@extends('layouts.app')
@section('title', 'News & Stories | The Commission Apparel')

@section('content')
<div class="bg-slate-50 min-h-screen text-slate-900 pt-24 pb-16">
    <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header Container -->
        <div class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl p-8 md:p-12 text-center shadow-sm max-w-4xl mx-auto">
            <span class="inline-block bg-[#cd202c] text-white text-[10px] font-black tracking-widest uppercase px-3 py-1 rounded-full mb-3 shadow-sm">
                THE COMMISSION CHRONICLES
            </span>
            <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-slate-900 mb-4">
                LATEST NEWS & STORIES
            </h1>
            <p class="text-slate-700 text-sm md:text-base max-w-2xl mx-auto font-normal leading-relaxed" style="color: #475569;">
                Explore program spotlights, championship moments, athlete interviews, and exclusive custom uniform reveals from across the nation.
            </p>
        </div>

        <!-- Category Filters -->
        @if($categories->isNotEmpty())
        <div class="flex flex-wrap items-center justify-center gap-2">
            <a href="{{ route('news.index') }}" class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ !request('category') ? 'bg-[#cd202c] text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-700 hover:text-[#cd202c] hover:border-[#cd202c]' }}">
                All Stories
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('news.index', ['category' => $cat]) }}" class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ request('category') === $cat ? 'bg-[#cd202c] text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-700 hover:text-[#cd202c] hover:border-[#cd202c]' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
        @endif

        <!-- Articles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            @forelse($articles as $article)
            <article class="bg-white border border-slate-200 hover:border-[#cd202c] rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col group">
                <!-- Thumbnail -->
                <a href="{{ route('news.show', $article->slug) }}" class="relative block overflow-hidden aspect-[16/10] bg-slate-100">
                    @if($article->cover_image)
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 font-bold">COMMISSION APPAREL</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-70"></div>
                    <div class="absolute top-3 left-3 bg-[#cd202c] text-white text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded shadow-sm">
                        {{ $article->category }}
                    </div>
                    @if($article->video_url)
                    <div class="absolute bottom-3 right-3 w-8 h-8 rounded-full bg-black/70 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white shadow-sm">
                        <svg class="w-4 h-4 text-white fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                    @endif
                </a>

                <!-- Content -->
                <div class="p-6 flex flex-col flex-1">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2" style="color: #64748b;">
                        {{ $article->published_at ? $article->published_at->format('M d, Y') : $article->created_at->format('M d, Y') }}
                    </div>
                    <h2 class="text-base md:text-lg font-black uppercase text-slate-900 leading-tight mb-2.5 group-hover:text-[#cd202c] transition-colors line-clamp-2">
                        <a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a>
                    </h2>
                    <p class="text-slate-700 text-xs leading-relaxed line-clamp-3 mb-6 flex-1 font-normal" style="color: #475569;">
                        {{ $article->summary }}
                    </p>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                        <span class="text-[11px] font-bold text-slate-500 uppercase" style="color: #64748b;">{{ $article->author ?? 'Commission Apparel' }}</span>
                        <a href="{{ route('news.show', $article->slug) }}" class="text-xs font-black uppercase text-[#cd202c] group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                            Read Story <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </article>
            @empty
            <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-slate-600 text-sm font-bold uppercase tracking-wider" style="color: #475569;">No articles published yet in this category.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($articles->hasPages())
        <div class="pt-4 flex justify-center">
            {{ $articles->links() }}
        </div>
        @endif

    </div>
</div>
@endsection
