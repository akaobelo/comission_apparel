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

$startMemory = memory_get_usage();

$controller = app(\App\Http\Controllers\AdminController::class);
$response = $controller->dashboard($request);

if ($response instanceof \Illuminate\View\View) {
    $html = $response->render();
    $peakMemory = memory_get_peak_usage();
    echo "Peak memory usage: " . round($peakMemory / 1024 / 1024, 2) . " MB\n";
    echo "Rendered HTML length: " . strlen($html) . " bytes\n";
}
