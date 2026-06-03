@extends('layouts.app')

@section('title', 'Sizing Charts | The Commission Apparel')

@section('content')
<div class="bg-slate-50 min-h-screen pt-32 pb-24" x-data="{ activeImage: null }">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-slate-900 mb-4">Sizing Charts</h1>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto">Review our sizing charts below to ensure the perfect fit for your custom apparel.</p>
        </div>

        <div class="space-y-16">
            @forelse($sizingCharts as $chart)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 bg-slate-900 border-b border-slate-800">
                    <h2 class="text-2xl font-black uppercase tracking-wide text-white">{{ $chart->title }}</h2>
                </div>
                <div class="p-6 flex justify-center bg-white">
                    <img src="{{ $chart->image_path }}" alt="{{ $chart->title }}" class="max-w-full h-auto rounded-lg cursor-pointer transition-transform hover:scale-[1.02]" @click="activeImage = '{{ $chart->image_path }}'">
                </div>
            </div>
            @empty
            <div class="text-center text-slate-500 py-12">
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
