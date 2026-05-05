<?php

$file = 'resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Add Archive button to Active Stores
$searchActiveStores = <<<'EOT'
                                    @if($store->order_deadline)
                                        <div class="text-[10px] font-bold text-{{ $store->order_deadline->isPast() ? 'red' : 'slate' }}-500 mt-0.5 uppercase tracking-wide">
                                            Deadline: {{ $store->order_deadline->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1.5 bg-white border border-secondary text-secondary text-xs font-bold rounded-lg hover:bg-secondary hover:text-white transition-colors flex-shrink-0">Edit</a>
                            </div>
EOT;

$replaceActiveStores = <<<'EOT'
                                    @if($store->order_deadline)
                                        <div class="text-[10px] font-bold text-{{ $store->order_deadline->isPast() ? 'red' : 'slate' }}-500 mt-0.5 uppercase tracking-wide">
                                            Deadline: {{ $store->order_deadline->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <form action="{{ route('admin.stores.archive', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this active store?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-500 text-xs font-bold rounded-lg hover:bg-slate-100 transition-colors">Archive</button>
                                    </form>
                                    <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1.5 bg-white border border-secondary text-secondary text-xs font-bold rounded-lg hover:bg-secondary hover:text-white transition-colors">Edit</a>
                                </div>
                            </div>
EOT;
$content = str_replace($searchActiveStores, $replaceActiveStores, $content);

// 2. Add Archive button to Finalized Master Orders
$searchFinalizedStores = <<<'EOT'
                                        <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wide rounded-lg transition-colors flex-shrink-0 text-center">Manage Store</a>
                                    </div>
                                </div>
                            </div>
EOT;

$replaceFinalizedStores = <<<'EOT'
                                        <form action="{{ route('admin.stores.archive', $store) }}" method="POST" class="w-full sm:w-auto text-center" onsubmit="return confirm('Archive this finalized master order? This removes it from the main view.')">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-2 bg-white border border-slate-300 text-slate-500 hover:bg-slate-50 text-xs font-bold uppercase tracking-wide rounded-lg transition-colors flex-shrink-0">Archive</button>
                                        </form>
                                        <a href="{{ route('admin.store.edit', $store) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wide rounded-lg transition-colors flex-shrink-0 text-center">Manage Store</a>
                                    </div>
                                </div>
                            </div>
EOT;
$content = str_replace($searchFinalizedStores, $replaceFinalizedStores, $content);


// 3. Add an Archived Stores section below Finalized Master Orders
$searchArchived = <<<'EOT'
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
EOT;

$replaceArchived = <<<'EOT'
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ═══ ARCHIVED STORES ═══ --}}
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mt-8">
                    <div class="p-6 border-b border-slate-200 bg-slate-100 flex items-center justify-between cursor-pointer hover:bg-slate-200 transition-colors" x-data="{ expanded: false }" @click="expanded = !expanded">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-500">Archived Stores & Orders</h2>
                            <p class="text-sm text-slate-400 mt-1">Past orders that have been archived for production auditing.</p>
                        </div>
                        <div class="flex items-center gap-4 text-slate-400">
                            <span class="text-sm font-bold">{{ $archivedStores->count() }} Stores</span>
                            <svg class="w-6 h-6 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-data="{ expanded: false }" x-show="$el.previousElementSibling.__x.getUnobservedData().expanded" class="divide-y divide-slate-100">
                        @if($archivedStores->isEmpty())
                            <div class="p-8 text-center text-slate-400 text-sm">No archived stores.</div>
                        @else
                            @foreach($archivedStores as $store)
                            <div class="p-4 flex items-center justify-between gap-4 bg-slate-50 opacity-75 hover:opacity-100 transition-opacity">
                                <div>
                                    <div class="font-bold text-sm text-slate-700">{{ $store->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $store->user->name }} · {{ $store->parentOrders->count() }} orders</div>
                                </div>
                                <a href="{{ route('admin.store.edit', $store) }}" class="px-3 py-1 bg-white border border-slate-300 text-slate-500 text-xs font-bold rounded hover:bg-slate-200 transition-colors flex-shrink-0">View</a>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>
EOT;
$content = str_replace($searchArchived, $replaceArchived, $content);

file_put_contents($file, $content);
echo "Admin Dashboard updated with archive functionality.\n";
