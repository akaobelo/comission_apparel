<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TeamStore;

$stores = TeamStore::all();
foreach ($stores as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Status: {$s->status} | Deadline: " . ($s->order_deadline ? $s->order_deadline->toIso8601String() : 'N/A') . " | Pricing Approved: " . ($s->pricing_approved ? 'Yes' : 'No') . "\n";
}
