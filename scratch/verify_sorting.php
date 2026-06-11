<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DesignCollection;
use App\Models\DesignCatalog;
use App\Models\LandingCollection;
use App\Models\Testimonial;
use App\Models\SizingChart;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    echo "========================================\n";
    echo "VERIFYING SORTING LOGIC ON CREATION\n";
    echo "========================================\n\n";

    // 1. Verify DesignCollection Sorting
    echo "1. Testing DesignCollection:\n";
    $col1 = DesignCollection::create(['name' => 'Test Col 1', 'sports' => []]);
    $col2 = DesignCollection::create(['name' => 'Test Col 2', 'sports' => []]);
    $col3 = DesignCollection::create(['name' => 'Test Col 3', 'sports' => []]);

    $col1->refresh(); $col2->refresh(); $col3->refresh();
    echo "   Col 3 (newest) Sort Order: {$col3->sort_order} (Expected: 1)\n";
    echo "   Col 2 Sort Order: {$col2->sort_order} (Expected: 2)\n";
    echo "   Col 1 (oldest) Sort Order: {$col1->sort_order} (Expected: 3)\n";
    if ($col3->sort_order == 1 && $col2->sort_order == 2 && $col1->sort_order == 3) {
        echo "   -> PASS\n\n";
    } else {
        echo "   -> FAIL\n\n";
    }

    // 2. Testing DesignCatalog (Unassigned)
    echo "2. Testing DesignCatalog (Unassigned):\n";
    $d1 = DesignCatalog::create(['name' => 'Test Design 1', 'types' => ['accessory'], 'category' => 'individual']);
    $d2 = DesignCatalog::create(['name' => 'Test Design 2', 'types' => ['accessory'], 'category' => 'individual']);
    $d3 = DesignCatalog::create(['name' => 'Test Design 3', 'types' => ['accessory'], 'category' => 'individual']);

    $d1->refresh(); $d2->refresh(); $d3->refresh();
    echo "   Design 3 (newest) Sort Order: {$d3->sort_order} (Expected: 1)\n";
    echo "   Design 2 Sort Order: {$d2->sort_order} (Expected: 2)\n";
    echo "   Design 1 (oldest) Sort Order: {$d1->sort_order} (Expected: 3)\n";
    if ($d3->sort_order == 1 && $d2->sort_order == 2 && $d1->sort_order == 3) {
        echo "   -> PASS\n\n";
    } else {
        echo "   -> FAIL\n\n";
    }

    // 3. Testing DesignCatalog (Within a Collection)
    echo "3. Testing DesignCatalog (Within Collection #{$col3->id}):\n";
    $dc1 = DesignCatalog::create(['name' => 'Coll Design 1', 'types' => ['accessory'], 'category' => 'individual', 'design_collection_id' => $col3->id]);
    $dc2 = DesignCatalog::create(['name' => 'Coll Design 2', 'types' => ['accessory'], 'category' => 'individual', 'design_collection_id' => $col3->id]);
    $dc3 = DesignCatalog::create(['name' => 'Coll Design 3', 'types' => ['accessory'], 'category' => 'individual', 'design_collection_id' => $col3->id]);

    $dc1->refresh(); $dc2->refresh(); $dc3->refresh();
    echo "   Coll Design 3 (newest) Sort Order: {$dc3->sort_order} (Expected: 1)\n";
    echo "   Coll Design 2 Sort Order: {$dc2->sort_order} (Expected: 2)\n";
    echo "   Coll Design 1 (oldest) Sort Order: {$dc1->sort_order} (Expected: 3)\n";
    if ($dc3->sort_order == 1 && $dc2->sort_order == 2 && $dc1->sort_order == 3) {
        echo "   -> PASS\n\n";
    } else {
        echo "   -> FAIL\n\n";
    }

    // 4. Testing LandingCollection
    echo "4. Testing LandingCollection:\n";
    $lc1 = LandingCollection::create(['tab_name' => 'Tab 1', 'title' => 'Title 1', 'description' => 'Desc 1']);
    $lc2 = LandingCollection::create(['tab_name' => 'Tab 2', 'title' => 'Title 2', 'description' => 'Desc 2']);
    $lc3 = LandingCollection::create(['tab_name' => 'Tab 3', 'title' => 'Title 3', 'description' => 'Desc 3']);

    $lc1->refresh(); $lc2->refresh(); $lc3->refresh();
    echo "   LC 3 (newest) Sort Order: {$lc3->sort_order} (Expected: 1)\n";
    echo "   LC 2 Sort Order: {$lc2->sort_order} (Expected: 2)\n";
    echo "   LC 1 (oldest) Sort Order: {$lc1->sort_order} (Expected: 3)\n";
    if ($lc3->sort_order == 1 && $lc2->sort_order == 2 && $lc1->sort_order == 3) {
        echo "   -> PASS\n\n";
    } else {
        echo "   -> FAIL\n\n";
    }

    // 5. Testing Testimonial
    echo "5. Testing Testimonial:\n";
    $t1 = Testimonial::create(['client_name' => 'Client 1', 'content' => 'Content 1']);
    $t2 = Testimonial::create(['client_name' => 'Client 2', 'content' => 'Content 2']);
    $t3 = Testimonial::create(['client_name' => 'Client 3', 'content' => 'Content 3']);

    $t1->refresh(); $t2->refresh(); $t3->refresh();
    echo "   Testimonial 3 (newest) Sort Order: {$t3->sort_order} (Expected: 1)\n";
    echo "   Testimonial 2 Sort Order: {$t2->sort_order} (Expected: 2)\n";
    echo "   Testimonial 1 (oldest) Sort Order: {$t1->sort_order} (Expected: 3)\n";
    if ($t3->sort_order == 1 && $t2->sort_order == 2 && $t1->sort_order == 3) {
        echo "   -> PASS\n\n";
    } else {
        echo "   -> FAIL\n\n";
    }

    // 6. Testing SizingChart
    echo "6. Testing SizingChart:\n";
    $sc1 = SizingChart::create(['title' => 'Chart 1', 'image_paths' => []]);
    $sc2 = SizingChart::create(['title' => 'Chart 2', 'image_paths' => []]);
    $sc3 = SizingChart::create(['title' => 'Chart 3', 'image_paths' => []]);

    $sc1->refresh(); $sc2->refresh(); $sc3->refresh();
    echo "   Chart 3 (newest) Sort Order: {$sc3->sort_order} (Expected: 1)\n";
    echo "   Chart 2 Sort Order: {$sc2->sort_order} (Expected: 2)\n";
    echo "   Chart 1 (oldest) Sort Order: {$sc1->sort_order} (Expected: 3)\n";
    if ($sc3->sort_order == 1 && $sc2->sort_order == 2 && $sc1->sort_order == 3) {
        echo "   -> PASS\n\n";
    } else {
        echo "   -> FAIL\n\n";
    }

    // Rollback so database is completely clean
    throw new \Exception("Rollback to keep database clean.");
});
