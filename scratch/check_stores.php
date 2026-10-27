<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TeamStore;

$updated = TeamStore::where('status', 'pending')->update(['payment_mode' => 'in_house']);
echo "Pending stores updated to in_house: {$updated}\n";

$stores = TeamStore::where('status', 'pending')->get();
foreach ($stores as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Status: {$s->status}, Mode: {$s->payment_mode}\n";
}
