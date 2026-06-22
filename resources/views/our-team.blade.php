@extends('layouts.app')

@section('title', 'Our Team | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-[30vh] flex flex-col pt-32 pb-14 justify-end overflow-hidden">
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/85 to-slate-900"></div>
    <div class="relative z-10 max-w-7xl mx-auto w-full px-6">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tighter uppercase text-white mb-6">OUR <span class="text-secondary ml-1">TEAM</span></h1>
        <p class="text-slate-300 mt-2 text-sm md:text-base">Find and contact your dedicated regional sales representative.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-6 py-10 md:py-14">
    <form action="{{ route('our-team') }}" method="GET" class="mb-10"
          hx-get="{{ route('our-team') }}"
          hx-target="#team-search-results"
          hx-select="#team-search-results"
          hx-swap="outerHTML"
          hx-trigger="input from:input[name='q'] delay:300ms, submit"
          hx-push-url="true">
        <div class="flex flex-col md:flex-row gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search by name, state, country, or title..."
                class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm"
            >
            <button type="submit" class="btn btn-primary py-3 px-7 text-xs uppercase tracking-wider font-bold">Search</button>
        </div>
    </form>

    <div id="team-search-results" class="space-y-16">

        {{-- US Territory Group --}}
        @if($usAgents->isNotEmpty())
        <section class="space-y-8">
            <div class="border-l-4 border-secondary pl-4">
                <h2 class="text-xl md:text-2xl font-black uppercase tracking-tight text-slate-900">Find your representative</h2>
                <p class="text-slate-500 text-xs md:text-sm font-medium mt-0.5">Find  the regional representative serving your area.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($usAgents as $state => $agentsInState)
                    @foreach($agentsInState as $agent)
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-lg hover:border-secondary/40 transition-all duration-300 flex flex-col justify-between group shadow-sm">
                        <div>
                            <div class="flex items-start gap-4 mb-5">
                                @if($agent->image_path)
                                    <img src="{{ $agent->image_path }}" alt="{{ $agent->name }}" class="w-16 h-16 rounded-full object-cover border border-slate-200 group-hover:border-secondary/60 transition-colors duration-300 shrink-0">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-xl text-slate-400 shrink-0">
                                        {{ substr($agent->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="inline-block bg-secondary/10 text-secondary text-[9px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                                        {{ $state }}
                                    </span>
                                    <h3 class="text-base font-black uppercase text-slate-900 tracking-wide group-hover:text-secondary transition-colors duration-300">{{ $agent->name }}</h3>
                                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mt-0.5">{{ $agent->title }}</p>
                                </div>
                            </div>
                            @if($agent->bio)
                                <p class="text-slate-600 text-xs leading-relaxed font-medium mb-6 line-clamp-4 hover:line-clamp-none transition-all duration-300">
                                    {{ $agent->bio }}
                                </p>
                            @endif
                        </div>
                        <div class="pt-4 border-t border-slate-100 mt-auto flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Territory: {{ $state }}, {{ strtoupper($agent->country) }}</span>
                            <a href="/quote?rep={{ urlencode($agent->name) }}" class="inline-flex items-center gap-1.5 text-secondary hover:text-slate-900 font-black text-[10px] uppercase tracking-wider transition-colors">
                                Contact Rep &rarr;
                            </a>
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
        </section>
        @endif

        {{-- International Group --}}
        @if($intlAgents->isNotEmpty())
        <section class="space-y-8">
            <div class="border-l-4 border-secondary pl-4">
                <h2 class="text-xl md:text-2xl font-black uppercase tracking-tight text-slate-900">International Representatives</h2>
                <p class="text-slate-500 text-xs md:text-sm font-medium mt-0.5">Connecting international programs with local support.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($intlAgents as $country => $agentsInCountry)
                    @foreach($agentsInCountry as $agent)
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-lg hover:border-secondary/40 transition-all duration-300 flex flex-col justify-between group shadow-sm">
                        <div>
                            <div class="flex items-start gap-4 mb-5">
                                @if($agent->image_path)
                                    <img src="{{ $agent->image_path }}" alt="{{ $agent->name }}" class="w-16 h-16 rounded-full object-cover border border-slate-200 group-hover:border-secondary/60 transition-colors duration-300 shrink-0">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-xl text-slate-400 shrink-0">
                                        {{ substr($agent->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="inline-block bg-secondary/10 text-secondary text-[9px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                                        {{ $country }}
                                    </span>
                                    <h3 class="text-base font-black uppercase text-slate-900 tracking-wide group-hover:text-secondary transition-colors duration-300">{{ $agent->name }}</h3>
                                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mt-0.5">{{ $agent->title }}</p>
                                </div>
                            </div>
                            @if($agent->bio)
                                <p class="text-slate-600 text-xs leading-relaxed font-medium mb-6 line-clamp-4 hover:line-clamp-none transition-all duration-300">
                                    {{ $agent->bio }}
                                </p>
                            @endif
                        </div>
                        <div class="pt-4 border-t border-slate-100 mt-auto flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                Territory: @if($agent->state) {{ $agent->state }}, @endif {{ $country }}
                            </span>
                            <a href="/quote?rep={{ urlencode($agent->name) }}" class="inline-flex items-center gap-1.5 text-secondary hover:text-slate-900 font-black text-[10px] uppercase tracking-wider transition-colors">
                                Contact Rep &rarr;
                            </a>
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
        </section>
        @endif

        {{-- Fallback Empty --}}
        @if($usAgents->isEmpty() && $intlAgents->isEmpty())
        <div class="max-w-md mx-auto text-center py-16 bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <h3 class="text-lg font-black uppercase text-slate-900 tracking-wide">No Representatives Found</h3>
            <p class="text-slate-500 text-xs font-medium mt-2 leading-relaxed">No sales representatives matched your search query. Try searching by state name, rep name, or location.</p>
        </div>
        @endif

    </div>
</div>
@endsection
