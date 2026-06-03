<?php
$html = file_get_contents('a:/comission_apparel/resources/views/coach/dashboard.blade.php');
$lines = explode("\n", $html);
$depth = 0;
foreach($lines as $i => $line) {
    if ($i >= 715 && $i <= 812) {
        $opens = substr_count($line, '<div');
        $closes = substr_count($line, '</div');
        $depth += ($opens - $closes);
        if ($i == 716) echo "Depth at Right Column start: " . $depth . "\n";
        if ($i == 810) echo "Depth after Branding & Artwork: " . $depth . "\n";
    }
}
