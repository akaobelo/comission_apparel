<?php
$html = file_get_contents('a:/comission_apparel/resources/views/coach/dashboard.blade.php');
$lines = explode("\n", $html);
$depth = 0;
foreach($lines as $i => $line) {
    $opens = substr_count($line, '<div');
    $closes = substr_count($line, '</div');
    $depth += ($opens - $closes);
    if (strpos($line, "activeCoachTab === 'sales'") !== false) {
        echo "Depth at Sales tab start: " . $depth . "\n";
    }
    if (strpos($line, "activeCoachTab === 'order_status'") !== false) {
        echo "Depth at Order Status tab start: " . $depth . "\n";
    }
}
echo "Final depth: $depth\n";
