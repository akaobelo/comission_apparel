@extends('layouts.app')

@section('title', 'Admin Dashboard | The Commission Apparel')

@section('content')
<div class="max-w-[1600px] mx-auto px-6 py-8 mt-16">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-red-500/20 border border-red-500/30 text-red-500 text-xs font-bold uppercase tracking-widest rounded-full mb-3">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                System Administrator
            </div>
            <h1 class="text-4xl lg:text-5xl font-black uppercase tracking-tight text-white drop-shadow-sm">System <span class="text-secondary">Control</span></h1>
            <p class="text-slate-400 text-lg mt-2">Manage the platform, review applications, and oversee global operations.</p>
        </div>

        <div class="flex gap-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline py-2 px-4 shadow-none">Secure Logout</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 font-medium flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-8 p-4 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 font-medium flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Pending Approvals -->
        <div class="lg:col-span-2 space-y-8">
            <section class="glass-panel p-0 overflow-hidden border-yellow-500/30 shadow-[0_5px_30px_rgba(234,179,8,0.05)]">
                <div class="p-6 border-b border-slate-700 bg-slate-800/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold uppercase flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-yellow-500/20 text-yellow-500 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </span>
                            Pending Coach Applications
                        </h3>
                    </div>
                    <span class="px-3 py-1 bg-yellow-500/20 text-yellow-500 rounded-full text-xs font-bold">{{ $pendingCoaches->count() }} Waiting</span>
                </div>
                
                <div class="overflow-x-auto">
                    @if($pendingCoaches->isEmpty())
                        <div class="p-8 text-center text-slate-500">
                            No pending applications right now.
                        </div>
                    @else
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900/80 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700">
                                    <th class="p-4 font-bold">Applicant / Organization</th>
                                    <th class="p-4 font-bold">Registered</th>
                                    <th class="p-4 font-bold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @foreach($pendingCoaches as $coach)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-white uppercase">{{ $coach->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $coach->organization ?? 'Organization Not Provided' }}</div>
                                        <div class="text-[10px] text-primary font-mono mt-1">{{ $coach->email }}</div>
                                    </td>
                                    <td class="p-4 text-slate-400">
                                        {{ $coach->created_at->diffForHumans() }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('admin.users.approve', $coach) }}" method="POST">
                                                @csrf
                                                <button class="px-4 py-2 bg-green-500/10 hover:bg-green-500 hover:text-white border border-green-500/50 text-green-400 rounded text-xs font-bold uppercase transition-colors">Approve</button>
                                            </form>
                                            <form action="{{ route('admin.users.decline', $coach) }}" method="POST">
                                                @csrf
                                                <button class="px-4 py-2 bg-red-500/10 hover:bg-red-500 hover:text-white border border-red-500/50 text-red-400 rounded text-xs font-bold uppercase transition-colors" onclick="return confirm('Are you sure you want to decline this application?')">Decline</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <!-- Pending Team Stores -->
            <section class="glass-panel p-0 overflow-hidden border-orange-500/30">
                <div class="p-6 border-b border-slate-700 bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-xl font-bold uppercase flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-500 flex items-center justify-center">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </span>
                        Pending Team Stores
                    </h3>
                    @if(isset($pendingStores) && $pendingStores->count() > 0)
                        <span class="px-3 py-1 bg-orange-500/20 text-orange-500 rounded-full text-xs font-bold">{{ $pendingStores->count() }} Waiting</span>
                    @endif
                </div>
                
                <div class="overflow-x-auto">
                    @if(empty($pendingStores) || $pendingStores->isEmpty())
                        <div class="p-8 text-center text-slate-500">
                            No team stores awaiting approval.
                        </div>
                    @else
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900/80 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700">
                                    <th class="p-4 font-bold">Store Request</th>
                                    <th class="p-4 font-bold">Coach Contact</th>
                                    <th class="p-4 font-bold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @foreach($pendingStores as $store)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-white uppercase">{{ $store->name }}</div>
                                        <div class="text-[10px] text-orange-400 font-mono mt-1">Requested: {{ $store->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-white">{{ $store->user->name ?? 'Unknown' }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $store->user->email ?? '' }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('admin.stores.approve', $store) }}" method="POST">
                                                @csrf
                                                <button class="px-4 py-2 bg-green-500/10 hover:bg-green-500 hover:text-white border border-green-500/50 text-green-400 rounded text-xs font-bold uppercase transition-colors">Activate</button>
                                            </form>
                                            <form action="{{ route('admin.stores.decline', $store) }}" method="POST">
                                                @csrf
                                                <button class="px-4 py-2 bg-red-500/10 hover:bg-red-500 hover:text-white border border-red-500/50 text-red-400 rounded text-xs font-bold uppercase transition-colors" onclick="return confirm('Decline this store?')">Decline</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <!-- Finalized Master Orders -->
            <section class="glass-panel p-0 overflow-hidden border-secondary/30">
                <div class="p-6 border-b border-slate-700 bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-xl font-bold uppercase flex items-center gap-3 text-secondary">
                        <span class="w-8 h-8 rounded-lg bg-secondary/20 flex items-center justify-center">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </span>
                        Finalized Master Orders (Production)
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    @if(empty($finalizedStores) || $finalizedStores->isEmpty())
                        <div class="p-8 text-center text-slate-500">
                            No finalized master orders from coaches yet.
                        </div>
                    @else
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900/80 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700">
                                    <th class="p-4 font-bold">Store Context</th>
                                    <th class="p-4 font-bold">Volume</th>
                                    <th class="p-4 font-bold text-center">Export</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @foreach($finalizedStores as $store)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-white uppercase">{{ $store->name }}</div>
                                        <div class="text-[10px] text-slate-500">Finalized: {{ $store->updated_at->format('M d, g:i A') }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-white">{{ $store->parentOrders->count() }} Orders</div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="{{ route('admin.stores.export', $store) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-white rounded font-bold uppercase text-[10px] tracking-widest hover:bg-secondary/80 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            Download CSV
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="glass-panel p-0 overflow-hidden border-primary/30">
                <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                    <h3 class="text-xl font-bold uppercase flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        Active Coaches
                    </h3>
                </div>
                
                <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                    @if($approvedCoaches->isEmpty())
                        <div class="p-8 text-center text-slate-500">
                            No approved coaches yet.
                        </div>
                    @else
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900/80 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700">
                                    <th class="p-4 font-bold">Coach Name</th>
                                    <th class="p-4 font-bold">Organization</th>
                                    <th class="p-4 font-bold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @foreach($approvedCoaches as $coach)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="p-4 font-bold text-white">{{ $coach->name }}</td>
                                    <td class="p-4 text-slate-400">{{ $coach->organization ?? 'N/A' }}</td>
                                    <td class="p-4 text-center">
                                        <span class="bg-green-500/10 text-green-400 px-2 py-1 rounded text-[10px] uppercase tracking-widest border border-green-500/20">Active</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
        </div>

        <!-- Right Column: System Stats -->
        <aside class="space-y-6">
            <div class="glass-panel p-6 bg-gradient-to-br from-slate-900 to-slate-800">
                <h4 class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total System Users</h4>
                <div class="text-4xl font-black text-white">{{ \App\Models\User::count() }}</div>
            </div>

            <div class="glass-panel p-6">
                <h4 class="text-sm font-bold uppercase border-b border-slate-700 pb-2 mb-4">Application History</h4>
                
                @if($declinedCoaches->isEmpty())
                    <p class="text-sm text-slate-500 italic">No declined applications.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($declinedCoaches as $coach)
                        <li class="flex justify-between items-center p-2 rounded bg-slate-900">
                            <div>
                                <p class="text-xs text-white font-bold">{{ $coach->name }}</p>
                                <p class="text-[10px] text-slate-500 uppercase">{{ $coach->organization }}</p>
                            </div>
                            <span class="text-[10px] text-red-500 font-bold uppercase tracking-widest px-2 bg-red-500/10 rounded">Declined</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            
            <div class="glass-panel bg-primary/5 border-primary/20 p-6 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-primary/20 text-primary flex items-center justify-center mb-4">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                </div>
                <h4 class="text-white font-bold uppercase text-sm mb-2">Server Status</h4>
                <p class="text-xs text-slate-400 mb-4">Database: Connected. All systems operational.</p>
                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-green-500 h-1.5 w-full"></div>
                </div>
            </div>
        </aside>

    </div>
</div>
@endsection
