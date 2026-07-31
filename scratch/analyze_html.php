<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

view()->share('errors', new \Illuminate\Support\ViewErrorBag);

$admin = \App\Models\User::where('role', 'admin')->first();
auth()->login($admin);

$request = \Illuminate\Http\Request::create('/admin/dashboard', 'GET');
$request->setLaravelSession(app('session')->driver());

$controller = app(\App\Http\Controllers\AdminController::class);
$response = $controller->dashboard($request);

if ($response instanceof \Illuminate\View\View) {
    $html = $response->render();
    file_put_contents('scratch/rendered.html', $html);
    echo "Saved html. Length: " . strlen($html) . "\n";
    
    // Analyze where the size is coming from
    // Let's count how many times certain tags appear
    echo "Number of forms: " . substr_count($html, '<form') . "\n";
    echo "Number of selects: " . substr_count($html, '<select') . "\n";
    echo "Number of options: " . substr_count($html, '<option') . "\n";
    echo "Number of checkboxes: " . substr_count($html, 'type="checkbox"') . "\n";
    echo "Number of divs: " . substr_count($html, '<div') . "\n";
}
