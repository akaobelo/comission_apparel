<?php
$content = file_get_contents('resources/views/admin/dashboard.blade.php');

// Finalized Master Orders fixes
$content = str_replace(
    '@foreach($finalizedStoreBatches as $batchId => $orders)', 
    '@foreach($finalizedStoreBatches as $batchId => $batchData)', 
    $content
);
$content = str_replace(
    '$store = $orders->first()->teamStore;', 
    "\$orders = \$batchData['orders'];\n                                  \$financials = \$batchData['financials'];\n                                  \$store = \$orders->first()->teamStore;", 
    $content
);

// We need to add the financials box to the Finalized Master Orders card.
$masterGridSearch = '<div class="mt-3 grid grid-cols-3 gap-4">
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-2xl font-black text-primary">{{ $totalAthletes }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Athletes</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-2xl font-black text-slate-900">{{ $totalItems }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Items</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-xs font-black text-slate-900 mt-1 truncate">{{ $store->order_deadline ? $store->order_deadline->format(\'M d, Y\') : \'Not Set\' }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mt-1">Deadline</div>
                                            </div>
                                        </div>';

$masterGridReplace = '<div class="mt-3 grid grid-cols-2 md:grid-cols-5 gap-3">
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-lg font-black text-primary">{{ $totalAthletes }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Athletes</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-lg font-black text-slate-900">{{ $totalItems }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Items</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-lg font-black text-slate-900">${{ number_format($financials["total_sales"] ?? 0, 2) }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Sales</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-lg font-black text-secondary">${{ number_format($financials["total_wholesale"] ?? 0, 2) }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Due TCA</div>
                                            </div>
                                            <div class="bg-green-50 rounded-lg p-3 border border-green-200 text-center">
                                                <div class="text-lg font-black text-green-600">${{ number_format($financials["net_proceeds"] ?? 0, 2) }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-green-700">Net Proceeds</div>
                                            </div>
                                        </div>';

$content = str_replace($masterGridSearch, $masterGridReplace, $content);

// Finalized Direct Orders fixes
$content = str_replace(
    '@foreach($finalizedDirectOrderBatches as $batchId => $orders)', 
    '@foreach($finalizedDirectOrderBatches as $batchId => $batchData)', 
    $content
);
$content = str_replace(
    '$firstOrder = $orders->first();', 
    "\$orders = \$batchData['orders'];\n                                  \$financials = \$batchData['financials'];\n                                  \$firstOrder = \$orders->first();", 
    $content
);

// We need to add the financials box to the Finalized Direct Orders card.
$directGridSearch = '<div class="mt-3 grid grid-cols-2 gap-4">
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-2xl font-black text-primary">{{ $totalAthletes }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Athletes/Lines</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-2xl font-black text-slate-900">{{ $totalItems }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Items</div>
                                            </div>
                                        </div>';

$directGridReplace = '<div class="mt-3 grid grid-cols-3 gap-3">
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-lg font-black text-primary">{{ $totalItems }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Items</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-lg font-black text-slate-900">${{ number_format($financials["total_sales"] ?? 0, 2) }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Total</div>
                                            </div>
                                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                                <div class="text-lg font-black text-secondary">${{ number_format($financials["total_wholesale"] ?? 0, 2) }}</div>
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Due TCA</div>
                                            </div>
                                        </div>';

$content = str_replace($directGridSearch, $directGridReplace, $content);

file_put_contents('resources/views/admin/dashboard.blade.php', $content);
echo "Done\n";
