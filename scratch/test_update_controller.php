<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\AdminController;
use App\Models\SizingChart;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Storage::fake('public');

DB::transaction(function() {
    $controller = new AdminController();

    // 1. Create initial chart
    $chart = SizingChart::create([
        'title' => 'Initial Sizing Chart',
        'sort_order' => 1,
        'image_paths' => ['/storage/sizing_charts/old1.png', '/storage/sizing_charts/old2.png'],
        'is_active' => true,
    ]);
    
    echo "Initial chart ID: {$chart->id}\n";

    // 2. Prepare update request
    // We want to keep 'old1.png', remove 'old2.png', and upload a new image 'new1.png'
    $newFile = UploadedFile::fake()->image('new1.png', 100, 100);

    $request = new Request();
    $request->setMethod('POST'); // The browser sends POST with _method=PUT
    $request->merge([
        '_method' => 'PUT',
        'title' => 'Updated Sizing Chart Title',
        'sort_order' => 2,
        'existing_images' => ['/storage/sizing_charts/old1.png'],
        'remove_images' => ['/storage/sizing_charts/old2.png'],
    ]);
    $request->files->set('images', [$newFile]);

    echo "Invoking updateSizingChart via Controller...\n";
    try {
        $response = $controller->updateSizingChart($request, $chart);
        echo "Response status: " . $response->getStatusCode() . "\n";
        
        // Reload chart
        $chart->refresh();
        echo "Updated Chart - Title: {$chart->title}, Sort Order: {$chart->sort_order}\n";
        echo "Updated Chart - Image Paths: " . json_encode($chart->image_paths) . "\n";
    } catch (\Illuminate\Validation\ValidationException $ve) {
        echo "Validation error: " . json_encode($ve->errors()) . "\n";
    } catch (\Exception $e) {
        echo "General error: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }

    throw new \Exception("Rollback to keep database clean");
});
