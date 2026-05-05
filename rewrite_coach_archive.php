<?php

$file = 'resources/views/coach/dashboard.blade.php';
$content = file_get_contents($file);

$searchBatches = <<<'EOT'
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
EOT;

$replaceBatches = <<<'EOT'
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
                                <div class="flex gap-3 items-center">
                                    <a href="{{ route('coach.direct-order.export', $batchId) }}" class="text-xs font-bold text-primary hover:text-secondary uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download CSV
                                    </a>
                                    <form action="{{ route('coach.direct-order.archive', $batchId) }}" method="POST" onsubmit="return confirm('Archive this batch? You can still view it in the archived section.')">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-slate-500 hover:text-slate-700 uppercase tracking-wider flex items-center gap-1">
                                            Archive
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($archivedOrderBatches->except('')->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mt-6 opacity-75 hover:opacity-100 transition-opacity">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-500 mb-4 flex items-center justify-between">
                        Archived Batches
                        <span class="bg-slate-100 text-slate-600 text-xs py-1 px-2 rounded-md">{{ $archivedOrderBatches->except('')->count() }}</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($archivedOrderBatches->except('') as $batchId => $batchOrders)
                            <div class="border border-slate-200 bg-slate-50 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold uppercase text-slate-500">{{ $batchOrders->first()->created_at->format('M d, Y') }}</span>
                                    <span class="bg-slate-200 text-slate-600 text-[10px] font-bold uppercase px-2 py-0.5 rounded">Archived</span>
                                </div>
                                <div class="text-sm font-bold text-slate-700 mb-3">{{ $batchOrders->count() }} Orders in Batch</div>
                                <a href="{{ route('coach.direct-order.export', $batchId) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download CSV
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
EOT;

$content = str_replace($searchBatches, $replaceBatches, $content);

file_put_contents($file, $content);
echo "Coach dashboard updated.\n";
