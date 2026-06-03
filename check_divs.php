<?php
$html = file_get_contents('a:/comission_apparel/resources/views/coach/dashboard.blade.php');
$lines = explode("\n", $html);
$depth = 0;
foreach($lines as $i => $line) {
    $opens = substr_count($line, '<div');
    $closes = substr_count($line, '</div');
    $depth += ($opens - $closes);
    if ($depth < 0) {
        echo "Negative depth at line " . ($i+1) . "\n";
        $depth = 0;
    }
}
echo "Final depth: $depth\n";
