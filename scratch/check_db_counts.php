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
    echo "Checking {$modelClass}...\n";
    try {
        $count = $modelClass::count();
        $chunkSize = 100;
        for ($i = 0; $i < $count; $i += $chunkSize) {
            $records = $modelClass::skip($i)->take($chunkSize)->get();
            foreach ($records as $rec) {
                foreach ($rec->toArray() as $k => $v) {
                    $len = strlen(serialize($v));
                    if ($len > 100000) {
                        echo "  [{$modelClass} ID: {$rec->id}] Field '{$k}' is huge! Size: " . ($len / 1024 / 1024) . " MB\n";
                    }
                }
            }
        }
    } catch (\Exception $e) {
        echo "  Error checking {$modelClass}: " . $e->getMessage() . "\n";
    }
}
echo "Done checking all.\n";
