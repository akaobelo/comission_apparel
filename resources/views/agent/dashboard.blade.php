@extends('layouts.app')

@section('title', 'Sales Agent Portal | The Commission Apparel')

@section('content')
<div class="max-w-[1600px] mx-auto px-6 py-8 mt-16" x-data="{ activeTab: 'performance' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-purple-100 border border-purple-200 text-purple-700 text-xs font-bold uppercase tracking-widest rounded-full mb-3">
                <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                Official Agent
            </div>
            <h1 class="text-4xl lg:text-5xl font-black uppercase tracking-tight text-slate-900">Agent <span class="text-secondary">Portal</span></h1>
            <p class="text-slate-600 text-lg mt-2">Welcome back, Marcus. Here is your global performance breakdown.</p>
        </div>

        <div class="glass-panel py-3 px-6 border border-purple-200 bg-white flex items-center gap-4 shadow-sm">
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest">Your Referral Link</p>
                <code class="text-primary font-mono select-all font-bold">commissionapparel.com/ref/marcus23</code>
            </div>
            <button class="w-10 h-10 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-primary hover:text-white transition-colors border border-slate-200" title="Copy Link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Stat 1 -->
        <div class="glass-panel p-6 bg-white border border-slate-200 border-l-4 border-l-primary shadow-sm">
            <h4 class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Active Clients</h4>
            <div class="flex items-end gap-3">
                <div class="text-4xl font-black text-slate-900">12</div>
                <div class="text-xs text-green-600 font-bold mb-2 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> +2 This Mth</div>
            </div>
        </div>

        <!-- Stat 2 -->
        <div class="glass-panel p-6 bg-white border border-slate-200 border-l-4 border-l-purple-500 shadow-sm">
            <h4 class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Total Orders Placed</h4>
            <div class="flex items-end gap-3">
                <div class="text-4xl font-black text-slate-900">342</div>
                <div class="text-xs text-slate-500 font-bold mb-2">Uniform Sets</div>
            </div>
        </div>

        <!-- Stat 3 -->
        <div class="glass-panel p-6 bg-white border border-slate-200 border-l-4 border-l-secondary shadow-sm">
            <h4 class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Total Commission Earned</h4>
            <div class="flex items-end gap-3">
                <div class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-emerald-600">$8,450</div>
            </div>
        </div>

        <!-- Stat 4 -->
        <div class="glass-panel p-6 bg-white border border-slate-200 border-l-4 border-l-orange-500 shadow-sm">
            <h4 class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Pending Payout</h4>
            <div class="flex items-end gap-3">
                <div class="text-4xl font-black text-slate-900">$1,200</div>
                <button class="text-xs bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors px-3 py-1 rounded font-bold uppercase tracking-wider ml-auto mb-1">Claim</button>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_350px] gap-8">
        
        <div class="glass-panel p-0 overflow-hidden bg-white shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200 bg-slate-50/80 flex justify-between items-center">
                <h3 class="text-xl font-bold uppercase text-slate-900">Client Directory</h3>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Search schools..." class="bg-white border border-slate-300 rounded-lg pl-9 pr-4 py-2 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none w-64 shadow-sm">
                </div>
            </div>
            
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="p-4 font-bold">Organization</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold">Orders</th>
                        <th class="p-4 font-bold">Revenue</th>
                        <th class="p-4 font-bold">Your Cut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-900 uppercase">Evans Middle Track</div>
                            <div class="text-xs text-slate-500 tracking-wider">Coach Jordan Smith</div>
                        </td>
                        <td class="p-4">
                            <span class="bg-green-100 text-green-700 border border-green-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-widest">Active Store</span>
                        </td>
                        <td class="p-4 text-slate-600">24 Pending</td>
                        <td class="p-4 font-mono font-medium">$4,200</td>
                        <td class="p-4 font-mono font-bold text-green-600">+$630</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-900 uppercase">Valley Lacrosse Club</div>
                            <div class="text-xs text-slate-500 tracking-wider">Coach Sarah Jenkins</div>
                        </td>
                        <td class="p-4">
                            <span class="bg-blue-100 text-blue-700 border border-blue-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-widest">In Production</span>
                        </td>
                        <td class="p-4 text-slate-600">42 Shipped</td>
                        <td class="p-4 font-mono font-medium">$6,800</td>
                        <td class="p-4 font-mono font-bold text-green-600">+$1,020</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-900 uppercase">Metro City Hoops</div>
                            <div class="text-xs text-slate-500 tracking-wider">Coach Dre Harris</div>
                        </td>
                        <td class="p-4">
                            <span class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-widest">Completed</span>
                        </td>
                        <td class="p-4 text-slate-600">18 Delivered</td>
                        <td class="p-4 font-mono font-medium">$1,800</td>
                        <td class="p-4 font-mono font-bold text-slate-500">Paid ($270)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Leaderboard -->
        <div class="glass-panel p-6 h-max bg-white shadow-sm border border-slate-200">
            <h3 class="text-xl font-bold uppercase border-b border-slate-200 pb-4 mb-6 flex items-center justify-between text-slate-900">
                Global Rankings
                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center gap-4 p-3 bg-gradient-to-r from-yellow-50 to-white rounded-xl border border-yellow-200">
                    <div class="w-8 h-8 rounded bg-yellow-400 text-slate-900 font-bold flex items-center justify-center">1</div>
                    <div class="flex-1">
                        <div class="font-bold text-slate-900 uppercase text-sm">A. Walker (USA)</div>
                        <div class="text-xs text-yellow-600 font-mono tracking-widest">$42K REVENUE</div>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-8 h-8 rounded bg-slate-200 text-slate-600 font-bold flex items-center justify-center">2</div>
                    <div class="flex-1">
                        <div class="font-bold text-slate-900 uppercase text-sm">D. Williams (CAN)</div>
                        <div class="text-xs text-slate-500 font-mono tracking-widest">$38K REVENUE</div>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-8 h-8 rounded bg-slate-200 text-slate-600 font-bold flex items-center justify-center">3</div>
                    <div class="flex-1">
                        <div class="font-bold text-slate-900 uppercase text-sm">M. Johnson (BAH)</div>
                        <div class="text-xs text-slate-500 font-mono tracking-widest">$34K REVENUE</div>
                    </div>
                </div>
                
                <div class="my-2 border-t border-dashed border-slate-200"></div>

                <div class="flex items-center gap-4 p-3 border-2 border-primary rounded-xl bg-primary/5">
                    <div class="w-8 h-8 rounded bg-primary text-white font-bold flex items-center justify-center">8</div>
                    <div class="flex-1">
                        <div class="font-bold text-slate-900 uppercase text-sm">You (M. Smith)</div>
                        <div class="text-xs text-primary font-mono tracking-widest font-bold">$18.4K REVENUE</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
