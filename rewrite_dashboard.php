<?php

$file = 'resources/views/coach/dashboard.blade.php';
$content = file_get_contents($file);

// Replace the start of the if-else block
$oldStart = <<<'EOT'
    {{-- ════ NO STORE YET ════ --}}
    @if(!$store)
EOT;

$newStart = <<<'EOT'
    @php
        $totalAthletes = $store ? $store->parentOrders->count() : 0;
        $isLocked = $store && $store->status === 'submitted_to_admin';
    @endphp

    <div x-data="{ activeCoachTab: '{{ !$store ? 'create_order' : 'overview' }}' }" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-2 inline-flex gap-2">
            <button
                type="button"
                @click="activeCoachTab = 'create_order'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'create_order' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Create An Order
            </button>
            <button
                type="button"
                @click="activeCoachTab = 'overview'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'overview' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Store Overview
            </button>
            @if($store && $store->status === 'approved')
            <button
                type="button"
                @click="activeCoachTab = 'sales'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'sales' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Sales
            </button>
            @endif
        </div>

        {{-- ════ CREATE AN ORDER TAB ════ --}}
        <div x-show="activeCoachTab === 'create_order'" x-cloak class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-8">
            <div class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-lg border border-slate-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Direct Order Builder</h2>
                            <p class="text-sm text-slate-600 mt-1">Place direct orders for your organization without requiring a public team store.</p>
                        </div>
                    </div>
                    
                    <div class="p-6" x-data="{ orderMode: 'person' }">
                        <div class="flex gap-4 mb-6">
                            <button type="button" @click="orderMode = 'person'" class="flex-1 py-3 px-4 rounded-xl border-2 transition-all font-bold text-sm uppercase tracking-wider text-center" :class="orderMode === 'person' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-500 hover:border-slate-300'">
                                Order by Person(s)
                            </button>
                            <button type="button" @click="orderMode = 'item'" class="flex-1 py-3 px-4 rounded-xl border-2 transition-all font-bold text-sm uppercase tracking-wider text-center" :class="orderMode === 'item' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-500 hover:border-slate-300'">
                                Order by Item(s)
                            </button>
                        </div>

                        <form action="{{ route('coach.direct-order.submit') }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_type" x-model="orderMode">
                            
                            {{-- By Person Fields --}}
                            <div x-show="orderMode === 'person'" class="space-y-4 mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Athlete Details</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">First Name</label>
                                        <input type="text" name="athlete_first_name" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Last Name</label>
                                        <input type="text" name="athlete_last_name" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Gender</label>
                                        <select name="gender" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Unisex">Unisex</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jersey # (Opt)</label>
                                        <input type="text" name="jersey_number" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jersey Name (Opt)</label>
                                        <input type="text" name="jersey_name" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                </div>
                            </div>

                            {{-- By Item Fields (Bulk) --}}
                            <div x-show="orderMode === 'item'" class="space-y-4 mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Bulk Order Details</h3>
                                <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm p-4 rounded-lg">
                                    <p class="font-bold mb-1">Bulk Order Mode</p>
                                    <p>Select the items below and enter the desired quantities and sizes. This will be added to your draft as a bulk entry.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Gender</label>
                                    <select name="gender" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                        <option value="Unisex">Unisex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>

                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 px-2">Select Assigned Items</h3>
                            <div class="space-y-3">
                                @forelse($assignedDesigns as $design)
                                    @php
                                        $sizedTypes = \App\Models\DesignCatalog::sizedTypes();
                                        $types = $design->types ?? [];
                                        $hasSizes = count(array_intersect($types, $sizedTypes)) > 0;
                                    @endphp
                                    <div x-data="{ selected: false }" class="border border-slate-200 rounded-xl p-4 transition-colors" :class="selected ? 'bg-primary/5 border-primary' : 'bg-white hover:border-slate-300'">
                                        <div class="flex items-start gap-4">
                                            <div class="pt-1">
                                                <input type="checkbox" name="items[{{ $design->id }}][selected]" value="1" x-model="selected" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                                            </div>
                                            <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0 flex items-center justify-center">
                                                @if(!empty($design->image_paths))
                                                    <img src="{{ Str::startsWith($design->image_paths[0], 'http') ? $design->image_paths[0] : asset('storage/' . $design->image_paths[0]) }}" alt="{{ $design->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-slate-900 leading-tight font-heading">{{ $design->name }}</h4>
                                                <div class="text-xs text-slate-500 mt-1 uppercase tracking-wider">{{ implode(', ', $types) }}</div>
                                                
                                                <div x-show="selected" x-collapse class="mt-4 pt-4 border-t border-slate-200/60">
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                        <div>
                                                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Quantity</label>
                                                            <input type="number" name="items[{{ $design->id }}][qty]" value="1" min="1" class="w-full border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" :required="selected">
                                                        </div>
                                                        @foreach($types as $t)
                                                            @if(in_array($t, $sizedTypes))
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">{{ $t }} Size</label>
                                                                <select name="items[{{ $design->id }}][sizes][{{ $t }}]" class="w-full border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" :required="selected">
                                                                    <option value="">Select Size</option>
                                                                    @foreach(\App\Models\DesignCatalog::sizeChart() as $size)
                                                                        <option value="{{ $size }}">{{ $size }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 bg-slate-50 rounded-lg text-center text-slate-500 text-sm">
                                        You have no assigned designs yet. Please contact The Commission Apparel.
                                    </div>
                                @endforelse
                            </div>
                            
                            <div class="mt-8">
                                <button type="submit" class="btn btn-primary w-full py-4 text-sm font-bold uppercase tracking-widest shadow-md">
                                    Add To Draft
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Draft Orders & Submissions --}}
            <div class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 mb-4 flex items-center justify-between">
                        Draft Orders
                        <span class="bg-amber-100 text-amber-800 text-xs py-1 px-2 rounded-md">{{ $directOrders->where('status', 'Draft')->count() }}</span>
                    </h2>
                    
                    @if($directOrders->where('status', 'Draft')->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm text-slate-500">Your draft is empty.</p>
                        </div>
                    @else
                        <div class="space-y-3 mb-6 max-h-[300px] overflow-y-auto pr-2">
                            @foreach($directOrders->where('status', 'Draft') as $draft)
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="font-bold text-sm text-slate-900">{{ $draft->athlete_name }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase">{{ $draft->gender }}</div>
                                    </div>
                                    <ul class="text-xs text-slate-600 space-y-1">
                                        @foreach($draft->items_json as $item)
                                            <li>{{ $item['qty'] }}x {{ $item['name'] }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-slate-100 pt-5">
                            <form action="{{ route('coach.direct-order.finalize') }}" method="POST" x-data="{ confirming: false }">
                                @csrf
                                <button type="button" x-show="!confirming" @click="confirming = true" class="btn bg-green-600 hover:bg-green-700 text-white w-full py-3 text-xs font-bold uppercase tracking-widest shadow-sm">
                                    Submit Draft To Production
                                </button>
                                <div x-show="confirming" x-cloak class="bg-red-50 border border-red-200 p-4 rounded-xl">
                                    <p class="text-xs text-red-800 font-bold mb-3 text-center">Are you sure? This is a final submission and cannot be undone.</p>
                                    <div class="flex gap-2">
                                        <button type="button" @click="confirming = false" class="flex-1 py-2 bg-white border border-slate-300 text-slate-600 rounded-lg text-xs font-bold uppercase">Cancel</button>
                                        <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg text-xs font-bold uppercase shadow-sm">Confirm Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                @if($directOrderBatches->except('')->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 mb-4">Submitted Batches</h2>
                    <div class="space-y-3">
                        @foreach($directOrderBatches->except('') as $batchId => $batchOrders)
                            <div class="border border-slate-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold uppercase text-slate-500">{{ $batchOrders->first()->created_at->format('M d, Y') }}</span>
                                    <span class="bg-blue-100 text-blue-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded">Submitted</span>
                                </div>
                                <div class="text-sm font-bold text-slate-900 mb-3">{{ $batchOrders->count() }} Orders in Batch</div>
                                <a href="{{ route('coach.direct-order.export', $batchId) }}" class="text-xs font-bold text-primary hover:text-secondary uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download CSV
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ════ STORE OVERVIEW TAB ════ --}}
        <div x-show="activeCoachTab === 'overview'" x-cloak>
            @if(!$store)
EOT;

$content = str_replace($oldStart, $newStart, $content);

// Now handle the inner if/else logic replacement
// Replace the old activeCoachTab div start with nothing (since we already opened it in overview tab)
$oldInnerStart = <<<'EOT'
    @else
    @php
        $totalAthletes = $store->parentOrders->count();
        $isLocked = $store->status === 'submitted_to_admin';
    @endphp

    <div x-data="{ activeCoachTab: 'overview' }" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-2 inline-flex gap-2">
            <button
                type="button"
                @click="activeCoachTab = 'overview'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'overview' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Store Overview
            </button>
            <button
                type="button"
                @click="activeCoachTab = 'sales'"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors"
                :class="activeCoachTab === 'sales' ? 'bg-secondary text-white' : 'text-slate-600 hover:bg-[#a11825]'"
            >
                Sales
            </button>
        </div>

    <div x-show="activeCoachTab === 'overview'" class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-8">
EOT;

$newInnerStart = <<<'EOT'
    @else
    <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-8">
EOT;

$content = str_replace($oldInnerStart, $newInnerStart, $content);

// Finally, the end of the overview tab and the start of the sales tab
$oldSalesStart = <<<'EOT'
    </div>

    {{-- SALES TAB --}}
    <div x-show="activeCoachTab === 'sales'" x-cloak>
EOT;

$newSalesStart = <<<'EOT'
    </div>
    @endif
    </div>

    {{-- ════ SALES TAB ════ --}}
    @if($store && $store->status === 'approved')
    <div x-show="activeCoachTab === 'sales'" x-cloak>
EOT;

$content = str_replace($oldSalesStart, $newSalesStart, $content);

// Remove the very last @endif that closed the (!$store)
$oldEnd = <<<'EOT'
    </div>
    @endif
</div>
@endsection
EOT;

$newEnd = <<<'EOT'
    </div>
    @endif
    </div>
</div>
@endsection
EOT;

$content = str_replace($oldEnd, $newEnd, $content);

file_put_contents($file, $content);
echo "Dashboard updated.\n";
