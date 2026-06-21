<?php
$html = file_get_contents('a:/comission_apparel/resources/views/admin/dashboard.blade.php');
$lines = explode("\n", $html);
$depth = 0;
foreach($lines as $i => $line) {
    // strip comments
    $line = preg_replace('/\{\{--.*?--\}\}/', '', $line);
    $opens = preg_match_all('/<div[ >]/i', $line);
    $closes = substr_count($line, '</div');
    $depth += ($opens - $closes);
    if ($depth < 0) {
        echo "Negative depth at line " . ($i+1) . " (Depth: $depth)\n";
        $depth = 0;
    }
}
echo "Final depth: $depth\n";
