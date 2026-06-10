<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TeamStore;

$stores = TeamStore::orderBy('created_at', 'desc')->get();
foreach ($stores as $store) {
    echo "ID: {$store->id}, Name: {$store->name}, Status: {$store->status}, Archived: " . ($store->is_archived ? 'Yes' : 'No') . ", Sort Order: {$store->sort_order}, Created: {$store->created_at}\n";
}
