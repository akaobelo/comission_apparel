<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$item = App\Models\StoreItem::first();
if ($item) {
    App\Models\StoreItem::where('id', $item->id)->update(['image_paths' => ['/test.png']]);
    $item->refresh();
    var_dump($item->getRawOriginal('image_paths'));
    var_dump($item->image_paths);
}
