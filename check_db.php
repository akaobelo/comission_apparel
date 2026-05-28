<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DesignCollection;
use App\Models\DesignCatalog;

$items = DesignCatalog::whereNotNull('design_collection_id')->orderBy('sort_order', 'desc')->take(20)->get();
foreach ($items as $item) {
    echo "Collection: {$item->design_collection_id}, Sort: {$item->sort_order}, ID: {$item->id}\n";
}
