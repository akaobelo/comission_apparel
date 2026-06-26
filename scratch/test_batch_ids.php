<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ParentOrder;
use Illuminate\Http\Request;

// Let's mock a request with search = 'Michael'
$search = 'Michael';

$archivedBatchQuery = ParentOrder::where('is_archived', true)
    ->whereNotNull('batch_id')
    ->select('batch_id')
    ->groupBy('batch_id');

$archivedBatchQuery->where(function($q) use ($search) {
    $q->where('batch_id', 'like', "%{$search}%")
      ->orWhereHas('user', function($q2) use ($search) {
          $q2->where('first_name', 'like', "%{$search}%")
             ->orWhere('last_name', 'like', "%{$search}%")
             ->orWhere('organization', 'like', "%{$search}%");
      })
      ->orWhereHas('teamStore', function($q2) use ($search) {
          $q2->where('name', 'like', "%{$search}%");
      });
});

$archivedBatchIds = $archivedBatchQuery->latest('batch_id')->paginate(10, ['*'], 'archive_batch_page');

echo "Total: " . $archivedBatchIds->total() . "\n";
echo "Count on this page: " . $archivedBatchIds->count() . "\n";
echo "Plucked IDs:\n";
print_r($archivedBatchIds->pluck('batch_id')->toArray());
