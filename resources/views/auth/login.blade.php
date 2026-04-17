@extends('layouts.app')

@section('title', 'Login | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-screen flex items-center justify-center pt-20 pb-12 overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-10 mix-blend-screen"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>
    
    <!-- Abstract glowing orbs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-sm px-6">
        <div class="glass-panel p-6 shadow-2xl border-white/10 animate-slide-up">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-[0_0_20px_rgba(56,189,248,0.4)] mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h1 class="text-2xl font-black uppercase tracking-tight text-white">System Portal</h1>
                <p class="text-slate-400 text-sm mt-1">Sign in to your account</p>
            </div>

            @if(session('error'))
                <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600">
                    @error('email')
                        <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Password</label>
                        <a href="#" class="text-xs text-primary hover:text-white transition-colors">Forgot?</a>
                    </div>
                    <input type="password" name="password" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 bg-slate-900 border-slate-700 rounded text-primary focus:ring-primary focus:ring-offset-slate-900">
                    <label for="remember" class="ml-2 text-sm text-slate-400">Remember me for 30 days</label>
                </div>

                <button type="submit" class="btn btn-primary w-full py-3 mt-2 text-sm uppercase tracking-widest font-bold shadow-[0_4px_20px_rgba(56,189,248,0.2)]">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center border-t border-slate-700/50 pt-6">
                <p class="text-slate-400 text-sm">Don't have a coach account? <br> <a href="{{ route('register') }}" class="text-primary hover:text-white transition-colors font-bold mt-1 inline-block">Apply for Access</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
