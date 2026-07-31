<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$totalSize = 0;
foreach (\App\Models\DesignCatalog::all() as $d) {
    $size = strlen(json_encode($d->toArray()));
    $totalSize += $size;
    if ($size > 10000) {
        echo "ID: {$d->id}, Name: {$d->name}, Size: " . round($size / 1024, 2) . " KB\n";
    }
}
echo "Total JSON size of all DesignCatalog rows: " . round($totalSize / 1024 / 1024, 2) . " MB\n";
