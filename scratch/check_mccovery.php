<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ParentOrder;
use App\Models\TeamStore;

echo "Archived Stores count: " . TeamStore::where('is_archived', true)->count() . "\n";
echo "Archived Orders count: " . ParentOrder::where('is_archived', true)->count() . "\n";

// Let's get all distinct batch IDs for archived orders:
$batches = ParentOrder::where('is_archived', true)
    ->whereNotNull('batch_id')
    ->select('batch_id')
    ->groupBy('batch_id')
    ->get();

echo "Total distinct archived batches in DB: " . $batches->count() . "\n";
foreach ($batches as $index => $b) {
    // count orders in this batch
    $totalOrdersInBatch = ParentOrder::where('batch_id', $b->batch_id)->count();
    $archivedOrdersInBatch = ParentOrder::where('batch_id', $b->batch_id)->where('is_archived', true)->count();
    $firstOrder = ParentOrder::where('batch_id', $b->batch_id)->first();
    $coachName = $firstOrder && $firstOrder->user ? $firstOrder->user->name : 'Unknown';
    echo "[$index] Batch: {$b->batch_id} | Coach: $coachName | Total Orders: $totalOrdersInBatch | Archived Orders: $archivedOrdersInBatch\n";
}
