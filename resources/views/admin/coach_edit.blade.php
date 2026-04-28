@extends('layouts.app')
@section('title', 'Edit Coach | Admin')
@section('content')
<div class="max-w-5xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Editing: {{ $user->name }}</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-8">
        {{-- Left: Edit Profile --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Coach Profile</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.coach.update', $user) }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                @error('first_name')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                @error('last_name')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                @error('email')<p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Organization</label>
                                <input type="text" name="organization" value="{{ old('organization', $user->organization) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Phone</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Sport / Activity</label>
                                <select name="sport" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    @foreach(['Football (Tackle)', 'Flag Football', 'Basketball', 'Baseball', 'Softball', 'Soccer', 'Track & Field', 'Cross Country', 'Lacrosse', 'Volleyball', 'Wrestling', 'Swimming', 'Tennis', 'Golf', 'Hockey', 'Cheerleading', 'Dance / Drill Team', 'Marching Band', 'Rugby', 'Bowling', 'Other'] as $sport)
                                        <option value="{{ $sport }}" {{ old('sport', $user->sport) === $sport ? 'selected' : '' }}>{{ $sport }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Account Status</label>
                                <select name="status" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="declined" {{ old('status', $user->status) === 'declined' ? 'selected' : '' }}>Declined</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary py-3 px-8 text-sm uppercase tracking-wider font-bold">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Assigned Designs --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Assigned Designs</h2>
                    <p class="text-xs text-slate-500 mt-1">Only these designs will appear in the coach's store builder.</p>
                </div>
                <div class="p-5">
                    {{-- Assign new design --}}
                    @if($designCatalog->count() > $assignedDesignIds ? count($assignedDesignIds) : 0)
                    <form action="{{ route('admin.coach.assign-design', $user) }}" method="POST" class="flex gap-2 mb-5">
                        @csrf
                        <div class="flex-1">
                            <select name="design_catalog_id" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                <option value="">Select a design to assign...</option>
                                @foreach($designCatalog as $design)
                                    @if(!in_array($design->id, $assignedDesignIds))
                                        <option value="{{ $design->id }}">{{ $design->name }} ({{ $design->type_label }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2.5 bg-secondary text-white text-sm font-bold rounded-lg hover:bg-[#a11825] transition-colors flex-shrink-0">Assign</button>
                    </form>
                    @endif

                    {{-- Assigned designs list --}}
                    @if(empty($assignedDesignIds))
                        <p class="text-sm text-slate-400 text-center py-6">No designs assigned to this coach yet.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($designCatalog->whereIn('id', $assignedDesignIds) as $design)
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                <div>
                                    <div class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-primary">{{ $design->type_label }}</div>
                                </div>
                                <form action="{{ route('admin.coach.remove-design', [$user, $design]) }}" method="POST" onsubmit="return confirm('Remove this design from the coach?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Coach's Team Store Info --}}
            @if($user->teamStore)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Team Store</h2>
                </div>
                <div class="p-5">
                    <div class="font-bold text-slate-900">{{ $user->teamStore->name }}</div>
                    <div class="text-xs text-slate-500 mt-1">Status: <span class="font-bold uppercase text-primary">{{ $user->teamStore->status }}</span></div>
                    <div class="mt-3">
                        <a href="{{ route('admin.store.edit', $user->teamStore) }}" class="btn btn-outline py-2 px-4 text-xs uppercase tracking-wide">Manage Store →</a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
