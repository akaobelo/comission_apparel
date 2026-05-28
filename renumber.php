<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DesignCollection;
use App\Models\DesignCatalog;

// Renumber all collections
$collections = DesignCollection::all();
foreach ($collections as $collection) {
    // Order by the current sort order so we respect the relative order they are already in
    $items = DesignCatalog::where('design_collection_id', $collection->id)
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
                
    $sort = 1;
    foreach ($items as $item) {
        $item->update(['sort_order' => $sort]);
        $sort++;
    }
}

$unassigned = DesignCatalog::whereNull('design_collection_id')
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
$sort = 1;
foreach ($unassigned as $item) {
    $item->update(['sort_order' => $sort]);
    $sort++;
}

echo "Renumbering complete.\n";
