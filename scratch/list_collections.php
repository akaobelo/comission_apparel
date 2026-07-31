<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$models = [
    \App\Models\User::class,
    \App\Models\TeamStore::class,
    \App\Models\ParentOrder::class,
    \App\Models\DesignCatalog::class,
    \App\Models\LandingCollection::class,
    \App\Models\QuoteRequest::class,
    \App\Models\StoreItem::class,
    \App\Models\DesignCollection::class,
    \App\Models\SiteSetting::class,
];

foreach ($models as $modelClass) {
    echo "Searching {$modelClass} for 'legacy'...\n";
    try {
        $records = $modelClass::all();
        foreach ($records as $rec) {
            $json = json_encode($rec->toArray());
            if (stripos($json, 'legacy') !== false) {
                echo "  Found in ID {$rec->id}: {$json}\n";
            }
        }
    } catch (\Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
