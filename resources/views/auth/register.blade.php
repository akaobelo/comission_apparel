@extends('layouts.app')

@section('title', 'Apply for Access | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-screen flex items-center justify-center pt-24 pb-12 overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-10 mix-blend-screen"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>
    
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-primary/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-xl px-6">
        <div class="glass-panel p-8 md:p-10 shadow-2xl border-white/10 animate-slide-up">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-[0_0_20px_rgba(56,189,248,0.4)] mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                <h1 class="text-2xl font-black uppercase tracking-tight text-white">Coach Application</h1>
                <p class="text-slate-400 text-sm mt-1">Register to build team stores and manage orders. <br> <span class="text-primary font-bold">All accounts require admin verification.</span></p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600" placeholder="e.g. Jordan Smith">
                        @error('name')
                            <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600" placeholder="coach@example.com">
                        @error('email')
                            <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Organization / School Name</label>
                        <input type="text" name="organization" value="{{ old('organization') }}" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600" placeholder="e.g. Springfield High Athletics">
                        @error('organization')
                            <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Sport / Activity</label>
                        <input type="text" name="sport" value="{{ old('sport') }}" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600" placeholder="e.g. Track & Field">
                        @error('sport')
                            <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600" placeholder="(555) 123-4567">
                        @error('phone')
                            <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Upload Team Logo (Optional)</label>
                        <input type="file" name="logo" accept="image/*" class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-400 focus:border-primary focus:outline-none transition-all file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:uppercase file:bg-primary/20 file:text-primary hover:file:bg-primary/30 cursor-pointer">
                        @error('logo')
                            <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600">
                        @error('password')
                            <p class="text-red-400 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-600">
                    </div>
                </div>

                <div class="bg-slate-900 rounded-lg p-4 border border-slate-700 flex gap-3 items-start mt-2">
                    <svg class="w-5 h-5 text-secondary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        By submitting this application, you agree to The Commission Apparel's terms of service. You will be notified via email once an administrator verifies your organizational affiliation and approves your account.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 mt-2 text-sm uppercase tracking-widest font-bold shadow-[0_4px_20px_rgba(56,189,248,0.2)]">
                    Submit Application
                </button>
            </form>

            <div class="mt-8 text-center border-t border-slate-700/50 pt-6">
                <p class="text-slate-400 text-sm">Already have an approved account? <br> <a href="{{ route('login') }}" class="text-primary hover:text-white transition-colors font-bold mt-1 inline-block">Return to Sign In</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
