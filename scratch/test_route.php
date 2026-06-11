<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "route with null: " . route('admin.sizing-charts.update', null) . "\n";
} catch (\Exception $e) {
    echo "error with null: " . $e->getMessage() . "\n";
}

try {
    echo "route with empty string: " . route('admin.sizing-charts.update', '') . "\n";
} catch (\Exception $e) {
    echo "error with empty string: " . $e->getMessage() . "\n";
}

try {
    echo "route with object having null id: " . route('admin.sizing-charts.update', new \App\Models\SizingChart()) . "\n";
} catch (\Exception $e) {
    echo "error with object having null id: " . $e->getMessage() . "\n";
}
