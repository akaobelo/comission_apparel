@extends('layouts.app')
@section('title', 'Thank You | The Commission Apparel')
@section('content')
<div class="max-w-[1200px] mx-auto px-6 pb-24 pt-32 lg:pt-40">
    <div class="text-center max-w-2xl mx-auto mb-16">
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
    
    <div class="relative max-w-4xl mx-auto rounded-2xl overflow-hidden shadow-2xl border border-slate-200">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent pointer-events-none"></div>
        <img src="{{ asset('images/coach-admin-preview.png') }}" alt="Coach Dashboard Preview" class="w-full h-auto object-cover">
        <div class="absolute bottom-6 left-6 right-6">
            <div class="bg-white/90 backdrop-blur border border-white/20 p-4 rounded-xl shadow-lg inline-block">
                <p class="text-sm font-bold uppercase tracking-wider text-slate-900">Manage Orders, Inventory, & Sales Data</p>
            </div>
        </div>
    </div>
</div>
@endsection
