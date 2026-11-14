@extends('layouts.app')
@section('title', 'Admin Credentials Settings | Admin')
@section('content')
<div class="max-w-2xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all shadow-sm hover:shadow cursor-pointer">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Admin Control Center</span>
        </a>
    </div>

    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Admin Credentials Settings</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Update Credentials</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6" x-data="{ showNewPass: false, showConfirmPass: false, showCurrentPass: false }">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    @error('email')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 mb-4">Change Password (Optional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">New Password</label>
                            <div class="relative">
                                <input :type="showNewPass ? 'text' : 'password'" type="password" name="new_password" minlength="8" placeholder="Minimum 8 characters" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 pr-12 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors p-1" tabindex="-1" title="Toggle password visibility">
                                    <svg x-show="!showNewPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showNewPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            @error('new_password')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Confirm New Password</label>
                            <div class="relative">
                                <input :type="showConfirmPass ? 'text' : 'password'" type="password" name="new_password_confirmation" minlength="8" placeholder="Re-enter new password" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 pr-12 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors p-1" tabindex="-1" title="Toggle password visibility">
                                    <svg x-show="!showConfirmPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showConfirmPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">Confirm Current Password to Save *</label>
                    <div class="relative">
                        <input :type="showCurrentPass ? 'text' : 'password'" type="password" name="current_password" required placeholder="Enter your current password" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 pr-12 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                        <button type="button" @click="showCurrentPass = !showCurrentPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors p-1" tabindex="-1" title="Toggle password visibility">
                            <svg x-show="!showCurrentPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showCurrentPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('current_password')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="w-full md:w-auto btn btn-primary py-3 px-8 text-sm uppercase tracking-wider font-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
