<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TeamStore;
use App\Models\DesignCollection;

$dwStore = TeamStore::find(14);
$ohStore = TeamStore::find(11);
$collections = DesignCollection::all();

echo "DW Store cover_image_path: " . ($dwStore->cover_image_path ?? 'NULL') . "\n";
echo "OH Store cover_image_path: " . ($ohStore->cover_image_path ?? 'NULL') . "\n";

foreach ($collections as $col) {
    echo "Collection ID: {$col->id} | Name: {$col->name} | image_path: " . ($col->image_path ?? 'NULL') . "\n";
}
