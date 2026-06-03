@extends('layouts.app')

@section('title', 'Sizing Charts | The Commission Apparel')

@section('content')
<div class="bg-slate-50 min-h-screen pt-32 pb-24" x-data="{ activeImage: null }">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-slate-900 mb-4">Sizing Charts</h1>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto">Review our sizing charts below to ensure the perfect fit for your custom apparel.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 mb-10">
            @forelse($sizingCharts as $chart)
            <div class="flex flex-col group cursor-pointer" @click="activeImage = '{{ $chart->image_path }}'">
                <div class="aspect-[4/5] bg-white rounded-2xl relative overflow-hidden transition-colors flex items-center justify-center border border-slate-200">
                    <img src="{{ $chart->image_path }}" alt="{{ $chart->title }}" class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105">
                </div>
                <div class="pt-3 flex flex-col text-center items-center w-full px-1">
                    <span class="text-xs md:text-sm font-medium text-red-600 mb-0.5 w-full truncate">View Size Chart</span>
                    <h2 class="text-sm md:text-base font-bold truncate w-full group-hover:text-secondary transition-colors" style="font-family: 'Arial Narrow', 'Franklin Gothic Medium', sans-serif; color: #0f172a;" title="{{ $chart->title }}">
                        {{ $chart->title }}
                    </h2>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center text-slate-500 py-12">
                <p>Sizing charts are coming soon. Please check back later!</p>
            </div>
            @endforelse
        </div>
        <!-- Lightbox Modal -->
        <div x-show="activeImage" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm" @click.self="activeImage = null" @keydown.escape.window="activeImage = null">
            <button @click="activeImage = null" class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <img :src="activeImage" alt="Expanded View" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl">
        </div>
    </div>
</div>
@endsection
