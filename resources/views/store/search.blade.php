@extends('layouts.app')

@section('title', 'Team Stores | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-[30vh] flex flex-col pt-32 pb-14 justify-end overflow-hidden">
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/85 to-slate-900"></div>
    <div class="relative z-10 max-w-7xl mx-auto w-full px-6">
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tighter uppercase text-white mb-6">TEAM <span class="text-secondary ml-1">STORES</span></h1>
        <p class="text-slate-300 mt-2 text-sm md:text-base">Choose your team and continue to the same parent order page.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-6 py-10 md:py-14">
    <form action="{{ route('store.search') }}" method="GET" class="mb-10"
          hx-get="{{ route('store.search') }}"
          hx-target="#store-search-results"
          hx-select="#store-search-results"
          hx-swap="outerHTML"
          hx-trigger="input from:input[name='q'] delay:300ms, submit"
          hx-push-url="true">
        <div class="flex flex-col md:flex-row gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search by team store, school, or sport"
                class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm"
            >
            <button type="submit" class="btn btn-primary py-3 px-7 text-xs uppercase tracking-wider font-bold">Search</button>
        </div>
    </form>

    <div id="store-search-results">

    @if($stores->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-12 text-center">
            <h2 class="text-2xl font-black uppercase tracking-tight text-slate-900 mb-2">No Team Stores Found</h2>
            <p class="text-slate-600">Try a different search term or check back later for newly opened stores.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
            @foreach($stores as $store)
                <a href="{{ route('store.show', $store->slug) }}" class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-primary/40 transition-all flex flex-col h-full">
                    {{-- Cover Banner --}}
                    @if($store->cover_image_url)
                        <div class="relative w-full aspect-[2.2/1] overflow-hidden bg-slate-950">
                            <img src="{{ $store->cover_image_url }}" alt="{{ $store->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                    @elseif($store->logo_url)
                        <div class="relative w-full aspect-[2.2/1] overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 flex items-center justify-center p-6">
                            <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="max-h-16 max-w-[70%] object-contain group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                    @else
                        <div class="relative w-full aspect-[2.2/1] overflow-hidden bg-slate-900">
                            <img src="https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=800" alt="{{ $store->name }}" class="w-full h-full object-cover opacity-60 group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                    @endif

                    {{-- Store Details --}}
                    <div class="p-4 sm:p-5 flex flex-col flex-1 justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-1">{{ $store->sport ?: ($store->user->sport ?? 'Team Athletics') }}</p>
                            <div class="flex items-end justify-between gap-3">
                                <h2 class="text-base sm:text-lg font-black text-slate-900 leading-snug group-hover:text-primary transition-colors">{{ $store->name }}</h2>
                                <span class="text-[10px] sm:text-xs font-black uppercase tracking-wider text-secondary shrink-0 whitespace-nowrap group-hover:translate-x-0.5 transition-transform mb-0.5">Open Store &rarr;</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center pb-8" hx-boost="true" hx-target="#store-search-results" hx-select="#store-search-results" hx-swap="outerHTML">
            {{ $stores->links() }}
        </div>
    @endif
    </div>
</div>
@endsection
