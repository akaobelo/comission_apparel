<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DesignCollection;

$collections = DesignCollection::withCount('designs')->get();
foreach ($collections as $c) {
    echo 'Collection ' . $c->id . ' has ' . $c->designs_count . " items.\n";
}
