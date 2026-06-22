@extends('layouts.app')

@section('title', 'Coach Registration | The Commission Apparel')

@section('content')
<div class="relative w-full min-h-screen flex items-center justify-center pt-28 pb-12 overflow-hidden bg-slate-100">
    <!-- Background Elements -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=2500')] bg-cover bg-center opacity-5 mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-100 via-slate-50/80 to-transparent"></div>

    <div class="relative z-10 w-full max-w-3xl px-6">
        <div class="bg-white rounded-xl shadow-xl overflow-hidden animate-slide-up border border-slate-200 p-10">
            <div class="text-center mb-10">
                <div class="w-16 h-16 mx-auto rounded-xl bg-secondary flex items-center justify-center shadow-md mb-6">
                    <img src="/images/LR.png" alt="LR Logo" class="w-12 h-12 object-contain">
                </div>
                <h1 class="text-[28px] font-black uppercase tracking-tight text-slate-900 mb-2">User Registration</h1>
                <p class="text-slate-600 text-sm font-medium">Create your account to get started.</p>
                <p class="text-secondary text-sm font-black mt-1 tracking-wide">Your account will be active immediately.</p>
            </div>

            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="e.g. Jordan" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                        @error('first_name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="e.g. Smith" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                        @error('last_name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Organization / School Name</label>
                    <input type="text" name="organization" value="{{ old('organization') }}" placeholder="e.g. Springfield High Athletics" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                    @error('organization') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="coach@example.com" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                        @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Confirm Email Address</label>
                        <input type="email" name="email_confirmation" value="{{ old('email_confirmation') }}" placeholder="Re-enter email address" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                        @error('email_confirmation') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="(555) 123-4567" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                        @error('phone') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Sport / Activity</label>
                        <select name="sport" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all">
                            <option value="">Select a sport...</option>
                            @foreach($sports as $sportOption)
                                <option value="{{ $sportOption }}" {{ old('sport') == $sportOption ? 'selected' : '' }}>{{ $sportOption }}</option>
                            @endforeach
                        </select>
                        @error('sport') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Commission Apparel Rep</label>
                    <p class="text-xs text-slate-500 mb-2">(If you are working with a representative, please enter their name below)</p>
                    <input type="text" name="sales_rep" value="{{ old('sales_rep') }}" placeholder="e.g. John Doe" class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                    @error('sales_rep') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6" x-data="{ showPass: false }">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Password</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" placeholder="Min. 8 characters" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 pr-12 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-1" tabindex="-1">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Confirm Password</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password_confirmation" placeholder="Re-enter password" required class="w-full bg-white border border-slate-300 rounded text-sm px-4 py-3 pr-12 text-slate-900 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 focus:outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-700 mb-2">Upload Team Logo (Optional)</label>
                    <input type="file" name="logo" accept="image/*" class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-slate-200 file:text-secondary hover:file:bg-slate-300 border border-slate-300 rounded bg-white pt-2 pb-2 pl-2">
                    @error('logo') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5 flex gap-4 mt-8">
                    <svg class="w-6 h-6 text-[#1e40af] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs md:text-sm text-slate-600 font-medium leading-relaxed">
                        By registering, you agree to The Commission Apparel's terms of service. Your account will be active immediately. Team stores still require admin approval after your design process is complete.
                    </p>
                </div>

                <button type="submit" class="w-full py-4 mt-6 bg-[#991b1b] hover:bg-[#7f1d1d] text-white text-[15px] uppercase tracking-widest font-black rounded shadow-md transition-colors">
                    Create My Account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
