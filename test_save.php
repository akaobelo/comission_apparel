<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DesignCatalog;
use Illuminate\Http\Request;

$c = App\Models\DesignCollection::first();
if ($c) {
    $items = DesignCatalog::where('design_collection_id', $c->id)->get();
    echo "Before save:\n";
    foreach ($items as $i) {
        echo "ID: {$i->id}, Sort: {$i->sort_order}\n";
    }
    
    $request = Request::create('/test', 'POST', [
        'designs' => [
            ['id' => $items[0]->id, 'sort_order' => 999],
        ]
    ]);
    
    foreach ($request->designs as $designData) {
        $design = DesignCatalog::find($designData['id']);
        if ($design && $design->design_collection_id == $c->id) {
            $design->update(['sort_order' => $designData['sort_order']]);
        }
    }
    
    $items = DesignCatalog::where('design_collection_id', $c->id)->get();
    echo "After save:\n";
    foreach ($items as $i) {
        echo "ID: {$i->id}, Sort: {$i->sort_order}\n";
    }
}
