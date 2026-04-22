@extends('layouts.app')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center bg-slate-50 relative overflow-hidden">
    <!-- Subtle Background Dot Pattern -->
    <div class="absolute inset-0 z-0 opacity-40" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;"></div>
    
    <div class="relative z-10 text-center px-6 max-w-2xl mx-auto">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-50 text-secondary mb-8 shadow-sm border border-red-100 mx-auto">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
        
        <h1 class="text-5xl md:text-6xl font-black tracking-tighter uppercase mb-4 text-slate-900">
            Under <span class="text-secondary">Construction</span>
        </h1>
        
        <p class="text-lg md:text-xl text-slate-600 font-medium mb-10 leading-relaxed">
            We are currently building out this functionality. This page will be available soon. Please check back later!
        </p>
        
        <a href="/" class="btn bg-slate-900 hover:bg-slate-800 text-white px-8 py-4 text-sm font-black uppercase tracking-wider rounded-md shadow-md transition-all inline-flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Return to Homepage
        </a>
    </div>
</section>
@endsection
