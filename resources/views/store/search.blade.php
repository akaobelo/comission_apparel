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
    <form action="{{ route('store.search') }}" method="GET" class="mb-10">
        <div class="flex flex-col md:flex-row gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search by store, coach, organization, or sport"
                class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm"
            >
            <button type="submit" class="btn btn-primary py-3 px-7 text-xs uppercase tracking-wider font-bold">Search</button>
        </div>
    </form>

    @if($stores->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-12 text-center">
            <h2 class="text-2xl font-black uppercase tracking-tight text-slate-900 mb-2">No Team Stores Found</h2>
            <p class="text-slate-600">Try a different search term or check back later for newly opened stores.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
            @foreach($stores as $store)
                <a href="{{ route('store.show', $store->slug) }}" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-primary/40 transition-all flex flex-col h-full">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-1">{{ $store->user->sport ?? 'Team Athletics' }}</p>
                            <h2 class="text-lg font-black text-slate-900 leading-tight">{{ $store->name }}</h2>
                            <p class="text-sm text-slate-600 mt-1">
                                {{ $store->user->organization ?? 'Organization not set' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">Coach {{ $store->user->name }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-green-100 text-green-700 border border-green-200">Open</span>
                            @if($store->user->logo_path)
                                <img src="{{ Str::startsWith($store->user->logo_path, 'http') ? $store->user->logo_path : '/storage/' . $store->user->logo_path }}" alt="Team Logo" class="w-12 h-12 md:w-14 md:h-14 object-contain rounded-full border border-slate-200 shadow-sm mt-1 bg-white">
                            @endif
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-[11px] md:text-xs text-slate-500">
                            @if($store->order_deadline)
                                Deadline: <span class="font-bold text-slate-700">{{ $store->order_deadline->format('M d, Y') }}</span>
                            @else
                                No deadline posted
                            @endif
                        </div>
                        <span class="text-[10px] md:text-xs font-black uppercase tracking-wider text-secondary">Open Store →</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $stores->links() }}
        </div>
    @endif
</div>
@endsection
