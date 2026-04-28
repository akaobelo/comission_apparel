@extends('layouts.app')

@section('title', 'Apply for Access | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-screen flex items-center justify-center pt-24 pb-12 overflow-hidden">
    <div class="absolute inset-0 bg-slate-50"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-5 mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-100 via-slate-50/80 to-transparent"></div>
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-primary/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-2xl px-6">
        <div class="glass-panel p-8 md:p-10 shadow-xl border border-slate-200 bg-white/90 backdrop-blur-xl animate-slide-up">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto rounded-xl bg-secondary flex items-center justify-center shadow-md mb-4">
                    <img src="/images/LR.png" alt="LR Logo" class="w-12 h-12 object-contain">
                </div>
                <h1 class="text-2xl font-black uppercase tracking-tight text-slate-900">Coach Registration</h1>
                <p class="text-slate-600 text-sm mt-1">Create your coach account to get started. <br> <span class="text-secondary font-bold">Your account will be active immediately.</span></p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="space-y-6" enctype="multipart/form-data" x-data="{ showPass: false, showConfirmPass: false }">
                @csrf

                {{-- Row 1: First Name + Last Name --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. Jordan">
                        @error('first_name')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. Smith">
                        @error('last_name')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Row 1.5: Organization --}}
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Organization / School Name</label>
                        <input type="text" name="organization" value="{{ old('organization') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. Springfield High Athletics">
                        @error('organization')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Row 2: Email + Confirm Email --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="coach@example.com">
                        @error('email')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Confirm Email Address</label>
                        <input type="email" name="email_confirmation" value="{{ old('email_confirmation') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="Re-enter email address">
                        @error('email_confirmation')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Row 3: Phone + Sport Dropdown --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="(555) 123-4567">
                        @error('phone')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Sport / Activity</label>
                        <div class="relative">
                            <select name="sport" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none appearance-none shadow-sm">
                                <option value="" disabled {{ old('sport') ? '' : 'selected' }}>Select a sport...</option>
                                @php
                                $sports = [
                                    'Football (Tackle)', 'Flag Football', 'Basketball', 'Baseball', 'Softball',
                                    'Soccer', 'Track & Field', 'Cross Country', 'Lacrosse', 'Volleyball',
                                    'Wrestling', 'Swimming', 'Tennis', 'Golf', 'Hockey', 'Cheerleading',
                                    'Dance / Drill Team', 'Marching Band', 'Rugby', 'Bowling', 'Other'
                                ];
                                @endphp
                                @foreach($sports as $sport)
                                    <option value="{{ $sport }}" {{ old('sport') === $sport ? 'selected' : '' }}>{{ $sport }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('sport')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Row 4: Password + Confirm Password (with show/hide) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Password</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 pr-12 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="Min. 8 characters">
                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors p-1" tabindex="-1">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Confirm Password</label>
                        <div class="relative">
                            <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 pr-12 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="Re-enter password">
                            <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors p-1" tabindex="-1">
                                <svg x-show="!showConfirmPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showConfirmPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Logo Upload --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Upload Team Logo (Optional)</label>
                    <input type="file" name="logo" accept="image/*" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-slate-600 focus:border-primary focus:outline-none transition-all shadow-sm file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:uppercase file:bg-secondary/10 file:text-secondary hover:file:bg-secondary/20 cursor-pointer">
                    @error('logo')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                </div>

                {{-- Disclaimer --}}
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 flex gap-3 items-start">
                    <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        By registering, you agree to The Commission Apparel's terms of service. Your account will be active immediately. Team stores still require admin approval after your design process is complete.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 mt-2 text-sm uppercase tracking-widest font-bold shadow-[0_4px_20px_rgba(192,30,46,0.3)]">
                    Create Coach Account
                </button>
            </form>

            <div class="mt-8 text-center border-t border-slate-200 pt-6">
                <p class="text-slate-600 text-sm">Already have an account? <a href="{{ route('login') }}" class="text-secondary hover:text-[#a11825] transition-colors font-bold">Sign In</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
