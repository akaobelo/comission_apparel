<?php

$file = 'resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Add the Campaign Stores tab button
$searchBtn = <<<'EOT'
            <button @click="setTab('testimonials')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'testimonials' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Testimonials</button>
            <button @click="setTab('security')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'security' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Security Logs</button>
        </div>
EOT;

$replaceBtn = <<<'EOT'
            <button @click="setTab('testimonials')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'testimonials' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Testimonials</button>
            <button @click="setTab('campaigns')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'campaigns' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Campaign Stores</button>
            <button @click="setTab('security')" class="pb-4 text-sm font-black uppercase tracking-wider whitespace-nowrap transition-all border-b-2 relative top-[1px]" :class="activeAdminTab === 'security' ? 'border-secondary text-secondary' : 'border-transparent text-slate-500 hover:text-slate-900'">Security Logs</button>
        </div>
EOT;

$content = str_replace($searchBtn, $replaceBtn, $content);

// 2. Add the Campaign Stores tab content at the end of the tabs wrapper
$searchEnd = <<<'EOT'
        </div>

    </div>
</div>
@endsection
EOT;

$replaceEnd = <<<'EOT'
        </div>

        {{-- ═══ CAMPAIGN STORES TAB ═══ --}}
        <div x-show="activeAdminTab === 'campaigns'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 flex items-center gap-2">
                                Campaign Stores
                                <span class="ml-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-[10px] font-black">{{ $campaignStores->count() }}</span>
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">Manage public storefronts owned by The Commission Apparel.</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.stores.create') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Campaign Name</label>
                                <input type="text" name="name" required placeholder="e.g. Bahamas Independence" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Description</label>
                                <textarea name="description" rows="2" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-primary outline-none text-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Order Deadline (Optional)</label>
                                <input type="datetime-local" name="order_deadline" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-900 focus:border-primary outline-none text-sm">
                            </div>
                            <button type="submit" class="btn btn-primary w-full py-3 uppercase tracking-widest text-xs font-bold shadow-md">Create Campaign Store</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="space-y-8">
                @forelse($campaignStores as $store)
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-start bg-slate-50">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-black text-slate-900 uppercase text-lg leading-none">{{ $store->name }}</h3>
                                <span class="bg-green-100 text-green-800 border border-green-200 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">Active</span>
                            </div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">{{ $store->items->count() }} Items Assigned</p>
                        </div>
                        <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wide rounded-lg shadow-sm transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Manage Store
                        </a>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div class="mb-5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Public Link</div>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ url('/store/' . $store->slug) }}" class="flex-1 bg-slate-50 border border-slate-200 rounded text-xs px-3 py-2 font-mono text-slate-600 outline-none">
                                <button type="button" onclick="navigator.clipboard.writeText('{{ url('/store/' . $store->slug) }}'); alert('Link copied!')" class="p-2 text-slate-500 hover:text-primary transition-colors border border-slate-200 rounded hover:border-primary hover:bg-primary/5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </button>
                                <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="p-2 text-slate-500 hover:text-primary transition-colors border border-slate-200 rounded hover:border-primary hover:bg-primary/5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-sm border-2 border-dashed border-slate-200 rounded-xl">
                    No campaign stores have been created yet.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
EOT;

$content = str_replace($searchEnd, $replaceEnd, $content);

file_put_contents($file, $content);
echo "Admin dashboard updated.\n";
