<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = \App\Models\DesignCatalog::count();
$unassigned = \App\Models\DesignCatalog::whereNull('design_collection_id')->count();
echo "Total designs: {$total}\n";
echo "Unassigned designs: {$unassigned}\n";
