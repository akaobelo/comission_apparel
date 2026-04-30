@extends('layouts.app')

@section('title', 'Request a Quote | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-[280px] flex flex-col justify-end mt-16 md:mt-0">
    <div class="absolute inset-0 bg-slate-900 border-b-2 border-primary">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-20 mix-blend-screen"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-transparent"></div>
    </div>
    
    <div class="relative z-10 max-w-5xl mx-auto px-6 w-full pb-8 pt-32 md:pt-40 text-center animate-slide-up">
        <div class="inline-block px-3 py-1 bg-primary/20 border border-primary/30 text-primary text-xs font-bold uppercase tracking-widest rounded-full mb-4">
            Custom Design Intake
        </div>
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-white drop-shadow-lg leading-none mb-3">
            Build Your <span class="text-secondary">Armor</span>
        </h1>
        <p class="text-slate-300 text-sm md:text-base max-w-2xl mx-auto">
            Ready for your 100% customized package? Tell us about your organization below, and our elite design team will deliver a comprehensive proposal and mockup within 48 hours.
        </p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-6 py-10">
    <div class="glass-panel p-6 md:p-8 relative overflow-hidden bg-white border border-slate-200 shadow-xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-[80px]"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-secondary/10 rounded-full blur-[80px]"></div>

        <form action="{{ route('quote.store') }}" method="POST" class="relative z-10 space-y-6">
            @csrf

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            @error('form')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                    {{ $message }}
                </div>
            @enderror
            
            <!-- Section 1 -->
            <div>
                <h3 class="text-lg font-black uppercase text-slate-900 mb-4 border-b border-slate-200 pb-1">1. Point of Contact</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. Coach" required>
                        @error('first_name')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. Jackson" required>
                        @error('last_name')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Position/Title</label>
                        <input type="text" name="position_title" value="{{ old('position_title') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. Head Coach, Athletic Director" required>
                        @error('position_title')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="coach@example.com" required>
                        @error('email')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Confirm Email Address</label>
                        <input type="email" name="email_confirmation" value="{{ old('email_confirmation') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="coach@example.com" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="(555) 123-4567" required>
                        @error('phone')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 -->
            <div>
                <h3 class="text-lg font-black uppercase text-slate-900 mb-4 border-b border-slate-200 pb-1">2. Organization Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Organization / Team Name</label>
                        <input type="text" name="organization_name" value="{{ old('organization_name') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. Metro High School Eagles" required>
                        @error('organization_name')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Apparel Category</label>
                        <div class="relative">
                            <select name="apparel_category" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none appearance-none shadow-sm">
                                <option value="" disabled {{ old('apparel_category') ? '' : 'selected' }}>Make Your Selection...</option>
                                @foreach($sports as $sport)
                                    <option value="{{ $sport }}" {{ old('apparel_category') === $sport ? 'selected' : '' }}>{{ $sport }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('apparel_category')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Estimated Quantity Needed</label>
                        <input type="number" name="estimated_quantity" value="{{ old('estimated_quantity') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 shadow-sm" placeholder="e.g. 45" min="1" required>
                        @error('estimated_quantity')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 -->
            <div>
                <h3 class="text-lg font-black uppercase text-slate-900 mb-4 border-b border-slate-200 pb-1">3. Design & Scope</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Package Type Wanted</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="cursor-pointer relative">
                                <input type="radio" name="package_type" value="base_uniforms" class="peer sr-only" {{ old('package_type') == 'base_uniforms' ? 'checked' : '' }} required>
                                <div class="h-full border border-slate-200 rounded-lg p-4 text-center hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-[0_0_15px_rgba(26,86,204,0.1)] transition-all">
                                    <span class="block text-slate-900 peer-checked:text-primary font-black uppercase mb-1">Base Uniforms</span>
                                    <span class="text-xs text-slate-500 peer-checked:text-primary/80">Jerseys & Shorts only</span>
                                </div>
                            </label>
                            
                            <label class="cursor-pointer relative">
                                <input type="radio" name="package_type" value="full_program_bundle" class="peer sr-only" {{ old('package_type', 'full_program_bundle') == 'full_program_bundle' ? 'checked' : '' }} required>
                                <div class="h-full border border-slate-200 rounded-lg p-4 text-center hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-[0_0_15px_rgba(26,86,204,0.1)] transition-all">
                                    <span class="block text-slate-900 peer-checked:text-primary font-black uppercase mb-1">Full Program Bundle</span>
                                    <span class="text-xs text-slate-500 peer-checked:text-primary/80">Uniforms + Warm-ups + Bags</span>
                                </div>
                            </label>

                            <label class="cursor-pointer relative">
                                <input type="radio" name="package_type" value="merch_only" class="peer sr-only" {{ old('package_type') == 'merch_only' ? 'checked' : '' }} required>
                                <div class="h-full border border-slate-200 rounded-lg p-4 text-center hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-[0_0_15px_rgba(26,86,204,0.1)] transition-all">
                                    <span class="block text-slate-900 peer-checked:text-primary font-black uppercase mb-1">Merch Only</span>
                                    <span class="text-xs text-slate-500 peer-checked:text-primary/80">Fan gear, hoodies, tees</span>
                                </div>
                            </label>
                        </div>
                        @error('package_type')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Target Delivery Date (If Known)</label>
                        <input type="date" name="target_delivery_date" value="{{ old('target_delivery_date') }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 block shadow-sm">
                        @error('target_delivery_date')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Design Vision / Existing Inspiration</label>
                        <textarea rows="3" name="design_vision" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-all placeholder:text-slate-400 leading-relaxed shadow-sm" placeholder="Tell us about your team colors, mascots, current vibe, or any pro/college teams whose style you want to emulate...">{{ old('design_vision') }}</textarea>
                        @error('design_vision')<p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-200 text-center">
                <button type="submit" class="btn btn-primary px-8 py-3 text-base w-full max-w-sm uppercase tracking-widest shadow-[0_4px_20px_rgba(26,86,204,0.3)]">
                    Submit Quote Request
                </button>
                <p class="text-slate-500 text-xs mt-3"><svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg> Secure SSL connection. We never share your data.</p>
            </div>
        </form>
    </div>
</div>
@endsection
