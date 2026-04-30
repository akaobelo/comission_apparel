<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$items = App\Models\StoreItem::whereNotNull('image_paths')->get();
foreach ($items as $item) {
    if (is_string($item->image_paths)) {
        $decoded = json_decode($item->image_paths, true);
        if (is_array($decoded)) {
            $item->image_paths = $decoded;
            $item->save();
            echo "Fixed item {$item->id}\n";
        }
    }
}
echo "Done.\n";
