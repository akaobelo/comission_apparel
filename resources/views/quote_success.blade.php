@extends('layouts.app')
@section('title', 'Thank You | The Commission Apparel')
@section('content')
<div class="max-w-[1200px] mx-auto px-6 pb-16" style="padding-top: clamp(2rem, 10vw, 7rem);">
    <div class="text-center max-w-2xl mx-auto mb-8">
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-slate-900 mb-6">Quote <span class="text-secondary">Requested</span> Successfully!</h1>
        <div class="p-6 bg-green-50 border border-green-200 rounded-xl mb-8">
            <p class="text-green-800 text-lg font-medium leading-relaxed">
                Thank you for your inquiry.<br>
                Please allow 24–48 hours for a representative to contact you to discuss your requirements.
            </p>
        </div>
        
        <div class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm text-left">
            <h2 class="text-2xl font-black uppercase tracking-tight text-slate-900 mb-4">Start Building Your Store</h2>
            <p class="text-slate-600 mb-6 text-lg">
                In the meantime, we encourage you to proceed with creating your profile. This will bring you one step closer to launching your fully functional Team Store.
            </p>
            <div class="flex justify-center md:justify-start">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 py-4 px-8 bg-secondary hover:bg-[#a11825] text-white text-base font-black uppercase tracking-widest rounded-xl transition-all shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Dashboard Sign-in / Sign-up
                </a>
            </div>
        </div>
    </div>
    
    <div class="relative max-w-5xl mx-auto rounded-2xl overflow-hidden shadow-2xl border border-slate-200" style="aspect-ratio: 3.2 / 1;">
        <img src="{{ asset('images/team-store-background-v2.png') }}" alt="Our Coach Dashboard" class="absolute w-full max-w-none h-auto left-0 -top-[9%] md:-top-[12%] lg:-top-[15%]">
    </div>
</div>
@endsection
