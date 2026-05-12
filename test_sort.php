<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$dc = \App\Models\DesignCatalog::latest()->get();
echo json_encode($dc->map(function($d) { return ['id' => $d->id, 'name' => strtolower($d->name)]; })->toArray());
