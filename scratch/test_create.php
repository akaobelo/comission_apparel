<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SizingChart;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    $controller = new AdminController();
    
    // Test creation validation and logic
    $req = Request::create('/admin/sizing-charts', 'POST', [
        'title' => 'Test Sizing Chart',
        'sort_order' => 5,
    ]);
    
    // We mock the files parameter since we can't upload a real file easily in CLI request creation
    // But we want to see if the database insert works
    echo "Creating sizing chart mock...\n";
    try {
        $chart = SizingChart::create([
            'title' => 'Test Sizing Chart 2',
            'sort_order' => 5,
            'image_paths' => ['/storage/sizing_charts/test.png'],
            'is_active' => true,
        ]);
        echo "Successfully created chart: ID={$chart->id}, Title={$chart->title}, Sort={$chart->sort_order}\n";
    } catch (\Exception $e) {
        echo "Error creating chart: " . $e->getMessage() . "\n";
    }
    
    throw new \Exception("Rollback");
});
