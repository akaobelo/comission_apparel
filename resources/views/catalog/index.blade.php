@extends('layouts.app')

@section('title', 'Design Catalog | The Commission Apparel')

@section('content')
<section class="pt-32 pb-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6">
        <div class="max-w-3xl">
            <p class="text-xs font-black uppercase tracking-widest text-secondary mb-3">Design Collections</p>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight uppercase text-slate-900">View Full Catalog</h1>
            <p class="text-slate-600 mt-4 text-base md:text-lg">
                Explore our latest team apparel concepts across all packages and individual items.
            </p>
        </div>
    </div>
</section>

<section class="py-12 bg-slate-50 min-h-[50vh]" x-data="{ previewOpen: false, previewSrc: '', previewAlt: '' }">
    <div class="max-w-7xl mx-auto px-6">
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <form method="GET" action="{{ route('catalog.index') }}" class="flex items-center gap-2">
                <label for="sport" class="text-xs font-black uppercase tracking-wider text-slate-600">Filter by sport</label>
                <select id="sport" name="sport" class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none">
                    <option value="">All Sports</option>
                    @foreach($availableSports as $sport)
                        <option value="{{ $sport }}" {{ $selectedSport === $sport ? 'selected' : '' }}>{{ $sport }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#a11825] transition-colors">
                    Apply
                </button>
                @if(!empty($selectedSport))
                    <a href="{{ route('catalog.index') }}" class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </form>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Showing {{ $designCatalog->count() }} design{{ $designCatalog->count() === 1 ? '' : 's' }}
            </p>
        </div>

        @if($designCatalog->isEmpty())
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-xl p-10 text-center text-slate-500 font-bold uppercase tracking-widest text-sm">
                No designs found for this sport yet.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach($designCatalog as $design)
                    @php
                        $imageSrc = null;
                        if (!empty($design->image_paths)) {
                            $imageSrc = $design->image_paths[0];
                        } elseif ($design->image_url) {
                            $imageSrc = $design->image_url;
                        }
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="bg-white border rounded-2xl overflow-hidden shadow-sm flex flex-col group relative transition-all duration-300 border-slate-200 hover:border-secondary/50 hover:shadow-lg">
                            <div class="aspect-[4/3] bg-[#f0f2f5] relative overflow-hidden group-hover:bg-[#e4e7ec] transition-colors flex items-center justify-center">
                            @if($imageSrc)
                                <button
                                    type="button"
                                    class="w-full h-full block"
                                    @click="previewOpen = true; previewSrc = '{{ $imageSrc }}'; previewAlt = '{{ addslashes($design->name) }}'; document.body.style.overflow = 'hidden';"
                                >
                                    <img src="{{ $imageSrc }}" alt="{{ $design->name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500 cursor-zoom-in">
                                </button>
                            @else
                                <div class="text-slate-400 font-medium text-xs">No Image</div>
                            @endif
                            </div>

                            <div class="p-6 flex flex-col flex-1 bg-white border-t border-slate-100">
                                <span class="text-[10px] font-black uppercase tracking-widest text-red-600 mb-2">{{ $design->type_label }}</span>
                                <h2 class="text-lg font-black text-slate-900 leading-tight mb-3">{{ $design->name }}</h2>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-2">{{ $design->category_label }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-8">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md" @click="previewOpen = false; document.body.style.overflow = 'auto';"></div>
        <div class="relative max-w-6xl w-full max-h-[90vh] flex items-center justify-center">
            <img :src="previewSrc" :alt="previewAlt" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl border border-white/20">
            <button
                type="button"
                class="absolute top-3 left-3 md:top-4 md:left-4 text-white/60 hover:text-white transition-colors"
                @click="previewOpen = false; document.body.style.overflow = 'auto';"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</section>
@endsection
