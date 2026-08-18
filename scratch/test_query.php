<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = \App\Models\ParentOrder::where('batch_id', 'f9eb911d-c028-4f55-b7ca-138b733d5c6b')->get();
echo "Total orders: " . $orders->count() . "\n";
foreach ($orders as $order) {
    echo "ID: " . $order->id . ", team_store_id: " . ($order->team_store_id ?? 'null') . ", batch_id: " . $order->batch_id . ", is_archived: " . ($order->is_archived ? 'true' : 'false') . "\n";
}
