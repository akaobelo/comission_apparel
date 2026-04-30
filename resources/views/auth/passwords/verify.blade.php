@extends('layouts.app')

@section('title', 'Verify Identity | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-screen flex items-center justify-center pt-20 pb-12 overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute inset-0 bg-slate-50"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-5 mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-100 via-slate-50/80 to-transparent"></div>
    
    <!-- Abstract glowing orbs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-lg px-8">
        <div class="glass-panel p-6 shadow-xl border border-slate-200 bg-white/90 backdrop-blur-xl animate-slide-up">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto rounded-xl bg-secondary flex items-center justify-center shadow-md mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h1 class="text-2xl font-black uppercase tracking-tight text-slate-900">Account Recovery</h1>
                <p class="text-slate-500 text-sm mt-1">Verify your identity to reset your password.</p>
            </div>

            @if(session('error'))
                <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/30 text-red-500 text-sm font-bold flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('password.verify.submit') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Registered Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="coach@example.com" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-300 shadow-sm">
                    @error('email')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="(555) 555-5555" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-300 shadow-sm">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Organization Name</label>
                    <input type="text" name="organization" value="{{ old('organization') }}" required placeholder="Springfield High" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-300 shadow-sm">
                    @error('organization')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-500 mt-2">
                    <span class="font-bold">Note:</span> These details must perfectly match the information you provided during registration.
                </div>

                <button type="submit" class="btn btn-primary w-full py-3 mt-4 text-sm uppercase tracking-widest font-bold shadow-[0_4px_20px_rgba(192,30,46,0.3)]">
                    Verify Identity
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-700 text-sm font-bold transition-colors">Return to Login</a>
            </div>
        </div>
    </div>
</div>
@endsection
