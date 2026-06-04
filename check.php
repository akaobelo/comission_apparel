<?php
$c = file_get_contents('resources/views/admin/dashboard.blade.php');
$start = strrpos(substr($c, 0, strpos($c, 'x-show="activeAdminTab === \'stores\'"')), '<div');
$end = strpos($c, 'x-show="activeAdminTab === \'coaches\'"');
$storesTab = substr($c, $start, $end - $start);

$lines = explode("\n", $storesTab);
$depth = 0;
foreach($lines as $i => $line) {
    $depth += substr_count($line, '<div');
    $depth -= substr_count($line, '</div');
    if ($depth === 0 && $i > 0 && $i < count($lines) - 2) {
        echo "Depth went to zero prematurely at line " . ($i + 1) . ":\n";
        echo $line . "\n";
        break;
    }
}
echo "Done.\n";
