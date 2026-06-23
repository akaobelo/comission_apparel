@extends('layouts.app')
@section('title', 'Admin Credentials Settings | Admin')
@section('content')
<div class="max-w-2xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
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
            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
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
                            <input type="password" name="new_password" minlength="8" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            @error('new_password')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" minlength="8" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">Confirm Current Password to Save *</label>
                    <input type="password" name="current_password" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
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
