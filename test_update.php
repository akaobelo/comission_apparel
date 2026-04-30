<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    App\Models\StoreItem::where('id', '>', 0)->update(['types' => ['uniform_top']]);
    echo "SUCCESS_TYPES\n";
} catch (\Throwable $e) {
    echo "ERROR_TYPES: " . $e->getMessage() . "\n";
}

try {
    App\Models\StoreItem::where('id', '>', 0)->update(['image_paths' => ['/test.png']]);
    echo "SUCCESS_IMAGE_PATHS\n";
} catch (\Throwable $e) {
    echo "ERROR_IMAGE_PATHS: " . $e->getMessage() . "\n";
}
