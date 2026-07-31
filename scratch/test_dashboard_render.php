<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Register the web middleware error bag
view()->share('errors', new \Illuminate\Support\ViewErrorBag);

$admin = \App\Models\User::where('role', 'admin')->first();
if (!$admin) {
    echo "No admin user found!\n";
    exit(1);
}

auth()->login($admin);

echo "Attempting to call AdminController@dashboard...\n";
try {
    $request = \Illuminate\Http\Request::create('/admin/dashboard', 'GET');
    // Set request session
    $request->setLaravelSession(app('session')->driver());
    
    $controller = app(\App\Http\Controllers\AdminController::class);
    $response = $controller->dashboard($request);
    
    echo "Controller returned response of type: " . get_class($response) . "\n";
    if ($response instanceof \Illuminate\View\View) {
        echo "Rendering view...\n";
        $html = $response->render();
        echo "View rendered successfully! Length: " . strlen($html) . "\n";
    }
} catch (\Throwable $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
