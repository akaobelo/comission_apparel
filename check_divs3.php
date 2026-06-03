<?php
$html = file_get_contents('a:/comission_apparel/resources/views/coach/dashboard.blade.php');
$lines = explode("\n", $html);
$depth = 0;
foreach($lines as $i => $line) {
    $opens = substr_count($line, '<div');
    $closes = substr_count($line, '</div');
    $depth += ($opens - $closes);
    if ($i == 959) echo "Depth before Sales tab: " . $depth . "\n";
    if ($i == 1011) echo "Depth before Order Status tab: " . $depth . "\n";
    if ($i == 1195) echo "Depth at end of Order Status tab: " . $depth . "\n";
}
echo "Final depth: " . $depth . "\n";
