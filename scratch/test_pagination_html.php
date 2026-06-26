<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ParentOrder;
use Illuminate\Pagination\LengthAwarePaginator;

$paginator = new LengthAwarePaginator(
    collect([1, 2]), // 2 items on this page
    12,             // 12 total items
    10,             // 10 per page
    2,              // current page is 2
    ['path' => '/admin', 'pageName' => 'archive_batch_page']
);

echo "Rended HTML:\n";
echo $paginator->links() . "\n";
