<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Storage::fake('public');

DB::transaction(function() {
    $controller = new AdminController();

    // Create 1 fake image file
    $file1 = UploadedFile::fake()->image('chart1.png', 100, 100);
    $file2 = UploadedFile::fake()->image('chart2.png', 100, 100);

    // Mock request
    $request = new Request();
    $request->setMethod('POST');
    $request->merge([
        'title' => 'Test Sizing Chart Controller',
        'sort_order' => 1,
    ]);
    
    // Set files array under 'images' (simulate images[] upload)
    $request->files->set('images', [$file1, $file2]);

    echo "Invoking createSizingChart via Controller...\n";
    try {
        $response = $controller->createSizingChart($request);
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Redirect target: " . $response->headers->get('Location') . "\n";
        
        // Let's print out created sizing charts
        $charts = \App\Models\SizingChart::where('title', 'Test Sizing Chart Controller')->get();
        echo "Created charts count: " . $charts->count() . "\n";
        foreach ($charts as $c) {
            echo "Chart ID: {$c->id}, Title: {$c->title}, Sort Order: {$c->sort_order}, Image Paths: " . json_encode($c->image_paths) . "\n";
        }
    } catch (\Illuminate\Validation\ValidationException $ve) {
        echo "Validation error: " . json_encode($ve->errors()) . "\n";
    } catch (\Exception $e) {
        echo "General error: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }

    throw new \Exception("Rollback to keep database clean");
});
