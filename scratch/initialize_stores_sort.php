<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TeamStore;

// Get all stores, newest first
$stores = TeamStore::orderBy('created_at', 'desc')->get();

$sort = 1;
foreach ($stores as $store) {
    $store->update(['sort_order' => $sort]);
    echo "Assigned sort order {$sort} to Store ID {$store->id} ({$store->name})\n";
    $sort++;
}

echo "Initialization complete.\n";
