@extends('layouts.app')

@section('title', 'Login | The Commission Apparel')

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
                    <img src="/images/LR.png" alt="LR Logo" class="w-12 h-12 object-contain">
                </div>
                <h1 class="text-2xl font-black uppercase tracking-tight text-slate-900">System Portal</h1>
                <p class="text-slate-500 text-sm mt-1">Sign in to your account</p>
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

            <form action="{{ route('login') }}" method="POST" class="space-y-6" x-data="{ showPass: false }">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm">
                    @error('email')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Password</label>
                        <a href="#" class="text-xs text-secondary hover:text-[#a11825] transition-colors font-bold">Forgot?</a>
                    </div>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 pr-12 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm">
                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors p-1" tabindex="-1">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 bg-white border-slate-300 rounded text-primary focus:ring-primary shadow-sm">
                    <label for="remember" class="ml-2 text-sm text-slate-600">Remember me for 30 days</label>
                </div>

                <button type="submit" class="btn btn-primary w-full py-3 mt-2 text-sm uppercase tracking-widest font-bold shadow-[0_4px_20px_rgba(192,30,46,0.3)]">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center border-t border-slate-200 pt-6">
                <p class="text-slate-600 text-sm">Don't have an account? <br> <a href="{{ route('register') }}" class="text-secondary hover:text-[#a11825] transition-colors font-bold mt-1 inline-block">Apply for Access</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
